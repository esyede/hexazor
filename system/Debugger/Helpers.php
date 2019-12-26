<?php

namespace System\Debugger;

defined('DS') or exit('No direct script access allowed.');

class Helpers
{
    /**
     * Mereturn HTML link ke editor.
     *
     * @param string $file
     * @param int    $line
     *
     * @return string
     */
    public static function editorLink($file, $line = null)
    {
        if ($editor = self::editorUri($file, $line)) {
            $file = strtr($file, '\\', '/');

            if (preg_match('#(^[a-z]:)?/.{1,50}$#i', $file, $m)
            && strlen($file) > strlen($m[0])) {
                $file = '...'.$m[0];
            }

            $file = strtr($file, '/', DIRECTORY_SEPARATOR);

            return self::formatHtml(
                '<a href="%" title="%">%<b>%</b>%</a>',
                $editor,
                $file.($line ? ":$line" : ''),
                rtrim(dirname($file), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR,
                basename($file),
                $line ? ":$line" : ''
            );
        }

        return self::formatHtml('<span>%</span>', $file.($line ? ":$line" : ''));
    }

    /**
     * Mereturn URI ke editor.
     *
     * @param string $file
     * @param int    $line
     *
     * @return string
     */
    public static function editorUri($file, $line = null)
    {
        if (Debugger::$editor && $file && is_file($file)) {
            return strtr(Debugger::$editor, [
                '%file' => rawurlencode($file),
                '%line' => $line ? (int) $line : 1,
            ]);
        }
    }

    public static function formatHtml($mask)
    {
        $args = func_get_args();

        return preg_replace_callback('#%#', function () use (&$args, &$count) {
            return htmlspecialchars($args[++$count], ENT_IGNORE | ENT_QUOTES, 'UTF-8');
        }, $mask);
    }

    public static function findTrace(array $trace, $method, &$index = null)
    {
        $m = explode('::', $method);

        foreach ($trace as $i => $item) {
            if (isset($item['function'])
            && $item['function'] === end($m)
            && isset($item['class']) === isset($m[1])
            && (
                !isset($item['class'])
                || $item['class'] === $m[0]
                || '*' === $m[0]
                || is_subclass_of($item['class'], $m[0])
            )) {
                $index = $i;

                return $item;
            }
        }
    }

    public static function getClass($obj)
    {
        return current(explode("\x00", get_class($obj)));
    }

    public static function fixStack($exception)
    {
        if (function_exists('xdebug_get_function_stack')) {
            $stack = [];
            foreach (array_slice(array_reverse(xdebug_get_function_stack()), 2, -1) as $row) {
                $frame = [
                    'file'     => $row['file'],
                    'line'     => $row['line'],
                    'function' => isset($row['function']) ? $row['function'] : '*unknown*',
                    'args'     => [],
                ];

                if (!empty($row['class'])) {
                    $frame['type'] = isset($row['type'])
                        && 'dynamic' === $row['type'] ? '->' : '::';
                    $frame['class'] = $row['class'];
                }

                $stack[] = $frame;
            }

            $ref = new \ReflectionProperty('Exception', 'trace');
            $ref->setAccessible(true);
            $ref->setValue($exception, $stack);
        }

        return $exception;
    }

    public static function fixEncoding($s)
    {
        return htmlspecialchars_decode(
            htmlspecialchars($s, ENT_NOQUOTES | ENT_IGNORE, 'UTF-8'),
            ENT_NOQUOTES
        );
    }

    public static function errorTypeToString($type)
    {
        $types = [
            E_ERROR             => 'PHP Fatal Error',
            E_USER_ERROR        => 'PHP User Error',
            E_RECOVERABLE_ERROR => 'PHP Recoverable Error',
            E_CORE_ERROR        => 'PHP Core Error',
            E_COMPILE_ERROR     => 'PHP Compile Error',
            E_PARSE             => 'PHP Parse Error',
            E_WARNING           => 'PHP Warning',
            E_CORE_WARNING      => 'PHP Core Warning',
            E_COMPILE_WARNING   => 'PHP Compile Warning',
            E_USER_WARNING      => 'PHP User Warning',
            E_NOTICE            => 'PHP Notice',
            E_USER_NOTICE       => 'PHP User Notice',
            E_STRICT            => 'PHP Strict Standards',
            E_DEPRECATED        => 'PHP Deprecated',
            E_USER_DEPRECATED   => 'PHP User Deprecated',
        ];

        return isset($types[$type]) ? $types[$type] : 'Unknown Error';
    }

    public static function getSource()
    {
        if (isset($_SERVER['REQUEST_URI'])) {
            return (!empty($_SERVER['HTTPS'])
                && strcasecmp($_SERVER['HTTPS'], 'off') ? 'https://' : 'http://').
                (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '').$_SERVER['REQUEST_URI'];
        }

        return empty($_SERVER['argv']) ? 'CLI' : 'CLI: '.implode(' ', $_SERVER['argv']);
    }

    public static function improveException($e)
    {
        $message = $e->getMessage();

        if (!$e instanceof \Error && !$e instanceof \ErrorException) {
            // jangan ngapa - ngapain
        } elseif (preg_match('#^Call to undefined function (\S+\\\\)?(\w+)\(#', $message, $m)) {
            $funcs = get_defined_functions();
            $funcs = array_merge($funcs['internal'], $funcs['user']);
            $hint = self::getSuggestion($funcs, $m[1].$m[2])
                ?: self::getSuggestion($funcs, $m[2]);
            $message .= ", did you mean $hint()?";
        } elseif (preg_match('#^Call to undefined method ([\w\\\\]+)::(\w+)#', $message, $m)) {
            $hint = self::getSuggestion(get_class_methods($m[1]), $m[2]);
            $message .= ", did you mean $hint()?";
        } elseif (preg_match('#^Undefined variable: (\w+)#', $message, $m)
        && !empty($e->context)) {
            $hint = self::getSuggestion(array_keys($e->context), $m[1]);
            $message = "Undefined variable $$m[1], did you mean $$hint?";
        } elseif (preg_match('#^Undefined property: ([\w\\\\]+)::\$(\w+)#', $message, $m)) {
            $rc = new \ReflectionClass($m[1]);
            $items = array_diff(
                $rc->getProperties(\ReflectionProperty::IS_PUBLIC),
                $rc->getProperties(\ReflectionProperty::IS_STATIC)
            );

            $hint = self::getSuggestion($items, $m[2]);
            $message .= ", did you mean $$hint?";
        } elseif (preg_match('#^Access to undeclared static property: ([\w\\\\]+)::\$(\w+)#', $message, $m)) {
            $rc = new \ReflectionClass($m[1]);
            $items = array_intersect(
                $rc->getProperties(\ReflectionProperty::IS_PUBLIC),
                $rc->getProperties(\ReflectionProperty::IS_STATIC)
            );

            $hint = self::getSuggestion($items, $m[2]);
            $message .= ", did you mean $$hint?";
        }

        if (isset($hint)) {
            $ref = new \ReflectionProperty($e, 'message');
            $ref->setAccessible(true);
            $ref->setValue($e, $message);
        }
    }

    public static function getSuggestion(array $items, $value)
    {
        $best = null;
        $min = (strlen($value) / 4 + 1) * 10 + .1;

        foreach (array_unique($items, SORT_REGULAR) as $item) {
            $item = is_object($item) ? $item->getName() : $item;
            if (($len = levenshtein($item, $value, 10, 11, 10)) > 0 && $len < $min) {
                $min = $len;
                $best = $item;
            }
        }

        return $best;
    }
}
