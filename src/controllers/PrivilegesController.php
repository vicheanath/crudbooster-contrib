<?php namespace crocodicstudio\crudbooster\controllers;

use CRUDBooster;
use crocodicstudio\crudbooster\contracts\Security\PrivilegeRolesInterface;
use crocodicstudio\crudbooster\models\CmsModul;
use crocodicstudio\crudbooster\models\CmsPrivilege;
use Illuminate\Support\Facades\Excel;
use Illuminate\Support\Facades\PDF;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;

class PrivilegesController extends CBController
{
    public function cbInit()
    {
        $this->module_name = "Privilege";
        $this->table = 'cms_privileges';
        $this->primary_key = 'id';
        $this->title_field = "name";
        $this->button_import = false;
        $this->button_export = false;
        $this->button_action_style = 'button_icon';
        $this->button_detail = false;
        $this->button_bulk_action = false;

        $this->col = [];
        $this->col[] = ["label" => "ID", "name" => "id"];
        $this->col[] = ["label" => "Name", "name" => "name"];
        $this->col[] = [
            "label" => "Superadmin",
            "name" => "is_superadmin",
            'callback_php' => '($row->is_superadmin)?"<span class=\"label label-success\">Superadmin</span>":"<span class=\"label label-default\">Standard</span>"',
        ];

        $this->form = [];
        $this->form[] = ["label" => "Name", "name" => "name", 'required' => true];
        $this->form[] = ["label" => "Is Superadmin", "name" => "is_superadmin", 'required' => true];
        $this->form[] = ["label" => "Theme Color", "name" => "theme_color", 'required' => true];

        $this->alert[] = [
            'message' => "You can use the helper <code>CRUDBooster::getMyPrivilegeId()</code> to get current user login privilege id, or <code>CRUDBooster::getMyPrivilegeName()</code> to get current user login privilege name",
            'type' => 'info',
        ];
    }

    public function getAdd()
    {
        $this->cbLoader();

        if (! CRUDBooster::isCreate() && $this->global_privilege == false) {
            CRUDBooster::insertLog(cbLang('log_try_add', ['module' => CRUDBooster::getCurrentModule()->name]));
            CRUDBooster::redirect(CRUDBooster::adminPath(), cbLang("denied_access"));
        }

        $data['page_title'] = "Add Data";
        $data['moduls'] = $this->assignableModules();
        $data['roles_by_module'] = collect();
        $data['page_menu'] = Route::getCurrentRoute()->getActionName();

        return view('crudbooster::privileges', $data);
    }

    public function postAddSave()
    {
        $this->cbLoader();

        if (! CRUDBooster::isCreate() && $this->global_privilege == false) {
            CRUDBooster::insertLog(cbLang('log_try_add_save', [
                'name' => Request::input($this->title_field),
                'module' => CRUDBooster::getCurrentModule()->name,
            ]));
            CRUDBooster::redirect(CRUDBooster::adminPath(), cbLang("denied_access"));
        }

        $this->validation();
        $this->input_assignment();
        $id = CmsPrivilege::query()->create($this->arr)->id;

        //set theme
        Session::put('theme_color', $this->arr['theme_color']);

        $roles = app(PrivilegeRolesInterface::class);
        $roles->sync($id, (array) Request::input('privileges'));
        $roles->refreshSession();

        CRUDBooster::redirect(CRUDBooster::mainpath(), cbLang("alert_add_data_success"), 'success');
    }

    public function getEdit($id)
    {
        $this->cbLoader();

        $row = CmsPrivilege::query()->toBase()->find($id);

        if (! CRUDBooster::isRead() && $this->global_privilege == false) {
            CRUDBooster::insertLog(cbLang("log_try_edit", [
                'name' => $row->{$this->title_field},
                'module' => CRUDBooster::getCurrentModule()->name,
            ]));
            CRUDBooster::redirect(CRUDBooster::adminPath(), cbLang('denied_access'));
        }

        $page_title = cbLang('edit_data_page_title', ['module' => 'Privilege', 'name' => $row->name]);

        $moduls = $this->assignableModules();
        $roles_by_module = app(PrivilegeRolesInterface::class)->byModule($id);
        $page_menu = Route::getCurrentRoute()->getActionName();

        return view('crudbooster::privileges', compact('row', 'page_title', 'moduls', 'roles_by_module', 'page_menu'));
    }

    public function postEditSave($id)
    {
        $this->cbLoader();

        $row = CRUDBooster::first($this->table, $id);

        if (! CRUDBooster::isUpdate() && $this->global_privilege == false) {
            CRUDBooster::insertLog(cbLang("log_try_add", ['name' => $row->{$this->title_field}, 'module' => CRUDBooster::getCurrentModule()->name]));
            CRUDBooster::redirect(CRUDBooster::adminPath(), cbLang('denied_access'));
        }

        $this->validation($id);
        $this->input_assignment($id);

        CmsPrivilege::query()->whereKey($id)->update($this->arr);

        $roles = app(PrivilegeRolesInterface::class);
        $roles->sync($id, (array) Request::input('privileges'));

        if ($id == CRUDBooster::myPrivilegeId()) {
            $roles->refreshSession();
            Session::put('theme_color', $this->arr['theme_color']);
        }

        CRUDBooster::redirect(CRUDBooster::mainpath(), cbLang("alert_update_data_success", [
            'module' => "Privilege",
            'title' => $row->name,
        ]), 'success');
    }

    public function getDelete($id)
    {
        $this->cbLoader();

        $row = CmsPrivilege::query()->toBase()->find($id);

        if (! CRUDBooster::isDelete() && $this->global_privilege == false) {
            CRUDBooster::insertLog(cbLang("log_try_delete", [
                'name' => $row->{$this->title_field},
                'module' => CRUDBooster::getCurrentModule()->name,
            ]));
            CRUDBooster::redirect(CRUDBooster::adminPath(), cbLang('denied_access'));
        }

        CmsPrivilege::query()->whereKey($id)->delete();
        app(PrivilegeRolesInterface::class)->sync($id, []);

        CRUDBooster::redirect(CRUDBooster::mainpath(), cbLang("alert_delete_data_success"), 'success');
    }

    /**
     * Modules shown in the privilege form, by name.
     */
    private function assignableModules()
    {
        return CmsModul::query()->unprotected()->notDeleted()->orderBy('name', 'asc')->get();
    }
}
