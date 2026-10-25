@php $selected = array_map('strval', array_filter((array) $state, fn ($value) => $value !== null)); @endphp
<div class="col-md-{{ $field->getColumnSpan() }}">
    <div class="form-label" id="{{ $field->getId() }}">
        {{ $field->getLabel() }}
        @if($field->isRequired())<span class="text-danger" aria-hidden="true">*</span>@endif
    </div>
    <input type="hidden" name="{{ $field->getName() }}" value="">
    <div role="group" aria-labelledby="{{ $field->getId() }}">
        @foreach($field->getOptions() as $optionValue => $optionLabel)
            <div class="form-check form-check-inline">
                <input type="checkbox" class="form-check-input" name="{{ $field->getName() }}[]" id="{{ $field->getId() }}-{{ $loop->index }}"
                       value="{{ $optionValue }}" @checked(in_array((string) $optionValue, $selected, true)) @if($field->isDisabled()) disabled @endif>
                <label class="form-check-label" for="{{ $field->getId() }}-{{ $loop->index }}">{{ $optionLabel }}</label>
            </div>
        @endforeach
    </div>
    @include('cb::fields.partials.help')
</div>
