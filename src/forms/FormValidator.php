<?php

namespace crocodicstudio\crudbooster\forms;

use crocodicstudio\crudbooster\contracts\Forms\FormValidatorInterface;
use crocodicstudio\crudbooster\contracts\Schema\SchemaInspectorInterface;
use crocodicstudio\crudbooster\forms\handlers\MoneyFieldHandler;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Builds Laravel rules from the form definition ('required', 'min', 'max', 'image', 'mimes',
 * 'validation', child columns) and validates the request.
 *
 * @deprecated 7.2 Only used by CBController modules; resource fields add their own rules. See
 *     docs/en/deprecations.md.
 */
class FormValidator implements FormValidatorInterface
{
    public function __construct(private readonly SchemaInspectorInterface $schema)
    {
    }

    public function validate(array $fields, FormContext $context): void
    {
        $input = $this->input($fields);
        $validator = Validator::make($input, $this->rules($fields, $input, $context));

        if (! $validator->fails()) {
            return;
        }

        $errors = $validator->messages();
        $message = cbLang('alert_validation_error', ['error' => implode(', ', $errors->all())]);

        if (Request::ajax()) {
            throw new HttpResponseException(response()->json(['message' => $message, 'message_type' => 'warning']));
        }

        throw new HttpResponseException(
            redirect()->back()->with('errors', $errors)->with(['message' => $message, 'message_type' => 'warning'])->withInput()
        );
    }

    public function rules(array $fields, array $input, FormContext $context): array
    {
        $rules = [];

        foreach ($fields as $field) {
            $name = $field['name'] ?? null;

            // Empty inputs (null via ConvertEmptyStringsToNull) are skipped, as in 5.x: modules commonly
            // declare hidden `required` fields that hook_before_add/edit fills in after validation.
            if (! $name || ! isset($input[$name])) {
                continue;
            }

            $type = $field['type'] ?? null;

            if ($type == 'child') {
                $rules = array_merge($rules, $this->childRules($field));
            }

            $rules[$name] = ! empty($field['validation'])
                ? $this->resolveUniqueRules($field['validation'], $name, $context)
                : implode('|', $this->implicitRules($field, $context));
        }

        return $rules;
    }

    /**
     * The request input, with money fields cleaned the way they are saved.
     */
    protected function input(array $fields): array
    {
        $input = Request::all();

        foreach ($fields as $field) {
            $name = $field['name'] ?? null;
            if ($name && ($field['type'] ?? null) == 'money' && isset($input[$name])) {
                $input[$name] = MoneyFieldHandler::clean($input[$name], $field);
            }
        }

        return $input;
    }

    /**
     * Rules from the field's 'required', 'min', 'max', 'image' and 'mimes' keys.
     */
    protected function implicitRules(array $field, FormContext $context): array
    {
        $rules = [];

        if (($field['type'] ?? null) != 'upload') {
            if (! empty($field['required'])) {
                $rules[] = 'required';
            }
        } elseif ($context->recordId) {
            // An upload is required on edit when the row has no file yet.
            $row = DB::table($context->table)->where($context->primaryKey, $context->recordId)->first();
            if (($row->{$field['name']} ?? '') == '') {
                $rules[] = 'required';
            }
        }

        foreach (['min', 'max', 'mimes'] as $key) {
            if (! empty($field[$key])) {
                $rules[] = $key.':'.$field[$key];
            }
        }
        if (! empty($field['image'])) {
            $rules[] = 'image';
        }

        return $rules;
    }

    protected function childRules(array $field): array
    {
        $rules = [];
        $prefix = str_slug($field['label'], '');

        foreach ($field['columns'] ?? [] as $column) {
            if (! isset($column['validation'])) {
                continue;
            }
            // https://laracasts.com/discuss/channels/general-discussion/array-validation-is-not-working/
            if (strpos($column['validation'], 'required') !== false) {
                $rules[$prefix.'-'.$column['name']] = 'required';
            }
            $rules[$prefix.'-'.$column['name'].'.*'] = $column['validation'];
        }

        return $rules;
    }

    /**
     * Completes "unique[:table,column,ignoreId]": the table defaults to the module's, the column
     * to the field, the ignored id to the edited row, and soft-deleted rows don't count.
     */
    protected function resolveUniqueRules(string $validation, string $name, FormContext $context): string
    {
        $rules = explode('|', $validation);

        foreach ($rules as &$rule) {
            if (substr($rule, 0, 6) != 'unique') {
                continue;
            }

            // A bare "unique" means the module's table.
            $params = explode(',', preg_replace('/^unique:?/', '', $rule));
            $table = ($params[0] ?? '') ?: $context->table;
            $column = ($params[1] ?? '') ?: $name;
            $ignoreId = ($params[2] ?? '') ?: ($context->recordId ?: '');

            $tableParts = explode('.', $table);
            $table = end($tableParts);

            $unique = [$table, $column, $ignoreId ?: 'NULL'];
            if ($this->schema->hasColumn($table, 'deleted_at')) {
                array_push($unique, $this->schema->primaryKey($table), 'deleted_at', 'NULL');
            }
            $rule = 'unique:'.implode(',', array_filter($unique));
        }

        return implode('|', $rules);
    }
}
