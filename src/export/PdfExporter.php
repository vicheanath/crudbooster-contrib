<?php

namespace crocodicstudio\crudbooster\export;

use crocodicstudio\crudbooster\contracts\Export\ExporterInterface;
use Symfony\Component\HttpFoundation\Response;

class PdfExporter implements ExporterInterface
{
    public function label(): string
    {
        return 'PDF';
    }

    public function export(array $data, array $options): Response
    {
        $pdf = app('dompdf.wrapper');
        $pdf->loadHTML(view('crudbooster::export', $data)->render());
        $pdf->setPaper($options['page_size'] ?? 'A4', $options['page_orientation'] ?? 'portrait');

        return $pdf->stream(($options['filename'] ?? 'export').'.pdf');
    }
}
