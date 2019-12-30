<?php

use PHPUnit\Framework\TestCase;
use System\Core\Controller;
use System\Debugger\Debugger;

class ControllerTest extends TestCase
{
    private $controller;

    public function __construct()
    {
        parent::__construct();
        require_once dirname(dirname(dirname(__DIR__))).'/index.php';
        Debugger::enable(false);
        $this->controller = new Controller();
    }

    public function testFailingMiddleware()
    {
        $this->expectException('\InvalidArgumentException');
        $this->controller->middleware('a-non-existant-middleware');
    }

    public function testSuccessMiddleware()
    {
        $this->assertNull($this->controller->middleware('auth'));
    }
}
