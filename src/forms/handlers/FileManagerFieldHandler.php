<?php

namespace crocodicstudio\crudbooster\forms\handlers;

use crocodicstudio\crudbooster\forms\FormContext;

/**
 * Turns a laravel-filemanager URL into the "uploads/..." path CRUDBooster serves files from.
 *
 * @deprecated 7.2 Use CrudBooster\Forms\Fields\FileUpload. See docs/en/deprecations.md.
 */
class FileManagerFieldHandler extends BaseFieldHandler
{
    protected array $types = ['filemanager'];

    protected function transform(array $field, mixed $value, array &$payload, FormContext $context): void
    {
        $name = $field['name'];
        $prefix = '/'.config('lfm.prefix').'/'.config('lfm.files_folder_name').'/';
        $payload[$name] = 'uploads/'.str_replace($prefix, '', (string) ($payload[$name] ?? ''));
    }
}
