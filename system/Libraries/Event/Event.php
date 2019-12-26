<?php

namespace System\Libraries\Event;

defined('DS') or exit('No direct script access allowed.');

use Closure;
use Exception;
use InvalidArgumentException;
use RuntimeException;
use System\Core\Config;

class Event
{
    private $action = 'handle';

    private $params = [];

    private $listeners = null;

    /**
     * Set nama event yang akan dipanggil listenernya.
     *
     * @param string $event
     *
     * @return $this
     */
    public function listener($event)
    {
        $events = Config::get('events');

        if (!isset($events[$event])) {
            throw new InvalidArgumentException("No matching event for this name: '{$event}'");
        }

        $this->listeners[$event] = $events[$event];

        return $this;
    }

    /**
     * Set nama action yang akan dipanggil.
     *
     * @param string $action
     *
     * @return $this
     */
    public function action($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Set parameter yang akan dioper ke action.
     *
     * @param array $params
     *
     * @return $this
     */
    public function params(array $params)
    {
        $this->params = $params;

        return $this;
    }

    /**
     * Panggil event listener.
     */
    public function fire()
    {
        foreach ($this->listeners as $listener) {
            if (!class_exists($listener)) {
                throw new RuntimeException('Listener class not found: '.$listener);
            }

            if (!method_exists($listener, $this->action)) {
                $method = $listener.'::'.$this->action.'()';

                throw new RuntimeException('Listener method not found: '.$method);
            }

            call_user_func_array([new $listener(), $this->action], $this->params);
        }
    }

    /**
     * Cek apakah listener ada (sudah didaftarkan).
     *
     * @param string $event
     *
     * @return bool
     */
    public function has($event)
    {
        return array_key_exists($event, $this->listeners);
    }

    /**
     * Timpa event yang sudah didaftarkan sebelumnya.
     *
     * @param string   $event
     * @param \Closure $callback
     */
    public function override($event, Closure $callback)
    {
        if (!$this->has($event)) {
            throw new Exception('Trying to override non-existent event: '.$event);
        }

        $this->listeners[$event] = $callback;
    }

    /**
     * Lupakan (hapus) event dari list terdaftar.
     *
     * @param string $event
     */
    public function forget($event)
    {
        unset($this->listeners[$event]);
    }
}
