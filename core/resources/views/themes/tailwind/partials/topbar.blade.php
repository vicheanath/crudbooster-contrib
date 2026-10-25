@php
    $cbUnread = \crocodicstudio\crudbooster\models\CmsNotification::query()->visibleTo(CRUDBooster::myId())->unread()->count();
    $cbName = (string) CRUDBooster::myName();
    $cbMenuItem = 'hover:bg-accent hover:text-accent-foreground flex items-center gap-2 rounded-md px-1.5 py-1 text-sm';
@endphp
<header class="bg-background/80 sticky top-0 z-20 flex h-14 shrink-0 items-center gap-2 border-b px-4 backdrop-blur sm:px-6 lg:px-8">
    <button type="button" class="cb-icon-btn lg:hidden" data-cb-sidebar-toggle aria-controls="cb-sidebar" aria-label="{{ __('Open menu') }}">
        <i class="fa fa-bars" aria-hidden="true"></i>
    </button>
    <div class="flex-1"></div>

    <div data-cb-component="ThemeToggle"></div>

    @if(Route::has('NotificationsControllerGetIndex'))
        <a href="{{ route('NotificationsControllerGetIndex') }}" class="cb-icon-btn relative" title="{{ cbLang('text_view_all_notification') }}">
            <i class="fa fa-bell-o" aria-hidden="true"></i>
            <span class="sr-only">Notifications</span>
            @if($cbUnread)
                <span class="bg-destructive absolute -top-0.5 -right-0.5 flex h-4 min-w-4 items-center justify-center rounded-full px-1 text-[10px] font-semibold text-white">{{ $cbUnread > 99 ? '99+' : $cbUnread }}</span>
            @endif
        </a>
    @endif

    <details class="relative">
        <summary class="hover:bg-muted flex cursor-pointer list-none items-center gap-2 rounded-lg p-1 pr-2 [&::-webkit-details-marker]:hidden">
            @if(CRUDBooster::myPhoto())
                <img src="{{ asset(CRUDBooster::myPhoto()) }}" alt="" class="size-7 rounded-full object-cover">
            @else
                <span class="bg-muted flex size-7 items-center justify-center rounded-full text-xs font-medium" aria-hidden="true">{{ mb_strtoupper(mb_substr($cbName, 0, 2)) }}</span>
            @endif
            <span class="hidden text-sm font-medium sm:block">{{ $cbName }}</span>
            <i class="fa fa-angle-down text-muted-foreground" aria-hidden="true"></i>
        </summary>
        <div class="bg-popover text-popover-foreground ring-foreground/10 absolute right-0 z-30 mt-1 w-56 rounded-lg p-1 shadow-md ring-1">
            <div class="text-muted-foreground px-1.5 py-1 text-xs font-medium">{{ CRUDBooster::myPrivilegeName() }}</div>
            <div class="bg-border -mx-1 my-1 h-px"></div>
            @if(Route::has('AdminCmsUsersControllerGetProfile'))
                <a href="{{ route('AdminCmsUsersControllerGetProfile') }}" class="{{ $cbMenuItem }}"><i class="fa fa-user w-4 text-center" aria-hidden="true"></i> {{ cbLang('label_button_profile') }}</a>
            @endif
            <a href="{{ route('getLockScreen') }}" class="{{ $cbMenuItem }}"><i class="fa fa-key w-4 text-center" aria-hidden="true"></i> {{ __('Lock screen') }}</a>
            <div class="bg-border -mx-1 my-1 h-px"></div>
            <a href="{{ route('getLogout') }}" class="{{ $cbMenuItem }} text-destructive hover:bg-destructive/10 hover:text-destructive" id="cb-logout"
               data-cb-confirm="{{ cbLang('alert_want_to_logout') }}" data-cb-confirm-label="{{ cbLang('button_logout') }}">
                <i class="fa fa-sign-out w-4 text-center" aria-hidden="true"></i> {{ cbLang('button_logout') }}
            </a>
        </div>
    </details>
</header>
