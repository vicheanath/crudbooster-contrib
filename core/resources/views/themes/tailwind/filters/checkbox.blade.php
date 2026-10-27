<label class="cb-btn has-checked:bg-muted has-checked:border-ring font-normal">
    <input type="checkbox" class="accent-primary size-4" name="filters[{{ $filter->getName() }}]" value="1" @checked($filter->isActive($value))>
    {{ $filter->getLabel() }}
</label>
