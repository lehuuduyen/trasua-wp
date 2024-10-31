<?php
/*
 * Cài đặt các gói plugin
 */

if (is_admin()) {
	if (isset($_GET['activate_plugin'])) add_action('admin_footer', 'activePluginScript');
	if (isset($_GET['disable_plugin'])) add_action('admin_footer', 'disablePluginScript');
}
function activePluginScript(){
	$path = $_GET['activate_plugin'];
	$basename = dirname($path);
	echo <<<HTML
	<script>
		(function($) {
			let activeEl = $('tr[data-plugin="${path}"] .activate > a');
			activeEl[0].click();
			alert('Đã kích hoạt plugin: ${basename}');
		})( jQuery );
	</script>
	HTML;
}
function disablePluginScript(){
	$path = $_GET['disable_plugin'];
	$basename = dirname($path);
	echo <<<HTML
	<script>
		(function($) {
			let activeEl = $('tr[data-plugin="${path}"] .deactivate > a');
			activeEl[0].click();
			alert('Đã tắt plugin: ${basename}');
		})( jQuery );
	</script>
	HTML;
}