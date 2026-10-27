@php $fileUrl = $field->getFileUrl($state); @endphp
@component('cb::fields.partials.island', ['field' => $field])
    @if($fileUrl)
        <div class="mb-2 flex items-center gap-3 text-sm">
            <a href="{{ $fileUrl }}" target="_blank" rel="noopener" class="font-medium underline-offset-4 hover:underline">{{ basename($state) }}</a>
            @unless($field->isDisabled())
                <label class="inline-flex items-center gap-1.5"><input type="checkbox" name="{{ $field->getRemoveInputName() }}" value="1"> {{ __('Remove') }}</label>
            @endunless
        </div>
    @endif
    <input type="file" id="{{ $field->getId() }}" name="{{ $field->getName() }}" class="cb-input"
           @if($field->getAccept()) accept="{{ $field->getAccept() }}" @endif @include('cb::fields.partials.attributes')>
@endcomponent
