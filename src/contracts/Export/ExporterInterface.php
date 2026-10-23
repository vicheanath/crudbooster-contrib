<?php

namespace crocodicstudio\crudbooster\contracts\Export;

use Symfony\Component\HttpFoundation\Response;

interface ExporterInterface
{
    /**
     * The name shown in the export dialog's format list.
     */
    public function label(): string;

    /**
     * @param  array  $data  What CBController::getIndex() returns with index_return on:
     *                       `result` (the paginated rows) and `columns`.
     * @param  array  $options  `filename`, `page_size` and `page_orientation`.
     */
    public function export(array $data, array $options): Response;
}
