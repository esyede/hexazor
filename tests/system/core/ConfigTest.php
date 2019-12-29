<?php

use PHPUnit\Framework\TestCase;
use System\Core\Config;

class ConfigTest extends TestCase
{
    public function __construct()
    {
        parent::__construct();
        require_once dirname(dirname(dirname(__DIR__))).'/index.php';
    }

    public function testInit()
    {
        return $this->assertInstanceOf(Config::class, Config::init());
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

    public function testHas()
    {
        return $this->assertTrue(Config::has('session.lifetime'));
    }

    public function testAll()
    {
        $all = Config::all();

        return $this->assertTrue(is_array($all) && count($all) > 5);
    }

    public function __destruct()
    {
        // ..
    }
}
