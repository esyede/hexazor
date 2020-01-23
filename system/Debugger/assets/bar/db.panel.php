<?php

namespace System\Debugger;

defined('DS') or exit('No direct script access allowed.');
use System\Database\Database;

$profiler = Database::profile();
$count = count($profiler);
?>
<style class="debugger-debug">
	#debugger-debug .debugger-dbPanel h2 {
		font: 11pt/1.5 sans-serif;
		margin: 0;
		padding: 2px 8px;
		background: #3484d2;
		color: white;
	}
</style>

<h1>Database</h1>

<div class="debugger-debug">
<table>
	<th style="font-size: 10pt">No.</th>
	<th style="font-size: 10pt">SQL</th>
	<th style="font-size: 10pt">Bindings</th>
	<th style="font-size: 10pt">Time</th>
	<?php if ($count < 1): ?>
		<tr>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>
	<?php else: ?>
		<?php for ($i = 0; $i < count($profiler); $i++): ?>
			<tr>
		 		<td><?php echo($i+1).'.'; ?></td>
				<td><pre><?php Dumper::dump($profiler[$i]['sql'], ['truncate' => 0]) ?></pre></td>
				<td><?php Dumper::dump($profiler[$i]['bindings'], ['truncate' => 0]) ?></td>
				<td style="color: #850"><?php echo $profiler[$i]['time'] ?></td>
			</tr>
		<?php endfor; ?>
	<?php endif; ?>
</table>
</div>