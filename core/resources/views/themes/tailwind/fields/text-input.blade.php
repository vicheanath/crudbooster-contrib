@component('cb::fields.partials.island', ['field' => $field])
    <input type="{{ $field->getType() }}" id="{{ $field->getId() }}" name="{{ $field->getName() }}" value="{{ is_scalar($state) ? $state : '' }}"
           class="cb-input @if($errors->has($field->getName())) cb-input-invalid @endif"
           @if($field->getPlaceholder()) placeholder="{{ $field->getPlaceholder() }}" @endif
           @if($field->getMaxLength()) maxlength="{{ $field->getMaxLength() }}" @endif
           @if($field->getType() === 'number') step="any" @endif
           @if($field->isPassword()) autocomplete="new-password" @endif
           @include('cb::fields.partials.attributes')>
@endcomponent
