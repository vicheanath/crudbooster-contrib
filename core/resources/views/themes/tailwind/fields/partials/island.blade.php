{{--
    A field rendered by its React component ($field->getComponent()). The slot is plain HTML that
    submits the same values; it shows until the script has loaded, or if it does not load.
    Set $bare to leave out the label (for fields that label themselves).
--}}
@php
    $cbInvalid = $errors->has($field->getName()) || $errors->has($field->getName().'.*');
    $cbProps = $field->getComponentProps() + ['invalid' => $cbInvalid];
@endphp
<div class="col-span-12 {{ \CrudBooster\Themes\TailwindTheme::span($field->getColumnSpan()) }}">
    @unless($bare ?? false)
        @include('cb::fields.partials.label')
    @endunless
    @if($field->getComponent())
        <div data-cb-component="{{ $field->getComponent() }}" data-cb-props="{{ json_encode($cbProps, JSON_INVALID_UTF8_SUBSTITUTE) }}">{{ $slot }}</div>
    @else
        {{ $slot }}
    @endif
    @include('cb::fields.partials.help')
</div>
