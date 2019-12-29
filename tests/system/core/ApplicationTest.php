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
    }

    public function testEnsureAppKeyIsProvided()
    {
        return $this->assertTrue(strlen(Config::get('app.application_key')) === 32);
    }

    public function testReconfigureTimezone()
    {
        return $this->assertSame(date_default_timezone_get(), 'Asia/Jakarta');
    }

    public function testReconfigureDebugger()
    {
        return $this->assertTrue(Debugger::isEnabled());
    }

    public function testRegisterDefinedFacades()
    {
        $this->assertTrue(is_string(Date::now()->get()));
    }

    public function testDispatchRouteDefinitions()
    {
        return $this->assertTrue(is_array(Router::getRoutes()));
    }

    public function __destruct()
    {
        // ..
    }
}
