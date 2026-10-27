<?php namespace crocodicstudio\crudbooster\controllers;

error_reporting(E_ALL ^ E_NOTICE);


use CB;
use crocodicstudio\crudbooster\actions\BulkAction;
use crocodicstudio\crudbooster\actions\DeleteAction;
use crocodicstudio\crudbooster\actions\DetailAction;
use crocodicstudio\crudbooster\actions\ExportAction;
use crocodicstudio\crudbooster\actions\FormAction;
use crocodicstudio\crudbooster\actions\ImportAction;
use crocodicstudio\crudbooster\actions\IndexAction;
use crocodicstudio\crudbooster\actions\LookupAction;
use crocodicstudio\crudbooster\actions\SaveAction;
use crocodicstudio\crudbooster\compatibility\CrudConfig;
use crocodicstudio\crudbooster\contracts\Files\FileUploaderInterface;
use crocodicstudio\crudbooster\contracts\Forms\FormProcessorInterface;
use crocodicstudio\crudbooster\contracts\Forms\FormValidatorInterface;
use crocodicstudio\crudbooster\forms\FormContext;
use CRUDBooster;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;

/**
 * The base controller of every CRUDBooster module. A module configures it in cbInit() through
 * the public properties below and customizes it with the hook_*() methods. Each endpoint
 * delegates to an action class resolved from the container (see src/actions), so apps can
 * replace one by binding their own subclass.
 *
 * @deprecated 7.2 Build new screens as a resource (CrudBooster\Resources\Resource, `php artisan
 *     crudbooster:make-resource`). See docs/en/deprecations.md.
 */
class CBController extends Controller
{
    public $data_inputan;

    public $columns_table;

    public $module_name;

    public $table;

    public $title_field;

    public $primary_key = 'id';

    public $arr = [];

    public $col = [];

    public $form = [];

    public $data = [];

    public $addaction = [];

    public $orderby = null;

    public $password_candidate = null;

    public $date_candidate = null;

    public $limit = 20;

    public $global_privilege = false;

    public $show_numbering = false;

    public $alert = [];

    public $index_button = [];

    public $button_filter = true;

    public $button_export = true;

    public $button_import = true;

    public $button_show = true;

    public $button_addmore = true;

    public $button_table_action = true;

    public $button_bulk_action = true;

    public $button_add = true;

    public $button_delete = true;

    public $button_cancel = true;

    public $button_save = true;

    public $button_edit = true;

    public $button_detail = true;

    public $button_action_style = 'button_icon';

    public $button_action_width = null;

    public $index_statistic = [];

    public $index_additional_view = [];

    public $pre_index_html = null;

    public $post_index_html = null;

    public $load_js = [];

    public $load_css = [];

    public $script_js = null;

    public $style_css = null;

    public $sub_module = [];

    public $show_addaction = true;

    public $table_row_color = [];

    public $button_selected = [];

    public $return_url = null;

    public $parent_field = null;

    public $parent_id = null;

    public $hide_form = [];

    public $index_return = false; //for export

    public $sidebar_mode = 'normal';

    public function cbLoader()
    {
        $this->cbInit();

        $this->checkHideForm();

        $this->primary_key = CB::pk($this->table);
        $this->columns_table = $this->col;
        $this->data_inputan = $this->form;
        $this->data['pk'] = $this->primary_key;
        $this->data['forms'] = $this->data_inputan;
        $this->data['hide_form'] = $this->hide_form;
        $this->data['addaction'] = ($this->show_addaction) ? $this->addaction : null;
        $this->data['table'] = $this->table;
        $this->data['title_field'] = $this->title_field;
        $this->data['appname'] = CRUDBooster::getSetting('appname');
        $this->data['alerts'] = $this->alert;
        $this->data['index_button'] = $this->index_button;
        $this->data['show_numbering'] = $this->show_numbering;
        $this->data['button_detail'] = $this->button_detail;
        $this->data['button_edit'] = $this->button_edit;
        $this->data['button_show'] = $this->button_show;
        $this->data['button_add'] = $this->button_add;
        $this->data['button_delete'] = $this->button_delete;
        $this->data['button_filter'] = $this->button_filter;
        $this->data['button_export'] = $this->button_export;
        $this->data['button_addmore'] = $this->button_addmore;
        $this->data['button_cancel'] = $this->button_cancel;
        $this->data['button_save'] = $this->button_save;
        $this->data['button_table_action'] = $this->button_table_action;
        $this->data['button_bulk_action'] = $this->button_bulk_action;
        $this->data['button_import'] = $this->button_import;
        $this->data['button_action_width'] = $this->button_action_width;
        $this->data['button_selected'] = $this->button_selected;
        $this->data['index_statistic'] = $this->index_statistic;
        $this->data['index_additional_view'] = $this->index_additional_view;
        $this->data['table_row_color'] = $this->table_row_color;
        $this->data['pre_index_html'] = $this->pre_index_html;
        $this->data['post_index_html'] = $this->post_index_html;
        $this->data['load_js'] = $this->load_js;
        $this->data['load_css'] = $this->load_css;
        $this->data['script_js'] = $this->script_js;
        $this->data['style_css'] = $this->style_css;
        $this->data['sub_module'] = $this->sub_module;
        $this->data['parent_field'] = (g('parent_field')) ?: $this->parent_field;
        $this->data['parent_id'] = (g('parent_id')) ?: $this->parent_id;

        if ($this->sidebar_mode == 'mini') {
            $this->data['sidebar_mode'] = 'sidebar-mini';
        } elseif ($this->sidebar_mode == 'collapse') {
            $this->data['sidebar_mode'] = 'sidebar-collapse';
        } elseif ($this->sidebar_mode == 'collapse-mini') {
            $this->data['sidebar_mode'] = 'sidebar-collapse sidebar-mini';
        } else {
            $this->data['sidebar_mode'] = '';
        }

        if (CRUDBooster::getCurrentMethod() == 'getProfile') {
            Session::put('current_row_id', CRUDBooster::myId());
            $this->data['return_url'] = Request::fullUrl();
        }

        view()->share($this->data);
    }

    public function view($template, $data)
    {
        $this->cbLoader();
        return view($template, $data);
    }

    private function checkHideForm()
    {
        if ($this->hide_form && count($this->hide_form)) {
            foreach ($this->form as $i => $f) {
                if (in_array($f['name'], $this->hide_form)) {
                    unset($this->form[$i]);
                }
            }
        }
    }

    public function getIndex()
    {
        $this->cbLoader();

        return app(IndexAction::class)->execute($this);
    }

    public function getExportData()
    {
        return redirect(CRUDBooster::mainpath());
    }

    public function postExportData()
    {
        return app(ExportAction::class)->execute($this);
    }

    public function postDataQuery()
    {
        return response()->json(DB::select(request('query')));
    }

    public function getDataTable()
    {
        return app(LookupAction::class)->dataTable();
    }

    public function getModalData()
    {
        return app(LookupAction::class)->modalData();
    }

    public function getUpdateSingle()
    {
        $table = request('table');
        DB::table($table)->where(CB::pk($table), request('id'))->update([request('column') => request('value')]);

        return redirect()->back()->with(['message_type' => 'success', 'message' => cbLang('alert_delete_data_success')]);
    }

    public function getFindData()
    {
        return app(LookupAction::class)->findData($this);
    }

    /**
     * Validates the submitted form against $this->form, redirecting back with the errors.
     */
    public function validation($id = null)
    {
        app(FormValidatorInterface::class)->validate($this->data_inputan, $this->formContext($id));
    }

    /**
     * Fills $this->arr with the row to save from the submitted form.
     */
    public function input_assignment($id = null)
    {
        app(FormProcessorInterface::class)->assign($this->data_inputan, $this->arr, $this->formContext($id));
    }

    /**
     * What the form saves into: the module's table and, when editing, the row's id.
     */
    public function formContext($id = null): FormContext
    {
        return new FormContext(
            (string) $this->table,
            (string) $this->primary_key,
            $id === null ? FormContext::CREATE : FormContext::EDIT,
            $id,
        );
    }

    /**
     * A read-only snapshot of the module's configuration.
     */
    public function crudConfig(): CrudConfig
    {
        return CrudConfig::fromController($this);
    }

    public function getAdd()
    {
        $this->cbLoader();

        return app(FormAction::class)->execute($this);
    }

    public function postAddSave()
    {
        $this->cbLoader();

        return app(SaveAction::class)->execute($this, FormContext::CREATE);
    }

    public function getEdit($id)
    {
        $this->cbLoader();

        return app(FormAction::class)->execute($this, $id);
    }

    public function postEditSave($id)
    {
        $this->cbLoader();

        return app(SaveAction::class)->execute($this, FormContext::EDIT, $id);
    }

    public function getDelete($id)
    {
        $this->cbLoader();

        return app(DeleteAction::class)->execute($this, $id);
    }

    public function getDetail($id)
    {
        $this->cbLoader();

        return app(DetailAction::class)->execute($this, $id);
    }

    public function getImportData()
    {
        $this->cbLoader();

        return app(ImportAction::class)->preview($this);
    }

    public function postDoneImport()
    {
        $this->cbLoader();

        return app(ImportAction::class)->mapColumns($this);
    }

    public function postDoImportChunk()
    {
        $this->cbLoader();

        return app(ImportAction::class)->importChunk($this);
    }

    public function postDoUploadImportData()
    {
        $this->cbLoader();

        return app(ImportAction::class)->upload($this);
    }

    public function postActionSelected()
    {
        $this->cbLoader();

        return app(BulkAction::class)->execute($this);
    }

    public function getDeleteImage()
    {
        $this->cbLoader();

        return app(DeleteAction::class)->deleteImage($this, request('id'), (string) request('column'));
    }

    public function postUploadSummernote()
    {
        $this->cbLoader();
        $this->echoUploadedFile('userfile');
    }

    public function postUploadFile()
    {
        $this->cbLoader();
        $this->echoUploadedFile('userfile');
    }

    private function echoUploadedFile(string $name)
    {
        if ($file = app(FileUploaderInterface::class)->upload($name, true)) {
            echo asset($file);
        }
    }

    /**
     * @deprecated 7.2 Use BulkAction::make($name)->action(fn (Collection $records) => ...) in
     *     Table::bulkActions().
     */
    public function actionButtonSelected($id_selected, $button_name)
    {
    }

    /**
     * @deprecated 7.2 Use Resource::modifyQuery() (every page) or Table::modifyQueryUsing() (the list only).
     */
    public function hook_query_index(&$query)
    {
    }

    /**
     * @deprecated 7.2 Use Column::formatStateUsing() or Column::state().
     */
    public function hook_row_index($index, &$value)
    {
    }

    /**
     * @deprecated 7.2 Use Resource::beforeCreate(array $data): array.
     */
    public function hook_before_add(&$arr)
    {
    }

    /**
     * @deprecated 7.2 Use Resource::afterCreate(Model $record).
     */
    public function hook_after_add($id)
    {
    }

    /**
     * @deprecated 7.2 Use Resource::beforeUpdate(array $data, Model $record): array.
     */
    public function hook_before_edit(&$arr, $id)
    {
    }

    /**
     * @deprecated 7.2 Use Resource::afterUpdate(Model $record).
     */
    public function hook_after_edit($id)
    {
    }

    /**
     * @deprecated 7.2 Use Resource::beforeDelete(Model $record).
     */
    public function hook_before_delete($id)
    {
    }

    /**
     * @deprecated 7.2 Use Resource::afterDelete(Model $record).
     */
    public function hook_after_delete($id)
    {
    }
}
