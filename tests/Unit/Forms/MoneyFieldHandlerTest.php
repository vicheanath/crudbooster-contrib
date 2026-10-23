<?php

namespace crocodicstudio\crudbooster\tests\Unit\Forms;

use crocodicstudio\crudbooster\forms\handlers\MoneyFieldHandler;
use PHPUnit\Framework\TestCase;

class MoneyFieldHandlerTest extends TestCase
{
    public function test_without_cents_only_digits_are_kept_as_in_5x(): void
    {
        $this->assertSame('125000', MoneyFieldHandler::clean('1,250.00'));
        $this->assertSame('-1500', MoneyFieldHandler::clean('-1,500'));
        $this->assertSame('', MoneyFieldHandler::clean(null));
    }

    public function test_with_cents_the_decimal_part_is_kept(): void
    {
        $dollars = ['priceformat_parameters' => ['prefix' => '$ ', 'thousandsSeparator' => ',', 'centsSeparator' => '.', 'centsLimit' => 2]];
        $this->assertSame('2345.67', MoneyFieldHandler::clean('$ 2,345.67', $dollars));

        // A prefix containing the cents separator, and "," as the cents separator.
        $rupiah = ['priceformat_parameters' => ['prefix' => 'Rp. ', 'thousandsSeparator' => '.', 'centsSeparator' => ',', 'centsLimit' => 2]];
        $this->assertSame('1000.50', MoneyFieldHandler::clean('Rp. 1.000,50', $rupiah));

        $this->assertSame('-12.30', MoneyFieldHandler::clean('-12.30', ['decimals' => 2]));
    }
}
