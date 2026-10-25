{{-- Resources, pages and links registered with the panel, by group. --}}
@foreach(app(\CrudBooster\Panel\Panel::class)->getNavigation() as $cbGroup => $cbItems)
    <div>
        @if($cbGroup !== '')
            <h2 class="mb-2 px-3 text-xs font-semibold tracking-wide text-gray-400 uppercase dark:text-gray-500">{{ $cbGroup }}</h2>
        @endif
        <ul class="space-y-1">
            @foreach($cbItems as $cbItem)
                <li>
                    <a href="{{ $cbItem->getUrl() }}" class="cb-nav-link {{ $cbItem->isActive() ? 'cb-nav-link-active' : '' }}"
                       @if($cbItem->isActive()) aria-current="page" @endif
                       @if($cbItem->shouldOpenUrlInNewTab()) target="_blank" rel="noopener" @endif>
                        <i class="{{ $cbItem->getIcon() }} w-5 text-center" aria-hidden="true"></i>
                        <span class="flex-1">{{ $cbItem->getLabel() }}</span>
                        @if(($cbBadge = $cbItem->getBadge()) !== null && $cbBadge !== '')
                            <span class="rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset {{ \CrudBooster\Themes\TailwindTheme::badge($cbItem->getBadgeColor()) }}">{{ $cbBadge }}</span>
                        @endif
                        @if($cbItem->shouldOpenUrlInNewTab())<i class="fa fa-external-link text-xs text-gray-400" aria-hidden="true"></i>@endif
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
@endforeach
