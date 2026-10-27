@props(['title' => null, 'description' => null, 'flush' => false])
<div {{ $attributes->merge(['class' => 'card']) }}>
    @if($title || isset($actions))
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                @if($title)<h3 class="card-title mb-0">{{ $title }}</h3>@endif
                @if($description)<div class="small text-body-secondary">{{ $description }}</div>@endif
            </div>
            @isset($actions)<div class="d-flex gap-2">{{ $actions }}</div>@endisset
        </div>
    @endif
    <div @class(['card-body', 'p-0' => $flush])>{{ $slot }}</div>
    @isset($footer)<div class="card-footer">{{ $footer }}</div>@endisset
</div>
