<?php

namespace CrudBooster\Tables\Columns;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class TextColumn extends Column
{
    protected ?int $limit = null;

    protected ?string $prefix = null;

    protected ?string $suffix = null;

    protected ?string $dateFormat = null;

    protected ?array $number = null;

    protected bool $html = false;

    public function limit(int $characters): static
    {
        $this->limit = $characters;

        return $this;
    }

    public function prefix(string $prefix): static
    {
        $this->prefix = $prefix;

        return $this;
    }

    public function suffix(string $suffix): static
    {
        $this->suffix = $suffix;

        return $this;
    }

    public function numeric(int $decimals = 0, string $decimalSeparator = '.', string $thousandsSeparator = ','): static
    {
        $this->number = [$decimals, $decimalSeparator, $thousandsSeparator];

        return $this->alignEnd();
    }

    /**
     * Two decimals and a currency prefix: money('$') shows "$1,250.00".
     */
    public function money(string $symbol = '', int $decimals = 2): static
    {
        $this->prefix = $symbol;

        return $this->numeric($decimals);
    }

    public function date(string $format = 'Y-m-d'): static
    {
        $this->dateFormat = $format;

        return $this;
    }

    public function dateTime(string $format = 'Y-m-d H:i'): static
    {
        return $this->date($format);
    }

    /**
     * Shows the value as HTML instead of escaping it. Only for values you trust.
     */
    public function html(bool $html = true): static
    {
        $this->html = $html;

        return $this;
    }

    protected function formatScalar(mixed $state): mixed
    {
        $state = parent::formatScalar($state);

        if ($this->dateFormat) {
            $state = $state instanceof CarbonInterface ? $state : Carbon::parse($state);
            $state = $state->format($this->dateFormat);
        } elseif ($this->number && is_numeric($state)) {
            $state = number_format((float) $state, ...$this->number);
        } elseif ($this->limit && ! $this->html) {
            $state = Str::limit(strip_tags((string) $state), $this->limit);
        }

        $state = $this->prefix.$state.$this->suffix;

        return $this->html ? new HtmlString($state) : $state;
    }
}
