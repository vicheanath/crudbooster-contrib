@props(['color' => 'secondary'])
<span {{ $attributes->merge(['class' => 'cb-badge '.\CrudBooster\Themes\TailwindTheme::badge($color)]) }}>{{ $slot }}</span>
