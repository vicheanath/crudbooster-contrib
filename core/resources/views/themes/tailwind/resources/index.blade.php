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

    <div class="cb-card">
        <form method="GET" action="{{ $resource::url() }}" id="cb-table-filters" class="flex flex-wrap items-center gap-2 border-b border-gray-200 p-3 dark:border-white/10">
            @if($sort)
                <input type="hidden" name="sort" value="{{ $sort }}">
                <input type="hidden" name="direction" value="{{ $direction }}">
            @endif

            @if($table->isSearchable())
                <div class="cb-input-group w-full sm:w-72">
                    <span class="cb-input-addon"><i class="fa fa-search" aria-hidden="true"></i></span>
                    <input type="search" name="search" value="{{ $search }}" class="cb-input cb-input-bare" placeholder="{{ __('Search') }}" aria-label="{{ __('Search') }}">
                </div>
            @endif

            @foreach($filters as $filter)
                {!! $filter->render($model, $filterValues[$filter->getName()] ?? null) !!}
            @endforeach

            @if($filters || $table->isSearchable())
                <button type="submit" class="cb-btn cb-btn-sm"><i class="fa fa-filter" aria-hidden="true"></i> {{ __('Filter') }}</button>
            @endif
            @if($search !== '' || $activeFilters)
                <a href="{{ $resource::url() }}" class="text-sm font-medium text-brand-600 hover:underline dark:text-brand-400">{{ __('Reset') }}</a>
            @endif

            <div class="ml-auto flex items-center gap-2">
                @if($hasBulk)
                    <div data-cb-component="BulkActions" data-cb-props="{{ json_encode(['actions' => $bulkActions]) }}">
                        <button type="button" class="cb-btn cb-btn-sm" disabled>{{ __('Bulk actions') }}</button>
                    </div>
                @endif
                <label class="sr-only" for="cb-per-page">{{ __('Per page') }}</label>
                <select id="cb-per-page" name="per_page" class="cb-input w-auto py-1.5" onchange="this.form.submit()">
                    @foreach($table->getPerPageOptions() as $option)
                        <option value="{{ $option }}" @selected($records->perPage() === $option)>{{ $option }}</option>
                    @endforeach
                </select>
            </div>
        </form>

        <div class="relative overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-white/10" id="cb-table">
                <thead class="bg-gray-50 dark:bg-white/5">
                <tr>
                    @if($hasBulk)
                        <th scope="col" class="w-px py-3 pr-2 pl-4"><input type="checkbox" class="size-4 rounded accent-brand-600" id="cb-select-all" aria-label="{{ __('Select all') }}"></th>
                    @endif
                    @foreach($columns as $column)
                        <th scope="col" class="px-4 py-3 text-xs font-semibold tracking-wide whitespace-nowrap text-gray-500 uppercase dark:text-gray-400 {{ $align[$column->getAlignment()] ?? 'text-left' }}"
                            @if($sort === $column->getName()) aria-sort="{{ $direction === 'asc' ? 'ascending' : 'descending' }}" @endif>
                            @if($column->isSortable())
                                <a href="{{ $sortUrl($column->getName()) }}" class="group inline-flex items-center gap-1 hover:text-gray-900 dark:hover:text-white">
                                    {{ $column->getLabel() }}
                                    <i class="fa {{ $sort === $column->getName() ? ($direction === 'asc' ? 'fa-sort-asc text-brand-600' : 'fa-sort-desc text-brand-600') : 'fa-sort text-gray-300 group-hover:text-gray-500 dark:text-gray-600' }}" aria-hidden="true"></i>
                                </a>
                            @else
                                {{ $column->getLabel() }}
                            @endif
                        </th>
                    @endforeach
                    <th scope="col" class="w-px px-4 py-3"><span class="sr-only">{{ __('Actions') }}</span></th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white dark:divide-white/5 dark:bg-transparent">
                @forelse($records as $record)
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                        @if($hasBulk)
                            <td class="py-3 pr-2 pl-4"><input type="checkbox" class="cb-select size-4 rounded accent-brand-600" name="records[]" value="{{ $record->getKey() }}" form="cb-bulk-form" aria-label="{{ __('Select') }}"></td>
                        @endif
                        @foreach($columns as $column)
                            <td class="px-4 py-3 text-gray-700 dark:text-gray-300 {{ $align[$column->getAlignment()] ?? '' }}">{!! $column->render($record) !!}</td>
                        @endforeach
                        <td class="px-4 py-2 text-right whitespace-nowrap">@include('cb::resources.partials.row-actions')</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($columns) + ($hasBulk ? 2 : 1) }}" class="px-4 py-16 text-center">
                            <i class="fa fa-inbox mb-2 block text-3xl text-gray-300 dark:text-gray-600" aria-hidden="true"></i>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $table->getEmptyStateMessage() }}</p>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if($records->total())
            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-gray-200 px-4 py-3 dark:border-white/10">
                <p class="text-sm text-gray-500 dark:text-gray-400">
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
