@component('cb::fields.partials.island', ['field' => $field])
    <div role="radiogroup" aria-labelledby="{{ $field->getId() }}-label" class="flex flex-wrap gap-4">
        @foreach($field->getOptions() as $optionValue => $optionLabel)
            <label class="inline-flex items-center gap-2 text-sm">
                <input type="radio" class="size-4 accent-primary" name="{{ $field->getName() }}" value="{{ $optionValue }}"
                       @checked((string) $state === (string) $optionValue) @if($field->isDisabled()) disabled @endif> {{ $optionLabel }}
            </label>
        @endforeach
    </div>
@endcomponent
