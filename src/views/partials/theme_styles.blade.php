{{-- AdminLTE 4 stylesheets for the pages that do not extend admin_template (login, lock screen, error pages, ...). --}}
@if (in_array(App::getLocale(), ['ar', 'fa']))
    <link href="{{ asset('vendor/crudbooster/assets/adminlte4/css/adminlte.rtl.min.css') }}" rel="stylesheet" type="text/css"/>
@else
    <link href="{{ asset('vendor/crudbooster/assets/adminlte4/css/adminlte.min.css') }}" rel="stylesheet" type="text/css"/>
@endif
<link href="{{ asset('vendor/crudbooster/assets/adminlte/font-awesome/css/font-awesome.min.css') }}" rel="stylesheet" type="text/css"/>
<link href="{{ asset('vendor/crudbooster/assets/css/bs3-compat.css') }}" rel="stylesheet" type="text/css"/>
@if (in_array(App::getLocale(), ['ar', 'fa']))
    <link href="{{ asset('vendor/crudbooster/assets/rtl.css') }}" rel="stylesheet" type="text/css"/>
@endif
