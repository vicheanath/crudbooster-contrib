@props(['href' => null, 'color' => 'secondary', 'icon' => null, 'type' => 'button', 'size' => null])
@php
    $classes = 'cb-btn'.($size === 'sm' ? ' cb-btn-sm' : '').($color !== 'secondary' ? ' ring-0 '.\CrudBooster\Themes\TailwindTheme::button($color) : '');
@endphp
@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>@if($icon)<i class="{{ $icon }}" aria-hidden="true"></i>@endif{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>@if($icon)<i class="{{ $icon }}" aria-hidden="true"></i>@endif{{ $slot }}</button>
@endif
