<div class="form-check mb-0">
    <input type="checkbox" class="form-check-input" name="filters[{{ $filter->getName() }}]" value="1" id="filter-{{ $filter->getName() }}" @checked($filter->isActive($value))>
    <label class="form-check-label small" for="filter-{{ $filter->getName() }}">{{ $filter->getLabel() }}</label>
</div>
