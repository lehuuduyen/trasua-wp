<?php

if (!function_exists("devn_freeshipping_by_payment_method_init")) {
    function devn_freeshipping_by_payment_method_init() {
        if (!class_exists("NTCO_FreeShipping_by_PaymentMethob")) {
            class NTCO_FreeShipping_by_PaymentMethob extends WC_Shipping_Method {
                public $_payment_methob_text = "";
                public $_payment_methob_default = [];
                public $hide_other_shipping = "";
                public $total_amount = "";
                public function __construct() {
                    $this->id = "ntco_freeshipping_by_paymentmethod";
                    $this->method_title = __("Freeship by payment method", "ntco-vn-checkout");
                    $this->method_description = __("Tùy chọn miễn phí vận chuyển cho hình thức thanh toán", "ntco-vn-checkout");
                    $this->init();
                    $this->enabled = $this->settings["enabled"];
                    $this->title = $this->settings["title"];
                    $this->_payment_methob_text = isset($this->settings["payment_methob_text"]) ? $this->settings["payment_methob_text"] : "";
                    $this->hide_other_shipping = $this->settings["hide_other_shipping"];
                    $this->total_amount = $this->settings["total_amount"];
                }
                public function init() {
                    $this->init_form_fields();
                    $this->init_settings();
                    add_action("woocommerce_update_options_shipping_" . $this->id, [$this, "process_admin_options"]);
                    add_filter("woocommerce_package_rates", [$this, "ntco_hide_shipping_when_free_is_available"], 100);
                }
                public function init_form_fields() {
                    $this->form_fields = ["enabled" => ["title" => __("Kích hoạt", "ntco-vn-checkout"), "type" => "checkbox", "label" => __("Kích hoạt miễn phí vận chuyển theo hình thức thanh toán", "ntco-vn-checkout"), "default" => "no"], "title" => ["title" => __("Tiêu đề", "ntco-vn-checkout"), "type" => "text", "description" => __("Mô tả cho phương thức vận chuyển", "ntco-vn-checkout"), "default" => __("Miễn phí vận chuyển", "ntco-vn-checkout")], "payment_methob_text" => ["title" => __("Chọn hình thức thanh toán", "ntco-vn-checkout"), "type" => "text", "description" => __("Nhập slug phương thức thanh toán. Mỗi phương thức cách nhau dấy phẩy. Ví dụ bacs, cheque, cod, paypal", "ntco-vn-checkout"), "default" => ""], "total_amount" => ["title" => __("Tổng đơn hàng", "ntco-vn-checkout"), "type" => "number", "description" => __("Tổng đơn hàng nhỏ nhất được áp dụng miễn phí vận chuyển", "ntco-vn-checkout"), "default" => 0], "hide_other_shipping" => ["title" => __("Hình thức giao hàng", "ntco-vn-checkout"), "label" => __("Ẩn những hình thức giao hàng khác nếu có miễn phí vận chuyển theo hình thức thanh toán", "ntco-vn-checkout"), "type" => "checkbox", "default" => "no"]];
                }
                public function calculate_shipping($package = []) {
                    $_payment_methob = WC()->session->get("chosen_payment_method");
                    $_subtotal = WC()->cart->get_subtotal();
                    if (in_array($_payment_methob, explode(", ", $this->_payment_methob_text)) && (!$this->total_amount || $this->total_amount <= $_subtotal)) {
                        $this->add_rate(["label" => $this->title, "cost" => 0, "taxes" => false, "package" => $package]);
                    }
                }
                public function ntco_hide_shipping_when_free_is_available($rates) {
                    $free = [];
                    if ($this->hide_other_shipping == "yes") {
                        foreach ($rates as $rate_id => $rate) {
                            if (in_array($rate->method_id, apply_filters("ntco_freeshipping_methob", ["free_shipping", "ntco_freeshipping_by_paymentmethod"]))) {
                                $free[$rate_id] = $rate;
                            }
                        }
                    }
                    return !empty($free) ? $free : $rates;
                }
            }
        }
    }
    add_action("woocommerce_shipping_init", "devn_freeshipping_by_payment_method_init");
}
if (!function_exists("ntco_freeshipping_by_paymentmethod_func")) {
    function ntco_freeshipping_by_paymentmethod_func($methods) {
        $methods["ntco_freeshipping_by_paymentmethod"] = "NTCO_FreeShipping_by_PaymentMethob";
        return $methods;
    }
    add_filter("woocommerce_shipping_methods", "ntco_freeshipping_by_paymentmethod_func");
}
