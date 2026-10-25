@php $src = $column->getImageUrl($state); @endphp
@if($src)
    <a href="{{ $url ?: $src }}" target="_blank" rel="noopener" class="inline-block">
        <img src="{{ $src }}" alt="" loading="lazy" width="{{ $column->getSize() }}" height="{{ $column->getSize() }}"
             class="{{ $column->isCircular() ? 'rounded-full' : 'rounded-md' }} ring-foreground/10 object-cover ring-1" style="width: {{ $column->getSize() }}px; height: {{ $column->getSize() }}px">
    </a>
@else
    <span class="bg-muted text-muted-foreground inline-flex items-center justify-center rounded-md" style="width: {{ $column->getSize() }}px; height: {{ $column->getSize() }}px"><i class="fa fa-picture-o" aria-hidden="true"></i></span>
@endif
