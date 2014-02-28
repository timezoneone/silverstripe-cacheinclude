<?php

namespace Heyday\CacheInclude\Tests;

use Heyday\CacheInclude\Configs\ArrayConfig;

class ArrayConfigTest extends \PHPUnit_Framework_TestCase
{
    protected $testData;

    protected function setUp()
    {
        $this->testData = array(
            'One',
            'Two'
        );
    }

    protected function tearDown()
    {
        $this->testData = null;
    }

    public function testIteration()
    {
        $config = new ArrayConfig($this->testData);
        foreach ($config as $key => $val) {
            $this->assertEquals($this->testData[$key], $val);
        }
    }
    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Configs are immutable
     */
    public function testSet()
    {
        $config = new ArrayConfig($this->testData);
        $config[2] = 'Three';
    }
    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Configs are immutable
     */
    public function testUnset()
    {
        $config = new ArrayConfig($this->testData);
        unset($config[1]);
    }

    public function testGet()
    {
        $config = new ArrayConfig($this->testData);
        $this->assertEquals('One', $config[0]);
        $this->assertEquals('Two', $config[1]);
    }

    public function testExists()
    {
        $config = new ArrayConfig($this->testData);
        $this->assertTrue(isset($config[0]));
        $this->assertFalse(isset($config[2]));
    }
}
