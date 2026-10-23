<?php

namespace crocodicstudio\crudbooster\files;

use crocodicstudio\crudbooster\contracts\Files\FileUploaderInterface;
use crocodicstudio\crudbooster\contracts\Security\AuthenticationServiceInterface;
use crocodicstudio\crudbooster\helpers\CBImage as Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Stores uploads on the default disk under uploads/{user id}/{Y-m}. Images are scaled down to
 * 1300px wide (or to the requested size) and get a 350x350 thumbnail in uploads_thumbnail/{Y-m}.
 */
class FileUploader implements FileUploaderInterface
{
    public function __construct(private readonly AuthenticationServiceInterface $auth)
    {
    }

    public function upload(string $name, bool $encrypt = false, $resizeWidth = null, $resizeHeight = null, $userId = null): ?string
    {
        // The current request, read on each call: field handlers keep this service across requests.
        $request = app(Request::class);
        if (! $request->hasFile($name)) {
            return null;
        }

        $file = $request->file($name);
        $ext = $file->getClientOriginalExtension();
        $directory = $this->directory($userId);
        Storage::makeDirectory($directory);

        $filename = $encrypt
            ? md5(str_random(5)).'.'.$ext
            : str_slug(str_slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)), '_').'.'.$ext;

        if (! Storage::putFileAs($directory, $file, $filename)) {
            return null;
        }

        $this->resizeImage($directory.'/'.$filename, $resizeWidth, $resizeHeight);

        return $directory.'/'.$filename;
    }

    public function uploadBase64(string $data, $userId = null): ?string
    {
        $contents = base64_decode($data);
        $mimeType = finfo_buffer(finfo_open(), $contents, FILEINFO_MIME_TYPE);
        $ext = explode('/', (string) $mimeType)[1] ?? null;

        if (! $ext) {
            return null;
        }

        $directory = $this->directory($userId);
        Storage::makeDirectory($directory);
        $path = $directory.'/'.md5(str_random(5)).'.'.$ext;

        if (! Storage::put($path, $contents)) {
            return null;
        }

        $this->resizeImage($path);

        return $path;
    }

    protected function directory($userId): string
    {
        return 'uploads/'.($userId ?: ($this->auth->currentUserId() ?: 0)).'/'.date('Y-m');
    }

    protected function resizeImage(string $path, $width = null, $height = null, int $quality = 100, int $thumbnailQuality = 75): void
    {
        $filename = basename($path);
        $directory = trim(str_replace($filename, '', $path), '/');
        $thumbnailDirectory = 'uploads_thumbnail/'.date('Y-m');
        Storage::makeDirectory($thumbnailDirectory);

        $imageExtensions = explode(',', config('crudbooster.IMAGE_EXTENSIONS', 'jpg,png,gif,bmp'));
        if (! in_array(strtolower(pathinfo($filename, PATHINFO_EXTENSION)), $imageExtensions)) {
            return;
        }

        $fullPath = Storage::path($directory.'/'.$filename);
        $img = Image::read($fullPath);
        if ($width && $height) {
            $img->cover((int) $width, (int) $height);
        } elseif ($width) {
            $img->scale(width: (int) $width);
        } elseif ($height) {
            $img->scale(height: (int) $height);
        } elseif ($img->width() > 1300) {
            $img->scale(width: 1300);
        }
        $img->save($fullPath, quality: $quality);

        $img = Image::read($fullPath);
        $img->cover(350, 350);
        $img->save(Storage::path($thumbnailDirectory.'/'.$filename), quality: $thumbnailQuality);
    }
}
