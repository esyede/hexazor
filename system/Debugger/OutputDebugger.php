<?php

namespace System\Debugger;

defined('DS') or exit('No direct script access allowed.');

class OutputDebugger
{
    const BOM = "\xEF\xBB\xBF";

    private $list = [];

    public static function enable()
    {
        $me = new static();
        $me->start();
    }

    public function start()
    {
        foreach (get_included_files() as $file) {
            if (self::BOM === fread(fopen($file, 'r'), 3)) {
                $this->list[] = [$file, 1, self::BOM];
            }
        }

        ob_start([$this, 'handler'], 1);
    }

    public function handler($s, $phase)
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

        if (isset($trace[0]['file'], $trace[0]['line'])) {
            $stack = $trace;
            unset($stack[0]['line'], $stack[0]['args']);
            $i = count($this->list);

            if ($i && $this->list[$i - 1][3] === $stack) {
                $this->list[$i - 1][2] .= $s;
            } else {
                $this->list[] = [$trace[0]['file'], $trace[0]['line'], $s, $stack];
            }
        }

        if (PHP_OUTPUT_HANDLER_FINAL === $phase) {
            return $this->renderHtml();
        }
    }

    private function renderHtml()
    {
        $res = '<style>code, pre {white-space:nowrap} '.
            'a {text-decoration:none} '.
            'pre {color:gray;display:inline} '.
            'big {color:red}</style><code>';

        foreach ($this->list as $item) {
            $stack = [];
            foreach (array_slice($item[3], 1) as $t) {
                $t += [
                    'class'    => '',
                    'type'     => '',
                    'function' => '',
                ];

                $tClass = $t['class'];
                $tType = $t['type'];
                $tFunction = $t['function'];
                $tLine = isset($t['line']) ? $t['line'] : '';

                $stack[] = "{$tClass}{$tType}{tFunction}()".(
                    isset($t['file'], $t['line']) ? ' in '.basename($t['file']).":$tLine" : ''
                );
            }

            $res .= Helpers::editorLink($item[0], $item[1]).
                ' <span title="'.htmlspecialchars(
                    implode("\n", $stack),
                    ENT_IGNORE | ENT_QUOTES,
                    'UTF-8'
                ).'">'.
                str_replace(self::BOM, '<big>BOM</big>', Dumper::toHtml($item[2])).
                "</span><br>\n";
        }

        return $res.'</code>';
    }
}
