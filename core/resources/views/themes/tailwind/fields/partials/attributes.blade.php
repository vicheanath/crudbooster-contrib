@foreach($field->getExtraAttributes() as $cbAttribute => $cbValue) {{ $cbAttribute }}="{{ $cbValue }}" @endforeach
@if($field->isDisabled()) disabled @endif
@if($field->isRequired()) required @endif
@if($errors->has($field->getName()) || $errors->has($field->getName().'.*')) aria-invalid="true" aria-describedby="{{ $field->getId() }}-error" @endif
