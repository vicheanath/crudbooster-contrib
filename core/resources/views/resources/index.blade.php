@extends('cb::layout')

@php
    $panel = app(\CrudBooster\Panel\Panel::class);
    $page_icon = $resource::getNavigationIcon();
    $breadcrumbs = [$resource::getPluralLabel() => null];
    $columns = $table->getColumns();
    $filters = $table->getFilters();
    $canDelete = $resource->can('delete');
    $bulkActions = array_values(array_filter($table->getBulkActions(), fn ($action) => $action->for(['records' => collect()])->isVisible()));
    $hasBulk = ($table->hasBulkDelete() && $canDelete) || $bulkActions;
    $sortUrl = function (string $column) use ($sort, $direction) {
        $next = $sort === $column && $direction === 'asc' ? 'desc' : 'asc';

        return request()->fullUrlWithQuery(['sort' => $column, 'direction' => $next, 'page' => null]);
    };
    $activeFilters = count(array_filter($filters, fn ($filter) => $filter->isActive($filterValues[$filter->getName()] ?? null)));
@endphp

@section('header_actions')
    @if($resource::hasPage('create') && $resource->can('create'))
        <a href="{{ $resource::url('create') }}" class="btn btn-sm btn-success" id="cb-create">
            <i class="fa fa-plus-circle"></i> {{ __('New :label', ['label' => $resource::getLabel()]) }}
        </a>
    @endif
@endsection

@section('content')
    {{ $panel->renderHooks('index.before', ['resource' => $resource]) }}

    <div class="card">
        <div class="card-header">
            <form method="GET" action="{{ $resource::url() }}" class="d-flex flex-wrap gap-2 align-items-center" id="cb-table-filters">
                @if($sort)
                    <input type="hidden" name="sort" value="{{ $sort }}">
                    <input type="hidden" name="direction" value="{{ $direction }}">
                @endif

                @if($table->isSearchable())
                    <div class="input-group input-group-sm" style="max-width: 320px">
                        <input type="search" name="search" value="{{ $search }}" class="form-control" placeholder="{{ __('Search') }}" aria-label="{{ __('Search') }}">
                        <button class="btn btn-outline-secondary" type="submit"><i class="fa fa-search"></i></button>
                    </div>
                @endif

                @foreach($filters as $filter)
                    {!! $filter->render($model, $filterValues[$filter->getName()] ?? null) !!}
                @endforeach

                @if($filters)
                    <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-filter"></i> {{ __('Filter') }}</button>
                @endif
                @if($search !== '' || $activeFilters)
                    <a href="{{ $resource::url() }}" class="btn btn-sm btn-link">{{ __('Reset') }}</a>
                @endif

                <div class="ms-auto d-flex align-items-center gap-2">
                    @if($hasBulk)
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" id="cb-bulk-toggle" disabled>
                                {{ __('Bulk actions') }}
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                @foreach($bulkActions as $action)
                                    <li>
                                        <button type="submit" form="cb-bulk-form" class="dropdown-item"
                                                formaction="{{ route($resource::getRouteName('bulk'), ['action' => $action->getName()]) }}"
                                                @if($message = $action->getConfirmationMessage()) onclick="return confirm(@js($message))" @endif>
                                            @if($action->getIcon())<i class="{{ $action->getIcon() }} text-{{ $action->getColor() }}"></i>@endif
                                            {{ $action->getLabel() }}
                                        </button>
                                    </li>
                                @endforeach
                                @if($table->hasBulkDelete() && $canDelete && ! $table->getBulkAction('delete'))
                                    <li>
                                        <button type="submit" form="cb-bulk-form" class="dropdown-item text-danger"
                                                formaction="{{ route($resource::getRouteName('bulk'), ['action' => 'delete']) }}"
                                                onclick="return confirm(@js(__('Delete the selected records?')))">
                                            <i class="fa fa-trash"></i> {{ __('Delete selected') }}
                                        </button>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    @endif
                    <select name="per_page" class="form-select form-select-sm w-auto" onchange="this.form.submit()" aria-label="{{ __('Per page') }}">
                        @foreach($table->getPerPageOptions() as $option)
                            <option value="{{ $option }}" @selected($records->perPage() === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0" id="cb-table">
                    <thead>
                    <tr>
                        @if($hasBulk)
                            <th style="width: 1%"><input type="checkbox" class="form-check-input" id="cb-select-all" aria-label="{{ __('Select all') }}"></th>
                        @endif
                        @foreach($columns as $column)
                            <th class="text-nowrap {{ $column->getAlignment() ? 'text-'.$column->getAlignment() : '' }}">
                                @if($column->isSortable())
                                    <a href="{{ $sortUrl($column->getName()) }}" class="link-body-emphasis text-decoration-none">
                                        {{ $column->getLabel() }}
                                        @if($sort === $column->getName())
                                            <i class="fa fa-sort-{{ $direction === 'asc' ? 'asc' : 'desc' }}"></i>
                                        @else
                                            <i class="fa fa-sort text-body-tertiary"></i>
                                        @endif
                                    </a>
                                @else
                                    {{ $column->getLabel() }}
                                @endif
                            </th>
                        @endforeach
                        <th class="text-end" style="width: 1%"><span class="visually-hidden">{{ __('Actions') }}</span></th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($records as $record)
                        <tr>
                            @if($hasBulk)
                                <td><input type="checkbox" class="form-check-input cb-select" name="records[]" value="{{ $record->getKey() }}" form="cb-bulk-form" aria-label="{{ __('Select') }}"></td>
                            @endif
                            @foreach($columns as $column)
                                <td class="{{ $column->getAlignment() ? 'text-'.$column->getAlignment() : '' }}">{!! $column->render($record) !!}</td>
                            @endforeach
                            <td class="text-end text-nowrap">
                                @include('cb::resources.partials.row-actions')
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($columns) + ($hasBulk ? 2 : 1) }}" class="text-center text-body-secondary py-5">
                                <i class="fa fa-inbox fa-2x d-block mb-2"></i>
                                {{ $table->getEmptyStateMessage() }}
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($records->hasPages() || $records->total())
            <div class="card-footer d-flex flex-wrap justify-content-between align-items-center gap-2">
                <small class="text-body-secondary">
                    {{ __('Showing :from to :to of :total', ['from' => $records->firstItem() ?? 0, 'to' => $records->lastItem() ?? 0, 'total' => $records->total()]) }}
                </small>
                {{ $records->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>

    {{-- Row and bulk buttons submit these through form="...", since forms cannot be nested in the table. --}}
    <form method="POST" id="cb-bulk-form" class="d-none">@csrf</form>
    <form method="POST" id="cb-action-form" class="d-none">@csrf<input type="hidden" name="_return" value="{{ request()->fullUrl() }}"></form>
    <form method="POST" id="cb-delete-form" class="d-none">@csrf @method('DELETE')<input type="hidden" name="_return" value="{{ request()->fullUrl() }}"></form>

    {{ $panel->renderHooks('index.after', ['resource' => $resource]) }}
@endsection

@push('bottom')
    <script>
        (function () {
            var all = document.getElementById('cb-select-all');
            var toggle = document.getElementById('cb-bulk-toggle');
            var boxes = function () { return document.querySelectorAll('.cb-select'); };
            var refresh = function () {
                if (toggle) toggle.disabled = !document.querySelector('.cb-select:checked');
            };
            if (all) all.addEventListener('change', function () {
                boxes().forEach(function (box) { box.checked = all.checked; });
                refresh();
            });
            boxes().forEach(function (box) { box.addEventListener('change', refresh); });
        })();
    </script>
@endpush
