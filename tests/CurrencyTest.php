<?php


use PHPUnit\Framework\TestCase;
use RGPSTT\CurrencyUtils;

final class CurrencyTest extends TestCase
{
    private $config = array(
        'EUR' =>
            array(
                'rate' => 1,
                'precision' => 2,
            ),
        'JPY' =>
            array(
                'rate' => 129.53,
                'precision' => 0,
            ),
        'USD' =>
            array(
                'rate' => 1.1497,
                'precision' => 2,
            ),
    );

    public function testCreation()
    {
        $c_u = new CurrencyUtils($this->config);

        $this->assertInstanceOf(CurrencyUtils::class, $c_u);
    }

    public function testSelfConversion()
    {
        $c_u = new CurrencyUtils($this->config);

        $this->assertEquals($c_u->convertFromDefaultCurrency(1, 'EUR'), 1);
        $this->assertEquals($c_u->convertToDefaultCurrency(1, 'EUR'), 1);
    }

    public function testInvalidCurrency()
    {
        $c_u = new CurrencyUtils($this->config);

        $this->expectException(InvalidArgumentException::class);

        $c_u->convertFromDefaultCurrency(1, 'ER');
        $c_u->convertToDefaultCurrency(1, 'ER');
    }
}
