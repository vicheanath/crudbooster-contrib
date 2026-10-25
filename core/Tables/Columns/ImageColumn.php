<?php

namespace CrudBooster\Tables\Columns;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * A thumbnail of an image path or URL. Paths are resolved on the disk (the FileUpload default).
 */
class ImageColumn extends Column
{
    protected string $view = 'cb::columns.image';

    protected ?string $disk = null;

    protected int $size = 40;

    protected bool $circular = false;

    public function disk(string $disk): static
    {
        $this->disk = $disk;

        return $this;
    }

    public function size(int $pixels): static
    {
        $this->size = $pixels;

        return $this;
    }

    public function circular(bool $circular = true): static
    {
        $this->circular = $circular;

        return $this;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function isCircular(): bool
    {
        return $this->circular;
    }

    public function getImageUrl(mixed $state): ?string
    {
        if (! is_string($state) || $state === '') {
            return null;
        }
        if (Str::startsWith($state, ['http://', 'https://', '//', 'data:image/'])) {
            return $state;
        }

        return Storage::disk($this->disk ?? config('crudbooster.PANEL.disk', 'public'))->url($state);
    }
}
