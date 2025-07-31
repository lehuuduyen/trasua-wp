<?php


defined("ABSPATH") or exit("No script kiddies please!");
add_action("woocommerce_shipping_init", "ghn_shipping_method_init");
add_filter("woocommerce_shipping_methods", "add_ghn_shipping_method");
ghn_api();
add_filter("ghn_service_name", "ntco_custom_ghn_service_name", 10, 2);

class NTCO_GHN_API {
	private $token = "";
	private $url_remote = "";
	private $my_token = "";
	private $print_link = "https://online-gateway.ghn.vn/a5/public-api/";
	public $_allhubs = "ghn_allhubs";
	public $_allhubs_group = "ghn_allhubs_option";
	public $_defaultHubsOptions = [];
	public $_ghn_webhook = "ghn_webhook";
	public $_ghn_webhook_group = "ghn_webhook_option";
	public $_defaultWebhookOptions = ["webhook_url" => "", "webhook_hash" => ""];
	public $_ghn_webhook_action = "";
	protected static $_instance;
	public static function instance() {
		if (is_null(self::$_instance)) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	public function __construct() {
		$this->token = ghn_class()->get_options("token_key");
		$moitruong = ghn_class()->get_options("moitruong");
		if ($moitruong == "test") {
			$this->url_remote = "https://dev-online-gateway.ghn.vn/shiip/public-api/";
			$this->print_link = "https://dev-online-gateway.ghn.vn/a5/public-api/";
		} else {
			$this->url_remote = "https://online-gateway.ghn.vn/shiip/public-api/";
		}
		$this->_ghn_webhook_action = esc_url(admin_url("admin-ajax.php?action=ntco_ghn_webhook&hash="));
		add_action("add_meta_boxes", [$this, "ghn_order_action"]);
		add_action("save_post", [$this, "ghn_save_meta_box"], 10, 2);
		add_action("wp_ajax_update_hubs", [$this, "ntco_update_hubs"]);
		add_action("wp_ajax_add_hubs", [$this, "ntco_add_hubs"]);
		add_action("wp_ajax_add_hubdistrict", [$this, "ntco_add_hubdistrict"]);
		add_action("wp_ajax_ghn_change_hub", [$this, "ntco_ghn_change_hub"]);
		add_action("wp_ajax_ghn_creat_order", [$this, "ntco_ghn_creat_order"]);
		add_action("wp_ajax_ghn_update_order", [$this, "ntco_ghn_update_order"]);
		add_action("wp_ajax_ghn_tracking_order", [$this, "ntco_ghn_tracking_order"]);
		add_action("wp_ajax_ghn_cancel_order", [$this, "ntco_ghn_cancel_order"]);
		add_action("wp_ajax_ghn_set_webhook", [$this, "ntco_ghn_set_webhook"]);
		add_action("wp_ajax_nopriv_ntco_ghn_webhook", [$this, "ntco_ghn_webhook_func"]);
		add_action("wp_ajax_ghn_calculatefee", [$this, "ghn_calculatefee_func"]);
		add_action("wp_ajax_ghn_creat_order_ajax", [$this, "ghn_creat_order_ajax_func"]);
		add_action("wp_ajax_ghn_sync_hubs", [$this, "ghn_sync_hubs_func"]);
		add_action("wp_ajax_ghn_set_mainHubs", [$this, "ghn_set_mainHubs"]);
		add_action("wp_ajax_ghn_creat_order_html", [$this, "ghn_creat_order_html"]);
		add_action("wp_ajax_ghn_print_api", [$this, "ghn_print_api_func"]);
		add_action("admin_init", [$this, "register_mysettings"]);
		add_option($this->_allhubs, $this->_defaultHubsOptions);
		add_option($this->_ghn_webhook, $this->_defaultWebhookOptions);
		add_action("ntco_vn_checkout_order_action", [$this, "ntco_ghn_order_action"]);
	}
	public function register_mysettings() {
		register_setting($this->_allhubs_group, $this->_allhubs);
		register_setting($this->_ghn_webhook_group, $this->_ghn_webhook);
	}
	public function delete_cache() {
		delete_transient($this->token . "_allhubs");
	}
	public function get_hubs_near($city_customer_id = "", $field = "district_id") {
		if (!$city_customer_id) {
			return false;
		}
		$all_hub_district = get_option(ghn_api()->_allhubs);
		$main_hub = $this->get_main_hubs();
		$allHubs = $this->getHubs();
		if (!$main_hub) {
			$main_hub = isset($allHubs[0]["_id"]) ? $allHubs[0]["_id"] : "";
		}
		$hub_near = ghn_class()->search_in_array_value($all_hub_district, $city_customer_id);
		if (in_array($main_hub, $hub_near)) {
			$hub_near = $main_hub;
		} else {
			if (!empty($hub_near)) {
				$hub_near = $hub_near[0];
			} else {
				$hub_near = $main_hub;
			}
		}
		$hub_near = ghn_class()->search_in_array($allHubs, "_id", $hub_near);
		if ($field == "all") {
			$hub_near = isset($hub_near[0]) ? $hub_near[0] : "";
		} else {
			$hub_near = isset($hub_near[0]) ? $hub_near[0][$field] : "";
		}
		return $hub_near;
	}
	public function get_main_hubs() {
		return get_option("ghn_main_hub");
	}
	public function get_hub_by_id($hubID = "", $field = "district_id") {
		$allHubs = $this->getHubs();
		$mainHub = ghn_class()->search_in_array($allHubs, "_id", $hubID);
		if (isset($mainHub[0])) {
			if ($field == "all") {
				return $mainHub[0];
			}
			return $mainHub[0][$field];
		}
		return false;
	}
	public function get_cURL($args = [], $url = "") {
		if (empty($args)) {
			return false;
		}
		$data = isset($args["data"]) ? $args["data"] : "";
		$action = isset($args["action"]) ? $args["action"] : "";
		$token = isset($args["token"]) && $args["token"] ? $args["token"] : $this->token;
		$shop_id = isset($data["shop_id"]) && $data["shop_id"] ? $data["shop_id"] : "";
		if (!$url) {
			$url = $this->url_remote;
		}
		$curl = curl_init();
		$args = [CURLOPT_URL => $url . $action, CURLOPT_RETURNTRANSFER => true, CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1, CURLOPT_SSL_VERIFYPEER => is_ssl(), CURLOPT_CUSTOMREQUEST => "POST", CURLOPT_HTTPHEADER => ["Content-Type: application/json", "Token: " . $token, "shopid: " . $shop_id]];
		if ($data) {
			$args[CURLOPT_POSTFIELDS] = json_encode($data);
		}
		curl_setopt_array($curl, $args);
		$response = curl_exec($curl);
		$result = json_decode($response, true);
		curl_close($curl);
		if (ghn_class()->get_options("debug")) {
			ob_start();
			print_r($action);
			print_r($data);
			print_r($result);
			$cont = ob_get_clean();
			$log = "User: " . $_SERVER["REMOTE_ADDR"] . " - " . date("F j, Y, g:i a") . PHP_EOL . $cont . PHP_EOL . "-------------------------" . PHP_EOL;
			file_put_contents(NTCO_GHNV2_PLUGIN_DIR . "/ghn_log.txt", $log, FILE_APPEND);
		}
		return $result;
	}
	public function get_CalculateFee($args = []) {
		if (empty($args)) {
			return false;
		}
		$args = wp_parse_args($args, ["Weight" => 0, "Length" => 0, "Width" => 0, "Height" => 0, "FromDistrictID" => "", "ToDistrictID" => "", "ServiceID" => "", "OrderCosts" => [], "CouponCode" => "", "InsuranceFee" => ""]);
		$data = ["token" => $this->token, "Weight" => (int) $args["Weight"], "Length" => (int) $args["Length"], "Width" => (int) $args["Width"], "Height" => (int) $args["Height"], "FromDistrictID" => (int) $args["FromDistrictID"], "ToDistrictID" => (int) $args["ToDistrictID"], "ServiceID" => $args["ServiceID"], "OrderCosts" => $args["OrderCosts"], "CouponCode" => sanitize_text_field($args["CouponCode"]), "InsuranceFee" => (float) $args["InsuranceFee"]];
		$args = ["data" => $data, "action" => "CalculateFee"];
		$result = $this->get_cURL($args);
		if ($result && is_array($result) && !empty($result)) {
			if (isset($result["code"]) && $result["code"] == 1) {
				$data = isset($result["data"]) ? $result["data"] : "";
				return $data;
			}
			$msg = isset($result["msg"]) ? $result["msg"] : "";
			$data["ErrorMessage"] = $msg;
			return $data;
		}
		return false;
	}
	public function ghn_calculatefee_func() {
		$hubID = isset($_POST["hubID"]) ? intval($_POST["hubID"]) : 0;
		$orderid = isset($_POST["orderid"]) ? intval($_POST["orderid"]) : 0;
		$weight = isset($_POST["weight"]) ? (int) $_POST["weight"] : 0;
		$length = isset($_POST["length"]) ? (int) $_POST["length"] : 0;
		$width = isset($_POST["width"]) ? (int) $_POST["width"] : 0;
		$height = isset($_POST["height"]) ? (int) $_POST["height"] : 0;
		$CouponCode = isset($_POST["couponcode"]) ? sanitize_text_field($_POST["couponcode"]) : "";
		$ServiceID = isset($_POST["serviceID"]) ? intval($_POST["serviceID"]) : "";
		$OrderCosts = isset($_POST["orderCosts"]) ? (array) $_POST["orderCosts"] : "";
		$InsuranceFee = isset($_POST["insuranceFee"]) ? (float) $_POST["insuranceFee"] : "";
		if ($hubID) {
			$FromDistrictID = $this->get_hub_by_id($hubID);
		}
		$order = wc_get_order($orderid);
		$ToDistrictID = "";
		if (!is_wp_error($order)) {
			$customer_infor = ghn_class()->get_customer_address_shipping($order);
			$ToDistrictID = $customer_infor["district"];
		}
		if (!$FromDistrictID || !$ToDistrictID || !$ServiceID) {
			wp_send_json_error("Kiểm tra lại dữ liệu gửi vào");
		}
		$OrderCosts_args = [];
		if ($OrderCosts && !empty($OrderCosts)) {
			foreach ($OrderCosts as $OrderCosts_item) {
				$OrderCosts_args[]["ServiceID"] = $OrderCosts_item;
			}
		}
		$args = ["Weight" => $weight, "Length" => $length, "Width" => $width, "Height" => $height, "FromDistrictID" => $FromDistrictID, "ToDistrictID" => $ToDistrictID, "ServiceID" => $ServiceID, "OrderCosts" => $OrderCosts_args, "CouponCode" => $CouponCode, "InsuranceFee" => $InsuranceFee];
		$result = $this->order_findAvailableServices($args);
		$result_args = [];
		if ($result) {
			$result_args["result_html"] = $this->total_order_html($result);
			if (isset($result["ErrorMessage"]) && !$result["ErrorMessage"]) {
				$result_args["allow_creat_order"] = true;
				wp_send_json_success($result_args);
			} else {
				$result_args["allow_creat_order"] = false;
				wp_send_json_success($result_args);
			}
		}
		wp_send_json_error();
		exit;
	}
	public function findAvailableServices($package = [], $hubid = "", $payment_methob = "") {
		$state = isset($package["destination"]["state"]) ? $package["destination"]["state"] : "";
		$ToDistrictID = isset($package["destination"]["city"]) ? ghn_class()->get_ghn_district_id($package["destination"]["city"]) : "";
		$cod_amout = isset($package["cart_subtotal"]) ? (float) $package["cart_subtotal"] : 0;
		if ($payment_methob && $payment_methob != "cod" && !ghn_class()->get_options("order_insurance")) {
			$cod_amout = 0;
		}
		$shop_id = "";
		if ($hubid) {
			$DistrictID = $this->get_hub_by_id($hubid);
			$shop_id = $hubid;
		}
		if (isset($DistrictID) && $DistrictID) {
			$from_district = $DistrictID;
		} else {
			$from_district = $this->get_hubs_near($state);
			$shop_id = $this->get_hubs_near($state, "_id");
		}
		$args = ["data" => ["shop_id" => (int) $shop_id, "from_district" => (int) $from_district, "to_district" => (int) $ToDistrictID], "action" => "v2/shipping-order/available-services"];
		$data_fee = [];
		$args = apply_filters("ghn_available_services_args", $args);
		$cache_key = "ghn_available_services_" . md5(json_encode($args));
		$services = get_transient($cache_key);
		if ($services === false) {
			$services = $this->get_cURL($args);
			if ($services && is_array($services) && !empty($services) && isset($services["code"]) && $services["code"] == 200) {
				set_transient($cache_key, $services, 2 * DAY_IN_SECONDS);
			}
		}
		if ($services && is_array($services) && !empty($services) && isset($services["code"]) && $services["code"] == 200) {
			$services = isset($services["data"]) ? $services["data"] : "";
			if ($services && is_array($services)) {
				$i = 0;
				foreach ($services as $service) {
					$short_name = isset($service["short_name"]) ? $service["short_name"] : "";
					$service_id = isset($service["service_id"]) ? (int) $service["service_id"] : 0;
					$service_type_id = isset($service["service_type_id"]) ? (int) $service["service_type_id"] : 0;
					if ($service_id && !in_array($service_type_id, [0, 4]) && $short_name) {
						$data = ["weight" => (int) ghn_class()->get_cart_contents_weight($package), "length" => (int) ghn_class()->get_cart_dimension_package($package, "length"), "width" => (int) ghn_class()->get_cart_dimension_package($package, "width"), "height" => (int) ghn_class()->get_cart_dimension_package($package), "from_district_id" => (int) $from_district, "to_district_id" => (int) $ToDistrictID, "coupon" => "", "insurance_value" => $cod_amout, "service_id" => (int) $service_id, "service_type_id" => (int) $service_type_id, "pick_station_id" => 0];
						$args = ["data" => $data, "action" => "v2/shipping-order/fee"];
						$args = apply_filters("ghn_get_fee_args", $args);
						$cache_fee_key = "ghn_get_fee_args_" . md5(json_encode($args));
						$result = get_transient($cache_fee_key);
						if ($result === false) {
							$result = $this->get_cURL($args);
							if ($result && is_array($result) && !empty($result) && isset($result["code"]) && $result["code"] == 200 && isset($result["data"]) && !empty($result["data"])) {
								set_transient($cache_fee_key, $result, 2 * DAY_IN_SECONDS);
							}
						}
						if ($result && is_array($result) && !empty($result) && isset($result["code"]) && $result["code"] == 200 && isset($result["data"]) && !empty($result["data"])) {
							$data_fee[$i] = $result["data"];
							$data_fee[$i]["_id"] = $shop_id;
							$data_fee[$i]["service_id"] = $service_id;
							$data_fee[$i]["service_type_id"] = $service_type_id;
							$data_fee[$i]["short_name"] = apply_filters("ghn_service_name", $short_name, $service_type_id);
						}
					}
					$i++;
				}
				return $data_fee;
			}
		}
		return false;
	}
	public function shift_date() {
		$args = ["action" => "v2/shift/date"];
		$args = apply_filters("ghn_shift_date_args", $args);
		$result = $this->get_cURL($args);
		return $result;
	}
	public function tracking($order_code) {
		if (!$order_code) {
			return false;
		}
		$args = ["data" => ["order_code" => $order_code], "action" => "v2/shipping-order/detail"];
		$args = apply_filters("ghn_order_detail_args", $args);
		$result = $this->get_cURL($args);
		return $result;
	}
	public function order_findAvailableServices($order = "", $hubid = "", $args = []) {
		if (!$order) {
			return false;
		}
		$args = wp_parse_args($args, ["weight" => 0, "length" => 0, "width" => 0, "height" => 0, "CouponCode" => "", "InsuranceFee" => ""]);
		$customer_infor = ghn_class()->get_customer_address_shipping($order);
		$ToDistrictID = ghn_class()->get_ghn_district_id($customer_infor["district"]);
		$state = $customer_infor["province"];
		$shop_id = "";
		if ($hubid) {
			$DistrictID = $this->get_hub_by_id($hubid);
			$shop_id = $hubid;
		}
		if (isset($DistrictID) && $DistrictID) {
			$from_district = $DistrictID;
		} else {
			$from_district = $this->get_hubs_near($state);
			$shop_id = $this->get_hubs_near($state, "_id");
		}
		$args_service = ["data" => ["shop_id" => (int) $shop_id, "from_district" => (int) $from_district, "to_district" => (int) $ToDistrictID], "action" => "v2/shipping-order/available-services"];
		$data_fee = [];
		$args_service = apply_filters("ghn_available_services_args", $args_service);
		$cache_key = "ghn_available_services_" . md5(json_encode($args_service));
		$services = get_transient($cache_key);
		if ($services === false) {
			$services = $this->get_cURL($args_service);
			if ($services && is_array($services) && !empty($services) && isset($services["code"]) && $services["code"] == 200) {
				set_transient($cache_key, $services, 2 * DAY_IN_SECONDS);
			}
		}
		if ($services && is_array($services) && !empty($services) && isset($services["code"]) && $services["code"] == 200) {
			$services = isset($services["data"]) ? $services["data"] : "";
			if ($services && is_array($services)) {
				$i = 0;
				foreach ($services as $service) {
					$short_name = isset($service["short_name"]) ? $service["short_name"] : "";
					$service_id = isset($service["service_id"]) ? (int) $service["service_id"] : 0;
					$service_type_id = isset($service["service_type_id"]) ? (int) $service["service_type_id"] : 0;
					if ($service_id && !in_array($service_type_id, [0, 4]) && $short_name) {
						$data = ["weight" => (int) $args["weight"], "length" => (int) $args["length"], "width" => (int) $args["width"], "height" => (int) $args["height"], "from_district_id" => (int) $from_district, "to_district_id" => (int) $ToDistrictID, "coupon" => $args["CouponCode"], "insurance_value" => (float) $args["InsuranceFee"], "service_id" => (int) $service_id, "service_type_id" => (int) $service_type_id, "pick_station_id" => 0];
						$args_order = ["data" => $data, "action" => "v2/shipping-order/fee"];
						$args_order = apply_filters("ghn_get_fee_args", $args_order);
						$cache_fee_key = "ghn_get_fee_args_" . md5(json_encode($args_order));
						$result = get_transient($cache_fee_key);
						if ($result === false) {
							$result = $this->get_cURL($args_order);
							if ($result && is_array($result) && !empty($result) && isset($result["code"]) && $result["code"] == 200 && isset($result["data"]) && !empty($result["data"])) {
								set_transient($cache_fee_key, $result, 2 * DAY_IN_SECONDS);
							}
						}
						if ($result && is_array($result) && !empty($result) && isset($result["code"]) && $result["code"] == 200 && isset($result["data"]) && !empty($result["data"])) {
							$data_fee[$i] = $result["data"];
							$data_fee[$i]["_id"] = $shop_id;
							$data_fee[$i]["service_id"] = $service_id;
							$data_fee[$i]["service_type_id"] = $service_type_id;
							$data_fee[$i]["short_name"] = apply_filters("ghn_service_name", $short_name, $service_type_id);
						}
					}
					$i++;
				}
				return $data_fee;
			}
		}
		return false;
	}
	public function getHubs() {
		if (false === ($allhubs = get_transient($this->token . "_allhubs"))) {
			$args = ["action" => "v2/shop/all"];
			$result = $this->get_cURL($args);
			if ($result && is_array($result) && !empty($result)) {
				if (isset($result["code"]) && $result["code"] == 200) {
					$data = isset($result["data"]["shops"]) ? $result["data"]["shops"] : "";
					set_transient($this->token . "_allhubs", $data);
					return $data;
				}
				ob_start();
				print_r($result);
				$error = ob_get_clean();
				$log = "User: " . $_SERVER["REMOTE_ADDR"] . " - " . date("F j, Y, g:i a") . PHP_EOL . $error . PHP_EOL . "-------------------------" . PHP_EOL;
				file_put_contents(NTCO_GHNV2_PLUGIN_DIR . "/ghn_log.txt", $log, FILE_APPEND);
			}
			$this->delete_cache();
			return false;
		}
		return $allhubs;
	}
	public function ghn_sync_hubs_func() {
		if (!wp_verify_nonce($_REQUEST["nonce"], "ghn_action_nonce_action")) {
			wp_send_json_error("Check nonce failed!");
		}
		$this->delete_cache();
		$hubs = $this->getHubs();
		if ($hubs) {
			wp_send_json_success("Cập nhật thành công");
		} else {
			wp_send_json_error("Có lỗi xảy ra");
		}
	}
	public function ghn_set_mainHubs() {
		if (!wp_verify_nonce($_REQUEST["nonce"], "action_nonce_update")) {
			wp_send_json_error("Check nonce failed!");
		}
		$hubid = isset($_POST["hubid"]) ? intval($_POST["hubid"]) : "";
		if (!$hubid) {
			wp_send_json_error("Sai mã kho hàng");
		}
		update_option("ghn_main_hub", $hubid);
		wp_send_json_success("Đã update thành công!");
		exit;
	}
	public function updateHubs($data = []) {
		if (!is_array($data) || empty($data)) {
			return false;
		}
		$args = ["data" => ["token" => $this->token, "Latitude" => 0, "Longitude" => 0, "IsMain" => false], "action" => "UpdateHubs"];
		foreach ($data as $k => $v) {
			if ($k == "HubID") {
				$args["data"][$k] = (int) $v;
			} else {
				if ($k == "IsMain") {
					$args["data"]["IsMain"] = $v == 1 ? true : false;
				} else {
					if ($k == "DistrictID") {
						$args["data"][$k] = ghn_class()->get_district_id_from_string($v);
					} else {
						$args["data"][$k] = $v;
					}
				}
			}
		}
		$result = $this->get_cURL($args);
		if ($result && is_array($result) && !empty($result)) {
			if (isset($result["code"]) && $result["code"] == 1) {
				$this->delete_cache();
				return true;
			}
			return isset($result["msg"]) ? $result["msg"] : false;
		}
		return false;
	}
	public function addHubs($data = []) {
		if (!is_array($data) || empty($data)) {
			return false;
		}
		$args = ["data" => ["district_id" => isset($data["DistrictID"]) ? intval(end(explode("_", $data["DistrictID"]))) : "", "ward_code" => "21209", "name" => isset($data["ContactName"]) ? $data["ContactName"] : "", "phone" => isset($data["ContactPhone"]) ? $data["ContactPhone"] : "", "address" => isset($data["Address"]) ? $data["Address"] : ""], "action" => "v2/shop/register"];
		$result = $this->get_cURL($args);
		if ($result && is_array($result) && !empty($result)) {
			if (isset($result["code"]) && $result["code"] == 200) {
				$this->delete_cache();
				return true;
			}
			return isset($result["code_message_value"]) ? $result["code_message_value"] : (isset($result["message"]) ? $result["message"] : false);
		}
		return false;
	}
	public function ntco_update_hubs() {
		if (!wp_verify_nonce($_REQUEST["nonce"], "action_nonce_update")) {
			wp_send_json_error();
			exit;
		}
		$data = isset($_POST["data"]) ? $_POST["data"] : [];
		if (true === ($results = $this->updateHubs($data))) {
			wp_send_json_success();
		} else {
			wp_send_json_error($results);
		}
		exit;
	}
	public function ntco_add_hubs() {
		if (!wp_verify_nonce($_REQUEST["nonce"], "action_nonce_add")) {
			wp_send_json_error("Lỗi bảo mật");
			exit;
		}
		$data = isset($_POST["data"]) ? $_POST["data"] : [];
		if (true === ($results = $this->addHubs($data))) {
			wp_send_json_success(__("Thêm cửa hàng/kho thành công! Đang làm mới..."));
		} else {
			wp_send_json_error($results);
		}
		exit;
	}
	public function ntco_add_hubdistrict() {
		if (!wp_verify_nonce($_REQUEST["nonce"], "action_nonce_update")) {
			wp_send_json_error("Check nonce failed");
			exit;
		}
		$hubid = isset($_POST["hubid"]) ? $_POST["hubid"] : 0;
		$districtID = isset($_POST["districtID"]) ? $_POST["districtID"] : [];
		if ($hubid) {
			$old_hub_district = get_option(ghn_api()->_allhubs);
			$old_hub_district[$hubid] = $districtID;
			if (update_option($this->_allhubs, $old_hub_district)) {
				wp_send_json_success("Update thành công");
			} else {
				wp_send_json_error("Không có gì thay đổi hoặc có lỗi khi update!");
			}
		}
		wp_send_json_error("Không tồn tại HubID");
		exit;
	}
	public function createOrder($data = []) {
		if (!is_array($data) || empty($data)) {
			return false;
		}
		$args = ["data" => $data, "action" => "v2/shipping-order/create"];
		$result = $this->get_cURL($args);
		return $result;
	}
	public function updateOrder($data = []) {
		if (!is_array($data) || empty($data)) {
			return false;
		}
		$args = [
			'data' => [
				'token' => $this->token,
				'ShippingOrderID' => isset($data['ShippingOrderID']) && $data['ShippingOrderID'] ? intval($data['ShippingOrderID']) : 0,
				'OrderCode' => isset($data['OrderCode']) && $data['OrderCode'] ? sanitize_text_field($data['OrderCode']) : '',
				'PaymentTypeID' => isset($data['PaymentTypeID']) && $data['PaymentTypeID'] ? intval($data['PaymentTypeID']) : 1,
				'FromDistrictID' => isset($data['FromDistrictID']) && $data['FromDistrictID'] ? intval($data['FromDistrictID']) : 0,
				'FromWardCode' => isset($data['FromWardCode']) && $data['FromWardCode'] ? sanitize_text_field($data['FromWardCode']) : '',
				'ToDistrictID' => isset($data['ToDistrictID']) && $data['ToDistrictID'] ? intval($data['ToDistrictID']) : 0,
				'ToWardCode' => isset($data['ToWardCode']) ? sanitize_text_field($data['ToWardCode']) : '',
				'Note' => isset($data['Note']) ? sanitize_textarea_field($data['Note']) : '',
				'SealCode' => isset($data['SealCode']) ? sanitize_text_field($data['SealCode']) : '',
				'ExternalCode' => isset($data['ExternalCode']) ? sanitize_text_field($data['ExternalCode']) : '',
				'ClientContactName' => isset($data['ClientContactName']) ? sanitize_text_field($data['ClientContactName']) : '',
				'ClientContactPhone' => isset($data['ClientContactPhone']) ? sanitize_text_field($data['ClientContactPhone']) : '',
				'ClientAddress' => isset($data['ClientAddress']) ? sanitize_text_field($data['ClientAddress']) : '',
				'ClientHubID' => isset($data['ClientHubID']) && $data['ClientHubID'] ? intval($data['ClientHubID']) : 0,
				'CustomerName' => isset($data['CustomerName']) ? sanitize_text_field($data['CustomerName']) : '',
				'CustomerPhone' => isset($data['CustomerPhone']) ? sanitize_text_field($data['CustomerPhone']) : '',
				'ShippingAddress' => isset($data['ShippingAddress']) ? sanitize_text_field($data['ShippingAddress']) : '',
				'CoDAmount' => isset($data['CoDAmount']) ? (float) $data['CoDAmount'] : 0,
				'NoteCode' => isset($data['NoteCode']) && $data['NoteCode'] ? sanitize_text_field($data['NoteCode']) : apply_filters('ntco_notecode_default', 'KHONGCHOXEMHANG'),
				'InsuranceFee' => isset($data['InsuranceFee']) ? (float) $data['InsuranceFee'] : 0,
				'ServiceID' => isset($data['ServiceID']) ? (int) $data['ServiceID'] : 0,
				'ToLatitude' => isset($data['ToLatitude']) ? (float) $data['ToLatitude'] : 0,
				'ToLongitude' => isset($data['ToLongitude']) ? (float) $data['ToLongitude'] : 0,
				'FromLat' => isset($data['FromLat']) ? (float) $data['FromLat'] : 0,
				'FromLng' => isset($data['FromLng']) ? (float) $data['FromLng'] : 0,
				'Content' => isset($data['Content']) ? sanitize_text_field($data['Content']) : '',
				'CouponCode' => isset($data['CouponCode']) ? sanitize_text_field($data['CouponCode']) : '',
				'Weight' => isset($data['Weight']) && $data['Weight'] ? (int) $data['Weight'] : 0,
				'Length' => isset($data['Length']) && $data['Length'] ? (int) $data['Length'] : 1,
				'Width' => isset($data['Width']) && $data['Width'] ? (int) $data['Width'] : 1,
				'Height' => isset($data['Height']) && $data['Height'] ? (int) $data['Height'] : 1,
				'OrderCosts' => isset($data['ShippingOrderCosts']) ? $data['ShippingOrderCosts'] : [],
				'CheckMainBankAccount' => false,
				'ReturnContactName' => '',
				'ReturnContactPhone' => '',
				'ReturnAddress' => '',
				'ReturnDistrictCode' => '',
				'ExternalReturnCode' => '',
				'IsCreditCreate' => false,
				'AffiliateID' => ghn_class()->myAffID(),
			],
			'action' => 'UpdateOrder'
		];

		$result = $this->get_cURL($args);
		return $result;
	}
	public function ghn_order_action() {
		$screen = wc_get_container()->get("Automattic\\WooCommerce\\Internal\\DataStores\\Orders\\CustomOrdersTableController")->custom_orders_table_usage_is_enabled() ? wc_get_page_screen_id("shop-order") : "shop_order";
		add_meta_box("ghn-action-id", __("Giao Hàng NHANH (GHN)", "ntco-ghn"), [$this, "ghn_order_action_callback"], $screen, "side", "high");
	}
	public function ghn_order_action_callback($post_or_order_object) {
		$order = $post_or_order_object instanceof WP_Post ? wc_get_order($post_or_order_object->ID) : $post_or_order_object;
		$order_id = $order->get_id();

		if (!$order_id) {
			return false;
		}

		wp_nonce_field('ghn_action_nonce_action', 'ghn_action_nonce');

		$ghn_order_fullinfor = $order->get_meta('_ghn_order_fullinfor', true);
		$ghn_ordercode = $order->get_meta('_ghn_ordercode', true);
		$ghn_order_status = $order->get_meta('_ghn_order_status', true);
		$ghn_order_submited = $order->get_meta('_ghn_order_submited', true);

		if ($ghn_ordercode && !in_array($ghn_order_status, ['Cancel', 'cancel'])) {
			echo "<div class='ghn_ordercode_html_wrap_$order_id'>";
			echo $this->ordercode_html($ghn_ordercode, $order_id);
			echo "</div>";
			echo "<p class='ajax_status_tracking'>";
			if ($ghn_order_status) {
				printf(__('<strong>Trạng thái:</strong> %s', 'ntco-ghn'), $this->get_status_text($ghn_order_status));
			}
			echo "</p>";
			echo "<p style='display: none'><a href='#' class='button button-primary ghn_update_order' data-ordercode='$ghn_ordercode'>";
			_e('Chỉnh sửa đơn hàng', 'ntco-ghn');
			echo "</a></p>";
			echo "<p style='display: none'><a href='#' class='button button-primary ghn_tracking_order' data-ordercode='$ghn_ordercode'>";
			_e('Kiểm tra đơn hàng', 'ntco-ghn');
			echo "</a></p>";
		} else {
			echo "<div class='ghn_ordercode_html_wrap_$order_id'>";
			echo "<a href='#' class='button button-primary ghn_creat_order_popup' data-orderid='" . esc_attr($order_id) . "'>";
			_e('Tạo vận đơn GHN', 'ntco-ghn');
			echo "</a>";
			echo "</div>";
		}
	}

	public function ordercode_html($ghn_ordercode, $postid = "") {
		ob_start();
		echo "<p>";
		printf(__('<strong>Mã vận đơn:</strong> %s', 'ntco-ghn'), $ghn_ordercode);
		echo "</p>";
		echo "<p><a href='#' class='button button-link-delete ghn_cancel_order' data-ordercode='" . esc_attr($ghn_ordercode) . "' data-postid='" . esc_attr($postid) . "'>";
		_e('Hủy đơn hàng', 'ntco-ghn');
		echo "</a></p>";
		echo "<p><a href='#' class='button ghn_print_order' data-ordercode='$ghn_ordercode' data-nonce='" . wp_create_nonce('nonce_print') . "'>";
		_e('In vận đơn theo mẫu GHN', 'ntco-ghn');
		echo "</a></p>";
		return ob_get_clean();
	}

	public function ntco_woocommerce_admin_order_data_after_order_details($order) {
		$customer_infor = ghn_class()->get_customer_address_shipping($order);
		extract($customer_infor);
		$shipping_methods = $order->get_shipping_methods();
		$HubID_Order = "";
		$method_id = "";

		foreach ($shipping_methods as $shipping_method) {
			foreach ($shipping_method->get_formatted_meta_data() as $meta_data) {
				if ($meta_data->key && $meta_data->key == "HubID" && !$HubID_Order) {
					$HubID_Order = $meta_data->value;
				}
				if ($meta_data->key && $meta_data->key == "ServiceID" && !$method_id) {
					$method_id = $meta_data->value;
				}
			}
		}

		$product_list = ghn_class()->get_product_args($order);
		$order_id = $order->get_id();
		$create_order_text = __('Đăng đơn hàng lên GHN', 'ntco-ghn');
		$create_button_text = __('Tạo đơn hàng', 'ntco-ghn');
		$cancel_button_text = __('Hủy tạo', 'ntco-ghn');

		echo '
    <div class="ghn_popup_style ghn_creat_popup ntco_options_style" id="ajax_wrap_' . $order_id . '">
        <div class="ntco_option_box">
            <table class="ntco_hubs_table widefat" cellspacing="0">
                <thead>
                    <tr>
                        <th colspan="2"><h2>' . $create_order_text . '</h2></th>
                    </tr>
                </thead>
                <tbody>';

		include "ghn-creat-order-html.php";

		echo '
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2" class="text_alignright">
                            <div class="ghn_msg"></div>
                            <a href="#" class="button button-primary ntco_float_right ntco_ghn_creat_order">' . $create_button_text . '</a>
                            <a href="#" class="button close_popup ntco_float_right">' . $cancel_button_text . '</a>
                            <span class="spinner"></span>
                        </td>
                    </tr>
                </tfoot>
            </table>
            <input type="hidden" value="' . $order_id . '" name="order_id" class="order_id"/>
            <input type="hidden" value="0" name="allow_creat_order" class="allow_creat_order"/>
        </div>
    </div>';
	}

	public function total_order_html($args = []) {
		if (!$args || !is_array($args)) {
			return false;
		}

		ob_start();

		if (!$args['ErrorMessage']) {
			echo '
        <div class="ntco_option_1col">
            <table>
                <tbody>
                    <tr class="total_order_api_shipfee">
                        <th>Phí vận chuyển:</th>
                        <td>' . wc_price($args['ServiceFee']) . '</td>
                    </tr>';

			if (isset($args['OrderCosts']) && !empty($args['OrderCosts'])) {
				foreach ($args['OrderCosts'] as $Costs) {
					echo '
                <tr>
                    <th>' . $Costs['Name'] . ':</th>
                    <td>' . wc_price($Costs['Cost']) . '</td>
                </tr>';
				}
			}

			echo '
                    <tr class="total_order_api_total">
                        <th>Tổng cộng:</th>
                        <td>' . ($args['DiscountFee'] ? wc_price($args['DiscountFee']) : wc_price($args['CalculatedFee'])) . '</td>
                    </tr>
                </tbody>
            </table>
        </div>';
		} else {
			echo '
        <div class="ntco_option_1col">
            <table>
                <tbody>
                    <tr>
                        <td colspan="2">' . $args['ErrorMessage'] . '</td>
                    </tr>
                </tbody>
            </table>
        </div>';
		}

		return ob_get_clean();
	}

	public function ghn_update_order_html($order) {
		$ghn_order_submited = $order->get_meta("_ghn_order_submited", true);
		$ghn_order_fullinfor = $order->get_meta("_ghn_order_fullinfor", true);
		$OrderID = isset($ghn_order_fullinfor["data"]["OrderID"]) ? $ghn_order_fullinfor["data"]["OrderID"] : 0;
		$OrderCode = isset($ghn_order_fullinfor["data"]["OrderCode"]) ? $ghn_order_fullinfor["data"]["OrderCode"] : "";
		$ghn_order_status = $order->get_meta("_ghn_order_status", true);
		if (!empty($ghn_order_submited) && $ghn_order_status != "Cancel") {
			if ($OrderID) {
				$ghn_order_submited["ShippingOrderID"] = $OrderID;
			}
			if ($OrderCode) {
				$ghn_order_submited["OrderCode"] = $OrderCode;
			}
			$this->ntco_form_creat_order_html($ghn_order_submited, $order);
		} else {
			$this->ntco_woocommerce_admin_order_data_after_order_details($order);
		}
	}
	public function ghn_creat_order_html() {
		$orderid = isset($_POST["orderid"]) ? intval($_POST["orderid"]) : "";
		if (!wp_verify_nonce($_REQUEST["nonce"], "ghn_action_nonce_action")) {
			exit("No naughty business please");
		}
		if ($orderid) {
			$order = wc_get_order($orderid);
			if ($order && !is_wp_error($order)) {
				ob_start();
				$this->ntco_woocommerce_admin_order_data_after_order_details($order);
				wp_send_json_success(["html" => ob_get_clean()]);
			}
		}
		wp_send_json_error("Lỗi: Thiếu Order ID");
		exit;
	}
	public function ghn_print_api_func() {
		if (!wp_verify_nonce($_REQUEST["nonce"], "nonce_print")) {
			wp_send_json_error("Vui lòng đăng nhập hoặc reload lại trang");
		}
		$ordercode = isset($_POST["ordercode"]) && $_POST["ordercode"] ? wp_unslash($_POST["ordercode"]) : "";
		$khoin = isset($_POST["khoin"]) && $_POST["khoin"] ? wp_unslash($_POST["khoin"]) : "";
		if (!$ordercode || !$khoin) {
			wp_send_json_error("Thiếu thông tin đầu vào");
		}
		$args = ["data" => ["order_codes" => [$ordercode]], "action" => "v2/a5/gen-token"];
		$result = $this->get_cURL($args);
		$code = isset($result["code"]) ? intval($result["code"]) : "";
		$message = isset($result["message"]) ? wp_unslash($result["message"]) : "";
		$token = isset($result["data"]["token"]) && $result["data"]["token"] ? $result["data"]["token"] : "";
		if ($code != 200) {
			wp_send_json_error($message);
		}
		if (!$token) {
			wp_send_json_error("Lỗi khi lấy token");
		}
		$link = "";
		switch ($khoin) {
			case "a5":
				$link = $this->print_link . "printA5?token=" . $token;
				break;
			case "x5270":
				$link = $this->print_link . "print52x70?token=" . $token;
				break;
			case "x8080":
				$link = $this->print_link . "print80x80?token=" . $token;
				break;
			default:
				wp_send_json_error("Không tìm được khổ in hợp lệ");
				exit;
		}

		wp_send_json_success($link);
		exit;
	}
	public function ntco_form_create_order_html($data = [], $order = "") {
		if (empty($data)) {
			return false;
		}

		// Parse the input data with default values
		$data = wp_parse_args($data, [
			"shop_id" => "", "payment_type_id" => "", "note" => "", "required_note" => "", "return_phone" => "",
			"return_address" => "", "return_district_id" => NULL, "return_ward_code" => "", "client_order_code" => "",
			"to_name" => "", "to_phone" => "", "to_address" => "", "to_ward_code" => "", "to_district_id" => "",
			"cod_amount" => "", "content" => "", "weight" => "", "length" => "", "width" => "", "height" => "",
			"pick_station_id" => "", "deliver_station_id" => "", "insurance_value" => "", "service_id" => "",
			"service_type_id" => "", "AffiliateID" => ""
		]);

		// Get customer information and product list
		$customer_infor = ghn_class()->get_customer_address_shipping($order);
		extract($customer_infor);
		$product_list = ghn_class()->get_product_args($order);

		// Begin HTML output
		$output = "<div class='ghn_popup_style ghn_create_popup ntco_options_style ghn_update_wrap' id='ajax_wrap_{$order->get_id()}'>
        <div class='ntco_option_box'>
            <table class='ntco_hubs_table widefat' cellspacing='0'>
                <thead>
                    <tr>
                        <th colspan='2'><h2>" . __("Đăng đơn hàng lên GHN", "ntco-ghn") . "</h2></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>" . __("Chọn cửa hàng/kho", "ntco-ghn") . "</td>
                        <td>
                            <select name='ghn_createorder_hub' id='ghn_createorder_hub'>
                                <option class=''>" . __("Chọn cửa hàng/kho", "ntco-ghn") . "</option>";

		$all_hubs = $this->getHubs();
		if (!empty($all_hubs) && is_array($all_hubs)) {
			foreach ($all_hubs as $hub) {
				$hubID = $hub["HubID"] ?? "";
				$Address = $hub["Address"] ?? "";
				$ContactName = $hub["ContactName"] ?? "";
				$output .= "<option value='{$hubID}' " . selected($hubID, $data["ClientHubID"], false) . ">";
				$output .= "#{$hubID} - {$ContactName} - {$Address}";
				$output .= "</option>";
			}
		}

		$output .= "</select><br>
                            <small>" . __("Phần này là tự động. Trong trường hợp thay đổi chi nhánh có thể sẽ làm phí vận chuyển thay đổi.", "ntco-ghn") . "</small>
                        </td>
                    </tr>
                    <tr>
                        <td colspan='2'>
                            <div class='ntco_option_2col ghn_order_customerinfor'>
                                <div class='ntco_option_col ghn_more_wrap'>
                                    <div class='ntco_option_col_title'>
                                        <strong>Thông tin khách hàng</strong>
                                        <small>Ẩn bớt</small>
                                    </div>
                                    <div class='ghn_customer_infor ghn_more_content'>
                                        <div class='ghn_customer_row'>
                                            <div class='ghn_customer_col'>" . __("Họ và tên", "ntco-ghn") . "</div>
                                            <div class='ghn_customer_col'>{$name}</div>
                                        </div>
                                        <div class='ghn_customer_row'>
                                            <div class='ghn_customer_col'>" . __("Số điện thoại", "ntco-ghn") . "</div>
                                            <div class='ghn_customer_col'>{$phone}</div>
                                        </div>
                                        <div class='ghn_customer_row'>
                                            <div class='ghn_customer_col'>" . __("Địa chỉ", "ntco-ghn") . "</div>
                                            <div class='ghn_customer_col'>{$address}</div>
                                        </div>
                                        <div class='ghn_customer_row'>
                                            <div class='ghn_customer_col'>" . __("Phường/Xã", "ntco-ghn") . "</div>
                                            <div class='ghn_customer_col'>" . ghn_class()->get_name_ward($ward) . "</div>
                                        </div>
                                        <div class='ghn_customer_row'>
                                            <div class='ghn_customer_col'>" . __("Khu vực", "ntco-ghn") . "</div>
                                            <div class='ghn_customer_col'>" . ghn_class()->get_name_city("{$province}_{$district}") . "</div>
                                        </div>
                                    </div>
                                </div>
                                <div class='ntco_option_col ghn_more_wrap'>
                                    <div class='ntco_option_col_title'>
                                        <strong>Thông tin sản phẩm</strong>
                                        <small>Ẩn bớt</small>
                                    </div>
                                    <div class='ghn_more_content'>
                                        <table class='prod_table'>
                                            <thead>
                                                <tr>
                                                    <th>" . __("Tên sp", "ntco-ghn") . "</th>
                                                    <th>" . __("Weight", "ntco-ghn") . "</th>
                                                    <th>" . __("SL", "ntco-ghn") . "</th>
                                                </tr>
                                            </thead>
                                            <tbody>";

		$content_order = "";
		if ($product_list && !is_wp_error($product_list) && !empty($product_list)) {
			foreach ($product_list as $product) {
				$content_order .= "{$product["name"]} x {$product["quantity"]} | ";
				$output .= "<tr>
                            <td>{$product["name"]}</td>
                            <td>{$product["weight"]}</td>
                            <td>{$product["quantity"]}</td>
                        </tr>";
			}
		}

		$output .= "</tbody>
                                        </table>
                                        <textarea name='ghn_contentOrder' id='ghn_contentOrder'>" . esc_textarea($content_order) . "</textarea>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan='2'>
                            <div class='ntco_option_2col ghn_order_customerinfor'>
                                <div class='ntco_option_col'>
                                    <strong>" . __("Gói hàng", "ntco-ghn") . "</strong>
                                    <table class='goihang_table'>
                                        <tbody>
                                            <tr>
                                                <td>" . __("Mã đơn hệ thống", "ntco-ghn") . "</td>
                                                <td><input type='text' name='ghn_ExternalCode' id='ghn_ExternalCode' value='" . ($data["ExternalCode"] ?? "") . "'/></td>
                                            </tr>
                                            <tr>
                                                <td>" . __("Giá trị gói hàng", "ntco-ghn") . "</td>
                                                <td><input type='number' name='ghn_InsuranceFee' id='ghn_InsuranceFee' value='{$data["InsuranceFee"]}'/> " . get_woocommerce_currency_symbol() . "</td>
                                            </tr>
                                            <tr>
                                                <td>" . __("Khối lượng", "ntco-ghn") . "</td>
                                                <td><input type='text' name='ghn_order_weight' id='ghn_order_weight' value='{$data["Weight"]}'> gram</td>
                                            </tr>
                                            <tr>
                                                <td>" . __("Kích thước (cm)", "ntco-ghn") . "</td>
                                                <td class='input_inline'>
                                                    <input type='text' name='ghn_order_length' id='ghn_order_length' value='{$data["Length"]}'> " . __("dài", "ntco-ghn") . "
                                                    <input type='text' name='ghn_order_width' id='ghn_order_width' value='{$data["Width"]}'> " . __("rộng", "ntco-ghn") . "
                                                    <input type='text' name='ghn_order_height' id='ghn_order_height' value='{$data["Height"]}'> " . __("cao", "ntco-ghn") . "
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>" . __("Ghi chú bắt buộc", "ntco-ghn") . " <span class='required'>*</span></td>
                                                <td>
                                                    <select name='ghn_ghichu_required' id='ghn_ghichu_required'>
                                                        <option value='CHOXEMHANGKHONGTHU' " . selected("CHOXEMHANGKHONGTHU", $data["NoteCode"], false) . ">" . __("Cho xem hàng, không cho thử", "ntco-ghn") . "</option>
                                                        <option value='CHOTHUHANG' " . selected("CHOTHUHANG", $data["NoteCode"], false) . ">" . __("Cho thử hàng", "ntco-ghn") . "</option>
                                                        <option value='KHONGCHOXEMHANG' " . selected("KHONGCHOXEMHANG", $data["NoteCode"], false) . ">" . __("Không cho thử hàng", "ntco-ghn") . "</option>
                                                    </select>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>" . __("Ghi chú", "ntco-ghn") . "</td>
                                                <td><textarea name='ghn_ghichu' id='ghn_ghichu'>" . esc_textarea($data["Note"]) . "</textarea></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class='ntco_option_col'>
                                    <strong>" . __("Gói cước", "ntco-ghn") . "</strong>";

		$rates = ghn_api()->order_findAvailableServices($order);
		if ($rates && !empty($rates)) {
			$output .= "<div class='ghn_all_goicuoc'>";
			foreach ($rates as $methob) {
				$ServiceID = isset($methob["ServiceID"]) ? intval($methob["ServiceID"]) : "";
				$ExpectedDeliveryTime = isset($methob["ExpectedDeliveryTime"]) ? date("d/m/Y", strtotime($methob["ExpectedDeliveryTime"])) : "";
				$Name = isset($methob["Name"]) ? esc_attr($methob["Name"]) : "";
				$ServiceFee = isset($methob["ServiceFee"]) ? $methob["ServiceFee"] : "";
				$Extras = isset($methob["Extras"]) ? $methob["Extras"] : [];
				$Extras_all = [];
				foreach ($Extras as $this_extras) {
					if ($this_extras["ServiceID"] != "53337") {
						$Extras_all[] = $this_extras;
					}
				}

				if ($ServiceID) {
					$output .= "<div class='ghn_all_goicuoc_list' data-extras='" . esc_attr(json_encode($Extras_all)) . "'>
                                <div class='ghn_all_goicuoc_col'>
                                    <label><input type='radio' name='ghn_services' data-fee='{$ServiceFee}' value='{$ServiceID}' " . checked($ServiceID, $data["ServiceID"], false) . "> {$Name} - " . wc_price($ServiceFee) . "</label>
                                </div>
                                <div class='ghn_all_goicuoc_col'>" . __("Dự kiến giao", "ntco-ghn") . " {$ExpectedDeliveryTime}</div>
                            </div>";
				}
			}
			$output .= "</div>";
		}

		$output .= "<div class='ghn_all_phuphi'>
                                        <strong>Phụ phí</strong>
                                        <div class='ghn_all_phuphi_list' data-phuphichoose='" . esc_attr(json_encode($data["ShippingOrderCosts"])) . "'></div>
                                    </div>
                                    <div class='ghn_nguoithanhtoan'>
                                        " . __("Người thanh toán:", "ntco-ghn") . "
                                        <label><input type='radio' name='ghn_PaymentTypeID' class='ghn_PaymentTypeID' value='1' " . checked(1, $data["PaymentTypeID"], false) . "> " . __("Người gửi", "ntco-ghn") . "</label>
                                        <label><input type='radio' name='ghn_PaymentTypeID' class='ghn_PaymentTypeID' value='2' " . checked(2, $data["PaymentTypeID"], false) . "> " . __("Người nhận", "ntco-ghn") . "</label>
                                    </div>
                                    <div class='ghn_tienthuho'>
                                        " . __("Tiền thu hộ (COD):", "ntco-ghn") . "
                                        <input type='number' name='ghn_tienthuho' id='ghn_tienthuho' data-total='{$data["InsuranceFee"]}' data-subtotal='{$data["InsuranceFee"]}' data-codamount='{$data["CoDAmount"]}' value='{$data["CoDAmount"]}'/> " . get_woocommerce_currency_symbol() . "
                                    </div>
                                    <div class='ghn_makhuyenmai'>
                                        " . __("Mã khuyến mại:", "ntco-ghn") . "
                                        <input type='text' name='ghn_CouponCode' id='ghn_CouponCode' value='{$data["CouponCode"]}'>
                                    </div>";

		$isPickAtStation = 2;
		foreach ($data["ShippingOrderCosts"] as $item) {
			if ($item["ServiceID"] == 53337) {
				$isPickAtStation = 1;
				$output .= "<div class='ghn_nguoithanhtoan'>
                            " . __("Gửi hàng tại điểm giao dịch (-2.000đ):", "ntco-ghn") . "
                            <label><input type='radio' name='ghn_isPickAtStation' class='ghn_isPickAtStation' value='1' " . checked(1, $isPickAtStation, false) . "> " . __("Có", "ntco-ghn") . "</label>
                            <label><input type='radio' name='ghn_isPickAtStation' class='ghn_isPickAtStation' value='2' " . checked(2, $isPickAtStation, false) . "> " . __("Không", "ntco-ghn") . "</label>
                        </div>
                    </div>
                </div>
            </td>
        </tr>
        <tr>
            <td colspan='2' class='total_order_api'></td>
        </tr>";
			}
		}

		$output .= "</tbody>
                <tfoot>
                    <tr>
                        <td colspan='2' class='text_alignright'>
                            <div class='ghn_msg'></div>
                            <a href='#' class='button button-primary ntco_float_right ntco_ghn_update_order'>" . __("Cập nhật", "ntco-ghn") . "</a>
                            <a href='#' class='button close_popup ntco_float_right'>" . __("Hủy chỉnh sửa", "ntco-ghn") . "</a>
                            <span class='spinner'></span>
                            <input type='hidden' name='ghn_ShippingOrderID' id='ghn_ShippingOrderID' value='{$data["ShippingOrderID"]}'>
                            <input type='hidden' name='ghn_OrderCode' id='ghn_OrderCode' value='{$data["OrderCode"]}'>
                        </td>
                    </tr>
                </tfoot>
            </table>
            <input type='hidden' value='{$order->get_id()}' name='order_id' class='order_id'/>
            <input type='hidden' value='0' name='allow_create_order' class='allow_create_order'/>
        </div>
    </div>";

		echo $output;
	}

	public function ghn_save_meta_box($post_id, $post) {
		$nonce_name = isset($_POST["ghn_action_nonce"]) ? $_POST["ghn_action_nonce"] : "";
		$nonce_action = "ghn_action_nonce_action";
		if (!isset($nonce_name)) {
			return NULL;
		}
		if (!wp_verify_nonce($nonce_name, $nonce_action)) {
			return NULL;
		}
		if (!current_user_can("edit_post", $post_id)) {
			return NULL;
		}
		if (wp_is_post_autosave($post_id)) {
			return NULL;
		}
		if (wp_is_post_revision($post_id)) {
			return NULL;
		}
	}
	public function ntco_ghn_change_hub() {
		if (!wp_verify_nonce($_REQUEST['nonce'], 'ghn_action_nonce_action')) {
			wp_send_json_error('Check nonce failed!');
		}

		$hubid = isset($_POST['hubid']) ? intval($_POST['hubid']) : '';
		$order_id = isset($_POST['order_id']) ? (int) $_POST['order_id'] : '';
		$weight = isset($_POST['weight']) ? (int) $_POST['weight'] : 0;
		$length = isset($_POST['length']) ? (int) $_POST['length'] : 0;
		$width = isset($_POST['width']) ? (int) $_POST['width'] : 0;
		$height = isset($_POST['height']) ? (int) $_POST['height'] : 0;
		$CouponCode = isset($_POST['CouponCode']) ? sanitize_text_field($_POST['CouponCode']) : '';
		$InsuranceFee = isset($_POST['InsuranceFee']) ? floatval($_POST['InsuranceFee']) : '';
		$method_id = isset($_POST['method_id']) ? intval($_POST['method_id']) : '';

		if (!$hubid || !$order_id) {
			wp_send_json_error('Kiểm tra lại dữ liệu gửi vào');
		}

		$args = [
			'weight' => $weight,
			'length' => $length,
			'width' => $width,
			'height' => $height,
			'CouponCode' => $CouponCode,
			'InsuranceFee' => $InsuranceFee
		];

		$order = wc_get_order($order_id);

		if ($order && !is_wp_error($order)) {
			$rates = ghn_api()->order_findAvailableServices($order, $hubid, $args);

			ob_start();
			if ($rates && !empty($rates)) {
				foreach ($rates as $method) {
					$ServiceID = isset($method['service_id']) ? intval($method['service_id']) : '';
					$ExpectedDeliveryTime = isset($method['ExpectedDeliveryTime']) ? date('d/m/Y', strtotime($method['ExpectedDeliveryTime'])) : '';
					$Name = isset($method['short_name']) ? esc_attr($method['short_name']) : '';
					$ServiceFee = isset($method['total']) ? $method['total'] : '';

					if ($ServiceID) {
						$checked = checked($ServiceID, $method_id, false);
						$service_json = esc_attr(json_encode($method));
						$fee_formatted = wc_price($ServiceFee);
						$expected_delivery = $ExpectedDeliveryTime ? "<div class='ghn_all_goicuoc_col'>" . __("Dự kiến giao", "ntco-ghn") . " $ExpectedDeliveryTime</div>" : '';

						echo "
                        <div class='ghn_all_goicuoc_list'>
                            <div class='ghn_all_goicuoc_col'>
                                <label>
                                    <input type='radio' name='ghn_services' data-service='$service_json' data-fee='$ServiceFee' value='$ServiceID' $checked>
                                    $Name - $fee_formatted
                                </label>
                            </div>
                            $expected_delivery
                        </div>
                    ";
					}
				}
			}
			wp_send_json_success(ob_get_clean());
		}

		wp_send_json_error('Có lỗi xảy ra');
	}

	public function ntco_ghn_creat_order() {
		if (!wp_verify_nonce($_REQUEST["nonce"], "ghn_action_nonce_action")) {
			wp_send_json_error("Check nonce failed!");
		}
		$hubID = isset($_POST["hubID"]) ? intval($_POST["hubID"]) : 0;
		$order_ID = isset($_POST["post_ID"]) ? intval($_POST["post_ID"]) : 0;
		$order = wc_get_order($order_ID);
		if (!$order_ID || is_wp_error($order)) {
			wp_send_json_error("Không tìm thấy Order");
		}
		if (!$hubID) {
			wp_send_json_error("Hãy chọn 1 cửa hàng/kho");
		}
		$customer_infor = ghn_class()->get_customer_address_shipping($order);
		$order_content = isset($_POST["ghn_contentOrder"]) ? sanitize_textarea_field($_POST["ghn_contentOrder"]) : "";
		$order_content = mb_substr($order_content, 0, 255);
		$shift_date = isset($_POST["shift_date"]) ? intval($_POST["shift_date"]) : "";
		$data = [
			'shop_id' => $hubID,
			'payment_type_id' => isset($_POST['PaymentTypeID']) ? intval($_POST['PaymentTypeID']) : 1,
			'note' => isset($_POST['noteOrder']) ? sanitize_textarea_field($_POST['noteOrder']) : '',
			'required_note' => isset($_POST['noteCode']) ? sanitize_text_field($_POST['noteCode']) : 'CHOXEMHANGKHONGTHU',
			'return_phone' => '',
			'return_address' => '',
			'return_district_id' => NULL,
			'return_ward_code' => '',
			'client_order_code' => isset($_POST['ExternalCode']) ? sanitize_text_field($_POST['ExternalCode']) : $order_ID,
			'to_name' => isset($customer_infor['name']) ? sanitize_text_field($customer_infor['name']) : '',
			'to_phone' => isset($customer_infor['phone']) ? sanitize_text_field($customer_infor['phone']) : '',
			'to_address' => isset($customer_infor['address']) ? sanitize_text_field($customer_infor['address']) : '',
			'to_ward_code' => isset($customer_infor['ward']) ? sanitize_text_field(ghn_class()->get_ghn_ward_id($customer_infor['ward'])) : '',
			'to_district_id' => isset($customer_infor['district']) ? intval(ghn_class()->get_ghn_district_id($customer_infor['district'])) : 0,
			'cod_amount' => isset($_POST['CoDAmount']) ? (float) $_POST['CoDAmount'] : 0,
			'content' => $order_content,
			'weight' => isset($_POST['ghn_order_weight']) && $_POST['ghn_order_weight'] ? (int) $_POST['ghn_order_weight'] : 0,
			'length' => isset($_POST['ghn_order_length']) && $_POST['ghn_order_length'] ? (int) $_POST['ghn_order_length'] : 1,
			'width' => isset($_POST['ghn_order_width']) && $_POST['ghn_order_width'] ? (int) $_POST['ghn_order_width'] : 1,
			'height' => isset($_POST['ghn_order_height']) && $_POST['ghn_order_height'] ? (int) $_POST['ghn_order_height'] : 1,
			'pick_station_id' => isset($_POST['ghn_isPickAtStation']) ? (int) $_POST['ghn_isPickAtStation'] : 1,
			'deliver_station_id' => 0,
			'insurance_value' => isset($_POST['InsuranceFee']) ? (float) $_POST['InsuranceFee'] : 0,
			'service_id' => isset($_POST['ghn_services']) ? (int) $_POST['ghn_services'] : 0,
			'service_type_id' => isset($_POST['service_type_id']) && $_POST['service_type_id'] ? (int) $_POST['service_type_id'] : ''
		];

		if ($shift_date) {
			$data["pick_shift"] = [$shift_date];
		}
		$order_items = ghn_class()->get_options("order_items");
		if ($order_items) {
			$product_list = vn_checkout()->get_product_args($order);
			if ($product_list && !is_wp_error($product_list)) {
				foreach ($product_list as $k => $item) {
					$name = isset($item["name"]) ? esc_attr($item["name"]) : "";
					$quantity = isset($item["quantity"]) ? floatval($item["quantity"]) : 1;
					$data["items"][$k] = apply_filters("ghn_items_create_order_args", ["name" => $name, "quantity" => $quantity], $item);
				}
			}
			unset($data["content"]);
		}
		$result = $this->createOrder(apply_filters("ghn_pre_data_before_create_order", $data, $order));
		$data_args = isset($result["data"]) ? $result["data"] : [];
		$msg = isset($result["message"]) ? $result["message"] : "";
		if (isset($result["code"]) && $result["code"] == 200) {
			$ghn_ordercode = isset($result["data"]["order_code"]) ? $result["data"]["order_code"] : "";
			if ($ghn_ordercode) {
				$order->update_meta_data("_ghn_order_fullinfor", $result);
				$order->update_meta_data("_ghn_ordercode", $ghn_ordercode);
				$order->update_meta_data("_ghn_order_submited", $data);
				$order->delete_meta_data("_ghn_order_status");
				$order->save();
				$result = ["result_html" => __("Đăng đơn thành công!...", "ntco-ghn"), "ghn_ordercode" => $ghn_ordercode, "ordercode_html" => $this->ordercode_html($ghn_ordercode, $order_ID)];
				do_action("ntco_after_ghn_create_order", $order_ID);
				wp_send_json_success($result);
			}
		}
		wp_send_json_error($msg);
		exit;
	}
	public function ntco_ghn_update_order() {
		if (!wp_verify_nonce($_REQUEST["nonce"], "ghn_action_nonce_action")) {
			wp_send_json_error("Check nonce failed!");
		}
		$hubID = isset($_POST["hubID"]) ? intval($_POST["hubID"]) : 0;
		$ghn_ShippingOrderID = isset($_POST["ghn_ShippingOrderID"]) ? intval($_POST["ghn_ShippingOrderID"]) : 0;
		$ghn_OrderCode = isset($_POST["ghn_OrderCode"]) ? sanitize_text_field($_POST["ghn_OrderCode"]) : "";
		$order_ID = isset($_POST["post_ID"]) ? intval($_POST["post_ID"]) : 0;
		$order = wc_get_order($order_ID);
		if (!$order_ID || is_wp_error($order)) {
			wp_send_json_error("Không tìm thấy Order");
		}
		if (!$hubID) {
			wp_send_json_error("Hãy chọn 1 cửa hàng/kho");
		}
		$customer_infor = ghn_class()->get_customer_address_shipping($order);
		$extras_all = [];
		$extras = isset($_POST["ghn_extras"]) && !empty($_POST["ghn_extras"]) ? $_POST["ghn_extras"] : [];
		if (!empty($extras)) {
			foreach ($extras as $ext) {
				$extras_all[] = ["ServiceID" => intval($ext)];
			}
		}
		$shift_date = isset($_POST["shift_date"]) ? intval($_POST["shift_date"]) : "";
		$data = [
			'ShippingOrderID' => $ghn_ShippingOrderID,
			'OrderCode' => $ghn_OrderCode,
			'PaymentTypeID' => isset($_POST['PaymentTypeID']) ? intval($_POST['PaymentTypeID']) : 1,
			'ClientContactName' => $this->get_hub_by_id($hubID, 'ContactName'),
			'ClientContactPhone' => $this->get_hub_by_id($hubID, 'ContactPhone'),
			'ClientAddress' => $this->get_hub_by_id($hubID, 'Address'),
			'ClientHubID' => $hubID,
			'FromDistrictID' => $this->get_hub_by_id($hubID, 'DistrictID'),
			'ToDistrictID' => isset($customer_infor['district']) ? intval($customer_infor['district']) : 0,
			'ToWardCode' => isset($customer_infor['ward']) ? sanitize_text_field($customer_infor['ward']) : '',
			'Note' => isset($_POST['noteOrder']) ? sanitize_textarea_field($_POST['noteOrder']) : '',
			'CustomerName' => isset($customer_infor['name']) ? sanitize_text_field($customer_infor['name']) : '',
			'CustomerPhone' => isset($customer_infor['phone']) ? sanitize_text_field($customer_infor['phone']) : '',
			'ShippingAddress' => isset($customer_infor['address']) ? sanitize_text_field($customer_infor['address']) : '',
			'CoDAmount' => isset($_POST['CoDAmount']) ? (float) $_POST['CoDAmount'] : 0,
			'NoteCode' => isset($_POST['noteCode']) ? sanitize_text_field($_POST['noteCode']) : '',
			'InsuranceFee' => isset($_POST['InsuranceFee']) ? (float) $_POST['InsuranceFee'] : 0,
			'ServiceID' => isset($_POST['ghn_services']) ? (int) $_POST['ghn_services'] : 0,
			'Content' => isset($_POST['ghn_contentOrder']) ? sanitize_textarea_field($_POST['ghn_contentOrder']) : '',
			'CouponCode' => isset($_POST['ghn_CouponCode']) ? sanitize_text_field($_POST['ghn_CouponCode']) : '',
			'Weight' => isset($_POST['ghn_order_weight']) && $_POST['ghn_order_weight'] ? (int) $_POST['ghn_order_weight'] : 0,
			'Length' => isset($_POST['ghn_order_length']) && $_POST['ghn_order_length'] ? (int) $_POST['ghn_order_length'] : 1,
			'Width' => isset($_POST['ghn_order_width']) && $_POST['ghn_order_width'] ? (int) $_POST['ghn_order_width'] : 1,
			'Height' => isset($_POST['ghn_order_height']) && $_POST['ghn_order_height'] ? (int) $_POST['ghn_order_height'] : 1,
			'ShippingOrderCosts' => $extras_all
		];

		if ($shift_date) {
			$data["pick_shift"] = [$shift_date];
		}
		$result = $this->updateOrder($data);
		$data_args = isset($result["data"]) ? $result["data"] : [];
		$msg = isset($result["msg"]) ? $result["msg"] : "";
		if (isset($result["code"]) && $result["code"] == 0) {
			$data_msg = $msg . "\\n";
			foreach ($data_args as $k => $v) {
				$data_msg .= $v . "\\n";
			}
			wp_send_json_error($data_msg);
		} else {
			if (isset($result["code"]) && $result["code"] == 1) {
				$order->update_meta_data("_ghn_order_fullinfor", $result);
				$order->update_meta_data("_ghn_order_submited", $data);
				$result = ["result_html" => __("Cập nhật thành công!...", "ntco-ghn"), "ghn_ordercode" => $ghn_OrderCode, "ordercode_html" => $this->ordercode_html($ghn_OrderCode, $order_ID)];
				wp_send_json_success($result);
			}
		}
		wp_send_json_error(__("Lỗi không xác định", "ntco-ghn"));
		exit;
	}
	public function ntco_ghn_cancel_order() {
		if (!wp_verify_nonce($_REQUEST["nonce"], "ghn_action_nonce_action")) {
			wp_send_json_error("Check nonce failed!");
		}
		$ordercode = isset($_POST["ordercode"]) ? sanitize_text_field($_POST["ordercode"]) : "";
		$post_ID = isset($_POST["post_ID"]) ? sanitize_text_field($_POST["post_ID"]) : "";
		if ($ordercode) {
			$args = ["data" => ["order_codes" => [$ordercode]], "action" => "v2/switch-status/cancel"];
			$result = $this->get_cURL($args);
			$msg = isset($result["message"]) ? sanitize_text_field($result["message"]) : "";
			if (isset($result["code"]) && $result["code"] == 200) {
				$order = wc_get_order($post_ID);
				if ($order && !is_wp_error($order)) {
					$order->delete_meta_data("_ghn_ordercode");
					$order->delete_meta_data("_ghn_order_submited");
					$order->delete_meta_data("_ghn_order_fullinfor");
					$order->delete_meta_data("_ghn_order_status");
					$order->save();
				}
				ob_start();
				$this->ghn_order_action_callback(wc_get_order($post_ID));
				$html = ob_get_clean();
				wp_send_json_success(["mess" => __("Đã hủy đơn hàng thành công", "ntco-ghn"), "fragments" => [".ghn_ordercode_html_wrap_" . $post_ID => $html]]);
			} else {
				wp_send_json_error($msg);
			}
		}
		exit;
	}
	public function get_status_text($CurrentStatus) {
		$text = __("Không xác định", "ntco-ghn");
		switch ($CurrentStatus) {
			case "ReadyToPick":
			case "ready_to_pick":
				$text = __("Đơn hàng mới tạo", "ntco-ghn");
				break;
			case "Picking":
			case "picking":
				$text = __("Đang lấy hàng", "ntco-ghn");
				break;
			case "money_collect_picking":
				$text = __("Đang thu tiền người gửi", "ntco-ghn");
				break;
			case "picked":
				$text = __("Nhân viên đã lấy hàng", "ntco-ghn");
				break;
			case "Storing":
			case "storing":
				$text = __("Hàng đã được lưu kho", "ntco-ghn");
				break;
			case "transporting":
				$text = __("Đang luân chuyển hàng", "ntco-ghn");
				break;
			case "sorting":
				$text = __("Đang phân loại hàng hóa", "ntco-ghn");
				break;
			case "Delivering":
			case "delivering":
				$text = __("Đang giao hàng", "ntco-ghn");
				break;
			case "money_collect_delivering":
				$text = __("Nhân viên đang thu tiền người nhận", "ntco-ghn");
				break;
			case "Delivered":
			case "delivered":
				$text = __("Giao thành công", "ntco-ghn");
				break;
			case "delivery_fail":
				$text = __("Nhân viên giao hàng thất bại", "ntco-ghn");
				break;
			case "waiting_to_return":
				$text = __("Đang đợi trả hàng về cho người gửi", "ntco-ghn");
				break;
			case "return":
			case "Return":
				$text = __("Chờ trả hàng", "ntco-ghn");
				break;
			case "return_transporting":
				$text = __("Đang luân chuyển hàng trả", "ntco-ghn");
				break;
			case "return_sorting":
				$text = __("Đang phân loại hàng trả", "ntco-ghn");
				break;
			case "returning":
				$text = __("Nhân viên đang đi trả hàng", "ntco-ghn");
				break;
			case "return_fail":
				$text = __("Nhân viên trả hàng thất bại", "ntco-ghn");
				break;
			case "Returned":
			case "returned":
				$text = __("Trả thành công", "ntco-ghn");
				break;
			case "exception":
				$text = __("Đơn hàng ngoại lệ không nằm trong quy trình", "ntco-ghn");
				break;
			case "damage":
				$text = __("Hàng bị hư hỏng", "ntco-ghn");
				break;
			case "WaitingToFinish":
				$text = __("Chờ thanh toán/chuyển COD", "ntco-ghn");
				break;
			case "Finish":
				$text = __("Đơn hàng hoàn tất", "ntco-ghn");
				break;
			case "Cancel":
			case "cancel":
				$text = __("Đã hủy", "ntco-ghn");
				break;
			case "LostOrder":
				$text = __("Hàng thất lạc", "ntco-ghn");
				break;
		}
		return $text;
	}
	public function ntco_ghn_tracking_order() {
		if (!wp_verify_nonce($_REQUEST["nonce"], "ghn_action_nonce_action")) {
			wp_send_json_error("Check nonce failed!");
		}
		$ordercode = isset($_POST["ordercode"]) ? sanitize_text_field($_POST["ordercode"]) : "";
		$post_ID = isset($_POST["post_ID"]) ? sanitize_text_field($_POST["post_ID"]) : "";
		if ($ordercode) {
			$args = ["data" => ["token" => $this->token, "OrderCode" => $ordercode], "action" => "OrderInfo"];
			$result = $this->get_cURL($args);
			$msg = isset($result["msg"]) ? sanitize_text_field($result["msg"]) : "";
			$data_args = isset($result["data"]) ? $result["data"] : [];
			if (isset($result["code"]) && $result["code"] == 1) {
				$CurrentStatus = isset($result["data"]["CurrentStatus"]) ? $result["data"]["CurrentStatus"] : "";
				$name = $this->get_status_text($CurrentStatus);
				$order = wc_get_order($post_ID);
				if ($order && !is_wp_error($order)) {
					$order->update_meta_data("_ghn_order_status", $CurrentStatus);
					$order->save();
				}
				$this->change_order_status($post_ID, $CurrentStatus);
				wp_send_json_success(sprintf(__("Trạng thái đơn hàng: %s", "ntco-ghn"), $name));
			} else {
				$data_msg = $msg . "\\n";
				foreach ($data_args as $k => $v) {
					$data_msg .= $v . "\\n";
				}
				wp_send_json_error($data_msg);
			}
		}
		wp_send_json_error(__("Lỗi không xác định", "ntco-ghn"));
		exit;
	}
	public function setWebhook($data = []) {
		if (!is_array($data) || empty($data)) {
			return false;
		}
		$webhook_url = isset($data["webhook_url"]) ? esc_url_raw($data["webhook_url"]) : "";
		if ($webhook_url) {
			$args = ["data" => ["token" => $this->token, "TokenClient" => [$this->token], "ConfigCod" => true, "ConfigReturnData" => true, "URLCallback" => $webhook_url, "ConfigField" => ["CoDAmount" => true, "CurrentWarehouseName" => true, "CustomerID" => true, "CustomerName" => true, "CustomerPhone" => true, "Note" => true, "OrderCode" => true, "ServiceName" => true, "ShippingOrderCosts" => true, "Weight" => true, "ReturnInfo" => true, "ExternalCode" => true], "ConfigStatus" => ["Cancel" => true, "Delivered" => true, "Delivering" => true, "Finish" => true, "LostOrder" => true, "Picking" => true, "ReadyToPick" => true, "Return" => true, "Returned" => true, "Storing" => true, "WaitingToFinish" => true]], "action" => "SetConfigClient"];
			$result = $this->get_cURL($args);
			return $result;
		}
		return false;
	}
	public function ntco_ghn_set_webhook() {
		if (!wp_verify_nonce($_REQUEST["nonce"], "webhook_nonce")) {
			wp_send_json_error("Check nonce failed!");
		}
		$webhook_url = isset($_POST["webhook_url"]) ? esc_url_raw($_POST["webhook_url"]) : "";
		if (!$webhook_url) {
			wp_send_json_error(__("Webhook URL không được để trống!", "ntco-ghn"));
		}
		$data = ["webhook_url" => $webhook_url];
		$result = $this->setWebhook($data);
		$data_args = isset($result["data"]) ? $result["data"] : [];
		$msg = isset($result["msg"]) ? $result["msg"] : "";
		if (isset($result["code"]) && $result["code"] == 0) {
			$data_msg = $msg . "\\n";
			foreach ($data_args as $k => $v) {
				$data_msg .= $v . "\\n";
			}
			wp_send_json_error($data_msg);
		} else {
			if (isset($result["code"]) && $result["code"] == 1) {
				wp_send_json_success(__("Đăng ký webhook thành công. Đang lưu cài đặt và tải lại trang web ..."));
			}
		}
		wp_send_json_error(__("Lỗi không xác định", "ntco-ghn"));
		exit;
	}
	public function change_order_status($orderID, $status = "") {
		if ($orderID) {
			$order = wc_get_order($orderID);
			if ($order && !is_wp_error($order)) {
				switch ($status) {
					case "cancel":
					case "Cancel":
						$order->delete_meta_data("_ghn_ordercode");
						$order->delete_meta_data("_ghn_order_submited");
						$order->delete_meta_data("_ghn_order_fullinfor");
						$order->delete_meta_data("_ghn_order_status");
						$order->set_status("cancelled");
						break;
					case "Finish":
					case "WaitingToFinish":
					case "delivered":
					case "Delivered":
						$order->set_status("completed");
						break;
				}
				$order->save();
			}
		}
	}
	public function ntco_ghn_webhook_func() {
		$POST = json_decode(file_get_contents("php://input"), true);
		if (isset($_POST) && empty($_POST)) {
			$_POST = $POST;
		}
		if (apply_filters("enable_debug_webhook", false)) {
			$log = "User: " . $_SERVER["REMOTE_ADDR"] . " - " . date("F j, Y, g:i a") . PHP_EOL . json_encode($_POST) . PHP_EOL . json_encode($_GET) . PHP_EOL . "-------------------------" . PHP_EOL;
			file_put_contents(NTCO_GHNV2_PLUGIN_DIR . "ghn_log.txt", $log, FILE_APPEND);
		}
		$hash = isset($_GET["hash"]) ? sanitize_text_field($_GET["hash"]) : "";
		$options = wp_parse_args(get_option($this->_ghn_webhook), $this->_defaultWebhookOptions);
		$webhook_hash = $options["webhook_hash"];
		if ($hash && $webhook_hash && $webhook_hash == $hash) {
			$CurrentStatus = isset($_POST["Status"]) ? sanitize_text_field($_POST["Status"]) : "";
			$OrderCode = isset($_POST["OrderCode"]) ? sanitize_text_field($_POST["OrderCode"]) : "";
			if ($OrderCode && $CurrentStatus) {
				$params = ["posts_per_page" => 1, "post_status" => "any", "meta_query" => ["ghn" => ["key" => "_ghn_ordercode", "value" => $OrderCode]], "meta_key" => "_ghn_ordercode", "meta_value" => $OrderCode];
				$orders = wc_get_orders($params);
				if ($orders && !is_wp_error($orders)) {
					foreach ($orders as $order) {
						$order->update_meta_data("_ghn_order_status", $CurrentStatus);
						$order->save();
						$this->change_order_status($order->get_id(), $CurrentStatus);
					}
				}
				wp_send_json_success("Cập nhật thành công!");
			}
		}
		wp_send_json_error("Lỗi", 500);
		exit;
	}
	public function ghn_creat_order_ajax_func() {
		$orderid = isset($_POST["orderid"]) ? intval($_POST["orderid"]) : "";
		$result = [];
		if ($orderid) {
			$order = wc_get_order($orderid);
			$customer_infor = ghn_class()->get_customer_address_shipping($order);
			extract($customer_infor);
			$shipping_methods = $order->get_shipping_methods();
			$HubID_Order = "";
			$method_id = "";
			foreach ($shipping_methods as $shipping_method) {
				foreach ($shipping_method->get_formatted_meta_data() as $meta_data) {
					if ($meta_data->key && $meta_data->key == "HubID" && !$HubID_Order) {
						$HubID_Order = $meta_data->value;
					}
				}
				foreach ($shipping_method->get_formatted_meta_data() as $meta_data) {
					if ($meta_data->key && $meta_data->key == "ServiceID" && !$method_id) {
						$method_id = $meta_data->value;
					}
				}
			}
			$product_list = ghn_class()->get_product_args($order);
			ob_start();
			include "ghn-creat-order-html.php";
			$result["result_html"] = ob_get_clean();
			wp_send_json_success($result);
		}
		wp_send_json_error();
		exit;
	}
	public function ntco_ghn_order_action($order) {
		$this->ghn_order_action_callback($order);
	}
}
function ghn_shipping_method_init() {
	if (!class_exists("WC_GHN_Shipping_Method")) {
		class WC_GHN_Shipping_Method extends WC_Shipping_Method {
			public $ghn_mess = "";
			public function __construct() {
				$this->id = "ghn_shipping_method";
				$this->method_title = __("Giao hàng nhanh (GHN)");
				$this->method_description = __("Tính phí vận chuyển và đồng bộ đơn hàng với giao hàng nhanh (GHN)");
				$this->init();
				$this->enabled = $this->settings["enabled"];
				$this->title = $this->settings["title"];
			}
			public function init() {
				$this->init_form_fields();
				$this->init_settings();
				add_action("woocommerce_update_options_shipping_" . $this->id, [$this, "process_admin_options"]);
			}
			public function init_form_fields() {
				$this->form_fields = ["enabled" => ["title" => __("Kích hoạt", "ntco-ghn"), "type" => "checkbox", "label" => __("Kích hoạt tính phí vận chuyển bằng GHN", "ntco-ghn"), "default" => "yes"], "title" => ["title" => __("Tiêu đề", "ntco-ghn"), "type" => "text", "description" => __("Mô tả cho phương thức vận chuyển", "ntco-ghn"), "default" => __("Vận chuyển qua GHN", "ntco-ghn")]];
			}
			public function calculate_shipping($package = []) {
				$payment_methob = WC()->session->get("chosen_payment_method");
				$rates = ghn_api()->findAvailableServices($package, "", $payment_methob);
				if ($rates && !empty($rates)) {
					foreach ($rates as $methob) {
						$HubID = isset($methob["_id"]) ? $methob["_id"] : "";
						$total = isset($methob["total"]) ? intval($methob["total"]) : 0;
						$service_id = isset($methob["service_id"]) ? intval($methob["service_id"]) : "";
						$service_type_id = isset($methob["service_type_id"]) ? intval($methob["service_type_id"]) : "";
						$short_name = isset($methob["short_name"]) ? sanitize_text_field(apply_filters("ghn_service_name", $methob["short_name"], $service_id)) : "";
						if ($service_id && $service_type_id && !in_array($service_type_id, apply_filters("ghn_exclude_service", []))) {
							$rate = ["id" => $this->id . "_" . $service_id, "label" => $short_name, "cost" => $total, "calc_tax" => "per_item", "meta_data" => ["ExpectedDeliveryTime" => isset($methob["ExpectedDeliveryTime"]) ? date("d/m/Y", strtotime($methob["ExpectedDeliveryTime"])) : "", "HubID" => $HubID, "ServiceID" => $service_id, "ServiceTypeID" => $service_type_id]];
							$this->add_rate($rate);
						}
					}
				}
			}
			public function ntco_no_shipping_cart() {
				return $this->ghn_mess;
			}
		}
	}
}
function add_ghn_shipping_method($methods) {
	$methods["ghn_shipping_method"] = "WC_GHN_Shipping_Method";
	return $methods;
}
function ghn_api() {
	return NTCO_GHN_API::instance();
}



function ntco_custom_ghn_service_name($service_name, $service_id) {
	switch ($service_name) {
		case "Bay":
			$service_name = "GHN - Nhanh";
			break;
		case "Đi bộ":
			$service_name = "GHN - Tiêu chuẩn";
			break;
	}
	return $service_name;
}

function ghn_append_sku_to_item_name($args, $item) {
    if (!empty($item['sku'])) {
        $args['name'] .= ' - ' . $item['sku'];
    }
    return $args;
}
add_filter('ghn_items_create_order_args', 'ghn_append_sku_to_item_name', 10, 2);
