<?php

namespace crocodicstudio\crudbooster\tests\fixtures\Panel;

use CrudBooster\Pages\Page;
use Illuminate\Http\Request;

class SalesReport extends Page
{
    protected static ?string $navigationIcon = 'fa fa-line-chart';

    protected static string $view = 'panel-fixtures::sales-report';

    public function data(ReportStats $stats): array
    {
        return ['count' => $stats->productCount()];
    }

    public function submit(Request $request)
    {
        $request->validate(['note' => 'required']);

        return back()->with(['message' => 'Saved '.e($request->input('note')), 'message_type' => 'success']);
    }
}
