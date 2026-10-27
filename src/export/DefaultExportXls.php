<?php


namespace crocodicstudio\crudbooster\export;


use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

/**
 * @deprecated 7.2 Only used by CBController modules. Write your own Laravel Excel export class. See
 *     docs/en/deprecations.md.
 */
class DefaultExportXls implements FromView
{
    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function view(): View
    {
        return view("crudbooster::export",$this->data);
    }
}