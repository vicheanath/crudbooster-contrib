@component('cb::fields.partials.island', ['field' => $field])
    <textarea id="{{ $field->getId() }}" name="{{ $field->getName() }}" rows="{{ $field->getRows() }}"
              class="cb-input"
              @if($field->getPlaceholder()) placeholder="{{ $field->getPlaceholder() }}" @endif
              @include('cb::fields.partials.attributes')>{{ is_scalar($state) ? $state : '' }}</textarea>
@endcomponent
