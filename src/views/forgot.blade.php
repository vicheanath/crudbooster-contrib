<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', App::getLocale()) }}" dir="{{ in_array(App::getLocale(), ['ar', 'fa']) ? 'rtl' : 'ltr' }}" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <title>{{cbLang("page_title_forgot")}} : {{$appname}}</title>
    <meta name='generator' content='CRUDBooster.com'/>
    <meta name='robots' content='noindex,nofollow'/>
    <link rel="shortcut icon"
          href="{{ CRUDBooster::getSetting('favicon')?asset(CRUDBooster::getSetting('favicon')):asset('vendor/crudbooster/assets/logo_crudbooster.png') }}">
    <meta content='width=device-width, initial-scale=1' name='viewport'>
    @include('crudbooster::partials.theme_styles')
    <link rel='stylesheet' href='{{asset("vendor/crudbooster/assets/css/main.css")}}'/>
    <style type="text/css">
        .login-page, .register-page {
            background: {{ CRUDBooster::getSetting("login_background_color")?:'#dddddd'}} url('{{ CRUDBooster::getSetting("login_background_image")?asset(CRUDBooster::getSetting("login_background_image")):asset('vendor/crudbooster/assets/bg_blur3.jpg') }}');
            color: {{ CRUDBooster::getSetting("login_font_color")?:'#ffffff' }}  !important;
            background-repeat: no-repeat;
            background-position: center;
            background-size: cover;
        }

        .login-box-body {
            padding: 1.25rem;
            border-radius: var(--bs-border-radius);
            box-shadow: 0px 0px 50px rgba(0, 0, 0, 0.8);
            background: rgba(255, 255, 255, 0.9);
            color: {{ CRUDBooster::getSetting("login_font_color")?:'#666666' }}  !important;
        }
    </style>
</head>
<body class="login-page d-flex flex-column justify-content-center align-items-center">
<div class="login-box">
    <div class="login-logo">
        <a href="{{url('/')}}">
            <img title='{!!($appname == 'CRUDBooster')?"<b>CRUD</b>Booster":$appname!!}'
                 src='{{ CRUDBooster::getSetting("logo")?asset(CRUDBooster::getSetting('logo')):asset('vendor/crudbooster/assets/logo_crudbooster.png') }}'
                 style='max-width: 100%;max-height:170px'/>
        </a>
    </div><!-- /.login-logo -->
    <div class="login-box-body">

        @if ( Session::get('message') != '' )
            <div class='alert alert-warning' role="alert">
                {{ Session::get('message') }}
            </div>
        @endif

        <p class="login-box-msg">{{cbLang("forgot_message")}}</p>
        <form action="{{ route('postForgot') }}" method="post">
            <input type="hidden" name="_token" value="{{ csrf_token() }}"/>
            <div class="input-group mb-3">
                <input type="email" class="form-control" name='email' required placeholder="Email Address"/>
                <span class="input-group-text"><i class="fa fa-envelope"></i></span>
            </div>
            <div class="row align-items-center">
                <div class="col-8">
                    {{cbLang("forgot_text_try_again")}} <a href='{{route("getLogin")}}'>{{cbLang("click_here")}}</a>
                </div><!-- /.col -->
                <div class="col-4">
                    <button type="submit" class="btn btn-primary w-100">{{cbLang("button_submit")}}</button>
                </div><!-- /.col -->
            </div>
        </form>

    </div><!-- /.login-box-body -->
</div><!-- /.login-box -->

@include('crudbooster::partials.theme_scripts')

</body>
</html>
