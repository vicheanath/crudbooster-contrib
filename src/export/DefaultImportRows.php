<?php

namespace crocodicstudio\crudbooster\export;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Reads the first sheet of an import file with slugged headings as keys,
 * matching what Laravel Excel 2.x Excel::load()->get() returned.
 *
 * @deprecated 7.2 Only used by CBController modules. Write your own Laravel Excel import class. See
 *     docs/en/deprecations.md.
 */
class DefaultImportRows implements ToCollection, WithHeadingRow
{
    public function collection(Collection $collection): void
    {
        // Rows are read through toCollection(); nothing to do per sheet.
    }

    public static function read($file)
    {
        $sheet = Excel::toCollection(new self, $file)->first() ?: collect();

        return $sheet->map(function ($row) {
            return (object) $row->toArray();
        });
    }
}
