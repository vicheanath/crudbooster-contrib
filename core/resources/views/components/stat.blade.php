@props(['label', 'value', 'icon' => null, 'color' => 'primary', 'url' => null, 'description' => null])
<div {{ $attributes->merge(['class' => 'small-box text-bg-'.$color]) }}>
    <div class="inner">
        <h3>{{ $value }}</h3>
        <p>{{ $label }}</p>
        @if($description)<p class="small mb-0">{{ $description }}</p>@endif
    </div>
    @if($icon)<i class="small-box-icon {{ $icon }}" aria-hidden="true"></i>@endif
    @if($url)<a href="{{ $url }}" class="small-box-footer link-light link-underline-opacity-0">{{ __('More info') }} <i class="fa fa-arrow-circle-right"></i></a>@endif
</div>
