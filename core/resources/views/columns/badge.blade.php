@if($display === null || $display === '')
    <span class="text-body-tertiary">{{ $column->getPlaceholder() }}</span>
@else
    @foreach($state instanceof \Illuminate\Support\Collection ? $state : [$state] as $item)
        <span class="badge text-bg-{{ $column->getColor($item, $record) }}">{{ $state instanceof \Illuminate\Support\Collection ? $column->formatState($item, $record) : $display }}</span>
    @endforeach
@endif
