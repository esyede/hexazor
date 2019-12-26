<?php

namespace System\Support;

defined('DS') or exit('No direct script access allowed.');

class Event
{
    public static $events = [];

    public static $queued = [];

    public static $flushers = [];

    public static function has($event)
    {
        return isset(static::$events[$event]);
    }

    public static function listen($event, callable $callback)
    {
        static::$events[$event][] = $callback;
    }

    public static function override($event, callable $callback)
    {
        static::clear($event);
        static::listen($event, $callback);
    }

    public static function queue($queue, $key, array $data = [])
    {
        static::$queued[$queue][$key] = $data;
    }

    public static function flusher($queue, callable $callback)
    {
        static::$flushers[$queue][] = $callback;
    }

    public static function clear($event)
    {
        unset(static::$events[$event]);
    }

    public static function first($event, array $parameters = [])
    {
        return head(static::fire($event, $parameters));
    }

    public static function until($event, array $parameters = [])
    {
        return static::fire($event, $parameters, true);
    }

    public static function flush($queue)
    {
        foreach (static::$flushers[$queue] as $flusher) {
            if (!isset(static::$queued[$queue])) {
                continue;
            }

            foreach (static::$queued[$queue] as $key => $payload) {
                array_unshift($payload, $key);
                call_user_func_array($flusher, $payload);
            }
        }
    }

    public static function fire($events, array $parameters = [], $halt = false)
    {
        $events = (array) $events;
        $responses = [];

        foreach ($events as $event) {
            if (static::has($event)) {
                foreach (static::$events[$event] as $callback) {
                    $response = call_user_func_array($callback, $parameters);
                    if ($halt && !is_null($response)) {
                        return $response;
                    }

                    $responses[] = $response;
                }
            }
        }

        return $halt ? null : $responses;
    }
}
