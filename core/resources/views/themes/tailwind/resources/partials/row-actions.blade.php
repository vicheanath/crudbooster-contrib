<div class="inline-flex items-center gap-0.5">
    @foreach($table->getActions() as $action)
        @php $action = $action->for(['record' => $record]); @endphp
        @continue(! $action->isVisible() || ($action->getAbility() && ! $resource->can($action->getAbility(), $record)))
        @if($action->isLink())
            <a href="{{ $action->getUrl() }}" class="cb-icon-btn {{ \CrudBooster\Themes\TailwindTheme::text($action->getColor()) }}" title="{{ $action->getLabel() }}"
               @if($action->shouldOpenUrlInNewTab()) target="_blank" rel="noopener" @endif>
                @if($action->getIcon())<i class="{{ $action->getIcon() }}" aria-hidden="true"></i><span class="sr-only">{{ $action->getLabel() }}</span>@else<span class="px-1 text-xs font-medium">{{ $action->getLabel() }}</span>@endif
            </a>
        @else
            <button type="submit" form="cb-action-form" class="cb-icon-btn {{ \CrudBooster\Themes\TailwindTheme::text($action->getColor()) }}" title="{{ $action->getLabel() }}"
                    formaction="{{ route($resource::getRouteName('action'), ['record' => $record->getKey(), 'action' => $action->getName()]) }}"
                    @if($message = $action->getConfirmationMessage()) data-cb-confirm="{{ $message }}" data-cb-confirm-color="{{ $action->getColor() }}" @endif>
                @if($action->getIcon())<i class="{{ $action->getIcon() }}" aria-hidden="true"></i><span class="sr-only">{{ $action->getLabel() }}</span>@else<span class="px-1 text-xs font-semibold">{{ $action->getLabel() }}</span>@endif
            </button>
        @endif
    @endforeach

    @if($table->hasDefaultActions())
        @if($resource::hasPage('view') && $resource->can('view', $record))
            <a href="{{ $resource::url('view', $record) }}" class="cb-icon-btn" title="{{ __('View') }}"><i class="fa fa-eye" aria-hidden="true"></i><span class="sr-only">{{ __('View') }}</span></a>
        @endif
        @if($resource::hasPage('edit') && $resource->can('update', $record))
            <a href="{{ $resource::url('edit', $record) }}" class="cb-icon-btn" title="{{ __('Edit') }}"><i class="fa fa-pencil" aria-hidden="true"></i><span class="sr-only">{{ __('Edit') }}</span></a>
        @endif
        @if($resource->can('delete', $record))
            <button type="submit" form="cb-delete-form" class="cb-icon-btn hover:bg-destructive/10 hover:text-destructive" title="{{ __('Delete') }}"
                    formaction="{{ route($resource::getRouteName('destroy'), ['record' => $record->getKey()]) }}"
                    data-cb-confirm="{{ __('Delete this record?') }}" data-cb-confirm-color="danger" data-cb-confirm-label="{{ __('Delete') }}">
                <i class="fa fa-trash-o" aria-hidden="true"></i><span class="sr-only">{{ __('Delete') }}</span>
            </button>
        @endif
    @endif
</div>
