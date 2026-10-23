<!-- REQUIRED JS SCRIPTS -->

<!-- jQuery 3.7.1: the bundled date, select, upload and editor plugins are jQuery plugins -->
<script src="{{ asset ('vendor/crudbooster/assets/jquery/jquery-3.7.1.min.js') }}"></script>
<!-- jQuery Migrate restores the jQuery 1/2 APIs older plugins and custom scripts still call.
     Set jQuery.migrateMute = false in the console to see what still needs them. -->
<script>jQuery.migrateMute = true;</script>
<script src="{{ asset ('vendor/crudbooster/assets/jquery/jquery-migrate-3.6.0.min.js') }}"></script>

<!-- Bootstrap 5.3 JS (with Popper) -->
<script src="{{ asset ('vendor/crudbooster/assets/bootstrap5/js/bootstrap.bundle.min.js') }}" type="text/javascript"></script>
<!-- Bootstrap 3 data attributes, jQuery plugin calls and AdminLTE 2 widgets on Bootstrap 5 -->
<script src="{{ asset ('vendor/crudbooster/assets/js/bs3-compat.js') }}" type="text/javascript"></script>
<!-- AdminLTE 4 App. Pages that declare no colour mode stay light rather than following the OS. -->
<script>
    if (!document.documentElement.hasAttribute('data-bs-theme')) {
        document.documentElement.setAttribute('data-bs-theme', 'light');
    }
</script>
<script src="{{ asset ('vendor/crudbooster/assets/adminlte4/js/adminlte.min.js') }}" type="text/javascript"></script>

<!--BOOTSTRAP DATEPICKER-->
<script src="{{ asset ('vendor/crudbooster/assets/adminlte/plugins/datepicker/bootstrap-datepicker.js') }}"></script>
<link rel="stylesheet" href="{{ asset ('vendor/crudbooster/assets/adminlte/plugins/datepicker/datepicker3.css') }}">

<!--BOOTSTRAP DATERANGEPICKER 2.1.27 AND MOMENT 2.13.0 -->
<script src="{{ asset ('vendor/crudbooster/assets/adminlte/plugins/daterangepicker/moment.min.js') }}"></script>
<script src="{{ asset ('vendor/crudbooster/assets/adminlte/plugins/daterangepicker/daterangepicker.js') }}"></script>
<link rel="stylesheet" href="{{ asset ('vendor/crudbooster/assets/adminlte/plugins/daterangepicker/daterangepicker-bs3.css') }}">

<!-- Bootstrap time Picker -->
<link rel="stylesheet" href="{{ asset ('vendor/crudbooster/assets/adminlte/plugins/timepicker/bootstrap-timepicker.min.css') }}">
<script src="{{ asset ('vendor/crudbooster/assets/adminlte/plugins/timepicker/bootstrap-timepicker.min.js') }}"></script>

<link rel='stylesheet' href='{{ asset("vendor/crudbooster/assets/lightbox/dist/css/lightbox.min.css") }}'/>
<script src="{{ asset('vendor/crudbooster/assets/lightbox/dist/js/lightbox.min.js') }}"></script>

<!--SWEET ALERT-->
<script src="{{asset('vendor/crudbooster/assets/sweetalert/dist/sweetalert.min.js')}}"></script>
<link rel="stylesheet" type="text/css" href="{{asset('vendor/crudbooster/assets/sweetalert/dist/sweetalert.css')}}">

<!--MONEY FORMAT-->
<script src="{{asset('vendor/crudbooster/jquery.price_format.2.0.min.js')}}"></script>

<!--DATATABLE 2-->
<link rel="stylesheet" href="{{ asset ('vendor/crudbooster/assets/datatables/dataTables.bootstrap5.min.css')}}">
<!-- Select2 drawn like Bootstrap 5 form controls; after select2.css, which the select2 fields load -->
<link rel="stylesheet" href="{{ asset ('vendor/crudbooster/assets/adminlte4/css/adminlte-select2.min.css')}}">
<script src="{{ asset ('vendor/crudbooster/assets/datatables/dataTables.min.js')}}"></script>
<script src="{{ asset ('vendor/crudbooster/assets/datatables/dataTables.bootstrap5.min.js')}}"></script>

<script>
    var ASSET_URL = "{{asset('/')}}";
    var APP_NAME = "{{Session::get('appname')}}";
    var ADMIN_PATH = '{{url(config("crudbooster.ADMIN_PATH")) }}';
    var NOTIFICATION_JSON = "{{route('NotificationsControllerGetLatestJson')}}";
    var NOTIFICATION_INDEX = "{{route('NotificationsControllerGetIndex')}}";

    var NOTIFICATION_YOU_HAVE = "{{cbLang('notification_you_have')}}";
    var NOTIFICATION_NOTIFICATIONS = "{{cbLang('notification_notification')}}";
    var NOTIFICATION_NEW = "{{cbLang('notification_new')}}";

    $(function () {
        $('.datatables-simple').DataTable();
    })
</script>
<script src="{{asset('vendor/crudbooster/assets/js/main.js').'?r='.time()}}"></script>

	
