@props(['type' => 'info', 'title' => null, 'dismissible' => false])
@php
    $styles = [
        'success' => ['bg-emerald-50 text-emerald-800 ring-emerald-600/20 dark:bg-emerald-500/10 dark:text-emerald-200 dark:ring-emerald-400/20', 'fa-check-circle'],
        'danger' => ['bg-red-50 text-red-800 ring-red-600/20 dark:bg-red-500/10 dark:text-red-200 dark:ring-red-400/20', 'fa-exclamation-circle'],
        'warning' => ['bg-amber-50 text-amber-800 ring-amber-600/20 dark:bg-amber-500/10 dark:text-amber-200 dark:ring-amber-400/20', 'fa-exclamation-triangle'],
        'info' => ['bg-sky-50 text-sky-800 ring-sky-600/20 dark:bg-sky-500/10 dark:text-sky-200 dark:ring-sky-400/20', 'fa-info-circle'],
    ];
    [$classes, $icon] = $styles[$type] ?? $styles['info'];
@endphp
<div {{ $attributes->merge(['class' => 'flex gap-3 rounded-lg p-4 text-sm ring-1 ring-inset '.$classes, 'role' => $type === 'danger' ? 'alert' : 'status']) }} @if($dismissible) data-cb-dismissible @endif>
    <i class="fa {{ $icon }} mt-0.5" aria-hidden="true"></i>
    <div class="min-w-0 flex-1">
        @if($title)<p class="mb-1 font-semibold">{{ $title }}</p>@endif
        {{ $slot }}
    </div>
    @if($dismissible)
        <button type="button" class="cursor-pointer opacity-60 hover:opacity-100" data-cb-dismiss aria-label="{{ __('Dismiss') }}"><i class="fa fa-times" aria-hidden="true"></i></button>
    @endif
</div>
