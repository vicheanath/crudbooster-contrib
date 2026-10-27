<?php

namespace crocodicstudio\crudbooster\actions;

use crocodicstudio\crudbooster\controllers\CBController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;

/**
 * The add form (no id) and the edit form (an id).
 *
 * @deprecated 7.2 Only used by CBController modules; a resource's form() builds its create and edit pages.
 *     See docs/en/deprecations.md.
 */
class FormAction extends CrudAction
{
    public function execute(CBController $controller, mixed ...$arguments): mixed
    {
        $id = $arguments[0] ?? null;

        return $id === null ? $this->add($controller) : $this->edit($controller, $id);
    }

    protected function add(CBController $controller): mixed
    {
        $module = $this->moduleName();
        $this->authorize(
            ($this->permissions->canCreate() || $controller->global_privilege) && $controller->button_add,
            cbLang('log_try_add', ['module' => $module])
        );

        $page_title = cbLang('add_data_page_title', ['module' => $module]);
        $page_menu = Route::getCurrentRoute()->getActionName();
        $command = 'add';

        return view('crudbooster::default.form', compact('page_title', 'page_menu', 'command'));
    }

    protected function edit(CBController $controller, $id): mixed
    {
        $row = $this->findRow($controller, $id);
        $module = $this->moduleName();
        $title = $this->titleOf($controller, $row);

        // The edit form needs update permission (saving enforces it too).
        $this->authorize(
            ($this->permissions->canUpdate() || $controller->global_privilege) && $controller->button_edit,
            cbLang('log_try_edit', ['name' => $title, 'module' => $module])
        );

        $page_menu = Route::getCurrentRoute()->getActionName();
        $page_title = cbLang('edit_data_page_title', ['module' => $module, 'name' => $title]);
        $command = 'edit';
        Session::put('current_row_id', $id);

        return view('crudbooster::default.form', compact('id', 'row', 'page_menu', 'page_title', 'command'));
    }
}
