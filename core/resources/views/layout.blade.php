{{--
    The frame of every panel page: the CRUDBooster admin layout with a page header.
    Sections: content, header_actions; variables: $page_title, $page_icon, $breadcrumbs ([label => url|null]).
--}}
@extends('crudbooster::admin_template')

@php
    // Variables the admin layout reads that only classic modules set.
    $sidebar_mode = $sidebar_mode ?? '';
    $load_css = $load_css ?? [];
    $load_js = $load_js ?? [];
    $style_css = $style_css ?? null;
    $script_js = $script_js ?? null;
    $alerts = $alerts ?? [];
@endphp

@section('page_header')
    <div class="row align-items-center g-2">
        <div class="col-md">
            <h1 class="cb-page-title mb-0">
                @if(!empty($page_icon))<i class="{{ $page_icon }}"></i>@endif
                {{ $page_title ?? '' }}
            </h1>
        </div>
        <div class="col-md-auto d-flex flex-wrap gap-2 align-items-center">
            @yield('header_actions')
        </div>
    </div>
    @if(!empty($breadcrumbs))
        <ol class="breadcrumb mt-1">
            <li class="breadcrumb-item"><a href="{{ CRUDBooster::adminPath() }}"><i class="fa fa-dashboard"></i> {{ cbLang('home') }}</a></li>
            @foreach($breadcrumbs as $label => $url)
                @if($url)
                    <li class="breadcrumb-item"><a href="{{ $url }}">{{ $label }}</a></li>
                @else
                    <li class="breadcrumb-item active" aria-current="page">{{ $label }}</li>
                @endif
            @endforeach
        </ol>
    @endif
@endsection
