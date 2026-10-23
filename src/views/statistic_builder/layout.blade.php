<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', App::getLocale()) }}" dir="{{ in_array(App::getLocale(), ['ar', 'fa']) ? 'rtl' : 'ltr' }}" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <title>{{ ($page_title)?Session::get('appname').': '.strip_tags($page_title):"Admin Area" }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    <meta name='generator' content='CRUDBooster.com'/>
    <meta name='robots' content='noindex,nofollow'/>
    <link rel="shortcut icon"
          href="{{ CRUDBooster::getSetting('favicon')?asset(CRUDBooster::getSetting('favicon')):asset('vendor/crudbooster/assets/logo_crudbooster.png') }}">
    <meta content='width=device-width, initial-scale=1' name='viewport'>

    <!-- AdminLTE 4, Font Awesome and the Bootstrap 3 compatibility layer -->
    @include('crudbooster::partials.theme_styles')
    <!-- Ionicons -->
    <link href="{{asset("vendor/crudbooster/ionic/css/ionicons.min.css")}}" rel="stylesheet" type="text/css"/>
    <link rel='stylesheet' href='{{asset("vendor/crudbooster/assets/css/main.css") }}'/>

    <!-- REQUIRED JS SCRIPTS: jQuery, Bootstrap 5 and the compatibility shim -->
    @include('crudbooster::partials.theme_scripts')

    <!--SWEET ALERT-->
    <script src="{{asset('vendor/crudbooster/assets/sweetalert/dist/sweetalert.min.js')}}"></script>
    <link rel="stylesheet" type="text/css" href="{{asset('vendor/crudbooster/assets/sweetalert/dist/sweetalert.css')}}">

    <style type="text/css">
        /* The widget panel that slides in from the right (AdminLTE 2's control sidebar). */
        .control-sidebar {
            position: fixed;
            top: 3.5rem;
            right: 0;
            bottom: 0;
            z-index: 1040;
            width: 230px;
            overflow-y: auto;
            color: #b8c7ce;
            background: #222d32;
            transform: translateX(100%);
            transition: transform .3s ease-in-out;
        }

        /* After a drop the builder puts a fresh copy of the panel inside the panel; lay that copy out in place. */
        .control-sidebar .control-sidebar {
            position: static;
            width: auto;
            transform: none;
            box-shadow: none;
        }

        .control-sidebar.control-sidebar-open {
            transform: none;
            box-shadow: -2px 0 8px rgba(0, 0, 0, .25);
        }

        .app-header .navbar-nav > .nav-item > .nav-link {
            color: var(--cb-skin-text);
        }
    </style>

    @stack('head')
</head>
<body class="{{ Session::get('theme_color') ?: 'skin-blue' }} layout-top-nav bg-body-tertiary">
<div id='app' class="app-wrapper wrapper">

    <nav class="app-header main-header navbar navbar-expand-md bg-body">
        <div class="container">
            <a href="{{url(config('crudbooster.ADMIN_PATH'))}}" title='{{Session::get('appname')}}'
               class="navbar-brand fw-semibold" style="color: var(--cb-skin-text)">{{CRUDBooster::getSetting('appname')}}</a>
            <button type="button" class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#navbar-collapse"
                    aria-controls="navbar-collapse" aria-expanded="false" aria-label="Toggle navigation">
                <i class="fa fa-bars" style="color: var(--cb-skin-text)"></i>
            </button>

            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse" id="navbar-collapse">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class='nav-link btn-save-statistic' href="#" title='Auto Save Status'><i class='fa fa-save'></i> Auto Save Ready</a></li>
                </ul>
                <!-- Navbar Right Menu -->
                <ul class="navbar-nav navbar-custom-menu">
                    <li class="nav-item"><a href="#" class='nav-link btn-show-sidebar' data-toggle="control-sidebar"><i class='fa fa-bars'></i> Add Widget</a></li>

                    <li class="nav-item"><a href="{{CRUDBooster::mainpath()}}" class="nav-link"><i class='fa fa-sign-out'></i> Exit</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Content Wrapper. Contains page content -->
    <main class="app-main content-wrapper">

        <!-- Main content -->
        <div id='content_section' class="app-content content pt-3">
          <div class="container">
            <!-- Your Page Content Here -->
            @yield('content')
          </div>
        </div><!-- /.content -->
    </main><!-- /.content-wrapper -->

    <!-- The Right Sidebar -->
    <aside class="control-sidebar control-sidebar-dark" data-bs-theme="dark">
        <!-- Content of the sidebar goes here -->
        <ul>
            <li class='connectedSortable' title='Drag To Main Area'>
                <div id='btn-smallbox' class='button-widget-area'>
                    <a href="#" data-component='smallbox' class='btn-add-widget add-small-box'>
                        <img src='{{asset("vendor/crudbooster/assets/statistic_builder/smallbox.png")}}'/>
                        <div class='title'>Small Box</div>
                    </a>
                </div>
            </li>
            <li class='connectedSortable' title='Drag To Main Area'>
                <div id='btn-table' class='button-widget-area'>
                    <a href="#" data-component='table' class='btn-add-widget add-table'>
                        <img src='{{asset("vendor/crudbooster/assets/statistic_builder/table.png")}}'/>
                        <div class='title'>Table</div>
                    </a>
                </div>
            </li>
            <li class='connectedSortable' title='Drag To Main Area'>
                <div id='btn-chartarea' class='button-widget-area'>
                    <a href="#" data-component='chartarea' class='btn-add-widget add-chart-area'>
                        <img src='{{asset("vendor/crudbooster/assets/statistic_builder/chart_area.png")}}'/>
                        <div class='title'>Chart Area</div>
                    </a>
                </div>
            </li>
            <li class='connectedSortable' title='Drag To Main Area'>
                <div id='btn-panelarea' class='button-widget-area'>
                    <a href="#" data-component='panelarea' class='btn-add-widget add-panel-area'>
                        <img src='{{asset("vendor/crudbooster/assets/statistic_builder/panel.png")}}'/>
                        <div class='title'>Panel Area</div>
                    </a>
                </div>
            </li>

            <li class='connectedSortable' title='Drag To Main Area'>
                <div id='btn-panelcustom' class='button-widget-area'>
                    <a href="#" data-component='panelcustom' class='btn-add-widget add-panel-custom'>
                        <img src='{{asset("vendor/crudbooster/assets/statistic_builder/panel.png")}}'/>
                        <div class='title'>Panel Custom</div>
                    </a>
                </div>
            </li>

            <li class='connectedSortable' title='Drag To Main Area'>
                <div id='btn-chartarea' class='button-widget-area'>
                    <a href="#" data-component='chartline' class='btn-add-widget add-chart-line'>
                        <img src='{{asset("vendor/crudbooster/assets/statistic_builder/chart_line.png")}}'/>
                        <div class='title'>Chart Line</div>
                    </a>
                </div>
            </li>

            <li class='connectedSortable' title='Drag To Main Area'>
                <div id='btn-chartarea' class='button-widget-area'>
                    <a href="#" data-component='chartbar' class='btn-add-widget add-chart-bar'>
                        <img src='{{asset("vendor/crudbooster/assets/statistic_builder/chart_bar.png")}}'/>
                        <div class='title'>Chart Bar</div>
                    </a>
                </div>
            </li>
        </ul>
    </aside>
    <!-- Footer -->
    <footer class="app-footer main-footer">
        <!-- To the right -->
        <div class="float-end d-none d-sm-inline">
            Powered By {{Session::get('appname')}}
        </div>
        <!-- Default to the left -->
        <strong>Copyright &copy; <?php echo date('Y') ?>. All rights reserved.</strong>
    </footer>

</div><!-- ./wrapper -->

<script type="text/javascript">
    // Open and close the widget panel ("Add Widget").
    // Bound on the button itself: the builder stops this click from reaching the document.
    $(function () {
        $('[data-toggle="control-sidebar"]').on('click', function (e) {
            e.preventDefault();
            $('.control-sidebar').toggleClass('control-sidebar-open');
        });
    });
</script>

@stack('bottom')
</body>
</html>