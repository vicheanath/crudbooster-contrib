<?php

namespace CrudBooster\Forms\Fields;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * The browser's date, datetime or time picker.
 */
class DatePicker extends TextInput
{
    protected ?string $component = 'DatePicker';

    protected function setUp(): void
    {
        $this->type('date');
    }

    public function withTime(bool $withTime = true): static
    {
        return $this->type($withTime ? 'datetime-local' : 'date');
    }

    public function timeOnly(): static
    {
        return $this->type('time');
    }

    protected function getRecordState(Model $record): mixed
    {
        $state = parent::getRecordState($record);
        if (! $state) {
            return $state;
        }

        $state = $state instanceof CarbonInterface ? $state : Carbon::parse($state);

        return $state->format(match ($this->type) {
            'datetime-local' => 'Y-m-d\TH:i',
            'time' => 'H:i',
            default => 'Y-m-d',
        });
    }

    protected function getTypeRules(): array
    {
        return $this->type === 'time' ? ['date_format:H:i'] : ['date'];
    }
}
