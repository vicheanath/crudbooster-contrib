@if($state === null)
    <span class="text-muted-foreground">{{ $column->getPlaceholder() }}</span>
@elseif($state)
    <i class="fa fa-check-circle text-base text-emerald-600 dark:text-emerald-400" title="{{ __('Yes') }}" aria-hidden="true"></i><span class="sr-only">{{ __('Yes') }}</span>
@else
    <i class="fa fa-times-circle text-muted-foreground/50 text-base" title="{{ __('No') }}" aria-hidden="true"></i><span class="sr-only">{{ __('No') }}</span>
@endif
