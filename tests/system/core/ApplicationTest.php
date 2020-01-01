<?php

use PHPUnit\Framework\TestCase;
use System\Core\Config;
use System\Core\Router;
use System\Debugger\Debugger;

class ApplicationTest extends TestCase
{
    public function __construct()
    {
        parent::__construct();
        require_once dirname(dirname(dirname(__DIR__))).'/index.php';
        Debugger::enable(false);
    }

    public function testEnsureAppKeyIsProvided()
    {
        $appkey = Config::get('app.application_key');

        $this->assertTrue(is_string($appkey));
        $this->assertGreaterThanOrEqual(strlen($appkey), 32);
        $this->assertFalse(!is_string($appkey));
        $this->assertFalse(strlen($appkey) < 32);
    }

    public function testReconfigureTimezone()
    {
        $timezone = date_default_timezone_get();

        $this->assertTrue(is_string($timezone));
        $this->assertSame($timezone, 'Asia/Jakarta');
    }

    public function testReconfigureDebugger()
    {
        $maxLength = Debugger::$maxLen;

        $this->assertTrue(is_int($maxLength));
        $this->assertTrue($maxLength === 300);
        $this->assertFalse($maxLength < 300);
    }

    public function testRegisterDefinedFacades()
    {
        $this->assertTrue(class_exists('\System\Facades\Date'));

        $now = Date::now();

        $this->assertTrue(is_object($now));
        $this->assertTrue(is_string($now->get()));
        $this->assertFalse(!is_string($now->get()));
    }

    public function testDispatchRouteDefinitions()
    {
        $this->assertTrue(is_array(Router::getRoutes()));
        $this->assertFalse(blank(Router::getRoutes()));
    }

    public function __destruct()
    {
        // ..
    }
}
