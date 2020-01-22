<?php

namespace System\Console\Commands;

defined('DS') or exit('No direct script access allowed.');

use Closure;
use System\Console\Command;
use System\Console\Table;
use System\Core\Route;
use System\Support\Str;

class RouteList extends Command
{
    protected $signature = 'route:list';
    protected $description = 'List all registered routes';

    /**
     * Tangani command ini.
     *
     * @return void
     */
    public function handle()
    {
        $routes = [];
        $this->getRouteDefinitions();
        $routes = Route::getRoutes();
        $table = new Table();

        if (empty($routes)) {
            $this->write("Your application doesn't have any routes.");
            $this->newline();
            exit();
        }

        $table->addHeader('No.')
            ->addHeader('SSL')
            ->addHeader('Domain')
            ->addHeader('Method')
            ->addHeader('URI')
            ->addHeader('Name')
            ->addHeader('Action')
            ->addHeader('Middleware');
        $number = 1;

        foreach ($routes as $route) {
            $ssl = isset($route['ssl']) && (true === $route['ssl']) ? 'true' : 'false';
            $domain = isset($route['domain']) ? $route['domain'] : '';
            $uri = $route['uri'];

            $methods = is_array($route['method']) ? $route['method'] : [$route['method']];
            $methods = implode('|', $methods);
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
                ->addColumn($number.'.')
                ->addColumn($ssl)
                ->addColumn($domain)
                ->addColumn($methods)
                ->addColumn($uri)
                ->addColumn($name)
                ->addColumn($action)
                ->addColumn($middlewares);

            $number++;
        }

        $this->write($table->getTable());

        $this->writeline('*) Middlewares that called from inside controller classes will not be listed.');
        $this->newline();
        exit();
    }

    /**
     * Ambil semua routes yang telah didefinisikan oleh user.
     *
     * @return mixed
     */
    private function getRouteDefinitions()
    {
        return require_once base_path('routes/web.php');
    }
}
