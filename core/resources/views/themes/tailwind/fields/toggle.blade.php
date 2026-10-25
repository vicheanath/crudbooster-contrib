@component('cb::fields.partials.island', ['field' => $field, 'bare' => true])
    <label class="inline-flex items-center gap-2 text-sm font-medium">
        <input type="hidden" name="{{ $field->getName() }}" value="0">
        <input type="checkbox" class="size-4 rounded accent-brand-600" id="{{ $field->getId() }}" name="{{ $field->getName() }}" value="1"
               @checked(filter_var($state, FILTER_VALIDATE_BOOLEAN)) @if($field->isDisabled()) disabled @endif>
        {{ $field->getLabel() }}
    </label>
@endcomponent
