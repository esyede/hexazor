<?php

namespace System\Debugger;

defined('DS') or exit('No direct script access allowed.');

use Debugger;
use System\Debugger\Interfaces\LoggerInterface;

class FireLogger implements LoggerInterface
{
    public $maxDepth = 3;

    public $maxLength = 150;

    private $payload = ['logs' => []];

    /**
     * Kirim pesan ke konsol FireLogger.
     *
     * @param mixed  $message
     * @param string $level
     *
     * @return bool
     */
    public function log($message, $level = self::DEBUG)
    {
        if (!isset($_SERVER['HTTP_X_FIRELOGGER']) || headers_sent()) {
            return false;
        }

        $time = number_format((microtime(true) - Debugger::$time) * 1000, 1, '.', ' ');

        $item = [
            'name' => 'PHP',
            'level' => $level,
            'order' => count($this->payload['logs']),
            'time' => str_pad($time, 8, '0', STR_PAD_LEFT).' ms',
            'template' => '',
            'message' => '',
            'style' => 'background:#767ab6',
        ];

        $args = func_get_args();

        if (isset($args[0]) && is_string($args[0])) {
            $item['template'] = array_shift($args);
        }

        if (isset($args[0])
        && ($args[0] instanceof \Exception
        || $args[0] instanceof \Throwable)) {
            $e = array_shift($args);
            $trace = $e->getTrace();
            if (isset($trace[0]['class'])
            && '\System\Debugger\Debugger' === $trace[0]['class']
            && ('shutdownHandler' === $trace[0]['function']
            || 'errorHandler' === $trace[0]['function'])) {
                unset($trace[0]);
            }

            $file = dirname(dirname(dirname($e->getFile())));
            $file = str_replace($file, "\xE2\x80\xA6", $e->getFile());

            $item['template'] = ($e instanceof \ErrorException
                ? '' : Helpers::getClass($e).': ').$e->getMessage().
                ($e->getCode() ? ' #'.$e->getCode() : '').
                ' in '.$file.':'.$e->getLine();

            $item['pathname'] = $e->getFile();
            $item['lineno'] = $e->getLine();
        } else {
            $trace = debug_backtrace();
            if (isset($trace[1]['class'])
            && '\System\Debugger\Debugger' === $trace[1]['class']
            && ('fireLog' === $trace[1]['function'])) {
                unset($trace[0]);
            }

            foreach ($trace as $frame) {
                if (isset($frame['file']) && is_file($frame['file'])) {
                    $item['pathname'] = $frame['file'];
                    $item['lineno'] = $frame['line'];

                    break;
                }
            }
        }

        $item['exc_info'] = ['', '', []];
        $item['exc_frames'] = [];

        foreach ($trace as $frame) {
            $frame += [
                'file' => null,
                'line' => null,
                'class' => null,
                'type' => null,
                'function' => null,
                'object' => null,
                'args' => null,
            ];

            $item['exc_info'][2][] = [
                $frame['file'],
                $frame['line'],
                "$frame[class]$frame[type]$frame[function]",
                $frame['object'],
            ];

            $item['exc_frames'][] = $frame['args'];
        }

        $errors = [self::DEBUG, self::INFO, self::WARNING, self::ERROR, self::CRITICAL];
        if (isset($args[0]) && in_array($args[0], $errors, true)) {
            $item['level'] = array_shift($args);
        }

        $item['args'] = $args;
        $this->payload['logs'][] = $this->jsonDump($item, -1);

        foreach (str_split(base64_encode(@json_encode($this->payload)), 4990) as $k => $v) {
            header("FireLogger-de11e-$k:$v");
        }

        return true;
    }

    /**
     * Implemantasi dump output versi json.
     *
     * @param mixed &$var
     * @param int   $level
     *
     * @return string
     */
    private function jsonDump(&$var, $level = 0)
    {
        if (is_bool($var) || is_null($var) || is_int($var) || is_float($var)) {
            return $var;
        } elseif (is_string($var)) {
            return Dumper::encodeString($var, $this->maxLength);
        } elseif (is_array($var)) {
            static $marker;

            if (null === $marker) {
                $marker = uniqid("\x00", true);
            }

            if (isset($var[$marker])) {
                return "\xE2\x80\xA6RECURSION\xE2\x80\xA6";
            } elseif ($level < $this->maxDepth || !$this->maxDepth) {
                $var[$marker] = true;
                $res = [];
                foreach ($var as $k => &$v) {
                    if ($k !== $marker) {
                        $res[$this->jsonDump($k)] = $this->jsonDump($v, $level + 1);
                    }
                }

                unset($var[$marker]);

                return $res;
            }

            return " \xE2\x80\xA6 ";
        } elseif (is_object($var)) {
            static $list = [];
            $arr = (array) $var;

            if (in_array($var, $list, true)) {
                return "\xE2\x80\xA6RECURSION\xE2\x80\xA6";
            } elseif ($level < $this->maxDepth || !$this->maxDepth) {
                $list[] = $var;
                $res = ["\x00" => '(object) '.Helpers::getClass($var)];
                foreach ($arr as $k => &$v) {
                    if (isset($k[0]) && "\x00" === $k[0]) {
                        $k = substr($k, strrpos($k, "\x00") + 1);
                    }

                    $res[$this->jsonDump($k)] = $this->jsonDump($v, $level + 1);
                }

                array_pop($list);

                return $res;
            }

            return " \xE2\x80\xA6 ";
        } elseif (is_resource($var)) {
            return 'resource '.get_resource_type($var);
        }

        return 'unknown type';
    }
}
