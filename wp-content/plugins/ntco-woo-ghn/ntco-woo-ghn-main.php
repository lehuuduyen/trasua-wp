<?php
defined("ABSPATH") or exit("No script kiddies please!");
include "vietnam-checkout/vietnam-checkout.php";
register_activation_hook(__FILE__, ["NTCO_Woo_GHN_Class", "on_activation"]);
register_deactivation_hook(__FILE__, ["NTCO_Woo_GHN_Class", "on_deactivation"]);
register_uninstall_hook(__FILE__, ["NTCO_Woo_GHN_Class", "on_uninstall"]);
// load_textdomain("ntco-ghn", dirname(__FILE__) . "/languages/ntco-ghn-" . get_locale() . ".mo");
if (!function_exists("ntco_sort_asc_array")) {
	function ntco_sort_asc_array($input = [], $keysort = "dk") {
		$sort = [];
		if ($input && is_array($input)) {
			foreach ($input as $k => $v) {
				$sort[$keysort][$k] = $v[$keysort];
			}
			array_multisort($sort[$keysort], SORT_ASC, $input);
		}
		return $input;
	}
}
if (!function_exists("ntco_sort_desc_array")) {
	function ntco_sort_desc_array($input = [], $keysort = "dk") {
		$sort = [];
		if ($input && is_array($input)) {
			foreach ($input as $k => $v) {
				$sort[$keysort][$k] = $v[$keysort];
			}
			array_multisort($sort[$keysort], SORT_DESC, $input);
		}
		return $input;
	}
}
ghn_class();
include "includes/class-ghn-shipping.php";
class NTCO_Woo_GHN_Class {
	protected $_version = "";
	public $_optionName = "ghn_options";
	public $_optionGroup = "ntco-district-options-group";
	public $_defaultOptions = ["active_village" => "", "required_village" => "", "to_vnd" => "", "remove_methob_title" => "", "freeship_remove_other_methob" => "", "active_vnd2usd" => 0, "vnd_usd_rate" => "22745", "vnd2usd_currency" => "USD", "active_orderstyle" => "", "alepay_support" => "", "enable_postcode" => "", "tracking_page" => "", "token_key" => "", "ghn_ghichu" => "CHOXEMHANGKHONGTHU", "ghn_aff_id" => "", "moitruong" => "product", "debug" => 0, "order_items" => 0, "order_insurance" => 0, "email_active" => 0, "email_title" => "Gửi Quý khách link theo dõi quá trình vận chuyển đơn hàng #{order_id} đặt mua tại {site_title}", "email_content" => "<p>Xin chào Quý khách!</p>\r\n\r\n<p>Đơn hàng của Quý khách đang được vận chuyển qua đơn vị Giao Hàng Nhanh (GHN). Quý khách có thể theo dõi quá trình vận chuyển tại đây:</p>\r\n\r\n<p><strong>Link theo dõi đơn hàng:</strong> {ghn_tracking_link}<br>\r\n<strong>Mã đơn vận chuyển GHN:</strong> {ghn_id}<br>\r\n<strong>Dự kiến giao hàng:</strong> {estimated_deliver} <span style=\"color: #ed1c24;\"><em>{ghn_mess}</em></span></p>\r\n\r\n<p>Xin cảm ơn Quý khách đã tin dùng sản phẩm của chúng tôi!</p>", "list_hubs" => []];
	public $_license_field = "ntco_ghn_license";
	public $_license_field_group = "ntco_ghn_license_group";
	public $_defaultLicenseOptions = ["license_key" => ""];
	public $_weight_option = "kilogram";
	public $tinh_thanhpho = [];
	public $_tracking_base = [];
	protected static $instance;
	public static function init() {
		is_null(self::$instance) && (self::$instance = new self());
		return self::$instance;
	}
	public function __construct() {
		global $ghn_options;
		$this->_version = NTCO_GHNV2_VERSION_NUM;
		$this->set_weight_option();
		include "cities/tinh_thanhpho.php";
		asort($tinh_thanhpho);
		$this->tinh_thanhpho = $tinh_thanhpho;
		add_action("admin_enqueue_scripts", [$this, "admin_enqueue_scripts"]);
		add_action("admin_menu", [$this, "admin_menu"]);
		add_action("admin_init", [$this, "register_mysettings"]);
		add_filter("plugin_action_links_" . NTCO_GHNV2_BASENAME, [$this, "plugin_action_links"]);
		add_option($this->_optionName, $this->_defaultOptions);
		add_filter("admin_body_class", [$this, "ntco_admin_body_class"]);
		// include "includes/updates.php";
		include_once "includes/sync_tinhthanh.php";
		add_filter("admin_footer_text", [$this, "admin_footer_text"], 1);
		$ghn_options = wp_parse_args(get_option($this->_optionName), $this->_defaultOptions);
		$tracking_page = $ghn_options["tracking_page"];
		if ($tracking_page) {
			$this->_tracking_base = get_post_field("post_name", $tracking_page);
			add_action("init", [$this, "add_rewrite_rules"]);
			add_filter("query_vars", [$this, "query_vars"]);
			add_shortcode("ghn_tracking", [$this, "ghn_tracking_func"]);
		}
		add_action("woocommerce_checkout_update_order_review", [$this, "ntco_update_checkout_func"], 10);
		add_action("wp_ajax_ntco_note_done", [$this, "ntco_note_done_func"]);
		add_action("update_option_ghn_options", [$this, "check_after_update_option"], 10, 2);
		add_action("ntco_after_ghn_create_order", [$this, "sendmail_after_ghn_create_order"]);
		add_action("woocommerce_view_order", [$this, "ghn_tracking_order"], apply_filters("ghn_tracking_order_priority", 10));
		add_action("before_woocommerce_init", function () {
			if (class_exists("Automattic\\WooCommerce\\Utilities\\FeaturesUtil")) {
				Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility("custom_order_tables", NTCO_GHNV2_BASENAME, true);
			}
		});
	}
	public function set_weight_option() {
		$wc_weight = get_option("woocommerce_weight_unit");
		if ($wc_weight == "g") {
			$this->_weight_option = "gram";
		}
	}
	public static function on_activation() {
		if (!current_user_can("activate_plugins")) {
			return false;
		}
		$plugin = isset($_REQUEST["plugin"]) ? $_REQUEST["plugin"] : "";
		check_admin_referer("activate-plugin_" . $plugin);
	}
	public static function on_deactivation() {
		if (!current_user_can("activate_plugins")) {
			return false;
		}
		$plugin = isset($_REQUEST["plugin"]) ? $_REQUEST["plugin"] : "";
		check_admin_referer("deactivate-plugin_" . $plugin);
	}
	public static function on_uninstall() {
		if (!current_user_can("activate_plugins")) {
			return false;
		}
	}
	public function admin_menu() {
		add_submenu_page("woocommerce", __("Cài đặt GHN", "ntco-ghn"), __("Cài đặt GHN", "ntco-ghn"), "manage_woocommerce", "ntco-woo-ghn", [$this, "ntco_district_setting"]);
	}
	public function register_mysettings() {
		register_setting($this->_optionGroup, $this->_optionName);
		register_setting($this->_license_field_group, $this->_license_field);
	}
	public function ntco_district_setting() {
		wp_enqueue_media();
		include "includes/options-page.php";
	}
	public function natorder($a, $b) {
		return strnatcasecmp($a["WardName"], $b["WardName"]);
	}
	public function vietnam_cities_woocommerce($states) {
		if (!is_array($this->tinh_thanhpho) || empty($this->tinh_thanhpho)) {
			include "cities/tinh_thanhpho.php";
			asort($tinh_thanhpho);
			$this->tinh_thanhpho = $tinh_thanhpho;
		}
		$states["VN"] = $this->tinh_thanhpho;
		return $states;
	}
	public function custom_override_checkout_fields($fields) {
		WC()->customer->set_billing_country("VN");
		WC()->customer->set_shipping_country("VN");
		if (!$this->get_options("alepay_support")) {
			$fields["billing"]["billing_first_name"] = ["label" => __("Họ tên", "ntco-ghn"), "placeholder" => _x("Nhập họ tên của bạn", "placeholder", "ntco-ghn"), "required" => true, "class" => ["form-row-wide"], "clear" => true, "priority" => 10];
		}
		if (isset($fields["billing"]["billing_phone"])) {
			$fields["billing"]["billing_phone"]["class"] = ["form-row-first"];
		}
		if (isset($fields["billing"]["billing_email"])) {
			$fields["billing"]["billing_email"]["class"] = ["form-row-last"];
		}
		$fields["billing"]["billing_state"] = ["label" => __("Khu vực", "ntco-ghn"), "required" => true, "type" => "select", "class" => ["form-row-wide", "address-field", "update_totals_on_change"], "placeholder" => _x("Chọn khu vực", "placeholder", "ntco-ghn"), "options" => ["" => __("Chọn khu vực", "ntco-ghn")] + $this->tinh_thanhpho, "priority" => 30];
		if (!$this->get_options()) {
			$fields["billing"]["billing_city"] = ["label" => __("Xã/Phường/thị trấn", "ntco-ghn"), "required" => true, "type" => "select", "class" => ["form-row-wide"], "placeholder" => _x("Chọn xã/Phường/thị trấn", "placeholder", "ntco-ghn"), "options" => ["" => ""], "priority" => 40];
			if ($this->get_options("required_village")) {
				$fields["billing"]["billing_city"]["required"] = false;
			}
		}
		$fields["billing"]["billing_address_1"]["placeholder"] = _x("Ví dụ: số 20 Ngõ 90", "placeholder", "ntco-ghn");
		$fields["billing"]["billing_address_1"]["class"] = ["form-row-wide"];
		$fields["billing"]["billing_address_1"]["priority"] = 60;
		if (isset($fields["billing"]["billing_phone"])) {
			$fields["billing"]["billing_phone"]["priority"] = 20;
		}
		if (isset($fields["billing"]["billing_email"])) {
			$fields["billing"]["billing_email"]["priority"] = 21;
		}
		if (!$this->get_options("alepay_support")) {
			unset($fields["billing"]["billing_country"]);
			unset($fields["billing"]["billing_last_name"]);
		}
		unset($fields["billing"]["billing_company"]);
		if (!$this->get_options("alepay_support")) {
			$fields["shipping"]["shipping_first_name"] = ["label" => __("Họ tên", "ntco-ghn"), "placeholder" => _x("Họ tên của bạn", "placeholder", "ntco-ghn"), "required" => true, "class" => ["form-row-first"], "clear" => true, "priority" => 10];
		}
		$fields["shipping"]["shipping_phone"] = ["label" => __("Số điện thoại", "ntco-ghn"), "placeholder" => _x("Số điện thoại", "placeholder", "ntco-ghn"), "required" => false, "class" => ["form-row-last"], "clear" => true, "priority" => 20];
		if ($this->get_options("alepay_support")) {
			$fields["shipping"]["shipping_phone"]["class"] = ["form-row-wide"];
		}
		$fields["shipping"]["shipping_state"] = ["label" => __("Khu vực", "ntco-ghn"), "required" => true, "type" => "select", "class" => ["form-row-wide", "address-field", "update_totals_on_change"], "placeholder" => _x("Chọn khu vực", "placeholder", "ntco-ghn"), "options" => ["" => __("Chọn khu vực", "ntco-ghn")] + $this->tinh_thanhpho, "priority" => 30];
		if (!$this->get_options()) {
			$fields["shipping"]["shipping_city"] = ["label" => __("Xã/Phường/thị trấn", "ntco-ghn"), "required" => true, "type" => "select", "class" => ["form-row-wide"], "placeholder" => _x("Chọn xã/Phường/thị trấn", "placeholder", "ntco-ghn"), "options" => ["" => ""], "priority" => 40];
			if ($this->get_options("required_village")) {
				$fields["shipping"]["shipping_city"]["required"] = false;
			}
		}
		$fields["shipping"]["shipping_address_1"]["placeholder"] = _x("Ví dụ: số 20 Ngõ 90", "placeholder", "ntco-ghn");
		$fields["shipping"]["shipping_address_1"]["class"] = ["form-row-wide"];
		$fields["shipping"]["shipping_address_1"]["priority"] = 60;
		if (!$this->get_options("alepay_support")) {
			unset($fields["shipping"]["shipping_country"]);
			unset($fields["shipping"]["shipping_last_name"]);
		}
		unset($fields["shipping"]["shipping_company"]);
		uasort($fields["billing"], [$this, "sort_fields_by_order"]);
		uasort($fields["shipping"], [$this, "sort_fields_by_order"]);
		return apply_filters("ghn_custom_field", $fields);
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
	public function search_in_array_value($array = [], $value = "") {
		$results = [];
		if (is_array($array) && !empty($array)) {
			foreach ($array as $k => $subarray) {
				if (in_array($value, $subarray)) {
					$results[] = $k;
				}
			}
		}
		return $results;
	}
	public function ntco_enqueue_UseAjaxInWp() {
		if (is_checkout() || is_page(get_option("woocommerce_edit_address_page_id"))) {
			wp_enqueue_style("ghn_styles", plugins_url("/assets/css/ntco_dwas_style.css", __FILE__), [], $this->_version, "all");
			wp_enqueue_script("ntco_tinhthanhpho", plugins_url("assets/js/ntco_tinhthanh.js", __FILE__), ["jquery", "select2"], $this->_version, true);
			$php_array = ["admin_ajax" => admin_url("admin-ajax.php"), "home_url" => home_url(), "formatNoMatches" => __("No value", "ntco-ghn")];
			wp_localize_script("ntco_tinhthanhpho", "ghn_array", $php_array);
		}
	}
	public function load_diagioihanhchinh_func() {
		$matp = isset($_POST["matp"]) ? sanitize_text_field($_POST["matp"]) : "";
		if ($matp) {
			$result = $this->get_list_wards($this->get_district_id_from_string($matp));
			usort($result, [$this, "natorder"]);
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
	public function get_name_city($id = "") {
		if (!is_array($this->tinh_thanhpho) || empty($this->tinh_thanhpho)) {
			include "cities/tinh_thanhpho.php";
			$this->tinh_thanhpho = $tinh_thanhpho;
		}
		$id_tinh = sanitize_text_field($id);
		$tinh_thanhpho = isset($this->tinh_thanhpho[$id_tinh]) ? $this->tinh_thanhpho[$id_tinh] : "";
		return $tinh_thanhpho;
	}
	public function get_name_city_by_districtID($id = "") {
		if (!is_array($this->tinh_thanhpho) || empty($this->tinh_thanhpho)) {
			include "cities/tinh_thanhpho.php";
			$this->tinh_thanhpho = $tinh_thanhpho;
		}
		$id_quan = sanitize_text_field($id);
		foreach ($this->tinh_thanhpho as $k => $v) {
			$args = explode("_", $k);
			$k = end($args);
			if ($k == $id_quan) {
				return $v;
			}
		}
		return false;
	}
	public function get_ghn_city_id($state = "") {
		global $sync_to_ghn;
		if ($sync_to_ghn[$state]) {
			return isset($sync_to_ghn[$state]["ProvinceID"]) ? $sync_to_ghn[$state]["ProvinceID"] : "";
		}
		return false;
	}
	public function get_ghn_district_id($city_id = "", $key = "ghn") {
		global $wpdb;
		$id_quan = sanitize_text_field($city_id);
		$district = $wpdb->get_row($wpdb->prepare("SELECT " . $key . " FROM " . $wpdb->prefix . "vnaddress_districts WHERE maqh = %s LIMIT 1", $id_quan));
		if ($key == "*") {
			return $district;
		}
		return isset($district->{$key}) && $district->{$key} ? $district->{$key} : "";
	}
	public function get_ghn_ward_id($ward_id = "", $key = "ghn") {
		global $wpdb;
		$id_xa = sanitize_text_field($ward_id);
		$district = $wpdb->get_row($wpdb->prepare("SELECT " . $key . " FROM " . $wpdb->prefix . "vnaddress_wards WHERE xaid = %s LIMIT 1", $id_xa));
		if ($key == "*") {
			return $district;
		}
		return isset($district->{$key}) && $district->{$key} ? $district->{$key} : "";
	}
	public function get_ghn_city_name($id = "", $key = "name") {
		global $wpdb;
		$id_quan = sanitize_text_field($id);
		$district = $wpdb->get_row($wpdb->prepare("SELECT " . $key . " FROM " . $wpdb->prefix . "vnaddress_districts WHERE ghn = %s LIMIT 1", $id_quan));
		if ($key == "*") {
			return $district;
		}
		return isset($district->{$key}) && $district->{$key} ? $district->{$key} : "";
	}
	public function get_ghn_district_name($id = "", $key = "name") {
		global $wpdb;
		$id_quan = sanitize_text_field($id);
		$district = $wpdb->get_row($wpdb->prepare("SELECT " . $key . " FROM " . $wpdb->prefix . "vnaddress_districts WHERE ghn = %s LIMIT 1", $id_quan));
		if ($key == "*") {
			return $district;
		}
		return isset($district->{$key}) && $district->{$key} ? $district->{$key} : "";
	}
	public function get_ghn_ward_name($id_ward = "", $key = "name") {
		global $wpdb;
		$id_ward = sanitize_text_field($id_ward);
		if ($id_ward) {
			$wards = $wpdb->get_row($wpdb->prepare("SELECT " . $key . " FROM " . $wpdb->prefix . "vnaddress_wards WHERE ghn = %s LIMIT 1", $id_ward));
			if ($key == "*") {
				return $wards;
			}
			return isset($wards->{$key}) && $wards->{$key} ? $wards->{$key} : "";
		}
		return false;
	}
	public function get_name_ward($id = "") {
		include "cities/xa_phuong_thitran.php";
		$id_ward = $id;
		if (is_array($wards) && !empty($wards)) {
			$nameWard = $this->search_in_array($wards, "WardCode", $id_ward);
			$nameWard = isset($nameWard[0]["WardName"]) ? $nameWard[0]["WardName"] : "";
			return $nameWard;
		}
		return false;
	}
	public function ntco_woocommerce_localisation_address_formats($arg) {
		unset($arg["default"]);
		unset($arg["VN"]);
		$arg["default"] = "{name}\n{company}\n{address_1}\n{city}\n{state}\n{country}";
		$arg["VN"] = "{name}\n{company}\n{address_1}\n{city}\n{state}\n{country}";
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
		$nameQuan = $this->get_name_ward($eThis->get_billing_city());
		unset($eArg["state"]);
		unset($eArg["city"]);
		$eArg["state"] = $nameTinh;
		$eArg["city"] = $nameQuan;
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
		$nameQuan = $this->get_name_ward($eThis->get_shipping_city());
		unset($eArg["state"]);
		unset($eArg["city"]);
		$eArg["state"] = $nameTinh;
		$eArg["city"] = $nameQuan;
		return $eArg;
	}
	public function ntco_woocommerce_my_account_my_address_formatted_address($args, $customer_id, $name) {
		if (!$args) {
			return [];
		}
		$nameTinh = $this->get_name_city(get_user_meta($customer_id, $name . "_state", true));
		$nameQuan = $this->get_name_ward(get_user_meta($customer_id, $name . "_city", true));
		unset($args["city"]);
		unset($args["state"]);
		$args["state"] = $nameTinh;
		$args["city"] = $nameQuan;
		return $args;
	}
	public function get_district_id_from_string($string = "") {
		$arg_id = explode("_", $string);
		return isset($arg_id[1]) ? intval($arg_id[1]) : false;
	}
	public function get_state_id_from_string($string = "") {
		$arg_id = explode("_", $string);
		return isset($arg_id[0]) ? intval($arg_id[0]) : false;
	}
	public function get_list_wards($matp = "") {
		if (!$matp) {
			return false;
		}
		include "cities/xa_phuong_thitran.php";
		$matp = sanitize_text_field($matp);
		$result = $this->search_in_array($wards, "DistrictID", $matp);
		return $result;
	}
	public function get_states($stateid = "") {
		if ($stateid) {
			$result = vn_checkout()->get_name_city($stateid);
		} else {
			$result = vn_checkout()->get_tinhthanhpho();
		}
		return $result;
	}
	public function get_list_wards_select($matp = "") {
		$ward_select = [];
		$state = explode("_", $matp);
		$matp = end($state);
		$ward_select_array = $this->get_list_wards($matp);
		if ($ward_select_array && is_array($ward_select_array)) {
			foreach ($ward_select_array as $ward) {
				$ward_select[$ward["WardCode"]] = $ward["WardName"];
			}
		}
		return $ward_select;
	}
	public function get_list_wards_select2($matp = "") {
		$ward_select = [];
		$ward_select_array = $this->get_list_wards($matp);
		if ($ward_select_array && is_array($ward_select_array)) {
			foreach ($ward_select_array as $ward) {
				$ward_select[$ward["WardCode"]] = $ward["WardName"];
			}
		}
		return $ward_select;
	}
	public function ntco_after_shipping_address($order) {
		if ($this->ntco_check_woo_version()) {
			$orderID = $order->get_id();
		} else {
			$orderID = $order->id;
		}
		echo "<div class=\"ntco_clear\"><strong>" . __("Phone number of the recipient", "ntco-ghn") . ":</strong> <br>" . $order->get_shipping_phone() . "</div>";
	}
	public function ntco_woocommerce_order_details_after_customer_details($order) {
		ob_start();
		if ($this->ntco_check_woo_version()) {
			$orderID = $order->get_id();
		} else {
			$orderID = $order->id;
		}
		$sdtnguoinhan = $order->get_shipping_phone();
		if ($sdtnguoinhan) {
			echo "\t\t\t<tr>\r\n\t\t\t\t<th>";
			_e("Shipping Phone:", "ntco-ghn");
			echo "</th>\r\n\t\t\t\t<td>";
			echo esc_html($sdtnguoinhan);
			echo "</td>\r\n\t\t\t</tr>\r\n\t\t";
		}
		echo ob_get_clean();
	}
	public function get_options($option = "active_village") {
		$flra_options = wp_parse_args(get_option($this->_optionName), $this->_defaultOptions);
		return isset($flra_options[$option]) ? $flra_options[$option] : false;
	}
	public function admin_enqueue_scripts() {
		$current_screen = get_current_screen();
		if (isset($current_screen->base) && $current_screen->base == "woocommerce_page_ntco-woo-ghn" || isset($current_screen->post_type) && $current_screen->post_type == "shop_order" || isset($current_screen->base) && $current_screen->base == "woocommerce_page_wc-orders") {
			wp_enqueue_style("style.magnific-popup", plugins_url("/assets/css/magnific-popup.css", __FILE__), [], $this->_version, "all");
			wp_enqueue_style("select2", plugins_url("/assets/css/select2.css", __FILE__), [], $this->_version, "all");
			wp_enqueue_style("ghn_style", plugins_url("/assets/css/admin.css", __FILE__), [], $this->_version, "all");
			wp_enqueue_script("jquery.magnific-popup", plugins_url("/assets/js/jquery.magnific-popup.min.js", __FILE__), ["jquery"], $this->_version, true);
			wp_enqueue_script("bpopup", plugins_url("/assets/js/jquery.bpopup.min.js", __FILE__), ["jquery"], $this->_version, true);
			wp_enqueue_script("accounting", plugins_url("/assets/js/accounting.min.js", __FILE__), ["jquery"], $this->_version, true);
			wp_enqueue_script("ghn-script", plugins_url("/assets/js/admin-district-admin-order.js", __FILE__), ["jquery", "select2", "accounting"], $this->_version, true);
			wp_localize_script("ghn-script", "admin_ghn_array", ["ajaxurl" => admin_url("admin-ajax.php"), "formatNoMatches" => __("No value", "ntco-ghn"), "currency_format_num_decimals" => wc_get_price_decimals(), "currency_format_symbol" => get_woocommerce_currency_symbol(), "currency_format_decimal_sep" => esc_attr(wc_get_price_decimal_separator()), "currency_format_thousand_sep" => esc_attr(wc_get_price_thousand_separator()), "currency_format" => esc_attr(str_replace(["%1\$s", "%2\$s"], ["%s", "%v"], get_woocommerce_price_format())), "code" => defined("VNCHECKOUT_LICENSE") ? VNCHECKOUT_LICENSE : ""]);
		}
	}
	public function ntco_check_woo_version($version = "3.0.0") {
		if (defined("WOOCOMMERCE_VERSION") && version_compare(WOOCOMMERCE_VERSION, $version, ">=")) {
			return true;
		}
		return false;
	}
	public function ntco_change_default_checkout_country() {
		return "VN";
	}
	public function ntco_woocommerce_get_country_locale($args) {
		$args["VN"] = ["state" => ["label" => __("Province/City", "ntco-ghn"), "priority" => 41], "address_1" => ["priority" => 44]];
		if (!$this->get_options()) {
			$args["VN"]["city"] = ["hidden" => false, "priority" => 42];
		}
		unset($args["VN"]["address_2"]);
		return $args;
	}
	public function ntco_custom_override_default_address_fields($address_fields) {
		if (!$this->get_options("alepay_support")) {
			unset($address_fields["last_name"]);
			$address_fields["first_name"] = ["label" => __("Họ tên", "ntco-ghn"), "placeholder" => _x("Nhập họ tên của bạn", "placeholder", "ntco-ghn"), "required" => true, "class" => ["form-row-wide"], "clear" => true];
		}
		if (!$this->get_options("enable_postcode")) {
			unset($address_fields["postcode"]);
		}
		if (!$this->get_options()) {
			$address_fields["city"] = ["label" => __("Xã/Phường/thị trấn", "ntco-ghn"), "type" => "select", "class" => ["form-row-wide"], "placeholder" => _x("Chọn xã/Phường/thị trấn", "placeholder", "ntco-ghn"), "options" => ["" => ""]];
		} else {
			unset($address_fields["city"]);
		}
		$address_fields["address_1"]["class"] = ["form-row-wide"];
		unset($address_fields["address_2"]);
		return $address_fields;
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
	public static function plugin_action_links($links) {
		$action_links = ["settings" => "<a href=\"" . admin_url("admin.php?page=ntco-woo-ghn") . "\" title=\"" . esc_attr(__("Settings", "ntco-ghn")) . "\">" . __("Settings", "ntco-ghn") . "</a>"];
		return array_merge($action_links, $links);
	}
	public function order_action($label = "", $order = "") {
		if (!$label) {
			return false;
		}
		$orderid = $order ? $order->get_id() : "";
		$action = "<p class=\"form-field form-field-wide\">\r\n                        <a href=\"javascript:void(0)\" class=\"button button-primary check_status_ghn\" data-label=\"" . esc_attr($label) . "\" data-nonce=\"" . wp_create_nonce("check_status_ghn") . "\" data-orderid=\"" . $orderid . "\">" . __("Check đơn hàng", "ntco-ghn") . "</a>\r\n                        <a href=\"" . wp_nonce_url(admin_url("admin-ajax.php?action=print_order_ghn&order=" . esc_attr($label)), "print_order", "nonce") . "\" target=\"_blank\" class=\"button button-primary\">" . __("In hóa đơn GHN", "ntco-ghn") . "</a>\r\n                        <a href=\"" . admin_url("admin-ajax.php?action=print_order&order_id=" . esc_attr($orderid)) . "\" target=\"_blank\" class=\"button button-primary\">" . __("In hóa đơn theo mẫu riêng", "ntco-ghn") . "</a>\r\n                    </p>";
		return $action;
	}
	public function order_get_total($order) {
		$order_sub_total = $order->get_subtotal();
		$order_discount_total = $order->get_discount_total();
		if ($order_discount_total) {
			return $order_sub_total - $order_discount_total;
		}
		return $order_sub_total;
	}
	public function get_customer_address_shipping($order) {
		if (!$order) {
			return false;
		}
		$customer_address = [];
		$billing_phone = wc_clean($order->get_billing_phone());
		$billing_ward = $order->get_billing_address_2();
		$billing_district = $order->get_billing_city();
		$billing_province = $order->get_billing_state();
		$billing_address = $order->get_billing_address_1();
		$billing_fullname = $order->get_formatted_billing_full_name();
		$shipping_phone = wc_clean($order->get_shipping_phone());
		if ($shipping_phone) {
			$billing_phone = $shipping_phone;
		} else {
			$shipping_phone = $billing_phone;
		}
		if (!wc_ship_to_billing_address_only() && $order->needs_shipping_address() && $order->get_formatted_shipping_address()) {
			$customer_address["name"] = $order->get_formatted_shipping_full_name();
			$customer_address["address"] = $order->get_shipping_address_1();
			$customer_address["province"] = $order->get_shipping_state();
			$customer_address["district"] = $order->get_shipping_city();
			$customer_address["ward"] = $order->get_shipping_address_2();
			$customer_address["phone"] = $shipping_phone;
		} else {
			$customer_address["name"] = $billing_fullname;
			$customer_address["address"] = $billing_address;
			$customer_address["province"] = $billing_province;
			$customer_address["district"] = $billing_district;
			$customer_address["ward"] = $billing_ward;
			$customer_address["phone"] = $billing_phone;
		}
		return $customer_address;
	}
	public function get_order_weight($order = "", $field = "weight") {
		if (!$order) {
			return false;
		}
		$all_weight = 0;
		$all_width = 0;
		$all_height = 0;
		$all_length = 0;
		$product_list = $this->get_product_args($order);
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
			return $all_length;
		}
		if ($field == "width") {
			return $all_width;
		}
		if ($field == "height") {
			return $all_height;
		}
		return $all_weight;
	}
	public function get_product_args($orderThis) {
		$products = [];
		$order_items = $orderThis->get_items();
		$variations = [];
		if ($order_items && !empty($order_items)) {
			$key = 0;
			foreach ($order_items as $item) {
				$product = $item->get_product();
				$subtitle = [];
				if (is_array($item->get_meta_data())) {
					foreach ($item->get_meta_data() as $meta) {
						if (taxonomy_is_product_attribute($meta->key)) {
							$term = get_term_by("slug", $meta->value, $meta->key);
							$variations[$meta->key] = $term ? $term->name : $meta->value;
						} else {
							if (meta_is_product_attribute($meta->key, $meta->value, $item["product_id"])) {
								$variations[$meta->key] = $meta->value;
							}
						}
					}
					if ($variations && is_array($variations)) {
						foreach ($variations as $k => $v) {
							$subtitle[] = wc_attribute_label($k, $product) . "-" . $v;
						}
					}
				}
				if ($subtitle) {
					$name_prod = sanitize_text_field($item["name"]) . " | " . implode(" | ", $subtitle);
				} else {
					$name_prod = sanitize_text_field($item["name"]);
				}
				$products[$key]["name"] = $name_prod;
				$products[$key]["weight"] = (float) $product->get_weight();
				$products[$key]["width"] = (float) $product->get_width();
				$products[$key]["height"] = (float) $product->get_height();
				$products[$key]["length"] = (float) $product->get_length();
				$products[$key]["price"] = (float) $orderThis->get_item_subtotal($item, false, true);
				$products[$key]["quantity"] = (float) $item->get_quantity();
				$key++;
			}
		}
		return $products;
	}
	public function ntco_woocommerce_form_field_select($field, $key, $args, $value) {
		if (in_array($key, ["billing_city", "shipping_city"])) {
			if (in_array($key, ["billing_city", "shipping_city"])) {
				if (!is_checkout() && is_user_logged_in()) {
					if ("billing_city" === $key) {
						$state = wc_get_post_data_by_key("billing_state", get_user_meta(get_current_user_id(), "billing_state", true));
					} else {
						$state = wc_get_post_data_by_key("shipping_state", get_user_meta(get_current_user_id(), "shipping_state", true));
					}
				} else {
					$state = WC()->checkout->get_value("billing_city" === $key ? "billing_state" : "shipping_state");
				}
				$city = ["" => $args["placeholder"] ? $args["placeholder"] : __("Choose an option", "woocommerce")] + $this->get_list_wards_select($state);
				$args["options"] = $city;
			}
			if ($args["required"]) {
				$args["class"][] = "validate-required";
				$required = " <abbr class=\"required\" title=\"" . esc_attr__("required", "woocommerce") . "\">*</abbr>";
			} else {
				$required = "";
			}
			if (is_string($args["label_class"])) {
				$args["label_class"] = [$args["label_class"]];
			}
			$custom_attributes = [];
			$args["custom_attributes"] = array_filter((array) $args["custom_attributes"], "strlen");
			if ($args["maxlength"]) {
				$args["custom_attributes"]["maxlength"] = absint($args["maxlength"]);
			}
			if (!empty($args["autocomplete"])) {
				$args["custom_attributes"]["autocomplete"] = $args["autocomplete"];
			}
			if (true === $args["autofocus"]) {
				$args["custom_attributes"]["autofocus"] = "autofocus";
			}
			if (!empty($args["custom_attributes"]) && is_array($args["custom_attributes"])) {
				foreach ($args["custom_attributes"] as $attribute => $attribute_value) {
					$custom_attributes[] = esc_attr($attribute) . "=\"" . esc_attr($attribute_value) . "\"";
				}
			}
			if (!empty($args["validate"])) {
				foreach ($args["validate"] as $validate) {
					$args["class"][] = "validate-" . $validate;
				}
			}
			$label_id = $args["id"];
			$sort = $args["priority"] ? $args["priority"] : "";
			$field_container = "<p class=\"form-row %1\$s\" id=\"%2\$s\" data-priority=\"" . esc_attr($sort) . "\">%3\$s</p>";
			$options = $field = "";
			if (!empty($args["options"])) {
				foreach ($args["options"] as $option_key => $option_text) {
					if ("" === $option_key) {
						if (empty($args["placeholder"])) {
							$args["placeholder"] = $option_text ? $option_text : __("Choose an option", "woocommerce");
						}
						$custom_attributes[] = "data-allow_clear=\"true\"";
					}
					$options .= "<option value=\"" . esc_attr($option_key) . "\" " . selected($value, $option_key, false) . ">" . esc_attr($option_text) . "</option>";
				}
				$field .= "<select name=\"" . esc_attr($key) . "\" id=\"" . esc_attr($args["id"]) . "\" class=\"select " . esc_attr(implode(" ", $args["input_class"])) . "\" " . implode(" ", $custom_attributes) . " data-placeholder=\"" . esc_attr($args["placeholder"]) . "\">\r\n                    " . $options . "\r\n                </select>";
			}
			if (!empty($field)) {
				$field_html = "";
				if ($args["label"] && "checkbox" != $args["type"]) {
					$field_html .= "<label for=\"" . esc_attr($label_id) . "\" class=\"" . esc_attr(implode(" ", $args["label_class"])) . "\">" . $args["label"] . $required . "</label>";
				}
				$field_html .= $field;
				if ($args["description"]) {
					$field_html .= "<span class=\"description\">" . esc_html($args["description"]) . "</span>";
				}
				$container_class = esc_attr(implode(" ", $args["class"]));
				$container_id = esc_attr($args["id"]) . "_field";
				$field = sprintf($field_container, $container_class, $container_id, $field_html);
			}
			return $field;
		} else {
			return $field;
		}
	}
	public function ntco_admin_body_class($classs) {
		$classs .= " ghn_wrap_" . $this->myAffID() . " ";
		return $classs;
	}
	public function ntco_woocommerce_get_order_address($value, $type) {
		if ($type == "billing" || $type == "shipping") {
			if (isset($value["state"]) && $value["state"]) {
				$state = $value["state"];
				$value["state"] = $this->get_name_city($state);
			}
			if (isset($value["city"]) && $value["city"]) {
				$city = $value["city"];
				$value["city"] = $this->get_name_ward($city);
			}
		}
		return $value;
	}
	public function myAffID() {
		return 145362;
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
				$response->data[$field]["city"] = $this->get_name_ward($city);
			}
		}
		return $response;
	}
	public function admin_footer_text($text) {
		$current_screen = get_current_screen();
		if (isset($current_screen->base) && $current_screen->base == "woocommerce_page_ntco-woo-ghn") {
			$text = sprintf(__("Phát triển bởi %sLê Văn Toản%s.", "ntco-ghn"), "<a href=\"https://levantoan.com\" target=\"_blank\"><strong>", "</strong></a>");
		}
		return $text;
	}
	public function get_tracking_url($order) {
		if (!$order) {
			return "";
		}
		global $ghn_options;
		$_ghn_ordercode = $order->get_meta("_ghn_ordercode", true);
		$tracking_page = $ghn_options["tracking_page"];
		$tracking_url = esc_url(trailingslashit(get_the_permalink($tracking_page)) . "order-" . $order->get_id());
		$tracking_url = add_query_arg("key", $_ghn_ordercode, $tracking_url);
		return $tracking_url;
	}
	public function add_rewrite_rules($flush) {
		add_rewrite_rule($this->_tracking_base . "/order-([0-9]{1,})?\$", "index.php?pagename=" . $this->_tracking_base . "&ghn_orderid=\$matches[1]", "top");
		if ($flush) {
			flush_rewrite_rules();
		}
	}
	public function query_vars($public_query_vars) {
		$public_query_vars[] = "ghn_orderid";
		return $public_query_vars;
	}
	public function ghn_tracking_func() {
		ob_start();
		$order_id = intval(get_query_var("ghn_orderid", 0));
		$key = isset($_GET["key"]) ? wc_clean($_GET["key"]) : "";
		$default_order_id = empty($_REQUEST["orderid"]) ? 0 : ltrim(wc_clean(wp_unslash($_REQUEST["orderid"])), "#");
		$default_order_email = empty($_REQUEST["order_email"]) ? "" : sanitize_email(wp_unslash($_REQUEST["order_email"]));
		if (!$order_id) {
			$order_id = $default_order_id;
		}
		if ($order_id) {
			$order = wc_get_order(apply_filters("woocommerce_shortcode_order_tracking_order_id", $order_id));
			if ($order && !is_wp_error($order)) {
				$_ghn_ordercode = $order->get_meta("_ghn_ordercode", true);
				if ($key && $key == $_ghn_ordercode || strtolower($order->get_billing_email()) === strtolower($default_order_email)) {
					do_action("woocommerce_track_order", $order->get_id());
					wc_get_template("order/tracking.php", ["order" => $order]);
				} else {
					wc_get_template("order/form-tracking.php");
				}
			} else {
				wc_get_template("order/form-tracking.php");
			}
		} else {
			wc_get_template("order/form-tracking.php");
		}
		return ob_get_clean();
	}
	public function ntco_update_checkout_func($array) {
		delete_transient("shipping-transient-version");
	}
	public function ntco_note_done_func() {
		if (!wp_verify_nonce($_REQUEST["nonce"], "nonce_note_done")) {
			exit("No naughty business please");
		}
		update_option("ntco_note_done", NTCO_GHNV2_NOTE_VERSION, "no");
		exit;
	}
	public function check_after_update_option($old_value, $value) {
		if ($old_value != $value) {
			$log_file = NTCO_GHNV2_PLUGIN_DIR . "ghn_log.txt";
			if (file_exists($log_file)) {
				unlink($log_file);
			}
		}
	}
	public function mail_string_filter($string, $orderThis) {
		$_ghn_order_fullinfor = $orderThis->get_meta("_ghn_order_fullinfor", true);
		$_ghn_ordercode = $orderThis->get_meta("_ghn_ordercode", true);
		$code_message_value = isset($_ghn_order_fullinfor["code_message_value"]) ? $_ghn_order_fullinfor["code_message_value"] : "";
		$expected_delivery_time = isset($_ghn_order_fullinfor["data"]["expected_delivery_time"]) ? $_ghn_order_fullinfor["data"]["expected_delivery_time"] : "";
		$estimated_deliver = "";
		if ($expected_delivery_time) {
			$estimated_deliver = date_i18n("d/m/Y", strtotime($expected_delivery_time . " +6 hours"));
		}
		$string = str_replace("{site_title}", get_bloginfo("name"), $string);
		$string = str_replace("{ghn_id}", $_ghn_ordercode, $string);
		$string = str_replace("{ghn_mess}", $code_message_value, $string);
		$string = str_replace("{ghn_tracking_link}", "https://donhang.ghn.vn/?order_code=" . $_ghn_ordercode, $string);
		$string = str_replace("{site_tracking_link}", $this->get_tracking_url($orderThis), $string);
		$string = str_replace("{order_id}", $orderThis->get_id(), $string);
		$string = str_replace("{estimated_deliver}", $estimated_deliver, $string);
		return apply_filters("ghn_mail_string_filter", $string, $orderThis);
	}
	public function sendmail_after_ghn_create_order($order_ID) {
		$orderThis = wc_get_order($order_ID);
		$flra_options = wp_parse_args(get_option($this->_optionName), $this->_defaultOptions);
		$email_active = isset($flra_options["email_active"]) ? intval($flra_options["email_active"]) : "";
		$email_title = isset($flra_options["email_title"]) ? $flra_options["email_title"] : "";
		$email_content = isset($flra_options["email_content"]) ? wpautop($flra_options["email_content"]) : "";
		if ($email_active == 0) {
			return false;
		}
		$_ghn_ordercode = $orderThis->get_meta("_ghn_ordercode", true);
		if ($orderThis->get_billing_email() && $email_active && $email_title && $email_content) {
			$to = $orderThis->get_billing_email();
			if ($_ghn_ordercode) {
				$subject = $this->mail_string_filter($email_title, $orderThis);
				$body = $this->mail_string_filter($email_content, $orderThis);
				$headers = ["Content-Type: text/html; charset=UTF-8"];
				$sendmail = wp_mail($to, $subject, $body, $headers);
			}
		}
	}
	public function date_format($date_string = "") {
		if (!$date_string) {
			return false;
		}
		$datetime = new DateTime($date_string);
		$timezone_offset_in_seconds = 25200;
		$timezone = timezone_name_from_abbr("", $timezone_offset_in_seconds, 0);
		$datetime->setTimezone(new DateTimeZone($timezone));
		$formatted_date = $datetime->format("d/m/Y H:i:s");
		return $formatted_date;
	}
	public function ghn_tracking_order($order_id) {
		$orderThis = wc_get_order($order_id);
		if (!$orderThis || is_wp_error($orderThis)) {
			return false;
		}
		$_ghn_ordercode = $orderThis->get_meta("_ghn_ordercode", true);
		if (!$_ghn_ordercode) {
			return NULL;
		}
		$ghn_tracking = ghn_api()->tracking($_ghn_ordercode);
		if ($ghn_tracking && isset($ghn_tracking["code"]) && $ghn_tracking["code"] == 200) {
			$created_date = isset($ghn_tracking["data"]["created_date"]) ? $ghn_tracking["data"]["created_date"] : "";
			$status = isset($ghn_tracking["data"]["status"]) ? $ghn_tracking["data"]["status"] : "";
			echo "            <section class=\"woocommerce-order-details\">\r\n                <h2 class=\"woocommerce-order-details__title\">Trạng thái đơn hàng - ";
			echo $_ghn_ordercode;
			echo "</h2>\r\n                <table class=\"woocommerce-table woocommerce-table--order-details order_details\">\r\n                    <thead>\r\n                    <tr>\r\n                        <th class=\"woocommerce-table__product-name product-name\">Thời gian</th>\r\n                        <th class=\"woocommerce-table__product-table product-total\">Trạng thái</th>\r\n                    </tr>\r\n                    </thead>\r\n                    <tbody>\r\n                        ";
			if (isset($ghn_tracking["data"]["log"])) {
				echo "                            ";
				foreach ($ghn_tracking["data"]["log"] as $item) {
					$status = isset($item["status"]) ? $item["status"] : "";
					$updated_date = isset($item["updated_date"]) ? $item["updated_date"] : "";
					echo "                                <tr class=\"woocommerce-table__line-item order_item\">\r\n                                    <td class=\"woocommerce-table__product-name\">";
					echo $this->date_format($updated_date);
					echo "</td>\r\n                                    <td class=\"woocommerce-table__product-total\">";
					echo ghn_api()->get_status_text($status);
					echo "</td>\r\n                                </tr>\r\n                            ";
				}
				echo "                        ";
			} else {
				echo "                            <tr class=\"woocommerce-table__line-item order_item\">\r\n                                <td class=\"woocommerce-table__product-name\">";
				echo $this->date_format($created_date);
				echo "</td>\r\n                                <td class=\"woocommerce-table__product-total\">";
				echo ghn_api()->get_status_text($status);
				echo "</td>\r\n                            </tr>\r\n                        ";
			}
			echo "                    </tbody>\r\n                </table>\r\n            </section>\r\n            ";
		}
	}
}
function ghn_get_template($template_name, $args = [], $template_path = "", $default_path = "") {
	if (!empty($args) && is_array($args)) {
		extract($args);
	}
	$located = ghn_locate_template($template_name, $template_path, $default_path);
	if (!file_exists($located)) {
		ghn_doing_it_wrong("ghn_get_template", sprintf(__("%s không tồn tại.", "ntco-ghn"), "<code>" . $located . "</code>"), "2.1");
	} else {
		$located = apply_filters("ghn_get_template", $located, $template_name, $args, $template_path, $default_path);
		do_action("ghn_before_template_part", $template_name, $template_path, $located, $args);
		include $located;
		do_action("ghn_after_template_part", $template_name, $template_path, $located, $args);
	}
}
function ghn_locate_template($template_name, $template_path = "", $default_path = "") {
	if (!$template_path) {
		$template_path = apply_filters("ghn_template_path", "ntco-ghn/");
	}
	if (!$default_path) {
		$default_path = untrailingslashit(plugin_dir_path(__FILE__)) . "/templates/";
	}
	$template = locate_template([trailingslashit($template_path) . $template_name, $template_name]);
	if (!$template) {
		$template = $default_path . $template_name;
	}
	return apply_filters("ghn_locate_template", $template, $template_name, $template_path);
}
function ghn_doing_it_wrong($function, $message, $version) {
	$message .= " Backtrace: " . wp_debug_backtrace_summary();
	if (is_ajax()) {
		do_action("doing_it_wrong_run", $function, $message, $version);
		error_log($function . " was called incorrectly. " . $message . ". This message was added in version " . $version . ".");
	} else {
		_doing_it_wrong($function, $message, $version);
	}
}
function ghn_class() {
	return NTCO_Woo_GHN_Class::init();
}
