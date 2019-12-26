<?php

namespace System\Debugger;

defined('DS') or exit('No direct script access allowed.');

?>
<h1>Errors</h1>

<div class="debugger-inner">
<table>
<?php foreach ($data as $item => $count): list($file, $line, $message) = explode('|', $item, 3); ?>
<tr>
	<td class="debugger-right"><?php echo $count ? "$count\xC3\x97" : ''; ?></td>
	<td><pre><?php echo htmlspecialchars($message, ENT_IGNORE, 'UTF-8'), ' in ', Helpers::editorLink($file, $line); ?></pre></td>
</tr>
<?php endforeach; ?>
</table>
</div>
