@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination') }}" class="flex items-center gap-1">
        @php $link = 'inline-flex min-w-8 h-8 items-center justify-center rounded-md px-2 text-sm'; @endphp
        @if ($paginator->onFirstPage())
            <span class="{{ $link }} text-gray-300 dark:text-gray-600" aria-disabled="true"><i class="fa fa-angle-left" aria-hidden="true"></i><span class="sr-only">{{ __('Previous') }}</span></span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="{{ $link }} text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/10"><i class="fa fa-angle-left" aria-hidden="true"></i><span class="sr-only">{{ __('Previous') }}</span></a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="{{ $link }} text-gray-400">{{ $element }}</span>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span aria-current="page" class="{{ $link }} bg-brand-600 font-semibold text-white">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="{{ $link }} text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/10">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="{{ $link }} text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/10"><i class="fa fa-angle-right" aria-hidden="true"></i><span class="sr-only">{{ __('Next') }}</span></a>
        @else
            <span class="{{ $link }} text-gray-300 dark:text-gray-600" aria-disabled="true"><i class="fa fa-angle-right" aria-hidden="true"></i><span class="sr-only">{{ __('Next') }}</span></span>
        @endif
    </nav>
@endif
