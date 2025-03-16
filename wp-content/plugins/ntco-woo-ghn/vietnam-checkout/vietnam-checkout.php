<?php

defined("ABSPATH") or exit("No script kiddies please!");
if (!class_exists("NTCO_VietNam_Checkout")) {
	class NTCO_VietNam_Checkout {
		protected $_version = "1.0.0";
		public $_optionName = "ntco_vncheckout";
		public $_optionGroup = "ntco-vn-checkout-options-group";
		public $_defaultOptions = ["active_village" => "", "shipping_to_village" => "", "required_village" => "", "to_vnd" => "", "remove_methob_title" => "", "freeship_remove_other_methob" => "", "khoiluong_quydoi" => "6000", "active_vnd2usd" => 0, "vnd_usd_rate" => "22745", "vnd2usd_currency" => "USD", "active_orderstyle" => 0, "alepay_support" => 0, "enable_postcode" => 0, "show_postcode" => 0, "enable_gender" => 0, "enable_getaddressfromphone" => 0, "enable_recaptcha" => 0, "recaptcha_sitekey" => "", "recaptcha_secretkey" => "", "hide_special_method" => 0, "roundup_ship" => 0, "not_required_email" => 0, "load_address" => 2, "convert_price_text" => 0];
		public $enabled_free_shipping = "";
		protected static $instance;
		public static function init() {
			is_null(self::$instance) && (self::$instance = new self());
			return self::$instance;
		}
		public function __construct() {
			global $vncheckout_settings;
			if (!defined("VN_CHECKOUT_DIR")) {
				define("VN_CHECKOUT_DIR", plugin_dir_path(__FILE__));
			}
			$this->load_plugin_textdomain();
			if (defined("NTCO_GHTK_VERSION_NUM")) {
				$old_option = get_option("sync_ghtk_v2");
			} else {
				$old_option = get_option("ntco_woo_district");
			}
			$current_option = get_option($this->_optionName);
			if ($old_option && !$current_option) {
				$this->_defaultOptions = wp_parse_args($old_option, $this->_defaultOptions);
			}
			add_option($this->_optionName, $this->_defaultOptions);
			$vncheckout_settings = $this->get_vncheckout_options();
			add_filter("woocommerce_states", [$this, "vietnam_cities_woocommerce"], 9999);
			add_action("woocommerce_checkout_process", [$this, "ntco_gender_field_process"]);
			add_action("wp_enqueue_scripts", [$this, "ntco_enqueue_UseAjaxInWp"], 999);
			add_action("admin_enqueue_scripts", [$this, "admin_enqueue_scripts"]);
			add_action("wp_ajax_load_diagioihanhchinh", [$this, "load_diagioihanhchinh_func"]);
			add_action("wp_ajax_nopriv_load_diagioihanhchinh", [$this, "load_diagioihanhchinh_func"]);
			add_filter("woocommerce_order_formatted_billing_address", [$this, "ntco_woocommerce_order_formatted_billing_address"], 10, 2);
			add_filter("woocommerce_order_formatted_shipping_address", [$this, "ntco_woocommerce_order_formatted_shipping_address"], 10, 2);
			add_filter("woocommerce_my_account_my_address_formatted_address", [$this, "ntco_woocommerce_my_account_my_address_formatted_address"], 10, 3);
			add_action("admin_init", [$this, "register_mysettings"]);
			include_once "apps.php";
			add_filter("woocommerce_get_country_locale", [$this, "ntco_woocommerce_get_country_locale"], 99);
			add_filter("woocommerce_billing_fields", [$this, "woocommerce_billing_fields"], 10, 2);
			add_filter("woocommerce_shipping_fields", [$this, "woocommerce_shipping_fields"], 10, 2);
			add_filter("woocommerce_checkout_fields", [$this, "custom_override_checkout_fields"], 99999);
			add_filter("woocommerce_admin_billing_fields", [$this, "ntco_woocommerce_admin_fields"], 99);
			add_filter("woocommerce_admin_shipping_fields", [$this, "ntco_woocommerce_admin_fields"], 99);
			add_filter("woocommerce_shipping_calculator_enable_postcode", "__return_false");
			add_filter("woocommerce_get_order_address", [$this, "ntco_woocommerce_get_order_address"], 99, 2);
			add_filter("woocommerce_rest_prepare_shop_order_object", [$this, "ntco_woocommerce_rest_prepare_shop_order_object"], 99, 3);
			add_filter("woocommerce_localisation_address_formats", [$this, "ntco_woocommerce_localisation_address_formats"], 999999);
			add_filter("woocommerce_formatted_address_replacements", [$this, "ntco_woocommerce_formatted_address_replacements"], 999999, 2);
			add_filter("woocommerce_customer_meta_fields", [$this, "woocommerce_customer_meta_fields"], 99);
			add_filter("woocommerce_general_settings", [$this, "woocommerce_general_settings"]);
			add_action("woocommerce_admin_field_selectajax", [$this, "woocommerce_admin_field_selectajax"]);
			add_action("woocommerce_checkout_update_order_review", [$this, "ntco_update_checkout_func"], 10);
			include_once "freeshipping-by-payment-methob.php";
			$free_shipping = get_option("woocommerce_ntco_freeshipping_by_paymentmethod_settings");
			$this->enabled_free_shipping = isset($free_shipping["enabled"]) ? $free_shipping["enabled"] : "no";
			add_action("wp_ajax_ghtk_update_country", [$this, "ghtk_update_country_func"]);
			add_action("woocommerce_created_customer", [$this, "woocommerce_created_customer"]);
			add_action("woocommerce_api_create_customer", [$this, "woocommerce_created_customer"]);
			add_action("vn_checkout_setting_general", [$this, "vn_checkout_setting_general"]);
			add_action("woocommerce_before_checkout_billing_form", [$this, "get_address_by_phonenumber"]);
			add_action("wp_ajax_nopriv_get_address_byphone", [$this, "get_address_byphone_func"]);
			add_filter("woocommerce_package_rates", [$this, "woocommerce_package_rates"], 10);
			if ($vncheckout_settings["roundup_ship"]) {
				add_filter("woocommerce_package_rates", [$this, "ntco_roundup_shipping_cost"], 999, 2);
			}
			include_once "includes/address-installer.php";
			add_action("woocommerce_calculated_shipping", [$this, "woocommerce_calculated_shipping"]);
		}
		public function load_plugin_textdomain() {
			$locale = determine_locale();
			$locale = apply_filters("plugin_locale", $locale, "ntco-vn-checkout");
			unload_textdomain("ntco-vn-checkout");
			// load_textdomain("ntco-vn-checkout", WP_LANG_DIR . "/plugins/ntco-vn-checkout-" . $locale . ".mo");
			// load_plugin_textdomain("ntco-vn-checkout", false, plugin_basename(dirname(__FILE__)) . "/languages");
		}
		public function woocommerce_created_customer($customer_id) {
			$customer = new WC_Customer($customer_id);
			if ($customer && !is_wp_error($customer)) {
				if (!$customer->get_billing_country()) {
					$customer->set_billing_country("VN");
				}
				if (!$customer->get_shipping_country()) {
					$customer->set_shipping_country("VN");
				}
				$customer->save();
			}
		}
		public function get_vncheckout_options() {
			return wp_parse_args(get_option($this->_optionName), $this->_defaultOptions);
		}
		public function vn_checkout_setting_general() {
			include "setting-general.php";
		}
		public function register_mysettings() {
			register_setting($this->_optionGroup, $this->_optionName);
		}
		public function vietnam_cities_woocommerce($states) {
			$states["VN"] = $this->get_tinhthanhpho();
			return $states;
		}
		public function custom_override_checkout_fields($fields) {
			if ($this->get_options("enable_gender")) {
				$fields["billing"]["billing_gender"] = ["label" => __("Gender", "ntco-vn-checkout"), "priority" => 5, "default" => "female", "required" => true, "type" => "radio", "options" => ["male" => __("Mr", "ntco-vn-checkout"), "female" => __("Mrs", "ntco-vn-checkout")]];
			}
			if ($this->get_options("not_required_email")) {
				$fields["billing"]["billing_email"]["required"] = false;
			}
			if ($this->get_options()) {
				unset($fields["billing"]["billing_address_2"]);
				$fields["billing"]["billing_address_1"]["class"] = ["form-row-wide"];
				unset($fields["shipping"]["shipping_address_2"]);
				$fields["shipping"]["shipping_address_1"]["class"] = ["form-row-wide"];
			}
			if (!$this->get_options("show_postcode")) {
				unset($fields["billing"]["billing_postcode"]);
				unset($fields["shipping"]["shipping_postcode"]);
			}
			if (!isset($fields["billing"]["billing_country"])) {
				$fields["billing"]["billing_country"] = ["type" => "hidden", "class" => [""], "input_class" => ["country_to_state"], "default" => "VN", "clear" => true, "priority" => 40];
			}
			if (!isset($fields["shipping"]["shipping_country"])) {
				$fields["shipping"]["shipping_country"] = ["type" => "hidden", "class" => [""], "input_class" => ["country_to_state"], "default" => "VN", "clear" => true, "priority" => 40];
			}
			if (get_theme_mod("checkout_fields_email_first", 0)) {
				$fields["billing"]["billing_email"]["class"] = ["form-row-wide"];
				$fields["billing"]["billing_first_name"]["class"] = ["form-row-first"];
				if ($this->get_options("alepay_support")) {
					$fields["billing"]["billing_phone"]["class"] = ["form-row-wide"];
				} else {
					$fields["billing"]["billing_phone"]["class"] = ["form-row-last"];
				}
			}
			uasort($fields["billing"], [$this, "sort_fields_by_order"]);
			uasort($fields["shipping"], [$this, "sort_fields_by_order"]);
			$fields = apply_filters("ntco_checkout_fields", $fields);
			$ward_is_null = apply_filters("ward_is_null", [498, 471, 318]);
			$billing_city = isset($_POST["billing_city"]) ? intval($_POST["billing_city"]) : "";
			if (in_array($billing_city, $ward_is_null)) {
				$fields["billing"]["billing_address_2"]["required"] = false;
			}
			$shipping_city = isset($_POST["shipping_city"]) ? intval($_POST["shipping_city"]) : "";
			if (in_array($shipping_city, $ward_is_null)) {
				$fields["shipping"]["shipping_address_2"]["required"] = false;
			}
			return apply_filters("ghtk_custom_field_checkout", $fields);
		}
		public function woocommerce_billing_fields($fields, $country) {
			if ($country == "VN") {
				if (!$this->get_options("alepay_support")) {
					$fields["billing_first_name"]["class"] = ["form-row-wide"];
					unset($fields["billing_last_name"]);
					unset($fields["billing_country"]);
					$fields["billing_country"] = ["type" => "hidden", "class" => [""], "input_class" => ["country_to_state"], "default" => "VN", "clear" => true, "priority" => 40];
					$fields["billing_first_name"]["label"] = __("Họ và tên", "ntco-vn-checkout");
					$fields["billing_first_name"]["placeholder"] = _x("Nhập họ và tên", "placeholder", "ntco-vn-checkout");
				}
				$fields["billing_phone"]["class"] = ["form-row-first"];
				$fields["billing_phone"]["priority"] = 30;
				$fields["billing_email"]["class"] = ["form-row-last"];
				$fields["billing_email"]["priority"] = 35;
				unset($fields["billing_company"]);
			}
			$fields["billing_phone"]["placeholder"] = _x("Nhập số điện thoại", "placeholder", "ntco-vn-checkout");
			$fields["billing_email"]["placeholder"] = _x("Nhập địa chỉ Email", "placeholder", "ntco-vn-checkout");
			return $fields;
		}
		public function woocommerce_shipping_fields($fields, $country) {
			if ($country == "VN") {
				$fields["shipping_phone"] = ["label" => __("Phone", "woocommerce"), "required" => "required" === get_option("woocommerce_checkout_phone_field", "required"), "type" => "tel", "class" => ["form-row-last"], "validate" => ["phone"], "autocomplete" => "tel", "priority" => 20];
				if (!$this->get_options("alepay_support")) {
					unset($fields["shipping_last_name"]);
					unset($fields["shipping_country"]);
					$fields["shipping_country"] = ["type" => "hidden", "class" => [""], "input_class" => ["country_to_state"], "default" => "VN", "clear" => true, "priority" => 40];
					$fields["shipping_first_name"]["label"] = __("Họ và tên", "ntco-vn-checkout");
					$fields["shipping_first_name"]["placeholder"] = _x("Nhập họ và tên", "placeholder", "ntco-vn-checkout");
				} else {
					$fields["shipping_phone"]["class"] = ["form-row-wide"];
				}
				$fields["shipping_phone"]["placeholder"] = _x("Nhập số điện thoại", "placeholder", "ntco-vn-checkout");
				unset($fields["shipping_company"]);
			}
			return $fields;
		}
		public function sort_fields_by_order($a, $b) {
			if (!isset($b["priority"]) || !isset($a["priority"]) || $a["priority"] == $b["priority"]) {
				return 0;
			}
			return $a["priority"] < $b["priority"] ? -1 : 1;
		}
		public function search_in_array($array, $key, $value) {
			$results = [];
			if (is_array($array)) {
				if (isset($array[$key]) && $array[$key] == $value) {
					$results[] = $array;
				} else {
					if (isset($array[$key]) && is_serialized($array[$key]) && in_array($value, maybe_unserialize($array[$key]))) {
						$results[] = $array;
					}
				}
				foreach ($array as $subarray) {
					$results = array_merge($results, $this->search_in_array($subarray, $key, $value));
				}
			}
			return $results;
		}
		public function check_file_open_status($file_url = "") {
			$load_address = apply_filters("load_address_type", $this->get_options("load_address"));
			if ($load_address == 4 || $load_address == 2) {
				if (!apply_filters("load_address_type_2", false)) {
					$status_key = "check_file_open_status";
					if (false === ($status = apply_filters("check_file_open_status_transient", get_transient($status_key), $load_address))) {
						$response = wp_remote_head($file_url, ["timeout" => 0.1]);
						$status = wp_remote_retrieve_response_code($response);
						if ($status == 200) {
							set_transient($status_key, $status, DAY_IN_SECONDS);
						}
					}
					if ($status == 200) {
						return 200;
					}
				} else {
					return 200;
				}
			}
			return false;
		}
		public function admin_enqueue_scripts() {
			$get_address = plugin_dir_url(__FILE__) . "get-address.php";
			if ($this->check_file_open_status($get_address) != 200) {
				$get_address = admin_url("admin-ajax.php");
			}
			$json_address = plugins_url("cities/ntco-vn-address.json", __FILE__);
			wp_enqueue_style("vn_checkout_styles", plugins_url("/assets/css/admin.css", __FILE__), ["jquery-ui-style"], $this->_version, "all");
			wp_enqueue_script("vn_checkout_admin_order", plugins_url("/assets/js/admin-district-admin-order.js", __FILE__), ["jquery", "wp-util", "jquery-ui-datepicker"], $this->_version, true);
			wp_localize_script("vn_checkout_admin_order", "admin_vncheckout_array", ["ajaxurl" => admin_url("admin-ajax.php"), "get_address" => $get_address, "json_address" => $json_address, "json_address_enable" => $this->get_options("load_address") == 2 ? true : false, "formatNoMatches" => __("No value", "ntco-vn-checkout"), "placeholder_city_text" => _x("Chọn Quận/Huyện", "placeholder", "ntco-vn-checkout"), "placeholder_ward_text" => _x("Chọn xã/phường/thị trấn", "placeholder", "ntco-vn-checkout"), "vnaddress_db_version" => get_option("vnaddress_db_version")]);
		}
		public function get_json_address_url() {
			return esc_url(apply_filters("get_json_address_url", plugins_url("cities/ntco-vn-address.json", __FILE__)));
		}
		public function ntco_enqueue_UseAjaxInWp() {
			if (is_checkout() || is_page(get_option("woocommerce_edit_address_page_id")) || apply_filters("enable_script_vn_checkout", false)) {
				if ($this->get_options("enable_getaddressfromphone") && !is_user_logged_in()) {
					wp_enqueue_style("magnific-popup", plugins_url("/assets/css/magnific-popup.css", __FILE__), [], $this->_version, "all");
					wp_enqueue_script("magnific-popup-jquery", plugins_url("assets/js/magnific-popup.js", __FILE__), ["jquery"], $this->_version, true);
				}
				wp_enqueue_style("vncheckout_styles", plugins_url("/assets/css/ntco_dwas_style.css", __FILE__), [], $this->_version, "all");
				if ($this->enable_recaptcha()) {
					wp_enqueue_script("recaptcha", "https://www.google.com/recaptcha/api.js?hl=vi", ["jquery"], $this->_version, true);
				}
				if (!wp_script_is("selectWoo")) {
					wp_enqueue_script("selectWoo", WC()->plugin_url() . "/assets/js/selectWoo/selectWoo.full.min.js", ["jquery"], "1.0.6");
				}
				wp_enqueue_script("ntco_tinhthanhpho", plugins_url("assets/js/ntco_tinhthanh" . apply_filters("vn_checkout_script_prefix", "") . ".js", __FILE__), ["jquery", "selectWoo"], $this->_version, true);
				$get_address = plugin_dir_url(__FILE__) . "get-address.php";
				if ($this->check_file_open_status($get_address) != 200) {
					$get_address = admin_url("admin-ajax.php");
				}
				$json_address = $this->get_json_address_url();
				$php_array = ["admin_ajax" => admin_url("admin-ajax.php"), "get_address" => $get_address, "json_address" => $json_address, "json_address_enable" => $this->get_options("load_address") == 2 ? true : false, "home_url" => home_url(), "formatNoMatches" => __("No value", "ntco-vn-checkout"), "phone_error" => __("Phone number is incorrect", "ntco-vn-checkout"), "loading_text" => __("Loading...", "ntco-vn-checkout"), "loadaddress_error" => __("Phone number does not exist", "ntco-vn-checkout"), "enabled_free_shipping" => false, "has_vtp" => boolval(class_exists("Woo_ViettelPost_Class")), "vnaddress_db_version" => get_option("vnaddress_db_version"), "placeholder_city_text" => _x("Chọn Quận/Huyện", "placeholder", "ntco-vn-checkout"), "placeholder_ward_text" => _x("Chọn xã/phường/thị trấn", "placeholder", "ntco-vn-checkout"), "code" => defined("VNCHECKOUT_LICENSE") ? VNCHECKOUT_LICENSE : ""];
				if ($this->enabled_free_shipping == "yes") {
					$php_array["enabled_free_shipping"] = true;
				}
				wp_localize_script("ntco_tinhthanhpho", "vncheckout_array", apply_filters("vncheckout_array_localize_script", $php_array));
			}
		}
		public function load_diagioihanhchinh_func() {
			$matp = isset($_POST["matp"]) ? sanitize_text_field($_POST["matp"]) : "";
			$maqh = isset($_POST["maqh"]) ? intval($_POST["maqh"]) : "";
			if ($matp) {
				$result = $this->get_list_district($matp);
				wp_send_json_success($result);
			}
			if ($maqh) {
				$result = $this->get_list_village($maqh);
				wp_send_json_success($result);
			}
			wp_send_json_error();
			exit;
		}
		public function ntco_get_name_location($arg = [], $id = "", $key = "") {
			if (is_array($arg) && !empty($arg)) {
				$nameQuan = $this->search_in_array($arg, $key, $id);
				$nameQuan = isset($nameQuan[0]["name"]) ? $nameQuan[0]["name"] : "";
				return $nameQuan;
			}
			return false;
		}
		public function get_tinhthanhpho() {
			include "cities/tinh_thanhpho.php";
			$tinh_thanhpho = apply_filters("vn_checkout_tinh_thanhpho", $tinh_thanhpho);
			return is_array($tinh_thanhpho) ? $tinh_thanhpho : [];
		}
		public function get_quanhuyen() {
			include "cities/quan_huyen.php";
			return apply_filters("vn_checkout_quanhuyen", $quan_huyen);
		}
		public function get_phuongxa() {
			include "cities/xa_phuong_thitran.php";
			return apply_filters("vn_checkout_phuongxa", $xa_phuong_thitran);
		}
		public function get_name_city($id = "") {
			if (has_filter("vn_checkout_get_name_city")) {
				return apply_filters("vn_checkout_get_name_city", $id);
			}
			$tinh_thanhpho = $this->get_tinhthanhpho();
			$id_tinh = sanitize_text_field($id);
			$tinh_thanhpho = isset($tinh_thanhpho[$id_tinh]) ? $tinh_thanhpho[$id_tinh] : "";
			return $tinh_thanhpho;
		}
		public function get_name_district($id = "") {
			if (has_filter("vn_checkout_get_name_district")) {
				return apply_filters("vn_checkout_get_name_district", $id);
			}
			$id_quan = sprintf("%03d", intval($id));
			$vnaddress_db_version = get_option("vnaddress_db_version");
			if ($vnaddress_db_version) {
				return self::get_district($id_quan, "name");
			}
			$quan_huyen = $this->get_quanhuyen();
			if (is_array($quan_huyen) && !empty($quan_huyen)) {
				$nameQuan = $this->search_in_array($quan_huyen, "maqh", $id_quan);
				$nameQuan = isset($nameQuan[0]["name"]) ? $nameQuan[0]["name"] : "";
				return $nameQuan;
			}
			return false;
		}
		public static function get_district($value = "", $key = "*") {
			global $wpdb;
			$district = $wpdb->get_row($wpdb->prepare("SELECT " . $key . " FROM " . $wpdb->prefix . "vnaddress_districts WHERE maqh = %s LIMIT 1", $value));
			if ($key == "*") {
				return $district;
			}
			return isset($district->{$key}) && $district->{$key} ? $district->{$key} : "";
		}
		public static function get_district_by_city($matp = "") {
			global $wpdb;
			$district = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "vnaddress_districts WHERE matp = %s LIMIT 200", $matp), ARRAY_A);
			return $district;
		}
		public function get_name_village($id = "") {
			if (has_filter("vn_checkout_get_name_village")) {
				return apply_filters("vn_checkout_get_name_village", $id);
			}
			$id_xa = sprintf("%05d", intval($id));
			$vnaddress_db_version = get_option("vnaddress_db_version");
			if ($vnaddress_db_version) {
				return self::get_village($id_xa, "name");
			}
			$xa_phuong_thitran = $this->get_phuongxa();
			if (is_array($xa_phuong_thitran) && !empty($xa_phuong_thitran)) {
				$name = $this->search_in_array($xa_phuong_thitran, "xaid", $id_xa);
				$name = isset($name[0]["name"]) ? $name[0]["name"] : "";
				return $name;
			}
			return false;
		}
		public static function get_village($value = "", $key = "*") {
			global $wpdb;
			$district = $wpdb->get_row($wpdb->prepare("SELECT " . $key . " FROM " . $wpdb->prefix . "vnaddress_wards WHERE xaid = %s LIMIT 1", $value));
			if ($key == "*") {
				return $district;
			}
			return isset($district->{$key}) && $district->{$key} ? $district->{$key} : "";
		}
		public static function get_village_by_district($maqh = "") {
			global $wpdb;
			$district = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "vnaddress_wards WHERE maqh = %s LIMIT 200", $maqh), ARRAY_A);
			return $district;
		}
		public function ntco_woocommerce_localisation_address_formats($arg) {
			$arg["VN"] = "{phone}\n{gender} {name}\n{company}\n{address_1}\n{address_2}\n{city}\n{state}\n{country}";
			return $arg;
		}
		public function ntco_woocommerce_order_formatted_billing_address($eArg, $eThis) {
			if (!$eArg) {
				return [];
			}
			if ($this->ntco_check_woo_version()) {
				$orderID = $eThis->get_id();
			} else {
				$orderID = $eThis->id;
			}
			$nameTinh = $this->get_name_city($eThis->get_billing_state());
			$nameQuan = $this->get_name_district($eThis->get_billing_city());
			$nameXa = $this->get_name_village($eThis->get_billing_address_2());
			unset($eArg["state"]);
			unset($eArg["city"]);
			unset($eArg["address_2"]);
			$eArg["state"] = $nameTinh;
			$eArg["city"] = $nameQuan;
			$eArg["address_2"] = $nameXa;
			$gender = $eThis->get_meta("_billing_gender", true);
			if (!isset($eArg["gender"]) && $gender) {
				$gender = $gender == "male" ? __("Mr", "ntco-vn-checkout") : __("Mrs", "ntco-vn-checkout");
				$eArg["gender"] = $gender;
			}
			return $eArg;
		}
		public function ntco_woocommerce_order_formatted_shipping_address($eArg, $eThis) {
			if (!$eArg) {
				return [];
			}
			if ($this->ntco_check_woo_version()) {
				$orderID = $eThis->get_id();
			} else {
				$orderID = $eThis->id;
			}
			$nameTinh = $this->get_name_city($eThis->get_shipping_state());
			$nameQuan = $this->get_name_district($eThis->get_shipping_city());
			$nameXa = $this->get_name_village($eThis->get_shipping_address_2());
			unset($eArg["state"]);
			unset($eArg["city"]);
			unset($eArg["address_2"]);
			$eArg["state"] = $nameTinh;
			$eArg["city"] = $nameQuan;
			$eArg["address_2"] = $nameXa;
			return $eArg;
		}
		public function ntco_woocommerce_my_account_my_address_formatted_address($args, $customer_id, $name) {
			if (!$args) {
				return [];
			}
			$nameTinh = $this->get_name_city(get_user_meta($customer_id, $name . "_state", true));
			$nameQuan = $this->get_name_district(get_user_meta($customer_id, $name . "_city", true));
			$nameXa = $this->get_name_village(get_user_meta($customer_id, $name . "_address_2", true));
			unset($args["address_2"]);
			unset($args["city"]);
			unset($args["state"]);
			$args["state"] = $nameTinh;
			$args["city"] = $nameQuan;
			$args["address_2"] = $nameXa;
			return $args;
		}
		public function natorder($a, $b) {
			return strnatcasecmp($a["name"], $b["name"]);
		}
		public function get_list_district($matp = "") {
			if (!$matp) {
				return false;
			}
			if (has_filter("vn_checkout_get_list_district")) {
				return apply_filters("vn_checkout_get_list_district", $matp, $this);
			}
			$matp = sanitize_text_field($matp);
			$vnaddress_db_version = get_option("vnaddress_db_version");
			if ($vnaddress_db_version) {
				$result = self::get_district_by_city($matp);
			} else {
				$result = $this->search_in_array($this->get_quanhuyen(), "matp", $matp);
			}
			usort($result, [$this, "natorder"]);
			return $result;
		}
		public function get_list_district_select($matp = "") {
			$district_select = [];
			if (has_filter("vn_checkout_get_list_district_select")) {
				$district_select = apply_filters("vn_checkout_get_list_district_select", $matp, $this);
			} else {
				$district_select_array = $this->get_list_district($matp);
				if ($district_select_array && is_array($district_select_array)) {
					foreach ($district_select_array as $district) {
						$district_select[$district["maqh"]] = $district["name"];
					}
				}
			}
			return $district_select;
		}
		public function get_list_village($maqh = "") {
			if (!$maqh) {
				return false;
			}
			if (has_filter("vn_checkout_get_list_village")) {
				return apply_filters("vn_checkout_get_list_village", $maqh, $this);
			}
			$maqh = sprintf("%03d", intval($maqh));
			$vnaddress_db_version = get_option("vnaddress_db_version");
			if ($vnaddress_db_version) {
				$result = self::get_village_by_district($maqh);
			} else {
				$result = $this->search_in_array($this->get_phuongxa(), "maqh", $maqh);
			}
			usort($result, [$this, "natorder"]);
			return $result;
		}
		public function get_list_village_select($maqh = "") {
			$village_select = [];
			if (has_filter("vn_checkout_get_list_village_select")) {
				$village_select = apply_filters("vn_checkout_get_list_village_select", $maqh, $this);
			} else {
				$village_select_array = $this->get_list_village($maqh);
				if ($village_select_array && is_array($village_select_array)) {
					foreach ($village_select_array as $village) {
						$village_select[$village["xaid"]] = $village["name"];
					}
				}
			}
			return $village_select;
		}
		public function get_options($option = "active_village") {
			$flra_options = wp_parse_args(get_option($this->_optionName), $this->_defaultOptions);
			return isset($flra_options[$option]) ? $flra_options[$option] : false;
		}
		public function ntco_check_woo_version($version = "3.0.0") {
			if (defined("WOOCOMMERCE_VERSION") && version_compare(WOOCOMMERCE_VERSION, $version, ">=")) {
				return true;
			}
			return false;
		}
		public function ntco_woocommerce_get_country_locale($args) {
			$args["VN"]["state"]["label"] = __("Tỉnh/Thành phố", "ntco-vn-checkout");
			$args["VN"]["state"]["required"] = true;
			$args["VN"]["state"]["class"] = ["form-row-first", "ntco-address-field"];
			$args["VN"]["state"]["placeholder"] = _x("Select Province/City", "placeholder", "ntco-vn-checkout");
			$args["VN"]["state"]["priority"] = 45;
			$args["VN"]["city"]["label"] = __("Quận huyện", "ntco-vn-checkout");
			$args["VN"]["city"]["required"] = true;
			$args["VN"]["city"]["type"] = "select";
			$args["VN"]["city"]["class"] = ["form-row-last", "address-field", "update_totals_on_change"];
			$args["VN"]["city"]["placeholder"] = _x("Chọn quận/huyện", "placeholder", "ntco-vn-checkout");
			$args["VN"]["city"]["priority"] = 50;
			$args["VN"]["address_2"]["label"] = __("Xã/Phường/Thị trấn", "ntco-vn-checkout");
			$args["VN"]["address_2"]["required"] = true;
			$args["VN"]["address_2"]["type"] = "select";
			$args["VN"]["address_2"]["class"] = ["form-row-first", "ntco-address-field"];
			$args["VN"]["address_2"]["placeholder"] = _x("Chọn xã/phường/thị trấn", "placeholder", "ntco-vn-checkout");
			$args["VN"]["address_2"]["priority"] = 60;
			if ($this->get_options("required_village")) {
				$args["VN"]["address_2"]["required"] = false;
			}
			if ($this->get_options("shipping_to_village") || function_exists("ntco_vnpost")) {
				$args["VN"]["address_2"]["class"] = ["form-row-first", "ntco-address-field", "update_totals_on_change"];
			}
			$args["VN"]["address_1"]["label"] = __("Địa chỉ", "ntco-vn-checkout");
			$args["VN"]["address_1"]["placeholder"] = _x("Toà nhà, số nhà, tên đường", "placeholder", "ntco-vn-checkout");
			$args["VN"]["address_1"]["priority"] = 70;
			$args["VN"]["address_1"]["class"] = ["form-row-last"];
			if (!$this->get_options("enable_postcode")) {
				$args["VN"]["postcode"] = ["required" => false, "hidden" => true];
			} else {
				$args["VN"]["postcode"] = ["required" => false, "hidden" => false];
			}
			return $args;
		}
		public function ntco_woocommerce_admin_fields($billing_fields) {
			foreach ($billing_fields as $key => $value) {
				$billing_fields[$key]["priority"] = 10;
			}
			if (isset($billing_fields["country"])) {
				$billing_fields["country"]["priority"] = 20;
			}
			if (isset($billing_fields["state"])) {
				$billing_fields["state"]["priority"] = 30;
				$billing_fields["state"]["label"] = __("Province/City", "ntco-vn-checkout");
			}
			if (isset($billing_fields["city"])) {
				$billing_fields["city"]["priority"] = 40;
				$billing_fields["city"]["class"] = "js_field-city select short";
				$billing_fields["city"]["label"] = __("District", "ntco-vn-checkout");
			}
			if (isset($billing_fields["address_2"])) {
				$billing_fields["address_2"]["priority"] = 50;
				$billing_fields["address_2"]["label"] = __("Commune/Ward/Town", "ntco-vn-checkout");
			}
			if (isset($billing_fields["address_1"])) {
				$billing_fields["address_1"]["priority"] = 60;
			}
			if (isset($billing_fields["postcode"])) {
				$billing_fields["postcode"]["priority"] = 70;
				$billing_fields["postcode"]["wrapper_class"] = "form-field-wide";
			}
			uasort($billing_fields, [$this, "sort_fields_by_order"]);
			return apply_filters("ntco_woocommerce_order_fields", $billing_fields);
		}
		public function get_cart_contents_weight($package = []) {
			$weight = 0;
			if (isset($package["contents"]) && !empty($package["contents"])) {
				foreach ($package["contents"] as $cart_item_key => $values) {
					$weight += (float) $values["data"]->get_weight() * $values["quantity"];
				}
				$weight = $this->convert_weight_to_gram($weight);
			}
			return apply_filters("wc_ntco_cart_contents_weight", $weight);
		}
		public function convert_weight_to_gram($weight) {
			get_option("woocommerce_weight_unit");
			switch (get_option("woocommerce_weight_unit")) {
				case "kg":
					$weight = $weight * 1000;
					break;
				case "lbs":
					$weight = $weight * 0;
					break;
				case "oz":
					$weight = $weight * 0;
					break;
			}
			return $weight;
		}
		public function convert_gram_to_weight($weight) {
			get_option("woocommerce_weight_unit");
			switch (get_option("woocommerce_weight_unit")) {
				case "kg":
					$weight = $weight * 0;
					break;
				case "lbs":
					$weight = $weight * 0;
					break;
				case "oz":
					$weight = $weight * 0;
					break;
			}
			return $weight;
		}
		public function convert_kg_to_gram($weight) {
			$weight = $weight * 1000;
			return $weight;
		}
		public function convert_weight_to_kg($weight) {
			get_option("woocommerce_weight_unit");
			switch (get_option("woocommerce_weight_unit")) {
				case "g":
					$weight = $weight * 0;
					break;
				case "lbs":
					$weight = $weight * 0;
					break;
				case "oz":
					$weight = $weight * 0;
					break;
			}
			return $weight;
		}
		public function get_cart_contents_weight_kg($package = []) {
			$weight = 0;
			if (isset($package["contents"]) && !empty($package["contents"])) {
				foreach ($package["contents"] as $cart_item_key => $values) {
					$weight += (float) $values["data"]->get_weight() * $values["quantity"];
				}
				$weight = $this->convert_weight_to_kg($weight);
			}
			return apply_filters("woocommerce_cart_contents_weight_kg", $weight);
		}
		public function convert_dimension_to_cm($dimension) {
			get_option("woocommerce_dimension_unit");
			switch (get_option("woocommerce_dimension_unit")) {
				case "m":
					$dimension = $dimension * 100;
					break;
				case "mm":
					$dimension = $dimension * 0;
					break;
				case "in":
					$dimension = $dimension * 0;
					break;
				case "yd":
					$dimension = $dimension * 0;
					break;
			}
			return $dimension;
		}
		public function convert_cm_to_dimension($dimension) {
			get_option("woocommerce_dimension_unit");
			switch (get_option("woocommerce_dimension_unit")) {
				case "m":
					$dimension = $dimension * 0;
					break;
				case "mm":
					$dimension = $dimension * 10;
					break;
				case "in":
					$dimension = $dimension * 0;
					break;
				case "yd":
					$dimension = $dimension * 0;
					break;
			}
			return $dimension;
		}
		public function get_order_weight($orderid = "", $field = "weight") {
			if (!$orderid) {
				return false;
			}
			$all_weight = 0;
			$all_width = 0;
			$all_height = 0;
			$all_length = 0;
			$product_list = $this->get_product_args($orderid);
			if ($product_list && !is_wp_error($product_list) && !empty($product_list)) {
				foreach ($product_list as $product) {
					$width = (float) $product["width"];
					$length = (float) $product["length"];
					$height = (float) $product["height"];
					$dimension_args = ["width" => $width, "length" => $length, "height" => $height];
					$min_dimension = array_search(min($dimension_args), $dimension_args);
					$dimension_args[$min_dimension] = $dimension_args[$min_dimension] * $product["quantity"];
					$product_item[] = $dimension_args;
					$all_weight += (float) ($product["quantity"] * $product["weight"]);
				}
				$max = 0;
				$key_max = 0;
				$min_val = 0;
				$mid_val = 0;
				foreach ($product_item as $key => $item) {
					if ($max <= max($item)) {
						$max = max($item);
						$key_max = $key;
					}
					$min_val += min($item);
					sort($item, SORT_NUMERIC);
					if ($mid_val <= $item[1]) {
						$mid_val = $item[1];
					}
				}
				$dimension_args = ["width" => $max, "length" => $mid_val, "height" => $min_val];
				$all_width = (float) $this->convert_dimension_to_cm($dimension_args["width"]);
				$all_height = (float) $this->convert_dimension_to_cm($dimension_args["height"]);
				$all_length = (float) $this->convert_dimension_to_cm($dimension_args["length"]);
			}
			if ($field == "length") {
				return apply_filters("get_order_length", $all_length, $orderid);
			}
			if ($field == "width") {
				return apply_filters("get_order_width", $all_width, $orderid);
			}
			if ($field == "height") {
				return apply_filters("get_order_height", $all_height, $orderid);
			}
			return apply_filters("get_order_weight", $all_weight, $orderid);
		}
		public function get_cart_dimension_package($package = [], $dimension_text = "height") {
			$dimension = 0;
			$product_item = [];
			if (isset($package["contents"]) && !empty($package["contents"])) {
				foreach ($package["contents"] as $cart_item_key => $values) {
					$width = (float) $values["data"]->get_width();
					$length = (float) $values["data"]->get_length();
					$height = (float) $values["data"]->get_height();
					$dimension_args = ["width" => $width, "length" => $length, "height" => $height];
					$min_dimension = array_search(min($dimension_args), $dimension_args);
					$dimension_args[$min_dimension] = $dimension_args[$min_dimension] * $values["quantity"];
					$product_item[] = $dimension_args;
				}
				$max = 0;
				$key_max = 0;
				$min_val = 0;
				$mid_val = 0;
				foreach ($product_item as $key => $item) {
					if ($max <= max($item)) {
						$max = max($item);
						$key_max = $key;
					}
					$min_val += min($item);
					sort($item, SORT_NUMERIC);
					if ($mid_val <= $item[1]) {
						$mid_val = $item[1];
					}
				}
				$dimension_args = ["width" => $min_val, "length" => $mid_val, "height" => $max];
				if ($dimension_text == "width") {
					$value = $dimension_args["width"];
				} else {
					if ($dimension_text == "length") {
						$value = $dimension_args["length"];
					} else {
						$value = $dimension_args["height"];
					}
				}
				$dimension = $this->convert_dimension_to_cm($value);
			}
			return apply_filters("wc_ntco_cart_contents_dimension", $dimension, $dimension_text);
		}
		public function get_contents_weight_gram($package = []) {
			$weight = (float) $this->get_cart_contents_weight($package);
			$width = (float) $this->get_cart_dimension_package($package, "width");
			$length = (float) $this->get_cart_dimension_package($package, "length");
			$height = (float) $this->get_cart_dimension_package($package, "height");
			$dimension = [$width, $length, $height];
			if (30 <= min($dimension) || 30 <= max($dimension)) {
				$khoiluong_quydoi = $this->get_options("khoiluong_quydoi") ? floatval($this->get_options("khoiluong_quydoi")) : 1;
				$weight_convert = $width * $length * $height / $khoiluong_quydoi;
				$weight_convert = $weight_convert * 1000;
				if ($weight < $weight_convert) {
					$weight = $weight_convert;
				}
			}
			return $weight;
		}
		public function order_get_shipping_total($order, $view = false) {
			$refunded = $order->get_total_shipping_refunded();
			$shipping_total = $order->get_shipping_total();
			$shipping_total_html = wc_price($shipping_total, ["currency" => $order->get_currency()]);
			if (0 < $refunded) {
				$shipping_total = $order->get_shipping_total() - $refunded;
				$shipping_total_html = "<del>" . wp_strip_all_tags(wc_price($shipping_total, ["currency" => $order->get_currency()])) . "</del> <ins>" . wc_price($shipping_total - $refunded, ["currency" => $order->get_currency()]) . "</ins>";
			}
			if ($view) {
				return $shipping_total_html;
			}
			return $shipping_total;
		}
		public function order_get_total($order) {
			$order_total = $order->get_total();
			$order_shipping_total = $this->order_get_shipping_total($order);
			$order_total_refunded = $order->get_total_refunded();
			if (function_exists("awcdp_init")) {
				$second_payment = floatval($order->get_meta("_awcdp_deposits_second_payment", true));
				if ($second_payment) {
					return $second_payment;
				}
			}
			return $order_total - $order_shipping_total - $order_total_refunded;
		}
		public function order_get_total_has_shipping($order) {
			$order_total = $order->get_total();
			$order_total_refunded = $order->get_total_refunded();
			if (function_exists("awcdp_init")) {
				$second_payment = floatval($order->get_meta("_awcdp_deposits_second_payment", true));
				if ($second_payment) {
					return $second_payment;
				}
			}
			return $order_total - $order_total_refunded;
		}
		public function get_customer_address_shipping($order) {
			if (!$order) {
				return false;
			}
			if (is_numeric($order)) {
				$order = wc_get_order($order);
			}
			$customer_address = [];
			if ($order && !is_wp_error($order)) {
				$billing_phone = wc_clean($order->get_billing_phone());
				$billing_ward = $order->get_billing_address_2();
				$billing_district = $order->get_billing_city();
				$billing_province = $order->get_billing_state();
				$billing_country = $order->get_billing_country();
				$billing_address = $order->get_billing_address_1();
				$billing_fullname = $order->get_formatted_billing_full_name();
				$billing_email = $order->get_billing_email();
				$shipping_phone = wc_clean($order->get_shipping_phone());
				if (!$shipping_phone) {
					$shipping_phone = $billing_phone;
				}
				if (!wc_ship_to_billing_address_only() && $order->needs_shipping_address() && $order->get_formatted_shipping_address()) {
					$customer_address["name"] = $order->get_formatted_shipping_full_name();
					$customer_address["address"] = $order->get_shipping_address_1();
					$customer_address["country"] = $order->get_shipping_country();
					$customer_address["province"] = $order->get_shipping_state();
					$customer_address["district"] = $order->get_shipping_city();
					$customer_address["ward"] = $order->get_shipping_address_2();
					$customer_address["email"] = $billing_email;
					$customer_address["phone"] = $shipping_phone;
				} else {
					$customer_address["name"] = $billing_fullname;
					$customer_address["address"] = $billing_address;
					$customer_address["country"] = $billing_country;
					$customer_address["province"] = $billing_province;
					$customer_address["district"] = $billing_district;
					$customer_address["ward"] = $billing_ward;
					$customer_address["email"] = $billing_email;
					$customer_address["phone"] = $billing_phone;
				}
			}
			return apply_filters("get_customer_address_shipping", $customer_address, $order);
		}
		public function ntco_woocommerce_get_order_address($value, $type) {
			if ($type == "billing" || $type == "shipping") {
				if (isset($value["state"]) && $value["state"]) {
					$state = $value["state"];
					$value["state"] = $this->get_name_city($state);
				}
				if (isset($value["city"]) && $value["city"]) {
					$city = $value["city"];
					$value["city"] = $this->get_name_district($city);
				}
				if (isset($value["address_2"]) && $value["address_2"]) {
					$address_2 = $value["address_2"];
					$value["address_2"] = $this->get_name_village($address_2);
				}
			}
			return $value;
		}
		public function ntco_woocommerce_rest_prepare_shop_order_object($response, $order, $request) {
			if (empty($response->data)) {
				return $response;
			}
			$fields = ["billing", "shipping"];
			foreach ($fields as $field) {
				if (isset($response->data[$field]["state"]) && $response->data[$field]["state"]) {
					$state = $response->data[$field]["state"];
					$response->data[$field]["state"] = $this->get_name_city($state);
				}
				if (isset($response->data[$field]["city"]) && $response->data[$field]["city"]) {
					$city = $response->data[$field]["city"];
					$response->data[$field]["city"] = $this->get_name_district($city);
				}
				if (isset($response->data[$field]["address_2"]) && $response->data[$field]["address_2"]) {
					$address_2 = $response->data[$field]["address_2"];
					$response->data[$field]["address_2"] = $this->get_name_village($address_2);
				}
			}
			return $response;
		}
		public function ntco_admin_print_footer_scripts() {
			$get_address = plugin_dir_url(__FILE__) . "get-address.php";
			if ($this->check_file_open_status($get_address) != 200) {
				$get_address = admin_url("admin-ajax.php");
			}
			echo "            <link rel='stylesheet' href='";
			echo plugins_url("/assets/css/pos_ntco.css", __FILE__);
			echo "' type='text/css' media='all' />\r\n\r\n            <script type='text/javascript'>\r\n                var vncheckout_array = ";
			echo json_encode(["admin_ajax" => admin_url("admin-ajax.php"), "get_address" => $get_address, "home_url" => home_url(), "formatNoMatches" => __("No value", "ntco-vn-checkout")]);
			echo "            </script>\r\n            <script type='text/javascript' src='";
			echo plugins_url("/assets/js/pos_ntco_tinhthanh.js", __FILE__);
			echo "'></script>\r\n            ";
		}
		public function ntco_woocommerce_formatted_address_replacements($replace, $args) {
			if (isset($replace["{city}"]) && is_numeric($replace["{city}"])) {
				$oldCity = isset($replace["{city}"]) ? $replace["{city}"] : "";
				$replace["{city}"] = $this->get_name_district($oldCity);
			}
			if (isset($replace["{city_upper}"]) && is_numeric($replace["{city_upper}"])) {
				$oldCityUpper = isset($replace["{city_upper}"]) ? $replace["{city_upper}"] : "";
				$replace["{city_upper}"] = strtoupper($this->get_name_district($oldCityUpper));
			}
			if (isset($replace["{address_2}"]) && is_numeric($replace["{address_2}"])) {
				$oldCity = isset($replace["{address_2}"]) ? $replace["{address_2}"] : "";
				$replace["{address_2}"] = $this->get_name_village($oldCity);
			}
			if (isset($replace["{address_2_upper}"]) && is_numeric($replace["{address_2_upper}"])) {
				$oldCityUpper = isset($replace["{address_2_upper}"]) ? $replace["{address_2_upper}"] : "";
				$replace["{address_2_upper}"] = strtoupper($this->get_name_village($oldCityUpper));
			}
			$replace["{phone}"] = isset($args["phone"]) && $args["phone"] ? $args["phone"] : "";
			if (!isset($replace["{gender}"]) && isset($args["gender"])) {
				$replace["{gender}"] = $args["gender"];
			} else {
				$replace["{gender}"] = "";
			}
			if (is_cart() && !is_checkout()) {
				$replace["{address_1}"] = "";
				$replace["{address_1_upper}"] = "";
				$replace["{address_2}"] = "";
				$replace["{address_2_upper}"] = "";
			}
			return $replace;
		}
		public function ntco_gender_field_process() {
			if (!isset($_POST["billing_gender"]) && $this->get_options("enable_gender") && !$_POST["billing_gender"]) {
				wc_add_notice(__("Please choose gender", "ntco-vn-checkout"), "error");
			}
		}
		public function woocommerce_customer_meta_fields($fields) {
			$fields["billing"]["fields"]["billing_country"]["priority"] = 20;
			$billing_fields_old = $fields["billing"]["fields"];
			$billing_fields = $fields["billing"]["fields"];
			foreach ($billing_fields_old as $key => $value) {
				if (!isset($value["priority"])) {
					if ($key == "billing_country") {
						$value["priority"] = "20";
					} else {
						if ($key == "billing_state") {
							$value["priority"] = "30";
							$value["label"] = __("Province/City", "ntco-vn-checkout");
						} else {
							if ($key == "billing_city") {
								$value["priority"] = "40";
								$value["class"] = "js_field-city";
								$value["label"] = __("District", "ntco-vn-checkout");
							} else {
								if ($key == "billing_address_2") {
									$value["priority"] = "50";
									$value["label"] = __("Commune/Ward/Town", "ntco-vn-checkout");
								} else {
									if ($key == "billing_address_1") {
										$value["priority"] = "60";
									} else {
										$value["priority"] = "10";
									}
								}
							}
						}
					}
				}
				$billing_fields[$key] = $value;
			}
			uasort($billing_fields, [$this, "sort_fields_by_order"]);
			$fields["billing"]["fields"] = $billing_fields;
			$shipping_fields_old = $fields["shipping"]["fields"];
			$shipping_fields = $fields["shipping"]["fields"];
			foreach ($shipping_fields_old as $key => $value) {
				if (!isset($value["priority"])) {
					if ($key == "shipping_country") {
						$value["priority"] = "20";
					} else {
						if ($key == "shipping_state") {
							$value["priority"] = "30";
							$value["label"] = __("Province/City", "ntco-vn-checkout");
						} else {
							if ($key == "shipping_city") {
								$value["priority"] = "40";
								$value["label"] = __("District", "ntco-vn-checkout");
							} else {
								if ($key == "shipping_address_2") {
									$value["priority"] = "50";
									$value["label"] = __("Commune/Ward/Town", "ntco-vn-checkout");
								} else {
									if ($key == "shipping_address_1") {
										$value["priority"] = "60";
									} else {
										$value["priority"] = "10";
									}
								}
							}
						}
					}
				}
				$shipping_fields[$key] = $value;
			}
			uasort($shipping_fields, [$this, "sort_fields_by_order"]);
			$fields["shipping"]["fields"] = $shipping_fields;
			return $fields;
		}
		public function woocommerce_general_settings($fields) {
			$fields_new = [];
			$i = 1;
			foreach ($fields as $field) {
				if ($field["id"] == "store_address" && isset($field["type"]) && $field["type"] != "sectionend") {
					$field["priority"] = 1;
				} else {
					if ($field["id"] == "woocommerce_default_country") {
						$field["priority"] = 2;
					} else {
						if ($field["id"] == "woocommerce_store_city") {
							$field["priority"] = 3;
							$field["class"] = "js_field-city";
							$field["title"] = __("District", "ntco-vn-checkout");
						} else {
							if ($field["id"] == "woocommerce_store_address_2") {
								$field["priority"] = 4;
								$field["title"] = __("Commune/Ward/Town", "ntco-vn-checkout");
							} else {
								if ($field["id"] == "woocommerce_store_address") {
									$field["priority"] = 5;
								} else {
									$field["priority"] = 10 + $i;
								}
							}
						}
					}
				}
				$fields_new[] = $field;
				$i++;
			}
			uasort($fields_new, [$this, "sort_fields_by_order"]);
			return $fields_new;
		}
		public function woocommerce_admin_field_selectajax($value) {
			$custom_attributes = [];
			if (!empty($value["custom_attributes"]) && is_array($value["custom_attributes"])) {
				foreach ($value["custom_attributes"] as $attribute => $attribute_value) {
					$custom_attributes[] = esc_attr($attribute) . "=\"" . esc_attr($attribute_value) . "\"";
				}
			}
			$field_description = WC_Admin_Settings::get_field_description($value);
			$description = $field_description["description"];
			$tooltip_html = $field_description["tooltip_html"];
			$option_value = $value["value"];
			echo "            <tr valign=\"top\">\r\n                <th scope=\"row\" class=\"titledesc\">\r\n                    <label for=\"";
			echo esc_attr($value["id"]);
			echo "\">";
			echo esc_html($value["title"]);
			echo " ";
			echo $tooltip_html;
			echo "</label>\r\n                </th>\r\n                <td class=\"forminp forminp-";
			echo esc_attr(sanitize_title($value["type"]));
			echo "\">\r\n                    <select\r\n                        name=\"";
			echo esc_attr($value["id"]);
			echo "multiselect" === $value["type"] ? "[]" : "";
			echo "\"\r\n                        id=\"";
			echo esc_attr($value["id"]);
			echo "\"\r\n                        style=\"";
			echo esc_attr($value["css"]);
			echo "\"\r\n                        class=\"";
			echo esc_attr($value["class"]);
			echo "\"\r\n                        ";
			echo implode(" ", $custom_attributes);
			echo "                        ";
			echo "multiselect" === $value["type"] ? "multiple=\"multiple\"" : "";
			echo "                    >\r\n                        ";
			foreach ($value["options"] as $key => $val) {
				echo "                            <option value=\"";
				echo esc_attr($key);
				echo "\"\r\n                                ";
				if (is_array($option_value)) {
					selected(in_array((string) $key, $option_value, true), true);
				} else {
					selected($option_value, (string) $key);
				}
				echo "                            >";
				echo esc_html($val);
				echo "</option>\r\n                            ";
			}
			echo "                    </select> ";
			echo $description;
			echo "                </td>\r\n            </tr>\r\n            ";
		}
		public function ntco_update_checkout_func($array) {
			delete_transient("shipping-transient-version");
		}
		public function ghtk_update_country_func() {
			if (!wp_verify_nonce($_REQUEST["nonce"], "admin_ghtk_nonce_action")) {
				wp_send_json_error("Lỗi kiểm tra mã bảo mật");
			}
			$customers_query = new WP_User_Query(["number" => 0, "fields" => "ID", "count_total" => true]);
			if ($customers_query->get_results()) {
				foreach ($customers_query->get_results() as $user_ID) {
					$wc_user = new WC_Customer($user_ID);
					if ($wc_user && !is_wp_error($wc_user)) {
						$wc_user->set_billing_country("VN");
						$wc_user->set_shipping_country("VN");
						$wc_user->save();
					}
				}
			}
			wp_send_json_success("Update thành công");
			exit;
		}
		public function get_product_args($orderThis) {
			$products = [];
			if (is_numeric($orderThis)) {
				$orderThis = wc_get_order($orderThis);
			}
			$order_items = $orderThis->get_items();
			if ($order_items && !empty($order_items)) {
				$key = 0;
				foreach ($order_items as $item) {
					$product = $item->get_product();
					if ($product && !is_wp_error($product)) {
						$subtitle = [];
						$meta_data = $item->get_formatted_meta_data("");
						if (is_array($item->get_meta_data())) {
							$variations = [];
							$hidden_order_itemmeta = apply_filters("woocommerce_hidden_order_itemmeta", ["_qty", "_tax_class", "_product_id", "_variation_id", "_line_subtotal", "_line_subtotal_tax", "_line_total", "_line_tax", "method_id", "cost", "_reduced_stock", "_price", "_wc_cog_item_cost", "_wc_cog_item_total_cost", "_wwp_wholesale_priced", "_wwp_wholesale_role", "_ywpar_total_points", "wholesale_customer"]);
							foreach ($meta_data as $meta_id => $meta) {
								if (!in_array($meta->key, $hidden_order_itemmeta, true)) {
									$variations[$meta->display_key] = wp_strip_all_tags($meta->display_value);
								}
							}
							if ($variations && is_array($variations)) {
								foreach ($variations as $k => $v) {
									$subtitle[] = $k . " - " . $v;
								}
							}
						}
						if ($subtitle) {
							$name_prod = sanitize_text_field($item["name"]) . " | " . implode(" | ", $subtitle);
						} else {
							$name_prod = sanitize_text_field($item["name"]);
						}
						$thisW = (float) $product->get_weight();
						$thisPrice = (float) $orderThis->get_item_subtotal($item, false, true);
						$thisQty = (float) $item->get_quantity();
						$products[$key]["id"] = $product->get_id();
						$products[$key]["name"] = $name_prod;
						$products[$key]["width"] = (float) $product->get_width();
						$products[$key]["height"] = (float) $product->get_height();
						$products[$key]["length"] = (float) $product->get_length();
						$products[$key]["weight"] = $this->convert_weight_to_kg($thisW);
						$products[$key]["price"] = $thisPrice;
						$products[$key]["quantity"] = $thisQty;
						$products[$key]["sku"] = $product->get_sku();
						$products[$key]["thumb"] = $product->get_image("woocommerce_thumbnail", ["style" => "width: 50px; height: 50px;"]);
						$key++;
					}
				}
			}
			return apply_filters("ntco_ghtk_create_order_products", $products, $this, $orderThis);
		}
		public function dwas_sort_desc_array($input = [], $keysort = "dk") {
			$sort = [];
			if ($input && is_array($input)) {
				foreach ($input as $k => $v) {
					$sort[$keysort][$k] = $v[$keysort];
				}
				array_multisort($sort[$keysort], SORT_DESC, $input);
			}
			return $input;
		}
		public function dwas_sort_asc_array($input = [], $keysort = "dk") {
			$sort = [];
			if ($input && is_array($input)) {
				foreach ($input as $k => $v) {
					$sort[$keysort][$k] = $v[$keysort];
				}
				array_multisort($sort[$keysort], SORT_ASC, $input);
			}
			return $input;
		}
		public function dwas_format_key_array($input = []) {
			$output = [];
			if ($input && is_array($input)) {
				foreach ($input as $k => $v) {
					$output[] = $v;
				}
			}
			return $output;
		}
		public function dwas_search_bigger_in_array($array, $key, $value) {
			$results = [];
			if (is_array($array)) {
				if (isset($array[$key]) && $array[$key] <= $value) {
					$results[] = $array;
				}
				foreach ($array as $subarray) {
					$results = array_merge($results, $this->dwas_search_bigger_in_array($subarray, $key, $value));
				}
			}
			return $results;
		}
		public function dwas_search_bigger_in_array_weight($array, $key, $value) {
			$results = [];
			if (is_array($array)) {
				if (isset($array[$key]) && $value <= $array[$key]) {
					$results[] = $array;
				}
				foreach ($array as $subarray) {
					$results = array_merge($results, $this->dwas_search_bigger_in_array_weight($subarray, $key, $value));
				}
			}
			return $results;
		}
		public function enable_recaptcha() {
			if ($this->get_options("enable_recaptcha") && $this->get_options("recaptcha_sitekey") && $this->get_options("recaptcha_secretkey")) {
				return true;
			}
			return false;
		}
		public function get_address_byphone_func() {
			$outputs = [];
			if ($this->enable_recaptcha()) {
				$token = isset($_POST["g-recaptcha-response"]) ? wp_unslash($_POST["g-recaptcha-response"]) : "";
				if (version_compare(phpversion(), "8.0.0", ">=")) {
					require_once "lib/recaptcha-master/src/autoload.php";
				} else {
					require_once "lib/recaptcha-1.2.4/src/autoload.php";
				}
				if (!$token) {
					wp_send_json_error("Lỗi bảo mật");
				} else {
					$recaptcha_secretkey = $this->get_options("recaptcha_secretkey");
					$recaptcha = new ReCaptcha\ReCaptcha($recaptcha_secretkey);
					$resp = $recaptcha->setExpectedHostname($_SERVER["SERVER_NAME"])->verify($token, $_SERVER["REMOTE_ADDR"]);
					if (!$resp->isSuccess()) {
						wp_send_json_error("Lỗi xác thực ReCaptcha");
					}
				}
			}
			$phone = isset($_POST["phone"]) ? sanitize_text_field($_POST["phone"]) : "";
			$country_default = "";
			$countries = WC()->countries->get_allowed_countries();
			if (!$this->get_options("alepay_support")) {
				$country_default = "VN";
			} else {
				if (count($countries) === 1) {
					$country_default = current(array_keys($countries));
				}
			}
			if (preg_match("/^0([0-9]{9,10})+\$/D", $phone) && 0 < WC()->cart->get_cart_contents_count()) {
				$args = ["number" => 1, "meta_query" => [["key" => "billing_phone", "value" => $phone, "compare" => "="]]];
				$user_query = new WP_User_Query($args);
				if ($user_query->get_results()) {
					foreach ($user_query->get_results() as $user) {
						$billing_state = get_user_meta($user->id, "billing_state", true);
						$billing_city = get_user_meta($user->id, "billing_city", true);
						$billing_address_2 = get_user_meta($user->id, "billing_address_2", true);
						$outputs["billing"]["billing_first_name"] = get_user_meta($user->id, "billing_first_name", true);
						$outputs["billing"]["billing_last_name"] = get_user_meta($user->id, "billing_last_name", true);
						$outputs["billing"]["billing_company"] = get_user_meta($user->id, "billing_company", true);
						$outputs["billing"]["billing_address_1"] = get_user_meta($user->id, "billing_address_1", true);
						$outputs["billing"]["billing_country"] = $country_default ? $country_default : get_user_meta($user->id, "billing_country", true);
						$outputs["billing"]["billing_state"] = $billing_state;
						$outputs["billing"]["billing_city"] = $billing_city;
						$outputs["billing"]["billing_address_2"] = $billing_address_2;
						$outputs["billing"]["billing_phone"] = get_user_meta($user->id, "billing_phone", true);
						$outputs["billing"]["billing_email"] = get_user_meta($user->id, "billing_email", true);
						$shipping_state = get_user_meta($user->id, "shipping_state", true);
						$shipping_city = get_user_meta($user->id, "shipping_city", true);
						$shipping_address_2 = get_user_meta($user->id, "shipping_address_2", true);
						$outputs["shipping"]["shipping_first_name"] = get_user_meta($user->id, "shipping_first_name", true);
						$outputs["shipping"]["shipping_last_name"] = get_user_meta($user->id, "shipping_last_name", true);
						$outputs["shipping"]["shipping_company"] = get_user_meta($user->id, "shipping_company", true);
						$outputs["shipping"]["shipping_address_1"] = get_user_meta($user->id, "shipping_address_1", true);
						$outputs["shipping"]["shipping_country"] = $country_default ? $country_default : get_user_meta($user->id, "shipping_country", true);
						$outputs["shipping"]["shipping_state"] = $shipping_state;
						$outputs["shipping"]["shipping_city"] = $shipping_city;
						$outputs["shipping"]["shipping_address_2"] = $shipping_address_2;
						$outputs["shipping"]["shipping_phone"] = get_user_meta($user->id, "shipping_phone", true);
					}
					wp_send_json_success(apply_filters("get_address_byphone", $outputs));
				} else {
					$args = ["post_type" => "shop_order", "post_status" => "any", "posts_per_page" => 1, "meta_query" => [["key" => "_billing_phone", "value" => $phone, "compare" => "="]]];
					$orderThis = new WP_Query($args);
					if ($orderThis->have_posts()) {
						$orderID = "";
						while ($orderThis->have_posts()) {
							$orderThis->the_post();
							$orderID = get_the_ID();
						}
						wp_reset_query();
						if ($orderID) {
							$orderData = wc_get_order($orderID);
							if ($orderData && !is_wp_error($orderData)) {
								$billing_state = $orderData->get_billing_state();
								$billing_city = $orderData->get_billing_city();
								$billing_address_2 = $orderData->get_billing_address_2();
								$outputs["billing"]["billing_first_name"] = $orderData->get_billing_first_name();
								$outputs["billing"]["billing_last_name"] = $orderData->get_billing_last_name();
								$outputs["billing"]["billing_company"] = $orderData->get_billing_company();
								$outputs["billing"]["billing_address_1"] = $orderData->get_billing_address_1();
								$outputs["billing"]["billing_country"] = $country_default ? $country_default : $orderData->get_billing_country();
								$outputs["billing"]["billing_state"] = $billing_state;
								$outputs["billing"]["billing_city"] = $billing_city;
								$outputs["billing"]["billing_address_2"] = $billing_address_2;
								$outputs["billing"]["billing_phone"] = $orderData->get_billing_phone();
								$outputs["billing"]["billing_email"] = $orderData->get_billing_email();
								$shipping_state = $orderData->get_shipping_state();
								$shipping_city = $orderData->get_shipping_city();
								$shipping_address_2 = $orderData->get_shipping_address_2();
								$outputs["shipping"]["shipping_first_name"] = $orderData->get_shipping_first_name();
								$outputs["shipping"]["shipping_last_name"] = $orderData->get_shipping_last_name();
								$outputs["shipping"]["shipping_company"] = $orderData->get_shipping_company();
								$outputs["shipping"]["shipping_address_1"] = $orderData->get_shipping_address_1();
								$outputs["shipping"]["shipping_country"] = $country_default ? $country_default : $orderData->get_shipping_country();
								$outputs["shipping"]["shipping_state"] = $shipping_state;
								$outputs["shipping"]["shipping_city"] = $shipping_city;
								$outputs["shipping"]["shipping_address_2"] = $shipping_address_2;
								$outputs["shipping"]["shipping_phone"] = $orderData->get_shipping_phone();
								wp_send_json_success(apply_filters("get_address_byphone", $outputs));
							}
						}
						wp_send_json_error();
					}
				}
			}
			wp_send_json_error();
			exit;
		}
		public function get_address_by_phonenumber() {
			if (!$this->get_options("enable_getaddressfromphone") || is_user_logged_in()) {
				return NULL;
			}
			echo "            <div class=\"get_address_byphone_wrap\">\r\n                <button class=\"get_address_byphone\" type=\"button\"\r\n                        data-mfp-src=\"#get_address_content\">";
			_e("+ Get address by number phone", "ntco-vn-checkout");
			echo "</button>\r\n                <div id=\"get_address_content\" class=\"mfp-hide\">\r\n                    <div class=\"get_address_content_input\">\r\n                        <input type=\"tel\" name=\"sdt_get_address\" id=\"sdt_get_address\" placeholder=\"";
			_e("Type your phone number", "ntco-vn-checkout");
			echo "\"/>\r\n                    </div>\r\n                    ";
			if ($this->enable_recaptcha()) {
				echo "                        <div class=\"g-recaptcha\" data-sitekey=\"";
				echo $this->get_options("recaptcha_sitekey");
				echo "\"></div>\r\n                    ";
			}
			echo "                    <div class=\"get_address_content_mess\"></div>\r\n                    <div class=\"get_address_content_button\">\r\n                        <a href=\"#\" class=\"btn_get_address\">";
			_e("Get Address", "ntco-vn-checkout");
			echo "</a>\r\n                        <a href=\"#\" class=\"btn_cancel\">";
			_e("Cancel", "ntco-vn-checkout");
			echo "</a>\r\n                    </div>\r\n                    <div class=\"get_address_content_footer\"></div>\r\n                </div>\r\n            </div>\r\n            ";
		}
		public function get_cart_quantity_package($package = []) {
			$quantity = 0;
			if (isset($package["contents"]) && !empty($package["contents"])) {
				foreach ($package["contents"] as $cart_item_key => $values) {
					$quantity += $values["quantity"];
				}
			}
			return apply_filters("wc_ntco_cart_quantity_package", $quantity, $package);
		}
		public function woocommerce_package_rates($rates) {
			global $vncheckout_settings;
			$hide_special_method = $vncheckout_settings["hide_special_method"];
			$new_rates = [];
			foreach ($rates as $k => $rate) {
				$methob_id = $rate->get_method_id();
				if (!in_array($methob_id, apply_filters("list_methob_remove_special", ["vtpost_shipping_method", "ghtk_shipping_method"]))) {
					$new_rates[$k] = $rate;
				}
			}
			if ($hide_special_method && $new_rates) {
				return $new_rates;
			}
			return $rates;
		}
		public function has_active($plugin = "ghtk") {
			switch ($plugin) {
				case "ghtk":
					$plugin_slug = "ntco-woo-ghtk/ntco-woo-ghtk.php";
					break;
				case "viettel":
					$plugin_slug = "ntco-vietnam-shipping/ntco-vietnam-shipping.php";
					break;
				case "vnshipping":
					$plugin_slug = "ntco-woo-address-selectbox/ntco-woo-address-selectbox.php";
					break;
				default:
					$plugin_slug = $plugin;
					break;
			}
			if (in_array($plugin_slug, apply_filters("active_plugins", get_option("active_plugins")))) {
				return true;
			}
			return false;
		}

		public function ntco_roundup_shipping_cost($rates, $package) {
			foreach ($rates as $rate) {
				$cost = $rate->cost;
				if (1000 < $cost) {
					$s1 = $cost / 1000;
					$s2 = intval($s1);
					$s3 = $s1 - $s2;
					if (0 <= $s3) {
						$s2 = $s2 + 1;
					}
					$rate->cost = $s2 * 1000;
				}
			}
			return $rates;
		}
		public function phone_hide($phone) {
			$rest = mb_substr($phone, 0, 3);
			$kitu = strlen($phone);
			if ($kitu == 10) {
				$textsao = "****";
			} else {
				if ($kitu == 11) {
					$textsao = "*****";
				} else {
					$textsao = "**";
				}
			}
			$rest1 = mb_substr($phone, -3);
			$text = $rest . $textsao . $rest1;
			return $text;
		}
		public function string_hide($string) {
			$text = mb_substr($string, apply_filters("string_hide_length", 5));
			return "*****" . $text;
		}
		public function woocommerce_calculated_shipping() {
			$address = [];
			$address["country"] = isset($_POST["calc_shipping_country"]) ? wc_clean(wp_unslash($_POST["calc_shipping_country"])) : "";
			$address["state"] = isset($_POST["calc_shipping_state"]) ? wc_clean(wp_unslash($_POST["calc_shipping_state"])) : "";
			$address["postcode"] = isset($_POST["calc_shipping_postcode"]) ? wc_clean(wp_unslash($_POST["calc_shipping_postcode"])) : "";
			$address["city"] = isset($_POST["calc_shipping_city"]) ? wc_clean(wp_unslash($_POST["calc_shipping_city"])) : "";
			$address = apply_filters("woocommerce_cart_calculate_shipping_address", $address);
			if ($address["country"]) {
				WC()->customer->set_billing_location($address["country"], $address["state"], $address["postcode"], $address["city"]);
				WC()->customer->save();
			}
		}
		public function hpos_enabled() {
			return get_option("woocommerce_custom_orders_table_enabled") == "no" ? false : true;
		}
	}
}
if (!function_exists("vn_checkout")) {
	function vn_checkout() {
		return NTCO_VietNam_Checkout::init();
	}
}
vn_checkout();
if (vn_checkout()->get_options("active_orderstyle")) {
	include_once "class-order-style.php";
}
if (!function_exists("license_send_mail_active_failed")) {
	function license_send_mail_active_failed($plugin_name, $setting_url) {
		$to = get_option("admin_email");
		$subject = "Có lỗi khi active license " . esc_html__($plugin_name) . " cho website - " . wp_parse_url(home_url(), PHP_URL_HOST);
		$body = "<p>Bạn hãy vào kiểm tra lại license của plugin \"" . esc_html__($plugin_name) . "\" ngay để tránh ảnh hưởng tới hoạt động của website nhé.</p><p><a href=\"" . admin_url($setting_url) . "\" target=\"_blank\" rel=\"nofollow\">Kiểm tra tại đây</a></p>";
		$body .= "<p>Có thắc mắc hãy liên hệ với tôi qua call/zalo: 0965419096 (Mr Toản)</p>";
		$headers = ["Content-Type: text/html; charset=UTF-8"];
		add_filter("wp_mail_from", "license_new_mail_from");
		function license_new_mail_from($old) {
			if (strpos($old, "wordpress@") !== false) {
				$old = get_option("admin_email");
			}
			return $old;
		}
		add_filter("wp_mail_from_name", "license_new_mail_from_name");
		function license_new_mail_from_name($old) {
			if ($old == "WordPress") {
				$old = get_option("blogname");
			}
			return $old;
		}
		$sendmail = wp_mail($to, $subject, $body, $headers);
		remove_filter("wp_mail_from", "license_new_mail_from");
		remove_filter("wp_mail_from_name", "license_new_mail_from_name");
	}
}
