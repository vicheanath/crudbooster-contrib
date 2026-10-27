@extends('cb::layout')

@php
    $panel = app(\CrudBooster\Panel\Panel::class);
    $page_icon = $resource::getNavigationIcon();
    $breadcrumbs = [$resource::getPluralLabel() => $resource::hasPage('index') ? $resource::url() : null, $page_title => null];
@endphp

@section('header_actions')
    @if($resource::hasPage('edit') && $resource->can('update', $record))
        <a href="{{ $resource::url('edit', $record) }}" class="cb-btn cb-btn-primary"><i class="fa fa-pencil" aria-hidden="true"></i> {{ __('Edit') }}</a>
    @endif
    @if($resource->can('delete', $record))
        <form method="POST" action="{{ route($resource::getRouteName('destroy'), ['record' => $record->getKey()]) }}">
            @csrf @method('DELETE')
            <button type="submit" class="cb-btn cb-btn-destructive" data-cb-confirm="{{ __('Delete this record?') }}" data-cb-confirm-color="danger" data-cb-confirm-label="{{ __('Delete') }}">
                <i class="fa fa-trash-o" aria-hidden="true"></i> {{ __('Delete') }}
            </button>
        </form>
    @endif
@endsection

@section('content')
    {{ $panel->renderHooks('detail.before', ['resource' => $resource, 'record' => $record]) }}

    <div class="cb-card overflow-hidden">
        <dl class="divide-y">
            @foreach($detail->getEntries() as $entry)
                <div class="grid gap-1 px-5 py-4 sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-muted-foreground text-sm font-medium">{{ $entry->getLabel() }}</dt>
                    <dd class="text-sm sm:col-span-2">{!! $entry->render($record) !!}</dd>
                </div>
            @endforeach
        </dl>
        @if($resource::hasPage('index'))
            <div class="bg-muted/50 border-t px-5 py-3 sm:px-6">
                <a href="{{ $resource::url() }}" class="cb-btn cb-btn-ghost -ml-2.5"><i class="fa fa-arrow-left" aria-hidden="true"></i> {{ __('Back to :label', ['label' => $resource::getPluralLabel()]) }}</a>
            </div>
        @endif
    </div>

    {{ $panel->renderHooks('detail.after', ['resource' => $resource, 'record' => $record]) }}
@endsection
