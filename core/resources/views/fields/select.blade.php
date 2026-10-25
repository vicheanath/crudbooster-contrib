@php
    $multiple = $field->isMultiple();
    $selected = array_map('strval', array_filter((array) $state, fn ($value) => $value !== null));
    $invalid = $errors->has($field->getName()) || $errors->has($field->getName().'.*');
@endphp
<div class="col-md-{{ $field->getColumnSpan() }}">
    @include('cb::fields.partials.label')
    @if($multiple)<input type="hidden" name="{{ $field->getName() }}" value="">@endif
    <select id="{{ $field->getId() }}" name="{{ $field->getName() }}{{ $multiple ? '[]' : '' }}"
            class="form-select @if($invalid) is-invalid @endif" @if($multiple) multiple size="{{ min(8, max(3, count($field->getOptions()))) }}" @endif
            @include('cb::fields.partials.attributes')>
        @unless($multiple)
            <option value="">{{ $field->getPlaceholder() ?? __('Select an option') }}</option>
        @endunless
        @foreach($field->getOptions() as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @selected(in_array((string) $optionValue, $selected, true))>{{ $optionLabel }}</option>
        @endforeach
    </select>
    @include('cb::fields.partials.help')
</div>
