<?php

namespace System\Console\Commands;

defined('DS') or exit('No direct script access allowed.');

use Closure;
use System\Console\Command;
use System\Console\Table;
use System\Core\Import;
use System\Core\Router;
use System\Support\Str;

class RouteList extends Command
{
    protected $signature = 'route:list';
    protected $description = 'List defined routes.';

    /**
     * Tangani command ini.
     *
     * @return void
     */
    public function handle()
    {
        $routes = [];

        Import::file('routes/web.php');
        $routes = Router::getRoutes();

        $table = new Table();

        if (empty($routes)) {
            $this->error("Your application doesn't have any routes.");
            $this->quit();
        }

        $table
            ->addHeader('SSL')
            ->addHeader('Domain')
            ->addHeader('Method')
            ->addHeader('URI')
            ->addHeader('Name')
            ->addHeader('Action')
            ->addHeader('Middleware');

        foreach ($routes as $route) {
            $ssl = isset($route['ssl']) && (true === $route['ssl']) ? 'true' : 'false';

            $domain = isset($route['domain']) ? $route['domain'] : '';

            $uri = $route['uri'];

            $methods = is_array($route['method']) ? $route['method'] : [$route['method']];
            $methods = implode(', ', $methods);

            $action = ($route['callback'] instanceof Closure)
                ? 'Closure'
                : Str::replaceFirst('App\\Http\\Controllers\\', '', $route['callback']);

            $name = '-';
            if (array_key_exists('name', $route)) {
                $name = $route['name'];
            }

            $middlewares = '';
            if (array_key_exists('middlewares', $route)) {
                $middlewares = implode(',', array_keys($route['middlewares']));
            }

            $table->addRow()
                ->addColumn($ssl)
                ->addColumn($domain)
                ->addColumn($methods)
                ->addColumn($uri)
                ->addColumn($name)
                ->addColumn($action)
                ->addColumn($middlewares);
        }

        $table->display();

        $this->plain('*) Middlewares that called from inside controller classes will not be listed.');
        $this->newline();
        $this->quit();
    }
}
