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
            <x-cb::alert type="danger" class="mb-4">{{ __('Please correct the errors below.') }}</x-cb::alert>
        @endif

        <div class="cb-card overflow-hidden">
            <div class="grid grid-cols-12 gap-x-6 gap-y-5 p-5 sm:p-6">
                @include('cb::forms.components', ['components' => $form->getSchema()])
            </div>
            <div class="flex flex-wrap items-center gap-2 border-t border-gray-200 bg-gray-50 px-5 py-3 sm:px-6 dark:border-white/10 dark:bg-white/5">
                <button type="submit" class="cb-btn cb-btn-primary" name="_after" value="index" id="cb-save">
                    <i class="fa fa-check" aria-hidden="true"></i> {{ __('Save') }}
                </button>
                @if(! $record && $resource->can('create'))
                    <button type="submit" class="cb-btn" name="_after" value="create">{{ __('Save and create another') }}</button>
                @endif
                <a href="{{ $return }}" class="ml-auto text-sm font-medium text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">{{ __('Cancel') }}</a>
            </div>
        </div>
    </form>

    {{ $panel->renderHooks('form.after', ['resource' => $resource, 'record' => $record, 'operation' => $operation]) }}
@endsection
