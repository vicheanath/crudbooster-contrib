<select name="filters[{{ $filter->getName() }}]" class="cb-input w-auto py-1.5" aria-label="{{ $filter->getLabel() }}">
    <option value="">{{ $filter->getLabel() }}: {{ __('All') }}</option>
    <option value="1" @selected((string) $value === '1')>{{ $filter->getTrueLabel() }}</option>
    <option value="0" @selected((string) $value === '0')>{{ $filter->getFalseLabel() }}</option>
</select>
