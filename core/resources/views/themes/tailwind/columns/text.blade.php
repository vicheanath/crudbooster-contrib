@if($display === null || $display === '')
    <span class="text-gray-400 dark:text-gray-600">{{ $column->getPlaceholder() }}</span>
@elseif($url)
    <a href="{{ $url }}" class="font-medium text-brand-600 hover:underline dark:text-brand-400" @if($column->shouldOpenUrlInNewTab()) target="_blank" rel="noopener" @endif>{{ $display }}</a>
@else
    {{ $display }}
@endif
