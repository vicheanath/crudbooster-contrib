@php
    $multiple = $field->isMultiple();
    $selected = array_map('strval', array_filter((array) $state, fn ($value) => $value !== null));
@endphp
@component('cb::fields.partials.island', ['field' => $field])
    @if($multiple)<input type="hidden" name="{{ $field->getName() }}" value="">@endif
    <select id="{{ $field->getId() }}" name="{{ $field->getName() }}{{ $multiple ? '[]' : '' }}" class="cb-input" @if($multiple) multiple @endif
            @include('cb::fields.partials.attributes')>
        @unless($multiple)<option value="">{{ $field->getPlaceholder() ?? __('Select an option') }}</option>@endunless
        @foreach($field->getOptions() as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @selected(in_array((string) $optionValue, $selected, true))>{{ $optionLabel }}</option>
        @endforeach
    </select>
@endcomponent
