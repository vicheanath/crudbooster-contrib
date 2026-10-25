@props(['label', 'value', 'icon' => null, 'color' => 'primary', 'url' => null, 'description' => null])
@php $tag = $url ? 'a' : 'div'; @endphp
<{{ $tag }} @if($url) href="{{ $url }}" @endif {{ $attributes->merge(['class' => 'cb-card flex items-center gap-4 p-4'.($url ? ' hover:bg-muted/50 transition-colors' : '')]) }}>
    @if($icon)
        <span class="flex size-10 shrink-0 items-center justify-center rounded-lg {{ \CrudBooster\Themes\TailwindTheme::iconBackground($color) }}"><i class="{{ $icon }}" aria-hidden="true"></i></span>
    @endif
    <div class="min-w-0">
        <p class="text-muted-foreground truncate text-sm">{{ $label }}</p>
        <p class="text-2xl font-semibold tracking-tight tabular-nums">{{ $value }}</p>
        @if($description)<p class="text-muted-foreground text-xs">{{ $description }}</p>@endif
    </div>
</{{ $tag }}>
