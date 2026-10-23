<?php

namespace crocodicstudio\crudbooster\actions;

use crocodicstudio\crudbooster\contracts\Audit\AuditLoggerInterface;
use crocodicstudio\crudbooster\contracts\Schema\SchemaInspectorInterface;
use crocodicstudio\crudbooster\contracts\Security\PermissionCheckerInterface;
use crocodicstudio\crudbooster\controllers\CBController;
use crocodicstudio\crudbooster\export\DefaultImportRows;
use CRUDBooster;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

/**
 * The import wizard: upload() stores the file, preview() maps its headings to the table's
 * columns, mapColumns() keeps the mapping and importChunk() inserts the rows, reporting progress.
 */
class ImportAction extends CrudAction
{
    public function __construct(
        PermissionCheckerInterface $permissions,
        AuditLoggerInterface $audit,
        protected readonly SchemaInspectorInterface $schema,
    ) {
        parent::__construct($permissions, $audit);
    }

    public function execute(CBController $controller, mixed ...$arguments): mixed
    {
        return $this->preview($controller);
    }

    public function upload(CBController $controller): mixed
    {
        if (! Request::hasFile('userfile')) {
            return redirect()->back();
        }

        $file = Request::file('userfile');
        $ext = $file->getClientOriginalExtension();

        $validator = Validator::make(['extension' => $ext], ['extension' => 'in:xls,xlsx,csv']);
        if ($validator->fails()) {
            return redirect()->back()->with(['message' => implode('<br/>', $validator->errors()->all()), 'message_type' => 'warning']);
        }

        $directory = 'uploads/'.CRUDBooster::myId().'/'.date('Y-m');
        Storage::makeDirectory($directory);

        $filename = md5(str_random(5)).'.'.$ext;
        $path = Storage::putFileAs($directory, $file, $filename) ? $directory.'/'.$filename : '';

        return redirect(CRUDBooster::mainpath('import-data').'?file='.base64_encode($path));
    }

    public function preview(CBController $controller): mixed
    {
        $data = [];
        $data['page_menu'] = Route::getCurrentRoute()->getActionName();
        $data['page_title'] = 'Import Data';

        if (request('file') && ! request('import')) {
            $rows = DefaultImportRows::read($this->path());
            Session::put('total_data_import', $rows ? count($rows) : 0);

            $headings = [];
            foreach ($rows as $row) {
                $headings = array_keys((array) $row) ?: $headings;
                break;
            }

            $data['table_columns'] = DB::getSchemaBuilder()->getColumnListing($controller->table);
            $data['data_import_column'] = $headings;
        }

        return view('crudbooster::import', $data);
    }

    public function mapColumns(CBController $controller): mixed
    {
        $data = [];
        $data['page_menu'] = Route::getCurrentRoute()->getActionName();
        $data['page_title'] = cbLang('button_import');
        Session::put('select_column', request('select_column'));

        return view('crudbooster::import', $data);
    }

    public function importChunk(CBController $controller): mixed
    {
        $fileKey = md5(request('file'));

        if (request('file') && request('resume') == 1) {
            $total = Session::get('total_data_import');
            $progress = round(intval(Cache::get('success_'.$fileKey)) / $total * 100, 2);
            if ($progress >= 100) {
                Cache::forget('success_'.$fileKey);
            }

            return response()->json(['progress' => $progress, 'last_error' => Cache::get('error_'.$fileKey)]);
        }

        $selectedColumns = array_filter((array) Session::get('select_column'));
        $tableColumns = DB::getSchemaBuilder()->getColumnListing($controller->table);
        $hasCreatedAt = $this->schema->hasColumn($controller->table, 'created_at');

        foreach (DefaultImportRows::read($this->path()) as $row) {
            $record = [];
            foreach ($selectedColumns as $columnIndex => $heading) {
                $column = $tableColumns[$columnIndex];

                if (! CRUDBooster::isForeignKey($column)) {
                    $record[$column] = $row->$heading;
                } elseif ($row->$heading != '') {
                    $record[$column] = intval($row->$heading) ? $row->$heading : $this->relatedId($column, $row->$heading);
                }
            }

            // Rows without a title are skipped.
            if ($controller->title_field && array_key_exists($controller->title_field, $record) && $record[$controller->title_field] == '') {
                continue;
            }

            try {
                if ($hasCreatedAt) {
                    $record['created_at'] = date('Y-m-d H:i:s');
                }
                DB::table($controller->table)->insert($record);
                Cache::increment('success_'.$fileKey);
            } catch (\Exception $e) {
                Cache::put('error_'.$fileKey, (string) $e, 500);
            }
        }

        return response()->json(['status' => true]);
    }

    /**
     * The id of the related row titled $title, created when missing.
     */
    protected function relatedId(string $column, $title)
    {
        $relationTable = CRUDBooster::getTableForeignKey($column);
        $module = DB::table('cms_moduls')->where('table_name', $relationTable)->first();

        $class = 'crocodicstudio\\crudbooster\\controllers\\'.$module->controller;
        if (! class_exists($class)) {
            $class = '\App\Http\Controllers\\'.$module->controller;
        }
        $relation = new $class;
        $relation->cbLoader();
        $titleField = $relation->title_field;

        $existing = DB::table($relationTable)->where($titleField, $title)->first();
        if ($existing) {
            return $existing->{$relation->primary_key};
        }

        $insert = [$titleField => $title];
        if ($this->schema->hasColumn($relationTable, 'created_at')) {
            $insert['created_at'] = date('Y-m-d H:i:s');
        }

        return DB::table($relationTable)->insertGetId($insert);
    }

    protected function path(): string
    {
        return Storage::path(base64_decode(request('file')));
    }
}
