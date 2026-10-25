<label class="inline-flex cursor-pointer items-center gap-2 rounded-lg px-2.5 py-1.5 text-sm ring-1 ring-inset ring-gray-300 has-checked:bg-brand-50 has-checked:ring-brand-600 dark:ring-white/10 dark:has-checked:bg-brand-500/15">
    <input type="checkbox" class="size-4 rounded accent-brand-600" name="filters[{{ $filter->getName() }}]" value="1" @checked($filter->isActive($value))>
    {{ $filter->getLabel() }}
</label>
