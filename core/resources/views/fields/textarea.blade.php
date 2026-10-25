<div class="col-md-{{ $field->getColumnSpan() }}">
    @include('cb::fields.partials.label')
    <textarea id="{{ $field->getId() }}" name="{{ $field->getName() }}" rows="{{ $field->getRows() }}"
              class="form-control @if($errors->has($field->getName())) is-invalid @endif"
              @if($field->getPlaceholder()) placeholder="{{ $field->getPlaceholder() }}" @endif
              @include('cb::fields.partials.attributes')>{{ is_scalar($state) ? $state : '' }}</textarea>
    @include('cb::fields.partials.help')
</div>
