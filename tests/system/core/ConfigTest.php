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
        $config = Config::init();

        $this->assertFalse($config instanceof Foo\Bar\Baz);
        $this->assertInstanceOf('\System\Core\Config', $config);
    }

    public function testGet()
    {
        $this->assertFalse(Config::get('session.lifetime') === 0);
        $this->assertTrue(Config::get('session.lifetime') === 60);
    }

    public function testSet()
    {
        Config::set('session.lifetime', 80);

        $this->assertSame(Config::get('session.lifetime'), 80);
        $this->assertFalse(Config::get('session.lifetime') !== 80);
    }

    public function testHas()
    {
        $this->assertTrue(Config::has('session.lifetime'));
        $this->assertFalse(Config::has('session.foobar'));
    }

    public function testAll()
    {
        $all = Config::all();

        $this->assertTrue(is_array($all) && count($all) > 5);
        $this->assertFalse(!is_array($all) && count($all) < 5);
    }

    public function testOffsetExists()
    {
        $this->assertTrue($this->config->offsetExists('session.lifetime'));
        $this->assertFalse(!$this->config->offsetExists('session.lifetime'));
    }

    public function testOffsetGet()
    {
        $this->assertSame($this->config->offsetGet('session.lifetime'), 80);
        $this->assertFalse($this->config->offsetGet('session.lifetime') !== 80);
    }

    public function testOffsetSet()
    {
        $this->assertTrue($this->config->offsetSet('session.lifetime', 100));
        $this->assertFalse($this->config->offsetGet('session.lifetime') !== 100);
        $this->assertTrue($this->config->get('session.lifetime') === 100);
        $this->assertFalse($this->config->get('session.lifetime') !== 100);
    }

    public function testOffsetUnset()
    {
        $this->assertTrue($this->config->offsetUnset('session.lifetime'));
        $this->assertArrayHasKey('lifetime', $this->config->get('session'));
        // put back default value
        $this->config->offsetSet('session.lifetime', 60);
    }

    public function __destruct()
    {
        // ..
    }
}
