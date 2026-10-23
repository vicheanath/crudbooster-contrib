<?php

namespace crocodicstudio\crudbooster\forms\handlers;

use crocodicstudio\crudbooster\forms\FormContext;
use Illuminate\Support\Facades\DB;

/**
 * A select2 field. With 'relationship_table' it is a many-to-many field saved in that pivot
 * table after the row is saved; with 'datatable_orig' ("table|column|key") the selected ids
 * are also written comma-separated to that column on edit.
 */
class Select2FieldHandler extends SelectFieldHandler
{
    use SyncsPivotTable;

    protected array $types = ['select2'];

    public function beforeSave(array $field, mixed $value, array &$payload, FormContext $context): void
    {
        if (empty($field['relationship_table'])) {
            parent::beforeSave($field, $value, $payload, $context);
        }
    }

    public function afterSave(array $field, mixed $value, FormContext $context): void
    {
        if (empty($field['relationship_table'])) {
            return;
        }

        if (! $context->isEdit() || empty($field['datatable_orig'])) {
            $this->syncPivot($field, $value, $context);

            return;
        }

        $params = explode('|', $field['datatable_orig']);
        DB::table($params[0])->where($params[2] ?? 'id', $context->recordId)->update([$params[1] => implode(',', (array) $value)]);
    }
}
