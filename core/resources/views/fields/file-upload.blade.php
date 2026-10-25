@php $fileUrl = $field->getFileUrl($state); @endphp
<div class="col-md-{{ $field->getColumnSpan() }}">
    @include('cb::fields.partials.label')
    @if($fileUrl)
        <div class="d-flex align-items-center gap-3 mb-2">
            @if($field->isImage())
                <a href="{{ $fileUrl }}" target="_blank" rel="noopener"><img src="{{ $fileUrl }}" alt="" class="rounded border" style="max-height: 80px"></a>
            @else
                <a href="{{ $fileUrl }}" target="_blank" rel="noopener"><i class="fa fa-file-o"></i> {{ basename($state) }}</a>
            @endif
            @unless($field->isDisabled())
                <div class="form-check mb-0">
                    <input type="checkbox" class="form-check-input" name="{{ $field->getRemoveInputName() }}" value="1" id="{{ $field->getId() }}-remove">
                    <label class="form-check-label" for="{{ $field->getId() }}-remove">{{ __('Remove') }}</label>
                </div>
            @endunless
        </div>
    @endif
    <input type="file" id="{{ $field->getId() }}" name="{{ $field->getName() }}"
           class="form-control @if($errors->has($field->getName())) is-invalid @endif"
           @if($field->getAccept()) accept="{{ $field->getAccept() }}" @endif
           @include('cb::fields.partials.attributes')>
    @include('cb::fields.partials.help')
</div>
