@if($display === null || $display === '')
    <span class="text-body-tertiary">{{ $column->getPlaceholder() }}</span>
@elseif($url)
    <a href="{{ $url }}" @if($column->shouldOpenUrlInNewTab()) target="_blank" rel="noopener" @endif>{{ $display }}</a>
@else
    {{ $display }}
@endif
