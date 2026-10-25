@if($display === null || $display === '')
    <span class="text-muted-foreground">{{ $column->getPlaceholder() }}</span>
@else
    <span class="inline-flex flex-wrap gap-1">
        @foreach($state instanceof \Illuminate\Support\Collection ? $state : [$state] as $item)
            <span class="cb-badge {{ \CrudBooster\Themes\TailwindTheme::badge($column->getColor($item, $record)) }}">{{ $state instanceof \Illuminate\Support\Collection ? $column->formatState($item, $record) : $display }}</span>
        @endforeach
    </span>
@endif
