<?php

namespace crocodicstudio\crudbooster\forms\handlers;

use crocodicstudio\crudbooster\forms\FormContext;
use Illuminate\Support\Facades\DB;

/**
 * Checked values are saved ";"-separated. With 'datatable' ("table,column") the selected ids
 * are saved as that column's values; with 'relationship_table' they go to a pivot table instead.
 *
 * @deprecated 7.2 Use CrudBooster\Forms\Fields\CheckboxList, with relationship() for a pivot table. See
 *     docs/en/deprecations.md.
 */
class CheckboxFieldHandler extends BaseFieldHandler
{
    use SyncsPivotTable;

    protected array $types = ['checkbox'];

    public function beforeSave(array $field, mixed $value, array &$payload, FormContext $context): void
    {
        if (empty($field['relationship_table'])) {
            parent::beforeSave($field, $value, $payload, $context);
        }
    }

    public function afterSave(array $field, mixed $value, FormContext $context): void
    {
        if (! empty($field['relationship_table'])) {
            $this->syncPivot($field, $value, $context);
        }
    }

    protected function transform(array $field, mixed $value, array &$payload, FormContext $context): void
    {
        if (! is_array($value)) {
            return;
        }

        if (! empty($field['datatable'])) {
            [$table, $column] = array_pad(explode(',', $field['datatable']), 2, null);
            $value = DB::table($table)->whereIn($this->schema->primaryKey($table), $value)->pluck($column)->toArray();
        }

        $payload[$field['name']] = implode(';', $value);
    }
}
