<?php

namespace crocodicstudio\crudbooster\actions;

use crocodicstudio\crudbooster\contracts\Schema\SchemaInspectorInterface;
use crocodicstudio\crudbooster\controllers\CBController;
use CRUDBooster;
use Illuminate\Support\Facades\DB;

/**
 * The JSON and modal lookups form inputs load their options from: select2 (findData),
 * dependent selects (dataTable) and the datamodal browser (modalData).
 */
class LookupAction
{
    public function __construct(protected readonly SchemaInspectorInterface $schema)
    {
    }

    public function findData(CBController $controller): mixed
    {
        $q = request('q');
        $id = request('id');
        $limit = request('limit') ?: 10;
        $format = request('format');

        $table1 = request('table1') ?: $controller->table;
        $table1PK = $this->schema->primaryKey((string) $table1);
        $column1 = request('column1') ?: $controller->title_field;
        $table2 = request('table2');
        $column2 = request('column2');
        $table3 = request('table3');
        $column3 = request('column3');
        $where = request('where');
        $fk = request('fk');
        $fkValue = request('fk_value');

        if (! $q && ! $id && ! $table1) {
            return response()->json(['items' => []]);
        }

        $orderTable = $table1;
        $orderColumn = $table1PK;

        $rows = DB::table($table1)->select($table1.'.*')->take($limit);

        if ($this->schema->hasColumn($table1, 'deleted_at')) {
            $rows->where($table1.'.deleted_at', null);
        }

        if ($fk && $fkValue) {
            $rows->where($table1.'.'.$fk, $fkValue);
        }

        if ($column1) {
            $orderColumn = $column1;
        }

        if ($table2 && $column2) {
            $rows->join($table2, $table2.'.'.$this->schema->primaryKey($table2), '=', $table1.'.'.$column1);
            foreach ($this->schema->columns($table2) as $column) {
                $rows->addSelect($table2.'.'.$column.' as '.$table2.'_'.$column);
            }
            $orderTable = $table2;
            $orderColumn = $column2;
        }

        if ($table3 && $column3) {
            $rows->join($table3, $table3.'.'.$this->schema->primaryKey($table3), '=', $table2.'.'.$column2);
            foreach ($this->schema->columns($table3) as $column) {
                $rows->addSelect($table3.'.'.$column.' as '.$table3.'_'.$column);
            }
            $orderTable = $table3;
            $orderColumn = $column3;
        }

        if ($id) {
            $rows->where($table1.'.'.$table1PK, $id);
        }

        if ($where) {
            $rows->whereRaw($where);
        }

        if ($format) {
            $format = str_replace('&#039;', "'", $format);
            $rows->addSelect(DB::raw("CONCAT($format) as text"));
            if ($q) {
                $rows->whereRaw("CONCAT($format) like ?", ['%'.$q.'%']);
            }
        } else {
            $rows->addSelect($orderTable.'.'.$orderColumn.' as text');
            if ($q) {
                $rows->where($orderTable.'.'.$orderColumn, 'like', '%'.$q.'%');
            }
            $rows->orderBy($orderTable.'.'.$orderColumn, 'asc');
        }

        return response()->json(['items' => $rows->get()]);
    }

    public function dataTable(): mixed
    {
        $table = request('table');
        $label = request('label');
        $where = urldecode((string) request('datatable_where'));
        $fkName = request('fk_name');
        $fkValue = request('fk_value');

        if (! ($table && $label && $fkName && $fkValue)) {
            return response()->json([]);
        }

        $query = DB::table($table);
        if ($where) {
            $query->whereRaw($where);
        }

        return response()->json(
            $query->select('id as select_value', $label.' as select_label')->where($fkName, $fkValue)->orderBy($label, 'asc')->get()
        );
    }

    public function modalData(): mixed
    {
        $table = CRUDBooster::parseSqlTable(request('table'))['table'];
        $where = urldecode((string) request('where'));
        $columns = explode(',', (string) request('columns'));
        $paginate = urldecode((string) request('paginate'));

        $result = DB::table($table);

        if (request('q')) {
            $result->where(function ($query) use ($columns) {
                foreach ($columns as $i => $column) {
                    $i == 0
                        ? $query->where($column, 'like', '%'.request('q').'%')
                        : $query->orWhere($column, 'like', '%'.request('q').'%');
                }
            });
        }

        if ($where) {
            $result->whereRaw($where);
        }

        $result->orderBy($this->schema->primaryKey($table), 'desc');

        $data = [];
        $data['result'] = $result->paginate($paginate ?: 6);
        $data['columns'] = $columns;

        return view('crudbooster::default.type_components.datamodal.browser', $data);
    }
}
