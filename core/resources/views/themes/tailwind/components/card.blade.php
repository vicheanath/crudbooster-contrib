@props(['title' => null, 'description' => null, 'flush' => false])
{{-- shadcn's Card (Nova): header, content and an optional muted footer. --}}
<section {{ $attributes->merge(['class' => 'cb-card flex flex-col gap-4 overflow-hidden py-4']) }}>
    @if($title || isset($actions))
        <header @class(['flex flex-wrap items-start justify-between gap-3 px-4', 'border-b pb-4' => $flush])>
            <div class="grid gap-1">
                @if($title)<h2 class="text-base leading-snug font-medium">{{ $title }}</h2>@endif
                @if($description)<p class="text-muted-foreground text-sm">{{ $description }}</p>@endif
            </div>
            @isset($actions)<div class="flex items-center gap-2">{{ $actions }}</div>@endisset
        </header>
    @endif
    <div @class(['px-4' => ! $flush, '-mt-4' => $flush])>{{ $slot }}</div>
    @isset($footer)
        <footer class="bg-muted/50 -mb-4 border-t p-4">{{ $footer }}</footer>
    @endisset
</section>
