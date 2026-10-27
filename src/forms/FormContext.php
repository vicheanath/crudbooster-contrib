<?php

namespace crocodicstudio\crudbooster\forms;

/**
 * What a form is saving: the module's table, and the row once it has an id.
 *
 * @deprecated 7.2 Only used by CBController modules. See docs/en/deprecations.md.
 */
final class FormContext
{
    public const CREATE = 'create';

    public const EDIT = 'edit';

    /**
     * @param  mixed  $recordId  The row being edited, or the new row's id after an insert.
     * @param  mixed  $insertId  What insertGetId() returned; differs from $recordId for UUID keys.
     */
    public function __construct(
        public readonly string $table,
        public readonly string $primaryKey = 'id',
        public readonly string $mode = self::CREATE,
        public readonly mixed $recordId = null,
        public readonly mixed $insertId = null,
    ) {
    }

    public function isEdit(): bool
    {
        return $this->mode === self::EDIT;
    }

    public function withRecord(mixed $recordId, mixed $insertId = null): self
    {
        return new self($this->table, $this->primaryKey, $this->mode, $recordId, $insertId ?? $recordId);
    }
}
