<?php

namespace crocodicstudio\crudbooster\actions;

use crocodicstudio\crudbooster\controllers\CBController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;

/**
 * @deprecated 7.2 Only used by CBController modules; override detail() in a resource. See
 *     docs/en/deprecations.md.
 */
class DetailAction extends CrudAction
{
    public function execute(CBController $controller, mixed ...$arguments): mixed
    {
        $id = $arguments[0] ?? null;
        $row = $this->findRow($controller, $id);
        $module = $this->moduleName();
        $title = $this->titleOf($controller, $row);

        $this->authorize(
            ($this->permissions->canRead() || $controller->global_privilege) && $controller->button_detail,
            cbLang('log_try_view', ['name' => $title, 'module' => $module])
        );

        $page_menu = Route::getCurrentRoute()->getActionName();
        $page_title = cbLang('detail_data_page_title', ['module' => $module, 'name' => $title]);
        $command = 'detail';
        Session::put('current_row_id', $id);

        return view('crudbooster::default.form', compact('row', 'page_menu', 'page_title', 'command', 'id'));
    }
}
