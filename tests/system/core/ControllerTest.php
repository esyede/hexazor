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

    public function testMiddleware()
    {
        // success
        $this->assertNull($this->controller->middleware('auth'));

        $name = 'a-non-existant-middleware';
        $exception = '\InvalidArgumentException';

        // fails
        if (PHP_VERSION_ID <= 50500) {
            // travis: phpunit <=  4.8
            $this->setExpectedException(
              $exception,
              'No local middleware found with name: '.$name
            );
        } else {
            // travis: phpunit >= 5.7
            $this->expectException($exception);
        }

        $this->controller->middleware($name);
    }
}
