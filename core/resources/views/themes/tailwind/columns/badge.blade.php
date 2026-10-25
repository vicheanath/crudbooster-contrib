@if($display === null || $display === '')
    <span class="text-gray-400 dark:text-gray-600">{{ $column->getPlaceholder() }}</span>
@else
    <span class="inline-flex flex-wrap gap-1">
        @foreach($state instanceof \Illuminate\Support\Collection ? $state : [$state] as $item)
            <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium ring-1 ring-inset {{ \CrudBooster\Themes\TailwindTheme::badge($column->getColor($item, $record)) }}">{{ $state instanceof \Illuminate\Support\Collection ? $column->formatState($item, $record) : $display }}</span>
        @endforeach
    </span>
@endif
