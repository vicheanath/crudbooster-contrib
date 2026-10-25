@props(['color' => 'secondary'])
<span {{ $attributes->merge(['class' => 'badge text-bg-'.$color]) }}>{{ $slot }}</span>
