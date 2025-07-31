<?php

if (!defined("ABSPATH")) {
    exit;
}
if (!class_exists("NTCO_vncheckout_VND_PayPal_Standard")) {
    class NTCO_vncheckout_VND_PayPal_Standard {
        protected $exchange_rate_to_vnd;
        protected $paypal_currency = "USD";
        public function __construct($exchange_rate_to_vnd = 22745, $paypal_currency = "") {
            $this->exchange_rate_to_vnd = (int) $exchange_rate_to_vnd;
            $this->paypal_currency = $paypal_currency;
            add_filter("woocommerce_paypal_supported_currencies", [$this, "add_vnd_paypal_valid_currency"]);
            add_filter("woocommerce_paypal_args", [$this, "convert_prices"], 11);
            add_filter("option_woocommerce_paypal_settings", [$this, "add_exchange_rate_info"], 11);
        }
        public function add_vnd_paypal_valid_currency($currencies) {
            array_push($currencies, "VND");
            return $currencies;
        }
        public function convert_prices($paypal_args) {
            if ($paypal_args["currency_code"] == "VND") {
                $paypal_args["currency_code"] = $this->paypal_currency;
                for ($i = 1; isset($paypal_args["amount_" . $i]); $i++) {
                    $paypal_args["amount_" . $i] = round($paypal_args["amount_" . $i] / $this->exchange_rate_to_vnd, 2);
                }
                if (0 < $paypal_args["shipping_1"]) {
                    $paypal_args["shipping_1"] = round($paypal_args["shipping_1"] / $this->exchange_rate_to_vnd, 2);
                }
                if (0 < $paypal_args["discount_amount_cart"]) {
                    $paypal_args["discount_amount_cart"] = round($paypal_args["discount_amount_cart"] / $this->exchange_rate_to_vnd, 2);
                }
                if (0 < $paypal_args["tax_cart"]) {
                    $paypal_args["tax_cart"] = round($paypal_args["tax_cart"] / $this->exchange_rate_to_vnd, 2);
                }
            }
            return $paypal_args;
        }
        public function add_exchange_rate_info($value) {
            if (!is_admin()) {
                if (!isset($value["description"])) {
                    $value["description"] = "";
                }
                $value["description"] .= "<br />";
                $value["description"] .= sprintf(__("The prices will be converted to %1\$s in the PayPal pages with the exchange rate %2\$s.", "ntco-vn-checkout"), "<span style='color:red'> " . $this->paypal_currency . "</span>", "<span style='color:red'> " . $this->paypal_currency . " / VND = " . $this->exchange_rate_to_vnd . "</span>");
            }
            return $value;
        }
    }
}
