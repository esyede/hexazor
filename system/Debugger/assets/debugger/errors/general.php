<?php

namespace System\Debugger;

defined('DS') or exit('No direct script access allowed.');

?>
<!DOCTYPE html><!-- "' --></script></style></noscript></xmp>
<meta charset="utf-8">
<meta name=robots content=noindex>
<meta name=generator content="Debugger">
<title>Server Error</title>

<style>
	#debugger-error { background: white; width: 500px; margin: 70px auto; padding: 10px 20px }
	#debugger-error h1 { font: bold 47px/1.5 sans-serif; background: none; color: #333; margin: .6em 0 }
	#debugger-error p { font: 21px/1.5 Georgia,serif; background: none; color: #333; margin: 1.5em 0 }
	#debugger-error small { font-size: 70%; color: gray }
</style>

<div id=debugger-error>
	<h1>Server Error</h1>

	<p>We're sorry! The server encountered an internal error and
	was unable to complete your request. Please try again later.</p>

	<p><small>Code: 500<?php if (!$logged): ?><br>Debugger is unable to log error.<?php endif; ?></small></p>
</div>
