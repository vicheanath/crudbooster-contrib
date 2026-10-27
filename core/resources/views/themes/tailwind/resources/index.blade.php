@extends('cb::layout')

@php
    $panel = app(\CrudBooster\Panel\Panel::class);
    $page_icon = $resource::getNavigationIcon();
    $breadcrumbs = [$resource::getPluralLabel() => null];
    $columns = $table->getColumns();
    $filters = $table->getFilters();
    $canDelete = $resource->can('delete');

    $bulkActions = [];
    foreach ($table->getBulkActions() as $action) {
        if ($action->for(['records' => collect()])->isVisible()) {
            $bulkActions[] = [
                'name' => $action->getName(), 'label' => $action->getLabel(), 'icon' => $action->getIcon(), 'color' => $action->getColor(),
                'confirm' => $action->getConfirmationMessage(), 'url' => route($resource::getRouteName('bulk'), ['action' => $action->getName()]),
            ];
        }
    }
    if ($table->hasBulkDelete() && $canDelete && ! $table->getBulkAction('delete')) {
        $bulkActions[] = [
            'name' => 'delete', 'label' => __('Delete selected'), 'icon' => 'fa fa-trash', 'color' => 'danger',
            'confirm' => __('Delete the selected records?'), 'url' => route($resource::getRouteName('bulk'), ['action' => 'delete']),
        ];
    }
    $hasBulk = count($bulkActions) > 0;

    $sortUrl = function (string $column) use ($sort, $direction) {
        $next = $sort === $column && $direction === 'asc' ? 'desc' : 'asc';

        return request()->fullUrlWithQuery(['sort' => $column, 'direction' => $next, 'page' => null]);
    };
    $activeFilters = count(array_filter($filters, fn ($filter) => $filter->isActive($filterValues[$filter->getName()] ?? null)));
    $align = ['end' => 'text-right', 'center' => 'text-center'];
@endphp

@section('header_actions')
    @if($resource::hasPage('create') && $resource->can('create'))
        <a href="{{ $resource::url('create') }}" class="cb-btn cb-btn-primary" id="cb-create">
            <i class="fa fa-plus" aria-hidden="true"></i> {{ __('New :label', ['label' => $resource::getLabel()]) }}
        </a>
    @endif
@endsection

@section('content')
    {{ $panel->renderHooks('index.before', ['resource' => $resource]) }}

    <div class="cb-card overflow-hidden">
        <form method="GET" action="{{ $resource::url() }}" id="cb-table-filters" class="flex flex-wrap items-center gap-2 border-b p-3">
            @if($sort)
                <input type="hidden" name="sort" value="{{ $sort }}">
                <input type="hidden" name="direction" value="{{ $direction }}">
            @endif

            @if($table->isSearchable())
                <div class="relative w-full sm:w-72">
                    <i class="fa fa-search text-muted-foreground pointer-events-none absolute top-1/2 left-2.5 -translate-y-1/2 text-xs" aria-hidden="true"></i>
                    <input type="search" name="search" value="{{ $search }}" class="cb-input pl-8" placeholder="{{ __('Search') }}" aria-label="{{ __('Search') }}">
                </div>
            @endif

            @foreach($filters as $filter)
                {!! $filter->render($model, $filterValues[$filter->getName()] ?? null) !!}
            @endforeach

            @if($filters || $table->isSearchable())
                <button type="submit" class="cb-btn"><i class="fa fa-filter" aria-hidden="true"></i> {{ __('Filter') }}</button>
            @endif
            @if($search !== '' || $activeFilters)
                <a href="{{ $resource::url() }}" class="cb-btn cb-btn-ghost">{{ __('Reset') }}</a>
            @endif

            <div class="ml-auto flex items-center gap-2">
                @if($hasBulk)
                    <div data-cb-component="BulkActions" data-cb-props="{{ json_encode(['actions' => $bulkActions]) }}">
                        <button type="button" class="cb-btn cb-btn-sm" disabled>{{ __('Bulk actions') }} <i class="fa fa-angle-down" aria-hidden="true"></i></button>
                    </div>
                @endif
                <label class="sr-only" for="cb-per-page">{{ __('Per page') }}</label>
                <select id="cb-per-page" name="per_page" class="cb-input w-auto" onchange="this.form.submit()">
                    @foreach($table->getPerPageOptions() as $option)
                        <option value="{{ $option }}" @selected($records->perPage() === $option)>{{ $option }}</option>
                    @endforeach
                </select>
            </div>
        </form>

        <div class="relative overflow-x-auto">
            <table class="w-full caption-bottom text-sm" id="cb-table">
                <thead class="[&_tr]:border-b">
                <tr>
                    @if($hasBulk)
                        <th scope="col" class="h-10 w-px pr-0 pl-4 align-middle"><input type="checkbox" class="accent-primary size-4" id="cb-select-all" aria-label="{{ __('Select all') }}"></th>
                    @endif
                    @foreach($columns as $column)
                        <th scope="col" class="text-foreground h-10 px-4 align-middle font-medium whitespace-nowrap {{ $align[$column->getAlignment()] ?? 'text-left' }}"
                            @if($sort === $column->getName()) aria-sort="{{ $direction === 'asc' ? 'ascending' : 'descending' }}" @endif>
                            @if($column->isSortable())
                                <a href="{{ $sortUrl($column->getName()) }}" class="group hover:bg-muted -mx-2 inline-flex items-center gap-1.5 rounded-md px-2 py-1">
                                    {{ $column->getLabel() }}
                                    <i class="fa {{ $sort === $column->getName() ? ($direction === 'asc' ? 'fa-sort-asc' : 'fa-sort-desc') : 'fa-sort text-muted-foreground/50 group-hover:text-muted-foreground' }}" aria-hidden="true"></i>
                                </a>
                            @else
                                {{ $column->getLabel() }}
                            @endif
                        </th>
                    @endforeach
                    <th scope="col" class="h-10 w-px px-4"><span class="sr-only">{{ __('Actions') }}</span></th>
                </tr>
                </thead>
                <tbody class="[&_tr:last-child]:border-0">
                @forelse($records as $record)
                    <tr class="hover:bg-muted/50 border-b transition-colors">
                        @if($hasBulk)
                            <td class="py-2 pr-0 pl-4 align-middle"><input type="checkbox" class="cb-select accent-primary size-4" name="records[]" value="{{ $record->getKey() }}" form="cb-bulk-form" aria-label="{{ __('Select') }}"></td>
                        @endif
                        @foreach($columns as $column)
                            <td class="px-4 py-2 align-middle {{ $align[$column->getAlignment()] ?? '' }}">{!! $column->render($record) !!}</td>
                        @endforeach
                        <td class="px-4 py-1.5 text-right align-middle whitespace-nowrap">@include('cb::resources.partials.row-actions')</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($columns) + ($hasBulk ? 2 : 1) }}" class="px-4 py-16 text-center">
                            <span class="bg-muted text-foreground mx-auto mb-3 flex size-10 items-center justify-center rounded-lg"><i class="fa fa-inbox" aria-hidden="true"></i></span>
                            <p class="text-muted-foreground text-sm">{{ $table->getEmptyStateMessage() }}</p>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if($records->total())
            <div class="flex flex-wrap items-center justify-between gap-3 border-t px-4 py-3">
                <p class="text-muted-foreground text-sm">
                    {{ __('Showing :from to :to of :total', ['from' => $records->firstItem() ?? 0, 'to' => $records->lastItem() ?? 0, 'total' => $records->total()]) }}
                </p>
                {{ $records->links('cb::partials.pagination') }}
            </div>
        @endif
    </div>

    {{-- Row and bulk buttons submit these through form="...", since forms cannot be nested in the table. --}}
    <form method="POST" id="cb-bulk-form" class="hidden">@csrf</form>
    <form method="POST" id="cb-action-form" class="hidden">@csrf<input type="hidden" name="_return" value="{{ request()->fullUrl() }}"></form>
    <form method="POST" id="cb-delete-form" class="hidden">@csrf @method('DELETE')<input type="hidden" name="_return" value="{{ request()->fullUrl() }}"></form>

    {{ $panel->renderHooks('index.after', ['resource' => $resource]) }}
@endsection
