@extends('cb::layout')

@php
    $panel = app(\CrudBooster\Panel\Panel::class);
    $page_icon = $resource::getNavigationIcon();
    $breadcrumbs = [$resource::getPluralLabel() => $resource::hasPage('index') ? $resource::url() : null, $page_title => null];
    $action = $record ? route($resource::getRouteName('update'), ['record' => $record->getKey()]) : route($resource::getRouteName('store'));
    $previous = url()->previous();
    $index = $resource::getIndexUrl();
    $return = $returnUrl ?: ($resource::hasPage('index') && str_starts_with($previous, $index) && $previous !== url()->current() ? $previous : $index);
@endphp

@section('content')
    {{ $panel->renderHooks('form.before', ['resource' => $resource, 'record' => $record, 'operation' => $operation]) }}

    <form method="POST" action="{{ $action }}" id="cb-form" @if($form->hasFileUploads()) enctype="multipart/form-data" @endif novalidate>
        @csrf
        @if($record) @method('PUT') @endif
        <input type="hidden" name="_return" value="{{ old('_return', $return) }}">

        @if($errors->any())
            <div class="alert alert-danger" role="alert">
                <i class="fa fa-exclamation-triangle"></i> {{ __('Please correct the errors below.') }}
            </div>
        @endif

        <div class="card">
            <div class="card-body">
                <div class="row g-3">
                    @include('cb::forms.components', ['components' => $form->getSchema()])
                </div>
            </div>
            <div class="card-footer d-flex flex-wrap gap-2">
                <button type="submit" class="btn btn-success" name="_after" value="index" id="cb-save">
                    <i class="fa fa-save"></i> {{ __('Save') }}
                </button>
                @if(! $record && $resource->can('create'))
                    <button type="submit" class="btn btn-outline-success" name="_after" value="create">
                        {{ __('Save and create another') }}
                    </button>
                @endif
                <a href="{{ $return }}" class="btn btn-link">{{ __('Cancel') }}</a>
            </div>
        </div>
    </form>

    {{ $panel->renderHooks('form.after', ['resource' => $resource, 'record' => $record, 'operation' => $operation]) }}
@endsection
