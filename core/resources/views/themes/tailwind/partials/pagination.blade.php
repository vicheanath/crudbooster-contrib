@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex items-center gap-1">
        @php
            $link = 'inline-flex size-8 items-center justify-center rounded-lg text-sm transition-all';
            $idle = $link.' hover:bg-muted hover:text-foreground';
        @endphp
        @if ($paginator->onFirstPage())
            <span class="{{ $link }} text-muted-foreground opacity-50" aria-disabled="true"><i class="fa fa-angle-left" aria-hidden="true"></i><span class="sr-only">{{ __('Previous') }}</span></span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="{{ $idle }}"><i class="fa fa-angle-left" aria-hidden="true"></i><span class="sr-only">{{ __('Previous') }}</span></a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="{{ $link }} text-muted-foreground">{{ $element }}</span>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span aria-current="page" class="{{ $link }} border-border bg-background dark:bg-input/30 border font-medium">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="{{ $idle }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="{{ $idle }}"><i class="fa fa-angle-right" aria-hidden="true"></i><span class="sr-only">{{ __('Next') }}</span></a>
        @else
            <span class="{{ $link }} text-muted-foreground opacity-50" aria-disabled="true"><i class="fa fa-angle-right" aria-hidden="true"></i><span class="sr-only">{{ __('Next') }}</span></span>
        @endif
    </nav>
@endif
