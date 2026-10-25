@foreach(app(\CrudBooster\Panel\Panel::class)->getNavigation() as $cbGroup => $cbItems)
    @if($cbGroup !== '')
        <li class="nav-header header">{{ $cbGroup }}</li>
    @endif
    @foreach($cbItems as $cbItem)
        <li class="nav-item">
            <a href="{{ $cbItem->getUrl() }}" class="nav-link {{ $cbItem->isActive() ? 'active' : '' }}"
               @if($cbItem->shouldOpenUrlInNewTab()) target="_blank" rel="noopener" @endif>
                <i class="nav-icon {{ $cbItem->getIcon() }}"></i>
                <p>
                    {{ $cbItem->getLabel() }}
                    @if(($cbBadge = $cbItem->getBadge()) !== null && $cbBadge !== '')
                        <span class="nav-badge badge text-bg-{{ $cbItem->getBadgeColor() }} me-3">{{ $cbBadge }}</span>
                    @endif
                </p>
            </a>
        </li>
    @endforeach
@endforeach
