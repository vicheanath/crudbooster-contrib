<div class="btn-group btn-group-sm" role="group">
    @foreach($table->getActions() as $action)
        @php $action = $action->for(['record' => $record]); @endphp
        @continue(! $action->isVisible() || ($action->getAbility() && ! $resource->can($action->getAbility(), $record)))
        @if($action->isLink())
            <a href="{{ $action->getUrl() }}" class="btn btn-outline-{{ $action->getColor() }}" title="{{ $action->getLabel() }}"
               @if($action->shouldOpenUrlInNewTab()) target="_blank" rel="noopener" @endif>
                @if($action->getIcon())<i class="{{ $action->getIcon() }}"></i>@else{{ $action->getLabel() }}@endif
            </a>
        @else
            <button type="submit" form="cb-action-form" class="btn btn-outline-{{ $action->getColor() }}" title="{{ $action->getLabel() }}"
                    formaction="{{ route($resource::getRouteName('action'), ['record' => $record->getKey(), 'action' => $action->getName()]) }}"
                    @if($message = $action->getConfirmationMessage()) onclick="return confirm(@js($message))" @endif>
                @if($action->getIcon())<i class="{{ $action->getIcon() }}"></i>@else{{ $action->getLabel() }}@endif
            </button>
        @endif
    @endforeach

    @if($table->hasDefaultActions())
        @if($resource::hasPage('view') && $resource->can('view', $record))
            <a href="{{ $resource::url('view', $record) }}" class="btn btn-outline-primary" title="{{ __('View') }}"><i class="fa fa-eye"></i></a>
        @endif
        @if($resource::hasPage('edit') && $resource->can('update', $record))
            <a href="{{ $resource::url('edit', $record) }}" class="btn btn-outline-success" title="{{ __('Edit') }}"><i class="fa fa-pencil"></i></a>
        @endif
        @if($resource->can('delete', $record))
            <button type="submit" form="cb-delete-form" class="btn btn-outline-danger" title="{{ __('Delete') }}"
                    formaction="{{ route($resource::getRouteName('destroy'), ['record' => $record->getKey()]) }}"
                    onclick="return confirm(@js(__('Delete this record?')))">
                <i class="fa fa-trash"></i>
            </button>
        @endif
    @endif
</div>
