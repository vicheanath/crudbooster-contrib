<?php

namespace crocodicstudio\crudbooster\actions;

use crocodicstudio\crudbooster\contracts\Audit\AuditLoggerInterface;
use crocodicstudio\crudbooster\contracts\Schema\SchemaInspectorInterface;
use crocodicstudio\crudbooster\contracts\Security\PermissionCheckerInterface;
use crocodicstudio\crudbooster\controllers\CBController;
use CRUDBooster;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Deletes a row (soft delete when the table has deleted_at), or with
 * `deleteImage()` the file in one of its columns.
 *
 * @deprecated 7.2 Only used by CBController modules; resources delete through Eloquent with
 *     beforeDelete()/afterDelete(). See docs/en/deprecations.md.
 */
class DeleteAction extends CrudAction
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
        $id = $arguments[0] ?? null;
        $row = $this->findRow($controller, $id);
        $module = $this->moduleName();
        $title = $this->titleOf($controller, $row);

        $this->authorize(
            ($this->permissions->canDelete() || $controller->global_privilege) && $controller->button_delete,
            cbLang('log_try_delete', ['name' => $title, 'module' => $module])
        );

        $this->audit->log(cbLang('log_delete', ['name' => $title, 'module' => $module]));

        $controller->hook_before_delete($id);
        $this->deleteRows($controller, [$id]);
        $controller->hook_after_delete($id);

        CRUDBooster::redirect(g('return_url') ?: CRUDBooster::referer(), cbLang('alert_delete_data_success'), 'success');
    }

    /**
     * Soft deletes when the table has a deleted_at column.
     */
    public function deleteRows(CBController $controller, array $ids): void
    {
        $query = DB::table($controller->table)->whereIn($controller->primary_key, $ids);

        if ($this->schema->hasColumn($controller->table, 'deleted_at')) {
            $query->update(['deleted_at' => date('Y-m-d H:i:s')]);
        } else {
            $query->delete();
        }
    }

    public function deleteImage(CBController $controller, $id, string $column): mixed
    {
        $row = $this->findRow($controller, $id);
        $module = $this->moduleName();
        $title = $this->titleOf($controller, $row);

        $this->authorize(
            $this->permissions->canDelete() || $controller->global_privilege,
            cbLang('log_try_delete_image', ['name' => $title, 'module' => $module])
        );

        $file = str_replace('uploads/', '', (string) ($row->{$column} ?? ''));
        if ($file !== '' && Storage::exists($file)) {
            Storage::delete($file);
        }

        DB::table($controller->table)->where($controller->primary_key, $id)->update([$column => null]);

        $this->audit->log(cbLang('log_delete_image', ['name' => $title, 'module' => $module]));

        CRUDBooster::redirect(Request::server('HTTP_REFERER'), cbLang('alert_delete_data_success'), 'success');
    }
}
