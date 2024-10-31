<?php
add_action('wp_footer', 'footerCustom');
function footerCustom()
{
	echo <<<HTML
	<style type="text/css">
		#wp-admin-bar-edit>a,
		#wp-admin-bar-edit>a:before{
			color: #DEFF00 !important;
			font-weight: bold
		}
	</style>
	<script>
		(function($) {
			$(document).ready(function(){
				// Thay thế link chỉnh sửa trang
				var outerEl = $('#wp-admin-bar-edit');
				if( outerEl.length > 0 ){
					var editLink = outerEl.find('#wp-admin-bar-edit_uxbuilder>a').attr('href');
					var originalLink = $('#wp-admin-bar-edit>a').attr('href');
					if( typeof editLink != 'undefined' ){
						outerEl.children('a').attr('href', editLink);
						$('#wp-admin-bar-edit_uxbuilder a').attr('href', originalLink).text('Trang chỉnh sửa đầy đủ');
					}
				}
			});
		})( jQuery );
	</script>
	HTML;
}