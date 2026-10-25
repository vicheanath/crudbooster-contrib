@props(['color' => 'secondary'])
<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium ring-1 ring-inset '.\CrudBooster\Themes\TailwindTheme::badge($color)]) }}>{{ $slot }}</span>
