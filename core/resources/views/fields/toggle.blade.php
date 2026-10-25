<div class="col-md-{{ $field->getColumnSpan() }}">
    <div class="form-check form-switch">
        <input type="hidden" name="{{ $field->getName() }}" value="0">
        <input type="checkbox" role="switch" class="form-check-input" id="{{ $field->getId() }}" name="{{ $field->getName() }}" value="1"
               @checked(filter_var($state, FILTER_VALIDATE_BOOLEAN)) @if($field->isDisabled()) disabled @endif>
        <label class="form-check-label" for="{{ $field->getId() }}">{{ $field->getLabel() }}</label>
    </div>
    @include('cb::fields.partials.help')
</div>
