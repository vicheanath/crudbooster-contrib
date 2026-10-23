<?php namespace crocodicstudio\crudbooster\controllers;

use CRUDBooster;
use crocodicstudio\crudbooster\controllers\concerns\RequiresSuperadmin;
use crocodicstudio\crudbooster\models\CmsMenu;
use crocodicstudio\crudbooster\models\CmsStatisticComponent;
use Illuminate\Support\Facades\Excel;
use Illuminate\Support\Facades\PDF;
use Illuminate\Support\Facades\Request;

class StatisticBuilderController extends CBController
{
    use RequiresSuperadmin;

    public function cbInit()
    {
        $this->table = "cms_statistics";
        $this->primary_key = "id";
        $this->title_field = "name";
        $this->limit = 20;
        $this->orderby = ["id" => "desc"];
        $this->global_privilege = false;

        $this->button_table_action = true;
        $this->button_action_style = "button_icon_text";
        $this->button_add = true;
        $this->button_delete = true;
        $this->button_edit = true;
        $this->button_detail = false;
        $this->button_show = true;
        $this->button_filter = false;
        $this->button_export = false;
        $this->button_import = false;

        $this->col = [];
        $this->col[] = ["label" => "Name", "name" => "name"];

        $this->form = [];
        $this->form[] = [
            "label" => "Name",
            "name" => "name",
            "type" => "text",
            "required" => true,
            "validation" => "required|min:3|max:255",
            "placeholder" => "You can only enter the letter only",
        ];

        $this->addaction = [];
        $this->addaction[] = ['label' => 'Builder', 'url' => CRUDBooster::mainpath('builder').'/[id]', 'icon' => 'fa fa-wrench'];
    }

    public function getShowDashboard()
    {
        $this->cbLoader();
        $m = CRUDBooster::sidebarDashboard();
        $m->path = str_replace("statistic_builder/show/", "", $m->path);
        if ($m->type != 'Statistic') {
            redirect('/');
        }
        $row = CRUDBooster::first($this->table, ['slug' => $m->path]);

        $id_cms_statistics = $row->id;
        $page_title = $row->name;

        return view('crudbooster::statistic_builder.show', compact('page_title', 'id_cms_statistics'));
    }

    public function getDashboard()
    {
        $this->cbLoader();

        $menus = CmsMenu::query()->visibleTo(CRUDBooster::myPrivilegeId())->dashboard()->active()->first();

        $slug = str_replace("statistic_builder/show/", "", $menus->path);

        $row = CRUDBooster::first($this->table, ['slug' => $slug]);
        $id_cms_statistics = $row->id;
        $page_title = $row->name;


        $data = [];
        $data['row'] = $row;
        $data['id_cms_statistics'] = $id_cms_statistics;
        $data['page_title'] = $page_title;

        return view('crudbooster::statistic_builder.show',$data);
    }

    public function getShow($slug)
    {
        $this->cbLoader();
        $row = CRUDBooster::first($this->table, ['slug' => $slug]);
        $id_cms_statistics = $row->id;
        $page_title = $row->name;

        return view('crudbooster::statistic_builder.show', compact('page_title', 'id_cms_statistics'));
    }

    public function getBuilder($id_cms_statistics)
    {
        $this->cbLoader();

        $this->denyUnlessSuperadmin('Builder', 'Statistic');

        $page_title = 'Statistic Builder';

        return view('crudbooster::statistic_builder.builder', compact('page_title', 'id_cms_statistics'));
    }

    public function getListComponent($id_cms_statistics, $area_name)
    {
        $rows = CmsStatisticComponent::query()->inArea($id_cms_statistics, $area_name)->get();

        return response()->json(['components' => $rows]);
    }

    public function getViewComponent($componentID)
    {

        $component = CmsStatisticComponent::query()->forComponentId($componentID)->first();
        $command = 'layout';
        $layout = view('crudbooster::statistic_builder.components.'.$component->component_name, compact('command', 'componentID'))->render();

        $component_name = $component->component_name;
        $area_name = $component->area_name;
        $config = json_decode($component->config);
        if ($config) {
            foreach ($config as $key => $value) {
                if ($value) {
                    $command = 'showFunction';
                    $value = view('crudbooster::statistic_builder.components.'.$component_name, compact('command', 'value', 'key', 'config', 'componentID'))->render();
                    $layout = str_replace('['.$key.']', $value, $layout);
                }
            }
        }

        return response()->json(compact('componentID', 'layout'));
    }

    public function postAddComponent()
    {
        $this->cbLoader();
        $this->denyUnlessSuperadmin('Add Component', 'Statistic');
        $component_name = Request::get('component_name');
        $id_cms_statistics = Request::get('id_cms_statistics');
        $sorting = Request::get('sorting');
        $area = Request::get('area');

        $componentID = md5(time());

        $command = 'layout';
        $layout = view('crudbooster::statistic_builder.components.'.$component_name, compact('command', 'componentID'))->render();

        $data = [
            'id_cms_statistics' => $id_cms_statistics,
            'componentID' => $componentID,
            'component_name' => $component_name,
            'area_name' => $area,
            'sorting' => $sorting,
            'name' => 'Untitled',
        ];
        CmsStatisticComponent::query()->create($data + ['created_at' => now()->format('Y-m-d H:i:s')]);

        return response()->json(compact('layout', 'componentID'));
    }

    public function postUpdateAreaComponent()
    {
        $this->denyUnlessSuperadmin('Move Component', 'Statistic');

        CmsStatisticComponent::query()->forComponentId(Request::get('componentid'))->update([
            'sorting' => Request::get('sorting'),
            'area_name' => Request::get('areaname'),
        ]);

        return response()->json(['status' => true]);
    }

    public function getEditComponent($componentID)
    {
        $this->cbLoader();

        $this->denyUnlessSuperadmin('Edit Component', 'Statistic');

        $component_row = CmsStatisticComponent::query()->forComponentId($componentID)->first();

        $config = json_decode($component_row->config);

        $command = 'configuration';

        return view('crudbooster::statistic_builder.components.'.$component_row->component_name, compact('command', 'componentID', 'config'));
    }

    public function postSaveComponent()
    {
        $this->denyUnlessSuperadmin('Save Component', 'Statistic');

        CmsStatisticComponent::query()->forComponentId(Request::get('componentid'))->update([
            'name' => Request::get('name'),
            'config' => json_encode(Request::get('config')),
        ]);

        return response()->json(['status' => true]);
    }

    public function getDeleteComponent($id)
    {
        $this->denyUnlessSuperadmin('Delete Component', 'Statistic');

        CmsStatisticComponent::query()->forComponentId($id)->delete();

        return response()->json(['status' => true]);
    }

    public function hook_before_add(&$arr)
    {
        //Your code here
        $arr['slug'] = str_slug($arr['name']);
    }
}
