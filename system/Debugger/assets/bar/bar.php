<?php

namespace System\Debugger;

defined('DS') or exit('No direct script access allowed.');

ob_start(); ?>
&nbsp;
<style id="debugger-debug-style" class="debugger-debug">
<?php readfile(__DIR__.'/bar.css'); ?>
<?php readfile(__DIR__.'/../Dumper/dumper.css'); ?>
</style>

<script id="debugger-debug-script">
<?php readfile(__DIR__.'/bar.js'); ?>
<?php readfile(__DIR__.'/../Dumper/dumper.js'); ?>
</script>


<?php foreach ($panels as $panel): if (!empty($panel['previous'])) {
    continue;
} ?>
	<div class="debugger-panel" id="debugger-debug-panel-<?php echo $panel['id']; ?>">
		<?php if ($panel['panel']): echo $panel['panel']; ?>
		<div class="debugger-icons">
			<a href="#" title="open in window">&curren;</a>
			<a href="#" rel="close" title="close window">&times;</a>
		</div>
		<?php endif; ?>
	</div>
<?php endforeach; ?>

<div id="debugger-debug-bar">
	<ul>
		<?php foreach ($panels as $panel): if (!$panel['tab']) {
    continue;
} ?>
		<?php if (!empty($panel['previous'])) {
    echo '</ul><ul class="debugger-previous">';
} ?>
		<li><?php if ($panel['panel']): ?><a href="#" rel="<?php echo $panel['id']; ?>"><?php echo trim($panel['tab']); ?></a><?php else: echo '<span>', trim($panel['tab']), '</span>'; endif; ?></li>
		<?php endforeach; ?>
		<li><a href="#" rel="close" title="close debug bar">&times;</a></li>
	</ul>
</div>
<?php $output = ob_get_clean(); ?>


<script>
(function() {
	if (!document.documentElement.classList) {
		document.write('<div style="position:fixed;right:0;bottom:0;z-index:30000;font:normal normal 12px/21px sans-serif;color:#333;background:#EDEAE0;border:1px solid #ccc;padding:.2em">Warning: Debugger requires IE 10+<\/div>');
		return;
	}

	window.addEventListener('DOMContentLoaded', function() {
		var debug = document.body.appendChild(document.createElement('div'));
		debug.id = 'debugger-debug';
		debug.innerHTML = <?php echo json_encode(Helpers::fixEncoding($output)); ?>;
		for (var i = 0, scripts = debug.getElementsByTagName('script'); i < scripts.length; i++) {
			(window.execScript || function(data) {
				window['eval'].call(window, data);
			})(scripts[i].innerHTML);
		}
		Debugger.Dumper.init(<?php echo json_encode($liveData); ?>);
		Debugger.Debug.init();
		debug.style.display = 'block';
	});
})();
</script>
