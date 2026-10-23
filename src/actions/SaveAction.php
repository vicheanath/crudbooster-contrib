<?php

namespace crocodicstudio\crudbooster\actions;

use crocodicstudio\crudbooster\contracts\Audit\AuditLoggerInterface;
use crocodicstudio\crudbooster\contracts\Forms\FormProcessorInterface;
use crocodicstudio\crudbooster\contracts\Schema\SchemaInspectorInterface;
use crocodicstudio\crudbooster\contracts\Security\PermissionCheckerInterface;
use crocodicstudio\crudbooster\controllers\CBController;
use crocodicstudio\crudbooster\controllers\LogsController;
use crocodicstudio\crudbooster\forms\FormContext;
use CRUDBooster;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

/**
 * Saves the add form (`execute($controller, 'create')`) or the edit form
 * (`execute($controller, 'edit', $id)`): validation(), input_assignment(), the before hook,
 * the insert or update, the fields' after-save steps (pivot and child rows), the after hook,
 * the log entry and the redirect.
 */
class SaveAction extends CrudAction
{
    public function __construct(
        PermissionCheckerInterface $permissions,
        AuditLoggerInterface $audit,
        protected readonly FormProcessorInterface $forms,
        protected readonly SchemaInspectorInterface $schema,
    ) {
        parent::__construct($permissions, $audit);
    }

    public function execute(CBController $controller, mixed ...$arguments): mixed
    {
        [$mode, $id] = array_pad($arguments, 2, null);

        return $mode === FormContext::EDIT ? $this->update($controller, $id) : $this->create($controller);
    }

    protected function create(CBController $controller): mixed
    {
        $module = $this->moduleName();
        $this->authorize(
            $this->permissions->canCreate() || $controller->global_privilege,
            cbLang('log_try_add_save', ['name' => Request::input($controller->title_field), 'module' => $module])
        );

        // Both are public, so modules can override them.
        $controller->validation();
        $controller->input_assignment();

        if ($this->schema->hasColumn($controller->table, 'created_at')) {
            $controller->arr['created_at'] = date('Y-m-d H:i:s');
        }

        $controller->hook_before_add($controller->arr);

        $insertId = $id = DB::table($controller->table)->insertGetId($controller->arr);

        // A UUID primary key set by the module is the row's id, not what insertGetId() returns.
        if (isset($controller->arr[$controller->primary_key]) && $controller->arr[$controller->primary_key] != $id) {
            $id = $controller->arr[$controller->primary_key];
        }

        $this->forms->afterSave($controller->data_inputan, $controller->formContext()->withRecord($id, $insertId));

        $controller->hook_after_add($insertId);

        $controller->return_url = $controller->return_url ?: request('return_url');

        $this->audit->log(cbLang('log_add', ['name' => $controller->arr[$controller->title_field] ?? null, 'module' => $module]));

        $saveMore = request('submit') == cbLang('button_save_more');
        if ($controller->return_url) {
            CRUDBooster::redirect($saveMore ? Request::server('HTTP_REFERER') : $controller->return_url, cbLang('alert_add_data_success'), 'success');
        }

        CRUDBooster::redirect(CRUDBooster::mainpath($saveMore ? 'add' : null), cbLang('alert_add_data_success'), 'success');
    }

    protected function update(CBController $controller, $id): mixed
    {
        $row = $this->findRow($controller, $id);
        $module = $this->moduleName();

        $this->authorize(
            $this->permissions->canUpdate() || $controller->global_privilege,
            cbLang('log_try_add', ['name' => $this->titleOf($controller, $row), 'module' => $module])
        );

        $controller->validation($id);
        $controller->input_assignment($id);

        if ($this->schema->hasColumn($controller->table, 'updated_at')) {
            $controller->arr['updated_at'] = date('Y-m-d H:i:s');
        }

        $controller->hook_before_edit($controller->arr, $id);

        DB::table($controller->table)->where($controller->primary_key, $id)->update($controller->arr);

        $this->forms->afterSave($controller->data_inputan, $controller->formContext($id));

        $controller->hook_after_edit($id);

        $controller->return_url = $controller->return_url ?: request('return_url');

        $this->audit->log(
            cbLang('log_update', ['name' => $controller->arr[$controller->title_field] ?? null, 'module' => $module]),
            LogsController::displayDiff(json_decode(json_encode($row), true), $controller->arr)
        );

        if ($controller->return_url) {
            CRUDBooster::redirect($controller->return_url, cbLang('alert_update_data_success'), 'success');
        }

        $saveMore = request('submit') == cbLang('button_save_more');
        CRUDBooster::redirect(CRUDBooster::mainpath($saveMore ? 'add' : null), cbLang('alert_update_data_success'), 'success');
    }
}
