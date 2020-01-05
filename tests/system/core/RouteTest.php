<?php

use PHPUnit\Framework\TestCase;
use System\Core\Route;
use System\Debugger\Debugger;

class RouteTest extends TestCase
{
    public function __construct()
    {
        parent::__construct();
        require_once dirname(dirname(dirname(__DIR__))).'/index.php';
        Debugger::enable(false);
    }

    public function testGroup()
    {
        Route::prefix('base')->group(function () {
            Route::get('/foo', 'Bar@baz');
        });

        $this->assertTrue(filled(Route::getGroups()));
        $this->assertTrue(filled(Route::getGroups()[0]));
        $this->assertSame(count(Route::getGroups()[0]), 8);
    }

    public function testNamespace()
    {
        Route::namespaces('backend');
        $this->assertSame(Route::getNamespace(), 'backend');
        $this->assertFalse(Route::getNamespace() === 'foobar');
    }

    public function testMiddleware()
    {
        Route::middleware('auth');

        $this->assertSame(
            Route::getMiddleware('auth'),
            'App\Http\Middleware\Authenticate@handle'
        );

        $this->assertTrue(Route::hasMiddleware('auth'));
        $this->assertSame(Route::getMiddleware('foobar'), null);
    }
}
