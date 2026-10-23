<!-- Left side column. contains the sidebar -->
@php
    $cbSidebarTheme = Str::endsWith(Session::get('theme_color') ?: 'skin-blue', '-light') ? 'light' : 'dark';
    $cbAdminPath = config('crudbooster.ADMIN_PATH');
@endphp
<aside class="app-sidebar main-sidebar bg-body-secondary shadow" data-bs-theme="{{ $cbSidebarTheme }}">

    <!-- Logo -->
    <div class="sidebar-brand logo">
        <a href="{{url($cbAdminPath)}}" title='{{Session::get('appname')}}' class="brand-link">
            <span class="brand-text">{{CRUDBooster::getSetting('appname')}}</span>
        </a>
    </div>

    <div class="sidebar-wrapper sidebar">

        <!-- Sidebar user panel (optional) -->
        <div class="user-panel d-flex align-items-center">
            <div class="image">
                @include('crudbooster::components.user_initials', ['class' => 'img-circle'])
            </div>
            <div class="info">
                <p>{{ CRUDBooster::myName() }}</p>
                <!-- Status -->
                <a href="#"><i class="fa fa-circle text-success"></i> {{ cbLang('online') }}</a>
            </div>
        </div>

        <nav class='main-menu mt-2'>

            <!-- Sidebar Menu -->
            <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="navigation" data-accordion="false">
                <li class="nav-header header">{{cbLang("menu_navigation")}}</li>
                <!-- Optionally, you can add icons to the links -->

                <?php $dashboard = CRUDBooster::sidebarDashboard();?>
                @if($dashboard)
                    <li data-id='{{$dashboard->id}}' class="nav-item">
                        <a href='{{CRUDBooster::adminPath()}}' class='nav-link {{ (Request::is($cbAdminPath)) ? 'active' : '' }} {{($dashboard->color)?"text-".$dashboard->color:""}}'>
                            <i class='nav-icon fa fa-dashboard'></i>
                            <p>{{cbLang("text_dashboard")}}</p>
                        </a>
                    </li>
                @endif

                @foreach(CRUDBooster::sidebarMenu() as $menu)
                    @php
                        $cbChildActive = [];
                        foreach ($menu->children ?? [] as $child) {
                            $cbChildActive[$child->id] = Request::is($child->url_path .= !Str::endsWith(Request::decodedPath(), $child->url_path) ? "/*" : "");
                        }
                        $cbMenuActive = Request::is($menu->url_path."*") || in_array(true, $cbChildActive, true);
                    @endphp
                    <li data-id='{{$menu->id}}' class='nav-item {{(!empty($menu->children))?"treeview":""}} {{ (!empty($menu->children) && $cbMenuActive)?"menu-open":""}}'>
                        <a href='{{ ($menu->is_broken)?"javascript:alert('".cbLang('controller_route_404')."')":$menu->url }}'
                           class='nav-link {{ $cbMenuActive ? "active" : "" }} {{($menu->color)?"text-".$menu->color:""}}'>
                            <i class='nav-icon {{$menu->icon}} {{($menu->color)?"text-".$menu->color:""}}'></i>
                            <p>
                                {{$menu->name}}
                                @if(!empty($menu->children))<i class="nav-arrow fa fa-angle-right"></i>@endif
                            </p>
                        </a>
                        @if(!empty($menu->children))
                            <ul class="nav nav-treeview treeview-menu">
                                @foreach($menu->children as $child)
                                    <li data-id='{{$child->id}}' class='nav-item'>
                                        <a href='{{ ($child->is_broken)?"javascript:alert('".cbLang('controller_route_404')."')":$child->url}}'
                                           class='nav-link {{ $cbChildActive[$child->id] ? "active" : "" }} {{($child->color)?"text-".$child->color:""}}'>
                                            <i class='nav-icon {{$child->icon}}'></i>
                                            <p>{{$child->name}}</p>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </li>
                @endforeach

                @if(CRUDBooster::isSuperadmin())
                    @php
                        $groupSetting = app(\crocodicstudio\crudbooster\contracts\Settings\SettingsRepositoryInterface::class)->groups();
                        $settingLinks = [['route' => route('SettingsControllerGetAdd'), 'icon' => 'fa fa-plus', 'label' => cbLang('Add_New_Setting'), 'active' => Request::is($cbAdminPath.'/settings/add*')]];
                        foreach ($groupSetting as $gs) {
                            $settingLinks[] = ['route' => route('SettingsControllerGetShow').'?group='.urlencode($gs).'&m=0', 'icon' => 'fa fa-wrench', 'label' => $gs, 'active' => $gs == Request::get('group')];
                        }

                        // Superadmin tools: a link, or a group of links.
                        $superadminMenu = [
                            ['icon' => 'fa fa-key', 'label' => cbLang('Privileges_Roles'), 'children' => [
                                ['route' => Route('PrivilegesControllerGetAdd'), 'icon' => 'fa fa-plus', 'label' => cbLang('Add_New_Privilege'), 'active' => Request::is($cbAdminPath.'/privileges/add*')],
                                ['route' => Route('PrivilegesControllerGetIndex'), 'icon' => 'fa fa-bars', 'label' => cbLang('List_Privilege'), 'active' => Request::is($cbAdminPath.'/privileges')],
                            ]],
                            ['icon' => 'fa fa-users', 'label' => cbLang('Users_Management'), 'children' => [
                                ['route' => Route('AdminCmsUsersControllerGetAdd'), 'icon' => 'fa fa-plus', 'label' => cbLang('add_user'), 'active' => Request::is($cbAdminPath.'/users/add*')],
                                ['route' => Route('AdminCmsUsersControllerGetIndex'), 'icon' => 'fa fa-bars', 'label' => cbLang('List_users'), 'active' => Request::is($cbAdminPath.'/users')],
                            ]],
                            ['route' => Route('MenusControllerGetIndex'), 'icon' => 'fa fa-bars', 'label' => cbLang('Menu_Management'), 'active' => Request::is($cbAdminPath.'/menu_management*')],
                            ['icon' => 'fa fa-wrench', 'label' => cbLang('settings'), 'children' => $settingLinks],
                            ['icon' => 'fa fa-th', 'label' => cbLang('Module_Generator'), 'children' => [
                                ['route' => Route('ModulsControllerGetStep1'), 'icon' => 'fa fa-plus', 'label' => cbLang('Add_New_Module'), 'active' => Request::is($cbAdminPath.'/module_generator/step1')],
                                ['route' => Route('ModulsControllerGetIndex'), 'icon' => 'fa fa-bars', 'label' => cbLang('List_Module'), 'active' => Request::is($cbAdminPath.'/module_generator')],
                            ]],
                            ['icon' => 'fa fa-dashboard', 'label' => cbLang('Statistic_Builder'), 'children' => [
                                ['route' => Route('StatisticBuilderControllerGetAdd'), 'icon' => 'fa fa-plus', 'label' => cbLang('Add_New_Statistic'), 'active' => Request::is($cbAdminPath.'/statistic_builder/add')],
                                ['route' => Route('StatisticBuilderControllerGetIndex'), 'icon' => 'fa fa-bars', 'label' => cbLang('List_Statistic'), 'active' => Request::is($cbAdminPath.'/statistic_builder')],
                            ]],
                            ['icon' => 'fa fa-fire', 'label' => cbLang('API_Generator'), 'children' => [
                                ['route' => Route('ApiCustomControllerGetGenerator'), 'icon' => 'fa fa-plus', 'label' => cbLang('Add_New_API'), 'active' => Request::is($cbAdminPath.'/api_generator/generator*')],
                                ['route' => Route('ApiCustomControllerGetIndex'), 'icon' => 'fa fa-bars', 'label' => cbLang('list_API'), 'active' => Request::is($cbAdminPath.'/api_generator')],
                                ['route' => Route('ApiCustomControllerGetScreetKey'), 'icon' => 'fa fa-bars', 'label' => cbLang('Generate_Screet_Key'), 'active' => Request::is($cbAdminPath.'/api_generator/screet-key*')],
                            ]],
                            ['icon' => 'fa fa-envelope-o', 'label' => cbLang('Email_Templates'), 'children' => [
                                ['route' => Route('EmailTemplatesControllerGetAdd'), 'icon' => 'fa fa-plus', 'label' => cbLang('Add_New_Email'), 'active' => Request::is($cbAdminPath.'/email_templates/add*')],
                                ['route' => Route('EmailTemplatesControllerGetIndex'), 'icon' => 'fa fa-bars', 'label' => cbLang('List_Email_Template'), 'active' => Request::is($cbAdminPath.'/email_templates')],
                            ]],
                            ['route' => Route('LogsControllerGetIndex'), 'icon' => 'fa fa-flag', 'label' => cbLang('Log_User_Access'), 'active' => Request::is($cbAdminPath.'/logs*')],
                        ];
                    @endphp

                    <li class="nav-header header">{{ cbLang('SUPERADMIN') }}</li>
                    @foreach($superadminMenu as $item)
                        @if(isset($item['children']))
                            @php $cbGroupOpen = collect($item['children'])->contains('active', true); @endphp
                            <li class='nav-item treeview {{ $cbGroupOpen ? 'menu-open' : '' }}'>
                                <a href='#' class='nav-link {{ $cbGroupOpen ? 'active' : '' }}'>
                                    <i class='nav-icon {{ $item['icon'] }}'></i>
                                    <p>{{ $item['label'] }} <i class="nav-arrow fa fa-angle-right"></i></p>
                                </a>
                                <ul class='nav nav-treeview treeview-menu'>
                                    @foreach($item['children'] as $link)
                                        <li class="nav-item">
                                            <a href='{{ $link['route'] }}' class='nav-link {{ $link['active'] ? 'active' : '' }}'>
                                                <i class='nav-icon {{ $link['icon'] }}'></i>
                                                <p>{{ $link['label'] }}</p>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </li>
                        @else
                            <li class="nav-item">
                                <a href='{{ $item['route'] }}' class='nav-link {{ $item['active'] ? 'active' : '' }}'>
                                    <i class='nav-icon {{ $item['icon'] }}'></i>
                                    <p>{{ $item['label'] }}</p>
                                </a>
                            </li>
                        @endif
                    @endforeach
                @endif

            </ul><!-- /.sidebar-menu -->

        </nav>

    </div>
    <!-- /.sidebar -->
</aside>
