<?php

/*
# File cấu hình hệ thống dựng web
*/
/*
/*
$blockPath = [
	'/wp-admin/plugin-install.php'
];
if( in_array(URI, $blockPath) ){
	header('Location: /');
	die;
}


function remove_menu_items(){
   remove_submenu_page( 'plugins.php','plugin-install.php' );
}

add_action( 'admin_menu', 'remove_menu_items', 999 );
*/

define("DOMAIN", $_SERVER['HTTP_HOST']);
define("URI", $_SERVER['REQUEST_URI']);
$home = "http" . (BUILDER_SSL ? "s" : "") . "://" . DOMAIN;
update_option("siteurl", $home);
update_option("home", $home);

add_filter('xmlrpc_enabled', '__return_false'); // Tắt XMLRPC để bảo mật hơn

//Kiểm tra xem có phải đăng nhập từ trang quản lý không
function _checkBuilderAdmin()
{
	$adminLoginKey = $_REQUEST["_is_builder_admin"] ?? $_COOKIE["_is_builder_admin"] ?? null;
	if (empty($adminLoginKey)) {
		return false;
	}
	if (get_option('_trangweb_builder_admin_key') == md5($adminLoginKey)) {
		return true;
	}
	return false;
}

//Cho phép admin cài plugin
if (empty($_COOKIE["_is_builder_admin"]) || isset($_COOKIE["_is_builder_admin"]) && !_checkBuilderAdmin()) {
	define("DISALLOW_FILE_EDIT", true);
	define("DISALLOW_FILE_MODS", true);
}

// Đăng nhập bằng key bên web chính
if (isset($_GET["auto_login_by_user"])) {
	if (isset($_GET['confirm'])) {
		$adminLoginKey = $_REQUEST["_is_builder_admin"] ?? $_COOKIE["_is_builder_admin"] ?? null;
		if (!empty($adminLoginKey)) {
			// Lưu key đăng nhập admin
			if (file_get_contents(BUILDER_HOME . "/api/builder?check_admin=" . $adminLoginKey) === "true") {
				update_option('_trangweb_builder_admin_key', md5($adminLoginKey));
			}
		}
		$user = get_user_by('login', urldecode($_GET["auto_login_by_user"]));
		$loginSuccess = false;
		if ($user && md5($user->user_pass) == ($_GET["_login_key"] ?? null)) {
			// Đăng nhập với quyền admin (không cho cài plugin)
			$loginSuccess = true;
		}
		if (isset($_REQUEST["_is_builder_admin"]) && _checkBuilderAdmin()) {
			// Đăng nhập với quyền admin (cho cài plugin)
			setcookie("_is_builder_admin", $_REQUEST["_is_builder_admin"], time() + 3600 * 24 * 60, "/");
			$loginSuccess = true;
		} else {
			setcookie("_is_builder_admin", "", 0, "/");
		}
		if ($loginSuccess) {
			wp_clear_auth_cookie();
			wp_set_current_user($user->ID);
			wp_set_auth_cookie($user->ID, true);
			if (isset($_GET['go_to_dashboard'])) {
				wp_redirect(admin_url());
			} else {
				wp_redirect('/');
			}
			die;
		}
	} else {
		header("location: {$_SERVER['REQUEST_URI']}&confirm");
		die;
	}
}


//Đình chỉ web
if (!empty(BUILDER_SUSPENDED)) {
	echo '<meta charset="utf-8">';
	die('<div style="font-size: 22px; color: red; text-align: center;padding: 100px 20px">' . BUILDER_SUSPENDED . '</div>');
}

add_action('wp_head', 'expiredNotify');
function expiredNotify()
{
	$expiredDays = floor((BUILDER_EXPIRED - time()) / 3600 / 24);
	if ($expiredDays < 31) {
		echo file_get_contents(BUILDER_HOME . "/api/builder/" . $_SERVER["HTTP_HOST"]);
	}
}

function tomjn_only_upload_for_admin($file)
{
	$diskUsed = folderSize(PUBLIC_ROOT) + $file["size"];
	$percent = $diskUsed / mb2Bytes(BUILDER_MAXDISK) * 100;
	$percent = round($percent);
	if ($percent > 95) {
		$file['error'] = 'Dung lượng của website đã đầy, hãy nâng cấp thêm dung lượng';
	}
	return $file;
}

add_filter('wp_handle_upload_prefilter', 'tomjn_only_upload_for_admin');


//Giao thức
function protocolFix()
{
	$protocol = "http://";
	$www = explode("www.", DOMAIN)[1] ?? false;

	//Chuyển sang https
	if (BUILDER_SSL) {
		if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == "off") {
			$protocol = "https://";
			$link = $protocol . DOMAIN . URI;
		}
	}

	//Chuyển sang WWW
	if (BUILDER_WWW) {
		if (!$www) {
			$link = $protocol . "www." . DOMAIN . URI;
		}
	} else if ($www) {
		$link = $protocol . $www . URI;
	}

	//Chuyển hướng
	if (!empty($link)) {
		header('HTTP/1.1 301 Moved Permanently');
		header("Location: $link");
		die;
	}
}

protocolFix();


/*
# Các function
*/

//Check dung lượng thư mục
function folderSize($dir)
{
	$dir = dirname($dir);
	$size = shell_exec('du -s --block-size=1 '.$dir.' | cut -f1');
	return $size;
}


//Chuyển Mb sang Byte
function mb2Bytes($size)
{
	return $size * 1024 * 1024;
}

//Chuyển byte sang Kb,Mb,Gb
function bytesConvert($size, $type = "auto")
{
	if ($size < 1024) {
		$out["auto"] = $size . " Bytes";
		$out["Bytes"] = $out["auto"];
	} else if (($size < 1048576) && ($size > 1023)) {
		$out["auto"] = round($size / 1024, 0) . " KB";
		$out["KB"] = $out["auto"];
	} elseif (($size < 1073741824) && ($size > 1048575)) {
		$out["auto"] = round($size / 1048576, 0) . " MB";
		$out["MB"] = $out["auto"];
	} else {
		$out["auto"] = round($size / 1073741824, 1) . " GB";
		$out["GB"] = $out["auto"];
	}
	return $out[$type] ?? $size;
}
// Xóa logo WP
add_action( 'admin_bar_menu', function ( $wp_admin_bar ) {
	$wp_admin_bar->remove_node( 'wp-logo' );
}, 999 );