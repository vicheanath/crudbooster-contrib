<!-- Left side column. contains the sidebar -->
@php
    $cbSidebarTheme = Str::endsWith(Session::get('theme_color') ?: 'skin-blue', '-light') ? 'light' : 'dark';
    $cbAdminPath = config('crudbooster.ADMIN_PATH');
@endphp
<aside class="app-sidebar main-sidebar bg-body-secondary shadow" data-bs-theme="{{ $cbSidebarTheme }}">

    <!-- Logo -->
    <div class="sidebar-brand logo">
        <a href="{{url($cbAdminPath)}}" title='{{Session::get('appname')}}' class="brand-link">
            {{-- The favicon setting, or the app name's first letter, stays visible when the sidebar collapses to icons. --}}
            @if(CRUDBooster::getSetting('favicon'))
                <img src="{{ asset(CRUDBooster::getSetting('favicon')) }}" alt="" class="brand-image rounded-circle shadow-sm">
            @else
                <span class="brand-image cb-brand-mark" aria-hidden="true">{{ mb_strtoupper(mb_substr(trim((string) CRUDBooster::getSetting('appname')) ?: 'C', 0, 1)) }}</span>
            @endif
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

                {{-- Resources, pages and links registered with the panel (CrudBooster\Panel\Panel). --}}
                @include('cb::navigation')

                @if(CRUDBooster::isSuperadmin())
                    {{-- A link, or a group of links. Shared with the Tailwind theme's sidebar. --}}
                    @php $superadminMenu = \CrudBooster\Navigation\SuperadminMenu::items(); @endphp

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
