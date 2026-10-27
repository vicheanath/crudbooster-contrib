@props(['type' => 'info', 'title' => null, 'dismissible' => false])
<div {{ $attributes->merge(['class' => 'alert alert-'.$type.($dismissible ? ' alert-dismissible fade show' : ''), 'role' => $type === 'danger' ? 'alert' : 'status']) }}>
    @if($title)<h4 class="alert-heading h6">{{ $title }}</h4>@endif
    {{ $slot }}
    @if($dismissible)<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('Dismiss') }}"></button>@endif
</div>
