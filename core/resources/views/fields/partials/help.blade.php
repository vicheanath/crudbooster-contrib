@php $cbError = $errors->first($field->getName()) ?: $errors->first($field->getName().'.*'); @endphp
@if($cbError)
    <div class="invalid-feedback d-block">{{ $cbError }}</div>
@endif
@if($field->getHelperText())
    <div class="form-text">{{ $field->getHelperText() }}</div>
@endif
