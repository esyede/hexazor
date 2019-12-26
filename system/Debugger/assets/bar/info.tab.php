<?php

namespace System\Debugger;

defined('DS') or exit('No direct script access allowed.');

$this->time = microtime(true) - Debugger::$time;
?>
<span class="debugger-label" title="System info"><b>System</b></span>
<svg version="1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" enable-background="new 0 0 48 48">
    <g fill="#00BCD4">
        <rect x="19" y="22" width="10" height="20"/>
        <rect x="6" y="12" width="10" height="30"/>
        <rect x="32" y="6" width="10" height="36"/>
    </g>
</svg>
<span class="debugger-label" title="System info">
	<?php echo str_replace(' ', ' ', number_format($this->time * 1000, 1, '.', ' ')); ?> ms
</span>
</span>
