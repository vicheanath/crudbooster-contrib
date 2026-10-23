<?php

namespace crocodicstudio\crudbooster\forms\handlers;

use crocodicstudio\crudbooster\contracts\Files\FileUploaderInterface;
use crocodicstudio\crudbooster\contracts\Schema\SchemaInspectorInterface;
use crocodicstudio\crudbooster\forms\FormContext;

/**
 * Stores the uploaded file (resized by 'resize_width'/'resize_height', renamed when 'encrypt'
 * or 'upload_encrypt' is set). Without a new file the current one, posted as "_{name}", is kept.
 */
class UploadFieldHandler extends BaseFieldHandler
{
    protected array $types = ['upload'];

    protected FileUploaderInterface $uploader;

    public function __construct(?FileUploaderInterface $uploader = null, ?SchemaInspectorInterface $schema = null)
    {
        parent::__construct($schema);
        $this->uploader = $uploader ?? app(FileUploaderInterface::class);
    }

    protected function skipsEmptyNullableColumn(): bool
    {
        return false;
    }

    protected function transform(array $field, mixed $value, array &$payload, FormContext $context): void
    {
        $name = $field['name'];
        $encrypt = ! empty($field['encrypt']) || ! empty($field['upload_encrypt']);

        $payload[$name] = $this->uploader->upload($name, $encrypt, $field['resize_width'] ?? null, $field['resize_height'] ?? null)
            ?: request('_'.$name);
    }
}
