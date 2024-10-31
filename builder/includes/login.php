<?php

/*
 * Đổi đường dẫn logo đăng nhập
 */
if (!function_exists('wpc_url_login')) {
	function wpc_url_login()
	{
		return '/';
	}

	add_filter('login_headerurl', 'wpc_url_login');
}
/*
 * Thêm CSS tùy chỉnh
 */
if (!function_exists('login_css')) {
	function login_css()
	{
		if( function_exists('flatsome_option') ) {
			$logo = flatsome_option('site_logo');
		}else {
			$custom_logo_id = get_theme_mod( 'custom_logo' );
			$image = wp_get_attachment_image_src( $custom_logo_id , 'full' );
			$logo = $image[0];
		}
		echo '
			<style>
				#login h1 a {
					background: url(' . $logo . ') no-repeat !important;
					background-size: 100%;
					height: auto !important
				}
			</style>
		';
		wp_enqueue_style('login_css', BUILDER_HOME . '/web-builder/assets/login/style.css'); // duong dan den file css moi
	}

	add_action('login_head', 'login_css');
}