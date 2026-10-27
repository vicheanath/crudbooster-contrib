@php $selected = array_map('strval', array_filter((array) $state, fn ($value) => $value !== null)); @endphp
@component('cb::fields.partials.island', ['field' => $field])
    <input type="hidden" name="{{ $field->getName() }}" value="">
    <div role="group" aria-labelledby="{{ $field->getId() }}-label" class="flex flex-wrap gap-4">
        @foreach($field->getOptions() as $optionValue => $optionLabel)
            <label class="inline-flex items-center gap-2 text-sm">
                <input type="checkbox" class="accent-primary size-4" name="{{ $field->getName() }}[]" value="{{ $optionValue }}"
                       @checked(in_array((string) $optionValue, $selected, true)) @if($field->isDisabled()) disabled @endif> {{ $optionLabel }}
            </label>
        @endforeach
    </div>
@endcomponent
