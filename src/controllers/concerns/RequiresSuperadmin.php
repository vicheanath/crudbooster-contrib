<?php

namespace crocodicstudio\crudbooster\controllers\concerns;

use CRUDBooster;

/**
 * For built-in tools that only a superadmin may use (API generator, statistic builder).
 */
trait RequiresSuperadmin
{
    /**
     * Logs the attempt and redirects to the admin home (a JSON error for AJAX calls) unless the
     * current admin is a superadmin.
     */
    protected function denyUnlessSuperadmin(string $action, string $module): void
    {
        if (! CRUDBooster::isSuperadmin()) {
            CRUDBooster::insertLog(cbLang('log_try_view', ['name' => $action, 'module' => $module]));
            CRUDBooster::redirect(CRUDBooster::adminPath(), cbLang('denied_access'));
        }
    }
}
