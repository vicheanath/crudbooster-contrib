<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', App::getLocale()) }}" dir="{{ in_array(App::getLocale(), ['ar', 'fa']) ? 'rtl' : 'ltr' }}" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <title>::LOCKSCREEN::</title>
    <meta name='generator' content='CRUDBooster'/>
    <meta name='robots' content='noindex,nofollow'/>
    <link rel="shortcut icon"
          href="{{ CRUDBooster::getSetting('favicon')?asset(CRUDBooster::getSetting('favicon')):asset('vendor/crudbooster/assets/logo_crudbooster.png') }}">
    <meta content='width=device-width, initial-scale=1' name='viewport'>
    @include('crudbooster::partials.theme_styles')

    <link rel='stylesheet' href='{{asset("vendor/crudbooster/assets/css/main.css")}}'/>
    <style type="text/css">
        .lockscreen {
            background: {{ CRUDBooster::getSetting("login_background_color")?:'#dddddd'}} url('{{ CRUDBooster::getSetting("login_background_image")?asset(CRUDBooster::getSetting("login_background_image")):asset('vendor/crudbooster/assets/bg_blur3.jpg') }}');
            color: {{ CRUDBooster::getSetting("login_font_color")?:'#ffffff' }}  !important;
            background-repeat: no-repeat;
            background-position: center;
            background-size: cover;
            min-height: 100vh;
        }

        .lockscreen .lockscreen-image > img {
            object-fit: cover;
        }
    </style>

</head>
<body class="lockscreen">
<!-- Automatic element centering -->
<div class="lockscreen-wrapper pt-5">
    <div class="lockscreen-logo">
        <a href="{{url('/')}}">
            <img title='{!!($appname == 'CRUDBooster')?"<b>CRUD</b>Booster":$appname!!}'
                 src='{{ CRUDBooster::getSetting("logo")?asset(CRUDBooster::getSetting('logo')):asset('vendor/crudbooster/assets/logo_crudbooster.png') }}'
                 style='max-width: 100%;max-height:170px'/>
        </a>
    </div>
    <!-- User name -->
    <div class="lockscreen-name">{{Session::get('admin_name')}}</div>

    <!-- START LOCK SCREEN ITEM -->
    <div class="lockscreen-item">
        <!-- lockscreen image -->
        <div class="lockscreen-image">
            <img src="{{ (Session::get('admin_photo'))?:asset("assets/adminlte/dist/img/user2-160x160.jpg") }}" alt="user image"/>
        </div>
        <!-- /.lockscreen-image -->

        <!-- lockscreen credentials (contains the form) -->
        <form class="lockscreen-credentials" method='post' action="{{url(config('crudbooster.ADMIN_PATH').'/unlock-screen')}}">
            <input type="hidden" name="_token" value="{{ csrf_token() }}"/>
            <div class="input-group">
                <input type="password" class="form-control" required name='password' placeholder="password"/>
                <button class="btn" type="submit"><i class="fa fa-arrow-right text-body-secondary"></i></button>
            </div>
        </form><!-- /.lockscreen credentials -->

    </div><!-- /.lockscreen-item -->
    <div class="text-center">
        {{cbLang("text_enter_the_password")}}
    </div>
    <div class='text-center'>
        <a href="{{route("getLogout")}}">{{cbLang('text_or_sign_in')}}</a>
    </div>
    <div class='lockscreen-footer text-center'>
        Copyright &copy; {{date("Y")}}<br>
        All rights reserved
    </div>
</div><!-- /.center -->


@include('crudbooster::partials.theme_scripts')
</body>
</html>