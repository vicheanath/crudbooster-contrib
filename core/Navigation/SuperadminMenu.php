<?php

namespace CrudBooster\Navigation;

use crocodicstudio\crudbooster\contracts\Settings\SettingsRepositoryInterface;
use Illuminate\Support\Facades\Request;

/**
 * The superadmin tools in the sidebar (privileges, users, menus, settings, generators, logs),
 * shared by the AdminLTE and Tailwind layouts. Each item is a link (route, icon, label, active)
 * or a group (icon, label, children).
 */
class SuperadminMenu
{
    public static function items(): array
    {
        $adminPath = config('crudbooster.ADMIN_PATH');
        $is = fn (string $pattern) => Request::is($adminPath.'/'.$pattern);

        $settingLinks = [['route' => route('SettingsControllerGetAdd'), 'icon' => 'fa fa-plus', 'label' => cbLang('Add_New_Setting'), 'active' => $is('settings/add*')]];
        foreach (app(SettingsRepositoryInterface::class)->groups() as $group) {
            $settingLinks[] = ['route' => route('SettingsControllerGetShow').'?group='.urlencode($group).'&m=0', 'icon' => 'fa fa-wrench', 'label' => $group, 'active' => $group == Request::get('group')];
        }

        return [
            ['icon' => 'fa fa-key', 'label' => cbLang('Privileges_Roles'), 'children' => [
                ['route' => route('PrivilegesControllerGetAdd'), 'icon' => 'fa fa-plus', 'label' => cbLang('Add_New_Privilege'), 'active' => $is('privileges/add*')],
                ['route' => route('PrivilegesControllerGetIndex'), 'icon' => 'fa fa-bars', 'label' => cbLang('List_Privilege'), 'active' => $is('privileges')],
            ]],
            ['icon' => 'fa fa-users', 'label' => cbLang('Users_Management'), 'children' => [
                ['route' => route('AdminCmsUsersControllerGetAdd'), 'icon' => 'fa fa-plus', 'label' => cbLang('add_user'), 'active' => $is('users/add*')],
                ['route' => route('AdminCmsUsersControllerGetIndex'), 'icon' => 'fa fa-bars', 'label' => cbLang('List_users'), 'active' => $is('users')],
            ]],
            ['route' => route('MenusControllerGetIndex'), 'icon' => 'fa fa-bars', 'label' => cbLang('Menu_Management'), 'active' => $is('menu_management*')],
            ['icon' => 'fa fa-wrench', 'label' => cbLang('settings'), 'children' => $settingLinks],
            ['icon' => 'fa fa-th', 'label' => cbLang('Module_Generator'), 'children' => [
                ['route' => route('ModulsControllerGetStep1'), 'icon' => 'fa fa-plus', 'label' => cbLang('Add_New_Module'), 'active' => $is('module_generator/step1')],
                ['route' => route('ModulsControllerGetIndex'), 'icon' => 'fa fa-bars', 'label' => cbLang('List_Module'), 'active' => $is('module_generator')],
            ]],
            ['icon' => 'fa fa-dashboard', 'label' => cbLang('Statistic_Builder'), 'children' => [
                ['route' => route('StatisticBuilderControllerGetAdd'), 'icon' => 'fa fa-plus', 'label' => cbLang('Add_New_Statistic'), 'active' => $is('statistic_builder/add')],
                ['route' => route('StatisticBuilderControllerGetIndex'), 'icon' => 'fa fa-bars', 'label' => cbLang('List_Statistic'), 'active' => $is('statistic_builder')],
            ]],
            ['icon' => 'fa fa-fire', 'label' => cbLang('API_Generator'), 'children' => [
                ['route' => route('ApiCustomControllerGetGenerator'), 'icon' => 'fa fa-plus', 'label' => cbLang('Add_New_API'), 'active' => $is('api_generator/generator*')],
                ['route' => route('ApiCustomControllerGetIndex'), 'icon' => 'fa fa-bars', 'label' => cbLang('list_API'), 'active' => $is('api_generator')],
                ['route' => route('ApiCustomControllerGetScreetKey'), 'icon' => 'fa fa-bars', 'label' => cbLang('Generate_Screet_Key'), 'active' => $is('api_generator/screet-key*')],
            ]],
            ['icon' => 'fa fa-envelope-o', 'label' => cbLang('Email_Templates'), 'children' => [
                ['route' => route('EmailTemplatesControllerGetAdd'), 'icon' => 'fa fa-plus', 'label' => cbLang('Add_New_Email'), 'active' => $is('email_templates/add*')],
                ['route' => route('EmailTemplatesControllerGetIndex'), 'icon' => 'fa fa-bars', 'label' => cbLang('List_Email_Template'), 'active' => $is('email_templates')],
            ]],
            ['route' => route('LogsControllerGetIndex'), 'icon' => 'fa fa-flag', 'label' => cbLang('Log_User_Access'), 'active' => $is('logs*')],
        ];
    }
}
