{{--
    The Tailwind theme's frame for resource and page screens.
    Sections: content, header_actions; variables: $page_title, $page_icon, $breadcrumbs ([label => url|null]).
--}}
@php
    $panel = app(\CrudBooster\Panel\Panel::class);
    $theme = $panel->getTheme();
    $appName = CRUDBooster::getSetting('appname') ?: 'CRUDBooster';
    $favicon = CRUDBooster::getSetting('favicon');
    $locale = str_replace('_', '-', App::getLocale());
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ in_array(App::getLocale(), ['ar', 'fa']) ? 'rtl' : 'ltr' }}" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex,nofollow">
    <title>{{ !empty($page_title) ? $appName.': '.strip_tags($page_title) : $appName }}</title>
    <script>
        {{-- Before the first paint, so a dark page never flashes white. --}}
        (function () {
            try {
                var mode = localStorage.getItem('cb-theme') || 'system';
                var dark = mode === 'dark' || (mode === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
                document.documentElement.classList.toggle('dark', dark);
            } catch (e) {}
        })();
    </script>
    <link rel="icon" href="{{ $favicon ? asset($favicon) : asset('vendor/crudbooster/assets/logo_crudbooster.png') }}">
    <link rel="stylesheet" href="{{ asset('vendor/crudbooster/assets/adminlte/font-awesome/css/font-awesome.min.css') }}">
    {{ $theme->getHeadHtml() }}
    @php
        // Strings for the React components (see core/resources/js/lib/lang.js).
        $cbLang = [
        'search' => __('Search…'),
        'noResults' => __('No results'),
        'selectOption' => __('Select an option'),
        'clear' => __('Clear'),
        'remove' => __('Remove'),
        'undo' => __('Undo'),
        'selected' => __(':count selected'),
        'bulkActions' => __('Bulk actions'),
        'confirm' => __('Confirm'),
        'cancel' => __('Cancel'),
        'areYouSure' => __('Are you sure?'),
        'showPassword' => __('Show password'),
        'hidePassword' => __('Hide password'),
        'dropFile' => __('Drop a file here or click to browse'),
        'replaceFile' => __('Choose another file'),
        'fileTooLarge' => __('The file is larger than :size KB.'),
        'willBeRemoved' => __('Will be removed when you save.'),
        'themeLight' => __('Light'),
        'themeDark' => __('Dark'),
        'themeSystem' => __('System'),
    ];
    @endphp
    <script type="application/json" id="cb-panel-config">@json(['lang' => $cbLang])</script>
    {{ $panel->renderHooks('head') }}
    @stack('head')
</head>
<body class="bg-background text-foreground h-full font-sans antialiased">
@if(method_exists($theme, 'assetsNeedPublishing') && $theme->assetsNeedPublishing())
    <div class="bg-amber-500/15 px-4 py-2 text-center text-sm text-amber-800 dark:text-amber-300" role="alert" id="cb-assets-warning">
        {{ __('The panel\'s assets are missing or out of date. Run') }}
        <code class="rounded bg-amber-500/20 px-1 font-mono">php artisan vendor:publish --tag=cb_asset --force</code>
    </div>
@endif

<div class="fixed inset-0 z-30 hidden bg-black/30 supports-backdrop-filter:backdrop-blur-xs lg:hidden" data-cb-sidebar-backdrop data-cb-sidebar-close></div>
@include('cb::partials.sidebar')

<div class="flex min-h-full flex-col lg:pl-64">
    @include('cb::partials.topbar')

    <main class="flex-1 px-4 py-6 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl">
            <header class="mb-6">
                @if(!empty($breadcrumbs))
                    <nav aria-label="Breadcrumb" class="mb-2">
                        <ol class="text-muted-foreground flex flex-wrap items-center gap-1.5 text-sm">
                            <li><a href="{{ CRUDBooster::adminPath() }}" class="hover:text-foreground transition-colors"><i class="fa fa-home" aria-hidden="true"></i><span class="sr-only">{{ cbLang('home') }}</span></a></li>
                            @foreach($breadcrumbs as $label => $url)
                                <li aria-hidden="true"><i class="fa fa-angle-right opacity-60"></i></li>
                                <li>
                                    @if($url)
                                        <a href="{{ $url }}" class="hover:text-foreground transition-colors">{{ $label }}</a>
                                    @else
                                        <span aria-current="page" class="text-foreground">{{ $label }}</span>
                                    @endif
                                </li>
                            @endforeach
                        </ol>
                    </nav>
                @endif
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h1 class="cb-page-title flex items-center gap-3 text-2xl font-semibold tracking-tight">
                        @if(!empty($page_icon))
                            <span class="bg-muted text-foreground flex size-9 items-center justify-center rounded-lg text-base"><i class="{{ $page_icon }}" aria-hidden="true"></i></span>
                        @endif
                        {{ $page_title ?? '' }}
                    </h1>
                    <div class="flex flex-wrap items-center gap-2">@yield('header_actions')</div>
                </div>
            </header>

            @foreach($alerts ?? [] as $alert)
                <x-cb::alert :type="$alert['type'] ?? 'info'" class="mb-4">{!! $alert['message'] !!}</x-cb::alert>
            @endforeach

            @if(Session::get('message') != '')
                <x-cb::alert :type="Session::get('message_type') ?: 'info'" class="mb-4" dismissible id="cb-flash">{!! Session::get('message') !!}</x-cb::alert>
            @endif

            @yield('content')
        </div>
    </main>

    <footer class="text-muted-foreground border-t px-4 py-4 text-sm sm:px-6 lg:px-8">
        <div class="mx-auto flex max-w-7xl flex-wrap justify-between gap-2">
            <span>&copy; {{ date('Y') }} {{ $appName }}</span>
            <span>{{ cbLang('powered_by') }} CRUDBooster</span>
        </div>
    </footer>
</div>

@stack('bottom')
{{ $panel->renderHooks('body.end') }}
</body>
</html>
