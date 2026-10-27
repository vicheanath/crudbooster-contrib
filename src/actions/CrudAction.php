<?php

namespace crocodicstudio\crudbooster\actions;

use crocodicstudio\crudbooster\contracts\Actions\CrudActionInterface;
use crocodicstudio\crudbooster\contracts\Audit\AuditLoggerInterface;
use crocodicstudio\crudbooster\contracts\Security\PermissionCheckerInterface;
use crocodicstudio\crudbooster\controllers\CBController;
use CRUDBooster;
use Illuminate\Support\Facades\DB;

/**
 * @deprecated 7.2 Only used by CBController modules. Use CrudBooster\Actions\Action in a resource. See
 *     docs/en/deprecations.md.
 */
abstract class CrudAction implements CrudActionInterface
{
    public function __construct(
        protected readonly PermissionCheckerInterface $permissions,
        protected readonly AuditLoggerInterface $audit,
    ) {
    }

    /**
     * Logs the attempt and redirects to the dashboard with "denied access" unless $allowed.
     */
    protected function authorize(bool $allowed, string $logMessage): void
    {
        if (! $allowed) {
            $this->audit->log($logMessage);
            CRUDBooster::redirect(CRUDBooster::adminPath(), cbLang('denied_access'));
        }
    }

    /**
     * The current module's name, or its path when it is not registered in cms_moduls.
     */
    protected function moduleName(): ?string
    {
        $module = CRUDBooster::getCurrentModule();

        return is_object($module) ? $module->name : $module;
    }

    protected function findRow(CBController $controller, $id): ?object
    {
        return DB::table($controller->table)->where($controller->primary_key, $id)->first();
    }

    protected function titleOf(CBController $controller, ?object $row): mixed
    {
        return $row && $controller->title_field ? ($row->{$controller->title_field} ?? null) : null;
    }
}
