<?php

namespace crocodicstudio\crudbooster\export;

class CsvExporter extends ExcelExporter
{
    protected string $extension = 'csv';

    public function label(): string
    {
        return 'CSV';
    }
}
