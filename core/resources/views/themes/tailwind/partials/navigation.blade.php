{{-- Resources, pages and links registered with the panel, by group. --}}
@foreach(app(\CrudBooster\Panel\Panel::class)->getNavigation() as $cbGroup => $cbItems)
    <div>
        @if($cbGroup !== '')
            <h2 class="text-sidebar-foreground/70 mb-1 flex h-8 items-center px-2 text-xs font-medium">{{ $cbGroup }}</h2>
        @endif
        <ul class="space-y-0.5">
            @foreach($cbItems as $cbItem)
                <li>
                    <a href="{{ $cbItem->getUrl() }}" class="cb-nav-link {{ $cbItem->isActive() ? 'cb-nav-link-active' : '' }}"
                       @if($cbItem->isActive()) aria-current="page" @endif
                       @if($cbItem->shouldOpenUrlInNewTab()) target="_blank" rel="noopener" @endif>
                        <i class="{{ $cbItem->getIcon() }} w-4 text-center" aria-hidden="true"></i>
                        <span class="flex-1">{{ $cbItem->getLabel() }}</span>
                        @if(($cbBadge = $cbItem->getBadge()) !== null && $cbBadge !== '')
                            <span class="cb-badge {{ \CrudBooster\Themes\TailwindTheme::badge($cbItem->getBadgeColor()) }}">{{ $cbBadge }}</span>
                        @endif
                        @if($cbItem->shouldOpenUrlInNewTab())<i class="fa fa-external-link text-xs opacity-60" aria-hidden="true"></i>@endif
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
@endforeach
