@if($state === null)
    <span class="text-body-tertiary">{{ $column->getPlaceholder() }}</span>
@elseif($state)
    <i class="fa fa-check-circle text-success" title="{{ __('Yes') }}"></i><span class="visually-hidden">{{ __('Yes') }}</span>
@else
    <i class="fa fa-times-circle text-danger" title="{{ __('No') }}"></i><span class="visually-hidden">{{ __('No') }}</span>
@endif
