<label id="{{ $field->getId() }}-label" for="{{ $field->getId() }}" class="cb-label">
    {{ $field->getLabel() }}
    @if($field->isRequired())<span class="text-destructive" aria-hidden="true">*</span>@endif
</label>
