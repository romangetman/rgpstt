<?php


use PHPUnit\Framework\TestCase;
use RGPSTT\DataLoader;

final class LoaderTest extends TestCase
{
    /**
     * @dataProvider loaderProvider
     */
    public function testLoadValidFile($path, $field_config)
    {
        $c = new DataLoader($path, $field_config);

        $this->assertInstanceOf(DataLoader::class, $c);
        $this->assertIsArray($c->getData());
    }

    public function testLoadInvalidFile()
    {
        $this->expectException(RuntimeException::class);

        new DataLoader('not_found', []);

    }

    /**
     * @dataProvider badLoaderProvider
     */
    public function testLoadFieldConfigMismatch($path, $field_config)
    {
        $this->expectException(LengthException::class);

        new DataLoader($path, $field_config);

    }

    public function testLoadEmptyFieldConfig()
    {
        $this->expectException(InvalidArgumentException::class);

        new DataLoader('input.csv', []);

    }

    public function loaderProvider()
    {
        return [
            [
                'input.csv',
                ['user_type' => 2, 'user_id' => 1, 'date' => 0, 'op_type' => 3, 'amount' => 4, 'currency' => 5,]
            ],
        ];
    }

    public function badLoaderProvider()
    {
        return [
            [
                'input.csv',
                ['user_type' => 2, 'user_id' => 1, 'date' => 0, 'op_type' => 3, 'amount' => 4, 'currency' => 6,]
            ]
        ];
    }
}
