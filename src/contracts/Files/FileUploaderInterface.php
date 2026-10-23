<?php

namespace crocodicstudio\crudbooster\contracts\Files;

interface FileUploaderInterface
{
    /**
     * Stores the request file `$name` under uploads/{user}/{Y-m} and returns its path,
     * or null when no file was sent. Images are resized and get a thumbnail.
     */
    public function upload(string $name, bool $encrypt = false, $resizeWidth = null, $resizeHeight = null, $userId = null): ?string;

    /**
     * Stores base64 encoded file data and returns its path, or null when its type is unknown.
     */
    public function uploadBase64(string $data, $userId = null): ?string;
}
