<?php

namespace CrudBooster\Forms\Fields;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Stores the uploaded file on a disk and saves its path. On edit, leaving the input empty keeps
 * the current file, and "Remove" clears it. Replaced files are left on the disk. Without image()
 * or acceptedFileTypes(), the extensions in crudbooster.UPLOAD_TYPES are accepted.
 *
 *     FileUpload::make('photo')->image()->directory('products')->maxSize(2048)
 */
class FileUpload extends Field
{
    protected string $view = 'cb::fields.file-upload';

    protected ?string $component = 'FileUpload';

    protected ?string $disk = null;

    protected string $directory = 'uploads';

    protected bool $image = false;

    protected array $acceptedFileTypes = [];

    protected ?int $maxSize = null;

    public function disk(string $disk): static
    {
        $this->disk = $disk;

        return $this;
    }

    public function directory(string $directory): static
    {
        $this->directory = trim($directory, '/');

        return $this;
    }

    public function image(bool $image = true): static
    {
        $this->image = $image;

        return $this;
    }

    /**
     * File extensions, e.g. ['pdf', 'docx'].
     */
    public function acceptedFileTypes(array $extensions): static
    {
        $this->acceptedFileTypes = $extensions;

        return $this;
    }

    /**
     * In kilobytes.
     */
    public function maxSize(int $kilobytes): static
    {
        $this->maxSize = $kilobytes;

        return $this;
    }

    public function getDisk(): string
    {
        return $this->disk ?? config('crudbooster.PANEL.disk', 'public');
    }

    public function isImage(): bool
    {
        return $this->image;
    }

    public function getAccept(): ?string
    {
        if ($this->acceptedFileTypes) {
            return implode(',', array_map(fn ($extension) => '.'.ltrim($extension, '.'), $this->acceptedFileTypes));
        }

        return $this->image ? 'image/*' : null;
    }

    public function getComponentProps(): array
    {
        $path = $this->getState();

        return [
            'value' => '',
            'accept' => $this->getAccept(),
            'image' => $this->image,
            'fileUrl' => $this->getFileUrl($path),
            'fileName' => $path ? basename($path) : null,
            'removeName' => $this->getRemoveInputName(),
            'maxSize' => $this->maxSize,
        ] + parent::getComponentProps();
    }

    public function getRemoveInputName(): string
    {
        return $this->name.'__remove';
    }

    public function getFileUrl(?string $path): ?string
    {
        return $path ? Storage::disk($this->getDisk())->url($path) : null;
    }

    public function isRequired(): bool
    {
        // A file is already stored; an empty input keeps it.
        if ($this->record && $this->record->getAttribute($this->name)) {
            return false;
        }

        return parent::isRequired();
    }

    public function getState(): mixed
    {
        return $this->record?->getAttribute($this->name);
    }

    public function getValueFromRequest(Request $request): mixed
    {
        if ($file = $request->file($this->name)) {
            return $file;
        }

        return $request->boolean($this->getRemoveInputName()) ? false : null;
    }

    /**
     * The file must be an image, or have one of the accepted extensions (crudbooster.UPLOAD_TYPES
     * by default), both by its name and by its detected content type.
     */
    protected function getTypeRules(): array
    {
        $rules = ['file'];
        if ($this->image) {
            $rules[] = 'image';
        }

        $extensions = $this->acceptedFileTypes
            ?: ($this->image ? [] : array_filter(array_map('trim', explode(',', (string) config('crudbooster.UPLOAD_TYPES')))));
        if ($extensions) {
            $rules[] = 'mimes:'.implode(',', $extensions);
            $rules[] = 'extensions:'.implode(',', $extensions);
        }
        if ($this->maxSize) {
            $rules[] = 'max:'.$this->maxSize;
        }

        return $rules;
    }

    public function shouldSave(mixed $value): bool
    {
        // null: nothing uploaded and nothing removed.
        return $value !== null;
    }

    public function dehydrate(mixed $value): mixed
    {
        if (! $value instanceof UploadedFile) {
            return null;
        }

        // A random name with the extension of the detected content type, never the client's.
        $path = $value->store($this->directory, ['disk' => $this->getDisk()]);

        return parent::dehydrate($path);
    }
}
