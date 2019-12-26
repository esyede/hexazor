<?php

namespace System\Debugger;

defined('DS') or exit('No direct script access allowed.');

?>
<style class="debugger-debug">
	#debugger-debug .debugger-DumpPanel h2 {
		font: 11pt/1.5 sans-serif;
		margin: 0;
		padding: 2px 8px;
		background: #3484d2;
		color: white;
	}
</style>

<h1>Dumper</h1>

<div class="debugger-inner debugger-DumpPanel">
<?php foreach ($data as $item): ?>
	<?php if ($item['title']):?>
	<h2><?php echo htmlspecialchars($item['title'], ENT_NOQUOTES, 'UTF-8'); ?></h2>
	<?php endif; ?>

	<?php echo $item['dump']; ?>
<?php endforeach; ?>
</div>
