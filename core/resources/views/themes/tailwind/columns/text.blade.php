@if($display === null || $display === '')
    <span class="text-muted-foreground">{{ $column->getPlaceholder() }}</span>
@elseif($url)
    <a href="{{ $url }}" class="font-medium underline-offset-4 hover:underline" @if($column->shouldOpenUrlInNewTab()) target="_blank" rel="noopener" @endif>{{ $display }}</a>
@else
    {{ $display }}
@endif
