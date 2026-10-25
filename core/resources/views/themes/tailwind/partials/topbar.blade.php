@php
    $cbUnread = \crocodicstudio\crudbooster\models\CmsNotification::query()->visibleTo(CRUDBooster::myId())->unread()->count();
    $cbName = (string) CRUDBooster::myName();
@endphp
<div class="sticky top-0 z-20 flex h-16 shrink-0 items-center gap-3 border-b border-gray-200 bg-white/80 px-4 backdrop-blur sm:px-6 lg:px-8 dark:border-white/10 dark:bg-gray-900/80">
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
                <span class="absolute -top-0.5 -right-0.5 flex min-w-4 items-center justify-center rounded-full bg-red-600 px-1 text-[10px] font-semibold text-white">{{ $cbUnread > 99 ? '99+' : $cbUnread }}</span>
            @endif
        </a>
    @endif

    <details class="relative">
        <summary class="flex cursor-pointer list-none items-center gap-2 rounded-lg p-1 pr-2 hover:bg-gray-100 dark:hover:bg-white/10 [&::-webkit-details-marker]:hidden">
            @if(CRUDBooster::myPhoto())
                <img src="{{ asset(CRUDBooster::myPhoto()) }}" alt="" class="size-8 rounded-full object-cover">
            @else
                <span class="flex size-8 items-center justify-center rounded-full bg-brand-100 text-xs font-semibold text-brand-700 dark:bg-brand-500/20 dark:text-brand-200" aria-hidden="true">{{ mb_strtoupper(mb_substr($cbName, 0, 2)) }}</span>
            @endif
            <span class="hidden text-sm font-medium sm:block">{{ $cbName }}</span>
            <i class="fa fa-angle-down text-gray-400" aria-hidden="true"></i>
        </summary>
        <div class="absolute right-0 z-30 mt-2 w-56 overflow-hidden rounded-lg bg-white py-1 shadow-lg ring-1 ring-black/5 dark:bg-gray-800 dark:ring-white/10">
            <div class="border-b border-gray-100 px-4 py-2 text-xs text-gray-500 dark:border-white/10 dark:text-gray-400">{{ CRUDBooster::myPrivilegeName() }}</div>
            @if(Route::has('AdminCmsUsersControllerGetProfile'))
                <a href="{{ route('AdminCmsUsersControllerGetProfile') }}" class="block px-4 py-2 text-sm hover:bg-gray-50 dark:hover:bg-white/5"><i class="fa fa-user w-5" aria-hidden="true"></i> {{ cbLang('label_button_profile') }}</a>
            @endif
            <a href="{{ route('getLockScreen') }}" class="block px-4 py-2 text-sm hover:bg-gray-50 dark:hover:bg-white/5"><i class="fa fa-key w-5" aria-hidden="true"></i> {{ __('Lock screen') }}</a>
            <a href="{{ route('getLogout') }}" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-50 dark:text-red-400 dark:hover:bg-white/5" id="cb-logout"
               data-cb-confirm="{{ cbLang('alert_want_to_logout') }}" data-cb-confirm-label="{{ cbLang('button_logout') }}">
                <i class="fa fa-sign-out w-5" aria-hidden="true"></i> {{ cbLang('button_logout') }}
            </a>
        </div>
    </details>
</div>
