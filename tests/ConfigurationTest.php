<?php


use PHPUnit\Framework\TestCase;

final class ConfigurationTest extends TestCase
{
    public function testLoadValidFile()
    {
        $c = new \RGPSTT\Configuration('config.yml');

        $this->assertInstanceOf(\RGPSTT\Configuration::class, $c);
    }

    public function testLoadInvalidFile()
    {
        $this->expectException(InvalidArgumentException::class);

        new \RGPSTT\Configuration('config-gone.yml');

    }
}
