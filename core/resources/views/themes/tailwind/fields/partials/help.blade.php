@php $cbError = $errors->first($field->getName()) ?: $errors->first($field->getName().'.*'); @endphp
@if($cbError)
    <p class="cb-error" id="{{ $field->getId() }}-error">{{ $cbError }}</p>
@endif
@if($field->getHelperText())
    <p class="cb-help">{{ $field->getHelperText() }}</p>
@endif
