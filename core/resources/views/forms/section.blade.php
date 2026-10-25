<div class="col-12">
    <fieldset class="border rounded-3 p-3">
        @if($section->getHeading())
            <legend class="float-none w-auto px-2 fs-6 fw-semibold mb-0">{{ $section->getHeading() }}</legend>
        @endif
        @if($section->getDescription())
            <p class="text-body-secondary small">{{ $section->getDescription() }}</p>
        @endif
        <div class="row g-3">
            @include('cb::forms.components', ['components' => $section->getSchema()])
        </div>
    </fieldset>
</div>
