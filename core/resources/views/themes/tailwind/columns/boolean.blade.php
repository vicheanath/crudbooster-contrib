@if($state === null)
    <span class="text-gray-400 dark:text-gray-600">{{ $column->getPlaceholder() }}</span>
@elseif($state)
    <i class="fa fa-check-circle text-lg text-emerald-500" title="{{ __('Yes') }}" aria-hidden="true"></i><span class="sr-only">{{ __('Yes') }}</span>
@else
    <i class="fa fa-times-circle text-lg text-gray-300 dark:text-gray-600" title="{{ __('No') }}" aria-hidden="true"></i><span class="sr-only">{{ __('No') }}</span>
@endif
