<?php

namespace System\Debugger;

defined('DS') or exit('No direct script access allowed.');

if (empty($data)) {
    return;
}
?>
<span style="
	display: inline-block;
	background: #D51616;
	color: white;
	font-weight: bold;
	margin: -1px -.4em;
	padding: 1px .4em;
" title="Severity errors">
	<?php echo $sum = array_sum($data), $sum > 1 ? ' errors' : ' error'; ?>
</span>
