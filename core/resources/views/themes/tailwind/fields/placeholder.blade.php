<div class="col-span-12 {{ \CrudBooster\Themes\TailwindTheme::span($field->getColumnSpan()) }}">
    <div class="cb-label">{{ $field->getLabel() }}</div>
    <div class="text-sm text-gray-700 dark:text-gray-300">{{ method_exists($field, 'getContent') ? ($field->getContent() ?? '—') : $state }}</div>
    @if($field->getHelperText())<p class="cb-help">{{ $field->getHelperText() }}</p>@endif
</div>
