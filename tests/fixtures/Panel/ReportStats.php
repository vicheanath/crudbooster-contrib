<?php

namespace crocodicstudio\crudbooster\tests\fixtures\Panel;

class ReportStats
{
    public function productCount(): int
    {
        return Product::query()->count();
    }
}
