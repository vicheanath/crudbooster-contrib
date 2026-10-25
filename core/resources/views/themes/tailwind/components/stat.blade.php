@props(['label', 'value', 'icon' => null, 'color' => 'primary', 'url' => null, 'description' => null])
@php $tag = $url ? 'a' : 'div'; @endphp
<{{ $tag }} @if($url) href="{{ $url }}" @endif {{ $attributes->merge(['class' => 'cb-card flex items-center gap-4 p-5'.($url ? ' transition hover:ring-brand-300 dark:hover:ring-brand-500/50' : '')]) }}>
    @if($icon)
        <span class="flex size-12 shrink-0 items-center justify-center rounded-lg text-lg text-white {{ \CrudBooster\Themes\TailwindTheme::iconBackground($color) }}"><i class="{{ $icon }}" aria-hidden="true"></i></span>
    @endif
    <div class="min-w-0">
        <p class="truncate text-sm text-gray-500 dark:text-gray-400">{{ $label }}</p>
        <p class="text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">{{ $value }}</p>
        @if($description)<p class="text-xs text-gray-500 dark:text-gray-400">{{ $description }}</p>@endif
    </div>
</{{ $tag }}>
