<fieldset class="col-span-12 rounded-xl border border-gray-200 p-5 dark:border-white/10">
    @if($section->getHeading())
        <legend class="px-2 text-sm font-semibold text-gray-900 dark:text-white">{{ $section->getHeading() }}</legend>
    @endif
    @if($section->getDescription())
        <p class="-mt-1 mb-4 text-sm text-gray-500 dark:text-gray-400">{{ $section->getDescription() }}</p>
    @endif
    <div class="grid grid-cols-12 gap-x-6 gap-y-5">
        @include('cb::forms.components', ['components' => $section->getSchema()])
    </div>
</fieldset>
