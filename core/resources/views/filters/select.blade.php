<select name="filters[{{ $filter->getName() }}]" class="form-select form-select-sm w-auto" aria-label="{{ $filter->getLabel() }}">
    <option value="">{{ $filter->getLabel() }}: {{ __('All') }}</option>
    @foreach($filter->getOptions($model) as $optionValue => $optionLabel)
        <option value="{{ $optionValue }}" @selected((string) $value === (string) $optionValue)>{{ $optionLabel }}</option>
    @endforeach
</select>
