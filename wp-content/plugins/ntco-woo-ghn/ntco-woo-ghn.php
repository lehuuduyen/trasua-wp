<?php
/*
 * Plugin Name: NTCO - GHN
 * Version: 1.0.0
 * Description: GHN
 * Author: NTCO
 * Text Domain: ntco-ghn
*/

defined('ABSPATH') or die('No script kiddies please!');

if (!defined('NTCO_GHNV2_VERSION_NUM'))
	define('NTCO_GHNV2_VERSION_NUM', '1.0.0');
if (!defined('NTCO_GHNV2_URL'))
	define('NTCO_GHNV2_URL', plugin_dir_url(__FILE__));
if (!defined('NTCO_GHNV2_BASENAME'))
	define('NTCO_GHNV2_BASENAME', plugin_basename(__FILE__));
if (!defined('NTCO_GHNV2_PLUGIN_DIR'))
	define('NTCO_GHNV2_PLUGIN_DIR', plugin_dir_path(__FILE__));
if (!defined('NTCO_GHNV2_NOTE_VERSION'))
	define('NTCO_GHNV2_NOTE_VERSION', 1);
include 'ntco-woo-ghn-main.php';
