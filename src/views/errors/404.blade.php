<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', App::getLocale()) }}" dir="{{ in_array(App::getLocale(), ['ar', 'fa']) ? 'rtl' : 'ltr' }}" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <title>404 PAGE NOT FOUND</title>
    <meta content='width=device-width, initial-scale=1' name='viewport'>
    @include('crudbooster::partials.theme_styles')
    <style>
        body {
            background: #ededed;
        }

        .error-page {
            display: flex;
            gap: 2rem;
            align-items: center;
            max-width: 640px;
            margin: 10vh auto 0;
            padding: 0 1rem;
        }

        .error-page > .headline {
            margin: 0;
            font-size: 6rem;
            font-weight: 300;
        }

        .error-page > .error-content > h3 {
            font-size: 1.5rem;
        }
    </style>
</head>
<body>

<!-- Main content -->
<section class="content">

    <div class="error-page">
        <h2 class="headline text-yellow"> 404</h2>
        <div class="error-content">
            <h3><i class="fa fa-warning text-yellow"></i> {{cbLang('page_not_found')}}</h3>
            <p>
                {{cbLang('page_not_found_text')}}
            </p>
            <p>Tips : <br/>
                {{cbLang('page_not_found_tips')}}
            </p>
        </div><!-- /.error-content -->
    </div><!-- /.error-page -->
</section><!-- /.content -->


@include('crudbooster::partials.theme_scripts')
</body>
</html>