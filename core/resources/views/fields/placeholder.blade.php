<div class="col-md-{{ $field->getColumnSpan() }}">
    <div class="form-label">{{ $field->getLabel() }}</div>
    <div class="form-control-plaintext">{{ method_exists($field, 'getContent') ? ($field->getContent() ?? '—') : $state }}</div>
    @if($field->getHelperText())<div class="form-text">{{ $field->getHelperText() }}</div>@endif
</div>
