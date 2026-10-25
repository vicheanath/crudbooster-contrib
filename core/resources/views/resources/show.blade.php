@extends('cb::layout')

@php
    $panel = app(\CrudBooster\Panel\Panel::class);
    $page_icon = $resource::getNavigationIcon();
    $breadcrumbs = [$resource::getPluralLabel() => $resource::hasPage('index') ? $resource::url() : null, $page_title => null];
@endphp

@section('header_actions')
    @if($resource::hasPage('edit') && $resource->can('update', $record))
        <a href="{{ $resource::url('edit', $record) }}" class="btn btn-sm btn-success"><i class="fa fa-pencil"></i> {{ __('Edit') }}</a>
    @endif
    @if($resource->can('delete', $record))
        <form method="POST" action="{{ route($resource::getRouteName('destroy'), ['record' => $record->getKey()]) }}" class="d-inline"
              onsubmit="return confirm(@js(__('Delete this record?')))">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fa fa-trash"></i> {{ __('Delete') }}</button>
        </form>
    @endif
@endsection

@section('content')
    {{ $panel->renderHooks('detail.before', ['resource' => $resource, 'record' => $record]) }}

    <div class="card">
        <div class="card-body">
            <dl class="row mb-0">
                @foreach($detail->getEntries() as $entry)
                    <dt class="col-sm-3 text-body-secondary fw-normal">{{ $entry->getLabel() }}</dt>
                    <dd class="col-sm-9">{!! $entry->render($record) !!}</dd>
                @endforeach
            </dl>
        </div>
        @if($resource::hasPage('index'))
            <div class="card-footer">
                <a href="{{ $resource::url() }}" class="btn btn-link px-0"><i class="fa fa-arrow-left"></i> {{ __('Back to :label', ['label' => $resource::getPluralLabel()]) }}</a>
            </div>
        @endif
    </div>

    {{ $panel->renderHooks('detail.after', ['resource' => $resource, 'record' => $record]) }}
@endsection
