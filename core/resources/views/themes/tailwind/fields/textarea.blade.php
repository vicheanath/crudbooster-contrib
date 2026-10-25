@component('cb::fields.partials.island', ['field' => $field])
    <textarea id="{{ $field->getId() }}" name="{{ $field->getName() }}" rows="{{ $field->getRows() }}"
              class="cb-input @if($errors->has($field->getName())) cb-input-invalid @endif"
              @if($field->getPlaceholder()) placeholder="{{ $field->getPlaceholder() }}" @endif
              @include('cb::fields.partials.attributes')>{{ is_scalar($state) ? $state : '' }}</textarea>
@endcomponent
