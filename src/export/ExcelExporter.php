<?php

namespace crocodicstudio\crudbooster\export;

use crocodicstudio\crudbooster\contracts\Export\ExporterInterface;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;

class ExcelExporter implements ExporterInterface
{
    protected string $extension = 'xls';

    public function label(): string
    {
        return 'Microsoft Excel (xls)';
    }

    public function export(array $data, array $options): Response
    {
        return Excel::download(new DefaultExportXls($data), ($options['filename'] ?? 'export').'.'.$this->extension);
    }
}
