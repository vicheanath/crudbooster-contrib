@props(['href' => null, 'color' => 'secondary', 'icon' => null, 'type' => 'button', 'size' => null])
@php $classes = 'btn btn-'.$color.($size === 'sm' ? ' btn-sm' : ''); @endphp
@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>@if($icon)<i class="{{ $icon }}"></i> @endif{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>@if($icon)<i class="{{ $icon }}"></i> @endif{{ $slot }}</button>
@endif
