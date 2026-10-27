<?php

namespace crocodicstudio\crudbooster\forms\handlers;

use crocodicstudio\crudbooster\forms\FormContext;
use Illuminate\Support\Facades\DB;

/**
 * Replaces the row's pivot rows in 'relationship_table' with the selected ids.
 * The pivot's columns are found with BaseFieldHandler::foreignKey().
 *
 * @deprecated 7.2 Select::relationship()->multiple() and CheckboxList::relationship() sync the pivot table.
 *     See docs/en/deprecations.md.
 */
trait SyncsPivotTable
{
    protected function syncPivot(array $field, mixed $selectedIds, FormContext $context): void
    {
        $pivot = $field['relationship_table'];
        $relatedTable = explode(',', $field['datatable'] ?? '')[0];
        $relatedKey = $this->foreignKey($relatedTable, $pivot);
        $ownerKey = $this->foreignKey($context->table, $pivot);

        DB::table($pivot)->where($ownerKey, $context->recordId)->delete();

        foreach ($selectedIds ?: [] as $selectedId) {
            DB::table($pivot)->insert([
                $ownerKey => $context->recordId,
                $relatedKey => $selectedId,
            ]);
        }
    }
}
