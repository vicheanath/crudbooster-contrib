@extends('cb::layout')

@php
    $panel = app(\CrudBooster\Panel\Panel::class);
    $page_icon = $page::getNavigationIcon();
    $breadcrumbs = [$page_title => null];
@endphp

@section('content')
    {{ $panel->renderHooks('page.before', ['page' => $page]) }}
    @include($page::getView(), $data + ['page' => $page])
    {{ $panel->renderHooks('page.after', ['page' => $page]) }}
@endsection
