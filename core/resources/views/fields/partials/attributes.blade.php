@foreach($field->getExtraAttributes() as $cbAttribute => $cbValue) {{ $cbAttribute }}="{{ $cbValue }}" @endforeach
@if($field->isDisabled()) disabled @endif
@if($field->isRequired()) required @endif
