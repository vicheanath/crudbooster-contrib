@php
    $cbAdminPath = config('crudbooster.ADMIN_PATH');
    $cbAppName = CRUDBooster::getSetting('appname') ?: 'CRUDBooster';
    $cbFavicon = CRUDBooster::getSetting('favicon');
    $cbIsActive = function ($url) {
        $path = trim((string) parse_url((string) $url, PHP_URL_PATH), '/');

        return $path !== '' && Request::is($path, $path.'/*');
    };
    $cbGroupLabel = 'text-sidebar-foreground/70 mb-1 flex h-8 items-center px-2 text-xs font-medium';
@endphp
<aside data-cb-sidebar id="cb-sidebar" class="bg-sidebar text-sidebar-foreground border-sidebar-border fixed inset-y-0 left-0 z-40 flex w-64 flex-col border-r" aria-label="{{ cbLang('menu_navigation') }}">
    <div class="flex h-14 shrink-0 items-center justify-between gap-2 px-3">
        <a href="{{ url($cbAdminPath) }}" class="hover:bg-sidebar-accent flex min-w-0 items-center gap-2 rounded-md p-1.5">
            @if($cbFavicon)
                <img src="{{ asset($cbFavicon) }}" alt="" class="size-8 rounded-lg">
            @else
                <span class="bg-sidebar-primary text-sidebar-primary-foreground flex size-8 shrink-0 items-center justify-center rounded-lg text-sm font-semibold" aria-hidden="true">{{ mb_strtoupper(mb_substr(trim($cbAppName), 0, 1)) }}</span>
            @endif
            <span class="truncate text-sm font-semibold">{{ $cbAppName }}</span>
        </a>
        <button type="button" class="cb-icon-btn lg:hidden" data-cb-sidebar-close aria-label="{{ __('Close menu') }}"><i class="fa fa-times" aria-hidden="true"></i></button>
    </div>

    <nav class="flex-1 space-y-4 overflow-y-auto px-2 pb-6">
        <ul class="space-y-0.5">
            @if($cbDashboard = CRUDBooster::sidebarDashboard())
                <li>
                    <a href="{{ CRUDBooster::adminPath() }}" class="cb-nav-link {{ Request::is($cbAdminPath) ? 'cb-nav-link-active' : '' }}">
                        <i class="fa fa-dashboard w-4 text-center" aria-hidden="true"></i> {{ cbLang('text_dashboard') }}
                    </a>
                </li>
            @endif

            {{-- Menus from Menu Management (classic modules, statistics, links). --}}
            @foreach(CRUDBooster::sidebarMenu() as $cbMenu)
                @if(!empty($cbMenu->children))
                    @php $cbOpen = collect($cbMenu->children)->contains(fn ($child) => $cbIsActive($child->url)); @endphp
                    <li>
                        <details class="group" @if($cbOpen) open @endif>
                            <summary class="cb-nav-link cursor-pointer list-none [&::-webkit-details-marker]:hidden">
                                <i class="{{ $cbMenu->icon }} w-4 text-center" aria-hidden="true"></i>
                                <span class="flex-1">{{ $cbMenu->name }}</span>
                                <i class="fa fa-angle-right opacity-60 transition-transform group-open:rotate-90" aria-hidden="true"></i>
                            </summary>
                            <ul class="border-sidebar-border mx-3.5 mt-0.5 space-y-0.5 border-l px-2.5 py-0.5">
                                @foreach($cbMenu->children as $cbChild)
                                    <li>
                                        <a href="{{ $cbChild->is_broken ? '#' : $cbChild->url }}" class="cb-nav-link {{ $cbIsActive($cbChild->url) ? 'cb-nav-link-active' : '' }}">
                                            <i class="{{ $cbChild->icon }} w-4 text-center" aria-hidden="true"></i> {{ $cbChild->name }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </details>
                    </li>
                @else
                    <li>
                        <a href="{{ $cbMenu->is_broken ? '#' : $cbMenu->url }}" class="cb-nav-link {{ $cbIsActive($cbMenu->url) ? 'cb-nav-link-active' : '' }}">
                            <i class="{{ $cbMenu->icon }} w-4 text-center" aria-hidden="true"></i> {{ $cbMenu->name }}
                        </a>
                    </li>
                @endif
            @endforeach
        </ul>

        @include('cb::partials.navigation')

        @if(CRUDBooster::isSuperadmin())
            <div>
                <h2 class="{{ $cbGroupLabel }}">{{ cbLang('SUPERADMIN') }}</h2>
                <ul class="space-y-0.5">
                    @foreach(\CrudBooster\Navigation\SuperadminMenu::items() as $cbItem)
                        @if(isset($cbItem['children']))
                            @php $cbOpen = collect($cbItem['children'])->contains('active', true); @endphp
                            <li>
                                <details class="group" @if($cbOpen) open @endif>
                                    <summary class="cb-nav-link cursor-pointer list-none [&::-webkit-details-marker]:hidden">
                                        <i class="{{ $cbItem['icon'] }} w-4 text-center" aria-hidden="true"></i>
                                        <span class="flex-1">{{ $cbItem['label'] }}</span>
                                        <i class="fa fa-angle-right opacity-60 transition-transform group-open:rotate-90" aria-hidden="true"></i>
                                    </summary>
                                    <ul class="border-sidebar-border mx-3.5 mt-0.5 space-y-0.5 border-l px-2.5 py-0.5">
                                        @foreach($cbItem['children'] as $cbLink)
                                            <li><a href="{{ $cbLink['route'] }}" class="cb-nav-link {{ $cbLink['active'] ? 'cb-nav-link-active' : '' }}">{{ $cbLink['label'] }}</a></li>
                                        @endforeach
                                    </ul>
                                </details>
                            </li>
                        @else
                            <li>
                                <a href="{{ $cbItem['route'] }}" class="cb-nav-link {{ $cbItem['active'] ? 'cb-nav-link-active' : '' }}">
                                    <i class="{{ $cbItem['icon'] }} w-4 text-center" aria-hidden="true"></i> {{ $cbItem['label'] }}
                                </a>
                            </li>
                        @endif
                    @endforeach
                </ul>
            </div>
        @endif
    </nav>
</aside>
