@php $invalid = $errors->has($field->getName()); @endphp
<div class="col-md-{{ $field->getColumnSpan() }}">
    @include('cb::fields.partials.label')
    @if($field->getPrefix() || $field->getSuffix())<div class="input-group @if($invalid) has-validation @endif">@endif
        @if($field->getPrefix())<span class="input-group-text">{{ $field->getPrefix() }}</span>@endif
        <input type="{{ $field->getType() }}" id="{{ $field->getId() }}" name="{{ $field->getName() }}"
               value="{{ is_scalar($state) ? $state : '' }}"
               class="form-control {{ $field->getType() === 'color' ? 'form-control-color' : '' }} @if($invalid) is-invalid @endif"
               @if($field->getPlaceholder()) placeholder="{{ $field->getPlaceholder() }}" @endif
               @if($field->getMaxLength()) maxlength="{{ $field->getMaxLength() }}" @endif
               @if($field->getType() === 'number') step="any" @endif
               @if($field->isPassword()) autocomplete="new-password" @endif
               @include('cb::fields.partials.attributes')>
        @if($field->getSuffix())<span class="input-group-text">{{ $field->getSuffix() }}</span>@endif
    @if($field->getPrefix() || $field->getSuffix())</div>@endif
    @include('cb::fields.partials.help')
</div>
