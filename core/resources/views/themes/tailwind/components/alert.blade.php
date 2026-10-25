@props(['type' => 'info', 'title' => null, 'dismissible' => false])
@php
    $styles = [
        'success' => ['text-emerald-700 dark:text-emerald-400', 'fa-check-circle'],
        'danger' => ['text-destructive', 'fa-exclamation-circle'],
        'warning' => ['text-amber-700 dark:text-amber-400', 'fa-exclamation-triangle'],
        'info' => ['', 'fa-info-circle'],
    ];
    [$tone, $icon] = $styles[$type] ?? $styles['info'];
@endphp
{{-- shadcn's Alert: a bordered card; the status colours the icon and the title. --}}
<div {{ $attributes->merge(['class' => 'bg-card flex gap-2.5 rounded-lg border px-3 py-2.5 text-sm', 'role' => $type === 'danger' ? 'alert' : 'status']) }} @if($dismissible) data-cb-dismissible @endif>
    <i class="fa {{ $icon }} {{ $tone }} mt-0.5" aria-hidden="true"></i>
    <div class="min-w-0 flex-1">
        @if($title)<p class="{{ $tone }} mb-0.5 font-medium">{{ $title }}</p>@endif
        <div class="text-muted-foreground">{{ $slot }}</div>
    </div>
    @if($dismissible)
        <button type="button" class="text-muted-foreground hover:text-foreground cursor-pointer" data-cb-dismiss aria-label="{{ __('Dismiss') }}"><i class="fa fa-times" aria-hidden="true"></i></button>
    @endif
</div>
