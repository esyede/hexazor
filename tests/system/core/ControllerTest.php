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
        $name = 'a-non-existant-middleware';

        if (PHP_VERSION_ID <= 50600) {
            $this->setExpectedException(
              '\InvalidArgumentException',
              'No local middleware found with name: '.$name
            );
        } else {
            $this->expectException('\InvalidArgumentException');
        }

        $this->controller->middleware($name);
    }

    public function testSuccessMiddleware()
    {
        $this->assertNull($this->controller->middleware('auth'));
    }
}
