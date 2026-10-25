@php $src = $column->getImageUrl($state); @endphp
@if($src)
    <a href="{{ $url ?: $src }}" target="_blank" rel="noopener" class="inline-block">
        <img src="{{ $src }}" alt="" loading="lazy" width="{{ $column->getSize() }}" height="{{ $column->getSize() }}"
             class="{{ $column->isCircular() ? 'rounded-full' : 'rounded-md' }} object-cover ring-1 ring-gray-200 dark:ring-white/10" style="width: {{ $column->getSize() }}px; height: {{ $column->getSize() }}px">
    </a>
@else
    <span class="inline-flex items-center justify-center rounded-md bg-gray-100 text-gray-400 dark:bg-white/5 dark:text-gray-600" style="width: {{ $column->getSize() }}px; height: {{ $column->getSize() }}px"><i class="fa fa-picture-o" aria-hidden="true"></i></span>
@endif
