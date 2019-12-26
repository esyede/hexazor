<?php

namespace System\Debugger;

defined('DS') or exit('No direct script access allowed.');

if (isset($this->cpuUsage) && $this->time) {
    foreach (getrusage() as $key => $val) {
        $this->cpuUsage[$key] -= $val;
    }

    $userUsage = -round(($this->cpuUsage['ru_utime.tv_sec'] * 1e6
        + $this->cpuUsage['ru_utime.tv_usec']) / $this->time / 10000);

    $systemUsage = -round(($this->cpuUsage['ru_stime.tv_sec'] * 1e6
        + $this->cpuUsage['ru_stime.tv_usec']) / $this->time / 10000);
}

$info = array_filter([
    'Execution time'                => str_replace(' ', ' ', number_format($this->time * 1000, 1, '.', ' ')).' ms',
    'CPU usage'                     => isset($userUsage) ? (int) $userUsage.' % + '.(int) $systemUsage.' %' : null,
    'Peak memory'                   => str_replace(' ', ' ', number_format(memory_get_peak_usage() / 1000000, 2, '.', ' ')).' MB',
    'Included files'                => count(get_included_files()),
    'Classes + interfaces + traits' => count(get_declared_classes()).' + '
        .count(get_declared_interfaces()).' + '.count(get_declared_traits()),
    'Your IP'   => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null,
    'Server IP' => isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : null,
    'PHP'       => PHP_VERSION,
    'Xdebug'    => extension_loaded('xdebug') ? phpversion('xdebug') : null,
    'Server'    => isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : null,
] + (array) $this->data);

?>
<style class="debugger-debug">
	#debugger-debug .debugger-InfoPanel td {
		white-space: nowrap;
	}
	#debugger-debug .debugger-InfoPanel td:nth-child(2) {
		font-weight: bold;
	}
	#debugger-debug .debugger-InfoPanel td[colspan='2'] b {
		float: right;
		margin-left: 2em;
	}
</style>

<h1>System Info</h1>

<div class="debugger-inner debugger-InfoPanel">
<table>
<?php foreach ($info as $key => $val): ?>
<tr>
<?php if (strlen($val) > 25): ?>
	<td colspan=2><?php echo htmlspecialchars($key, null, 'UTF-8'); ?> 
		<b><?php echo htmlspecialchars($val, null, 'UTF-8'); ?>
		</b>
	</td>
<?php else: ?>
	<td><?php echo htmlspecialchars($key, null, 'UTF-8'); ?></td>
	<td><?php echo htmlspecialchars($val, null, 'UTF-8'); ?></td>
<?php endif; ?>
</tr>
<?php endforeach; ?>
</table>
</div>
