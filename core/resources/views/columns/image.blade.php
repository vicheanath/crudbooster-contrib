@php $src = $column->getImageUrl($state); @endphp
@if($src)
    <a href="{{ $url ?: $src }}" target="_blank" rel="noopener">
        <img src="{{ $src }}" alt="" loading="lazy" width="{{ $column->getSize() }}" height="{{ $column->getSize() }}"
             class="{{ $column->isCircular() ? 'rounded-circle' : 'rounded' }} border" style="object-fit: cover">
    </a>
@else
    <span class="text-body-tertiary">{{ $column->getPlaceholder() }}</span>
@endif
