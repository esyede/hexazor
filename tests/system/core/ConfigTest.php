<?php

use PHPUnit\Framework\TestCase;
use System\Core\Config;
use System\Debugger\Debugger;

class ConfigTest extends TestCase
{
    private $config;

    public function __construct()
    {
        parent::__construct();
        $this->config = new Config();
        require_once dirname(dirname(dirname(__DIR__))).'/index.php';
        Debugger::enable(false);
    }

    public function testInit()
    {
        return $this->assertInstanceOf('\System\Core\Config', Config::init());
    }

    public function testGet()
    {
        return $this->assertSame(Config::get('session.lifetime'), 3600);
    }

    public function testSet()
    {
        Config::set('session.lifetime', 8000);

        return $this->assertSame(Config::get('session.lifetime'), 8000);
    }

    public function testGetAfterSet()
    {
        return $this->assertSame(Config::get('session.lifetime'), 8000);
    }

    public function testHas()
    {
        return $this->assertTrue(Config::has('session.lifetime'));
    }

    public function testAll()
    {
        $all = Config::all();

        return $this->assertTrue(is_array($all) && count($all) > 5);
    }

    public function testOffsetExists()
    {
        return $this->assertTrue($this->config->offsetExists('session.lifetime'));
    }

    public function testOffsetGet()
    {
        return $this->assertSame($this->config->offsetGet('session.lifetime'), 8000);
    }

    public function testOffsetSet()
    {
        return $this->assertTrue($this->config->offsetSet('session.lifetime', 10000));
    }

    public function testOffsetGetAfterSet()
    {
        return $this->assertSame($this->config->offsetGet('session.lifetime'), 10000);
    }

    public function testOffsetUnset()
    {
        $assertion = $this->assertTrue($this->config->offsetUnset('session.lifetime'));
        $this->config->offsetSet('session.lifetime', 3600);

        return $assertion;
    }

    public function __destruct()
    {
        // ..
    }
}
