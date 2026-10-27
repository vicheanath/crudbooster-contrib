<?php

namespace crocodicstudio\crudbooster\actions;

use crocodicstudio\crudbooster\contracts\Audit\AuditLoggerInterface;
use crocodicstudio\crudbooster\contracts\Security\PermissionCheckerInterface;
use crocodicstudio\crudbooster\controllers\CBController;
use CRUDBooster;
use Illuminate\Support\Facades\Request;

/**
 * The index page's "selected rows" buttons: delete, or the module's own buttons
 * ($button_selected), handled by its actionButtonSelected().
 *
 * @deprecated 7.2 Only used by CBController modules. Use CrudBooster\Actions\BulkAction in
 *     Table::bulkActions(). See docs/en/deprecations.md.
 */
class BulkAction extends CrudAction
{
    public function __construct(
        PermissionCheckerInterface $permissions,
        AuditLoggerInterface $audit,
        protected readonly DeleteAction $delete,
    ) {
        parent::__construct($permissions, $audit);
    }

    public function execute(CBController $controller, mixed ...$arguments): mixed
    {
        $ids = Request::input('checkbox');
        $button = Request::input('button_name');

        if (! $ids) {
            CRUDBooster::redirect(Request::server('HTTP_REFERER'), cbLang('alert_select_a_data'), 'warning');
        }

        if ($button == 'delete') {
            $module = $this->moduleName();
            $this->authorize($this->permissions->canDelete(), cbLang('log_try_delete_selected', ['module' => $module]));

            $controller->hook_before_delete($ids);
            $this->delete->deleteRows($controller, (array) $ids);
            $this->audit->log(cbLang('log_delete', ['name' => implode(',', (array) $ids), 'module' => $module]));
            $controller->hook_after_delete($ids);

            return redirect()->back()->with(['message_type' => 'success', 'message' => cbLang('alert_delete_selected_success')]);
        }

        $type = 'success';
        $message = cbLang('alert_action', ['action' => ucwords(str_replace(['-', '_'], ' ', (string) $button))]);

        if ($controller->actionButtonSelected($ids, $button) === false) {
            $message = ! empty($controller->alert['message']) ? $controller->alert['message'] : 'Error';
            $type = ! empty($controller->alert['type']) ? $controller->alert['type'] : 'danger';
        }

        return redirect()->back()->with(['message_type' => $type, 'message' => $message]);
    }
}
