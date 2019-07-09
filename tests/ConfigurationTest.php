<?php


use PHPUnit\Framework\TestCase;
use RGPSTT\Configuration;

final class ConfigurationTest extends TestCase
{
    public function testLoadValidFile()
    {
        $c = new Configuration('config.yml');

        $this->assertInstanceOf(Configuration::class, $c);
    }

    public function testLoadInvalidFile()
    {
        $this->expectException(InvalidArgumentException::class);

        new Configuration('config-gone.yml');

    }
}
