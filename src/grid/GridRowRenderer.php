<?php

namespace crocodicstudio\crudbooster\grid;

use crocodicstudio\crudbooster\controllers\CBController;

/**
 * Renders the cells of the index table: the bulk checkbox, numbering, column values
 * (images, downloads, str_limit, nl2br, callbacks) and the row action buttons.
 * Each cell goes through the module's hook_row_index().
 */
class GridRowRenderer
{
    public function render(CBController $controller, iterable $rows, array $columns, ?array $addaction, int $limit): array
    {
        $tablePK = $controller->primary_key;
        $table = $controller->table;
        $page = request('page') ?: 1;
        $number = ($page - 1) * $limit + 1;
        $html = [];

        foreach ($rows as $row) {
            $cells = [];

            if ($controller->button_bulk_action) {
                $cells[] = "<input type='checkbox' class='checkbox' name='checkbox[]' value='".$row->{$tablePK}."'/>";
            }

            if ($controller->show_numbering) {
                $cells[] = $number.'. ';
                $number++;
            }

            foreach ($columns as $column) {
                if (($column['visible'] ?? null) === false) {
                    continue;
                }
                $cells[] = $this->cell($column, $row, $table, $controller->title_field);
            }

            if ($controller->button_table_action) {
                $button_action_style = $controller->button_action_style;
                // parent_field reaches the view through the data cbLoader() shares.
                $cells[] = "<div class='button_action' style='text-align:right'>".view('crudbooster::components.action', compact('addaction', 'row', 'button_action_style'))->render().'</div>';
            }

            foreach ($cells as $i => $value) {
                $controller->hook_row_index($i, $value);
                $cells[$i] = $value;
            }

            $html[] = $cells;
        }

        return $html;
    }

    protected function cell(array $column, object $row, string $table, $titleField)
    {
        $value = empty($column['field']) ? null : ($row->{$column['field']} ?? null);
        $title = $titleField ? ($row->{$titleField} ?? null) : null;
        $label = $column['label'] ?? null;

        if (isset($column['image'])) {
            $pic = $value == '' ? asset('vendor/crudbooster/avatar.jpg') : ((strpos($value, 'http://') !== false) ? $value : asset($value));
            $value = "<a data-lightbox='roadtrip' rel='group_{".$table."}' title='$label: $title' href='".$pic."'><img width='40px' height='40px' src='".$pic."'/></a>";
        }

        if (! empty($column['download'])) {
            $url = (strpos((string) $value, 'http://') !== false) ? $value : asset($value).'?download=1';
            $value = $value
                ? "<a class='btn btn-xs btn-primary' href='$url' target='_blank' title='Download File'><i class='fa fa-download'></i> Download</a>"
                : ' - ';
        }

        if (! empty($column['str_limit'])) {
            $value = str_limit(trim(strip_tags((string) $value)), $column['str_limit']);
        }

        if (! empty($column['nl2br'])) {
            $value = nl2br((string) $value);
        }

        if (! empty($column['callback_php'])) {
            $callback = $column['callback_php'];
            foreach ($row as $k => $v) {
                $callback = str_replace('['.$k.']', (string) $v, $callback);
            }
            @eval('$value = '.$callback.';');
        }

        if (isset($column['callback'])) {
            $value = call_user_func($column['callback'], $row);
        }

        // Serialized [['label' => ...], ...] values (e.g. from old checkbox fields) show their labels.
        $unserialized = is_string($value) ? @unserialize($value) : false;
        if (is_array($unserialized) && $unserialized) {
            $labels = [];
            foreach ($unserialized as $item) {
                if (! empty($item['label'])) {
                    $labels[] = $item['label'];
                }
            }
            if ($labels) {
                $value = implode(', ', $labels);
            }
        }

        return $value;
    }
}
