<?php

namespace crocodicstudio\crudbooster\export;

/**
 * @deprecated 7.2 Only used by CBController modules. Use Excel::download(..., \Maatwebsite\Excel\Excel::CSV)
 *     in a BulkAction. See docs/en/deprecations.md.
 */
class CsvExporter extends ExcelExporter
{
    protected string $extension = 'csv';

    public function label(): string
    {
        return 'CSV';
    }
}
