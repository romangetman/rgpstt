<?php


use PHPUnit\Framework\TestCase;
use RGPSTT\Configuration;
use RGPSTT\CurrencyUtils;
use RGPSTT\DataLoader;
use RGPSTT\OperationsLog;

final class FeeCalculationTest extends TestCase
{
    public function testCalculation()
    {
        $configuration = new Configuration('config.yml');

        $loader = new DataLoader('input2.csv', $configuration['csv_field_config']);

        $c_utils = new CurrencyUtils($configuration['currencies']);

        $log_data = $loader->getRawData();

        $oplog = new OperationsLog($log_data, $configuration);

        $fees = [];

        foreach ($oplog->getLogData() as $operation) {
            $fees[] = $c_utils->roundCurrency($oplog->calculateFee($operation), $operation->getCurrency());
        }

        $this->assertEquals(0, $fees[0]);
        $this->assertEquals(0.7, $fees[1]);
        $this->assertEquals(0.3, $fees[2]);
        $this->assertEquals(0.3, $fees[3]);
    }


}
