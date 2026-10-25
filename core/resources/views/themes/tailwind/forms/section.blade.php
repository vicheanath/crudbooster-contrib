<fieldset class="col-span-12 rounded-xl border p-5">
    @if($section->getHeading())
        <legend class="px-2 text-sm font-medium">{{ $section->getHeading() }}</legend>
    @endif
    @if($section->getDescription())
        <p class="text-muted-foreground -mt-1 mb-4 text-sm">{{ $section->getDescription() }}</p>
    @endif
    <div class="grid grid-cols-12 gap-x-6 gap-y-5">
        @include('cb::forms.components', ['components' => $section->getSchema()])
    </div>
</fieldset>
