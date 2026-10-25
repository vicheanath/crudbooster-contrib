<label id="{{ $field->getId() }}-label" for="{{ $field->getId() }}" class="cb-label">
    {{ $field->getLabel() }}
    @if($field->isRequired())<span class="text-red-500" aria-hidden="true">*</span>@endif
</label>
