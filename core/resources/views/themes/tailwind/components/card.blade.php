@props(['title' => null, 'description' => null, 'flush' => false])
<section {{ $attributes->merge(['class' => 'cb-card overflow-hidden']) }}>
    @if($title || isset($actions))
        <header class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-200 px-5 py-4 dark:border-white/10">
            <div>
                @if($title)<h2 class="text-base font-semibold text-gray-900 dark:text-white">{{ $title }}</h2>@endif
                @if($description)<p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">{{ $description }}</p>@endif
            </div>
            @isset($actions)<div class="flex items-center gap-2">{{ $actions }}</div>@endisset
        </header>
    @endif
    <div @class(['p-5' => ! $flush])>{{ $slot }}</div>
    @isset($footer)
        <footer class="border-t border-gray-200 bg-gray-50 px-5 py-3 dark:border-white/10 dark:bg-white/5">{{ $footer }}</footer>
    @endisset
</section>
