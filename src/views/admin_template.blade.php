<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', App::getLocale()) }}" dir="{{ in_array(App::getLocale(), ['ar', 'fa']) ? 'rtl' : 'ltr' }}" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <title>{{ ($page_title)?get_setting('appname').': '.strip_tags($page_title):"Admin Area" }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    <meta name='generator' content='CRUDBooster {{ \crocodicstudio\crudbooster\commands\CrudboosterVersionCommand::$version }}'/>
    <meta name='robots' content='noindex,nofollow'/>
    <link rel="shortcut icon"
          href="{{ CRUDBooster::getSetting('favicon')?asset(CRUDBooster::getSetting('favicon')):asset('vendor/crudbooster/assets/logo_crudbooster.png') }}">
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <!-- AdminLTE 4.10 (bundles Bootstrap 5.3) -->
    @if (in_array(App::getLocale(), ['ar', 'fa']))
        <link href="{{ asset("vendor/crudbooster/assets/adminlte4/css/adminlte.rtl.min.css") }}" rel="stylesheet" type="text/css"/>
    @else
        <link href="{{ asset("vendor/crudbooster/assets/adminlte4/css/adminlte.min.css") }}" rel="stylesheet" type="text/css"/>
    @endif
    <!-- Font Awesome 4.7 Icons: module and menu icons are stored as "fa fa-*" -->
    <link href="{{asset("vendor/crudbooster/assets/adminlte/font-awesome/css")}}/font-awesome.min.css" rel="stylesheet" type="text/css"/>
    <!-- Ionicons -->
    <link href="{{asset("vendor/crudbooster/ionic/css/ionicons.min.css")}}" rel="stylesheet" type="text/css"/>
    <!-- Bootstrap 3 / AdminLTE 2 markup support for existing views -->
    <link href="{{ asset("vendor/crudbooster/assets/css/bs3-compat.css") }}" rel="stylesheet" type="text/css"/>

    <!-- support rtl-->
    @if (in_array(App::getLocale(), ['ar', 'fa']))
        <link href="{{ asset("vendor/crudbooster/assets/rtl.css")}}" rel="stylesheet" type="text/css"/>
    @endif

    <link rel='stylesheet' href='{{asset("vendor/crudbooster/assets/css/main.css") }}'/>

    <!-- load css -->
    <style type="text/css">
        @if($style_css)
            {!! $style_css !!}
        @endif
    </style>
    @if($load_css)
        @foreach($load_css as $css)
            <link href="{{$css}}" rel="stylesheet" type="text/css"/>
        @endforeach
    @endif

    <style type="text/css">
        .btn-group-action .btn-action {
            cursor: default
        }

        #box-header-module {
            box-shadow: 10px 10px 10px #dddddd;
        }

        .sub-module-tab li {
            background: #F9F9F9;
            cursor: pointer;
        }

        .sub-module-tab li.active {
            background: #ffffff;
            box-shadow: 0px -5px 10px #cccccc
        }

        .sub-module-tab > li > a,
        .sub-module-tab > li.active > a,
        .sub-module-tab > li > a:hover {
            border: none;
        }

        .breadcrumb {
            margin: 0 0 0 0;
            padding: 0 0 0 0;
        }

        .form-group > label:first-child {
            display: block
        }

        #table_dashboard.table-bordered, #table_dashboard.table-bordered thead tr th, #table_dashboard.table-bordered tbody tr td {
            border: 1px solid #bbbbbb !important;
        }
    </style>

    @stack('head')
</head>
@php
    // The AdminLTE 2 skin chosen per privilege (skin-blue, skin-red-light, ...) still picks the
    // header colour; a "-light" skin gets AdminLTE 4's light sidebar, the others the dark one.
    $cbSkin = Session::get('theme_color') ?: 'skin-blue';
@endphp
<body class="layout-fixed sidebar-expand-lg bg-body-tertiary {{ $cbSkin }} {{ config('crudbooster.ADMIN_LAYOUT') }} {{($sidebar_mode)?:''}}">
<div id='app' class="app-wrapper wrapper">

    <!-- Header -->
@include('crudbooster::header')

<!-- Sidebar -->
@include('crudbooster::sidebar')

<!-- Content Wrapper. Contains page content -->
    <main class="app-main content-wrapper">

        <div class="app-content-header content-header">
          <div class="container-fluid">
            <?php
            $module = CRUDBooster::getCurrentModule();
            ?>
            @if($module)
              <div class="row align-items-center g-2">
                <div class="col-md">
                <h1 class="cb-page-title">
                    <!--Now you can define $page_icon alongside $page_tite for custom forms to follow CRUDBooster theme style -->
                    <i class='{!! ($page_icon)?:$module->icon !!}'></i> {!! ucwords(($page_title)?:$module->name) !!} &nbsp;&nbsp;

                    <!--START BUTTON -->

                    @if(CRUDBooster::getCurrentMethod() == 'getIndex')
                        @if($button_show)
                            <a href="{{ CRUDBooster::mainpath().'?'.http_build_query(Request::all()) }}" id='btn_show_data' class="btn btn-sm btn-primary"
                               title="{{cbLang('action_show_data')}}">
                                <i class="fa fa-table"></i> {{cbLang('action_show_data')}}
                            </a>
                        @endif

                        @if($button_add && CRUDBooster::isCreate())
                            <a href="{{ CRUDBooster::mainpath('add').'?return_url='.urlencode(Request::fullUrl()).'&parent_id='.g('parent_id').'&parent_field='.$parent_field }}"
                               id='btn_add_new_data' class="btn btn-sm btn-success" title="{{cbLang('action_add_data')}}">
                                <i class="fa fa-plus-circle"></i> {{cbLang('action_add_data')}}
                            </a>
                        @endif
                    @endif


                    @if($button_export && CRUDBooster::getCurrentMethod() == 'getIndex')
                        <a href="javascript:void(0)" id='btn_export_data' data-url-parameter='{{$build_query}}' title='Export Data'
                           class="btn btn-sm btn-primary btn-export-data">
                            <i class="fa fa-upload"></i> {{cbLang("button_export")}}
                        </a>
                    @endif

                    @if($button_import && CRUDBooster::getCurrentMethod() == 'getIndex')
                        <a href="{{ CRUDBooster::mainpath('import-data') }}" id='btn_import_data' data-url-parameter='{{$build_query}}' title='Import Data'
                           class="btn btn-sm btn-primary btn-import-data">
                            <i class="fa fa-download"></i> {{cbLang("button_import")}}
                        </a>
                    @endif

                <!--ADD ACTIon-->
                    @if(!empty($index_button))

                        @foreach($index_button as $ib)
                            <a href='{{$ib["url"]}}' id='{{str_slug($ib["label"])}}' class='btn {{($ib['color'])?'btn-'.$ib['color']:'btn-primary'}} btn-sm'
                               @if($ib['onClick']) onClick='return {{$ib["onClick"]}}' @endif
                               @if($ib['onMouseOver']) onMouseOver='return {{$ib["onMouseOver"]}}' @endif
                               @if($ib['onMouseOut']) onMouseOut='return {{$ib["onMouseOut"]}}' @endif
                               @if($ib['onKeyDown']) onKeyDown='return {{$ib["onKeyDown"]}}' @endif
                               @if($ib['onLoad']) onLoad='return {{$ib["onLoad"]}}' @endif
                            >
                                <i class='{{$ib["icon"]}}'></i> {{$ib["label"]}}
                            </a>
                    @endforeach
                @endif
                <!-- END BUTTON -->
                </h1>
                </div>

                <div class="col-md-auto">
                <ol class="breadcrumb float-md-end">
                    <li class="breadcrumb-item"><a href="{{CRUDBooster::adminPath()}}"><i class="fa fa-dashboard"></i> {{ cbLang('home') }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{$module->name}}</li>
                </ol>
                </div>
              </div>
            @else
                <h1 class="cb-page-title">{{Session::get('appname')}}
                    <small class="text-body-secondary"> {{ cbLang('text_dashboard') }} </small>
                </h1>
            @endif
          </div>
        </div>


        <!-- Main content -->
        <div id='content_section' class="app-content content">
          <div class="container-fluid">

            @if(@$alerts)
                @foreach(@$alerts as $alert)
                    <div class='callout callout-{{$alert["type"]}}'>
                        {!! $alert['message'] !!}
                    </div>
                @endforeach
            @endif


            @if (Session::get('message')!='')
                <div class='alert alert-{{ Session::get("message_type") }} alert-dismissible fade show' role="alert">
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    <h4><i class="icon fa fa-info"></i> {{ cbLang("alert_".Session::get("message_type")) }}</h4>
                    {!!Session::get('message')!!}
                </div>
            @endif



        <!-- Your Page Content Here -->
            @yield('content')
          </div>
        </div><!-- /.content -->
    </main><!-- /.content-wrapper -->

    <!-- Footer -->
    @include('crudbooster::footer')

</div><!-- ./wrapper -->


@include('crudbooster::admin_template_plugins')

<!-- load js -->
@if($load_js)
    @foreach($load_js as $js)
        <script src="{{$js}}"></script>
    @endforeach
@endif
<script type="text/javascript">
    var site_url = "{{url('/')}}";
    @if($script_js)
        {!! $script_js !!}
    @endif
</script>

@stack('bottom')

<!-- Optionally, you can add Slimscroll and FastClick plugins.
      Both of these plugins are recommended to enhance the
      user experience -->
</body>
</html>
