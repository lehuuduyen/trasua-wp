<?php

if (!defined("ABSPATH")) {
	exit;
}
if (!function_exists("woo_district_change_existing_currency_symbol")) {
	add_filter("woocommerce_currency_symbol", "woo_district_change_existing_currency_symbol", 10, 2);
	function woo_district_change_existing_currency_symbol($currency_symbol, $currency) {
		global $vncheckout_settings;
		if ($vncheckout_settings["to_vnd"]) {
			switch ($currency) {
				case "VND":
					$currency_symbol = "VND";
					break;
			}
		}
		return $currency_symbol;
	}
}
if (!function_exists("ghtk_remove_shipping_label")) {
	add_filter("woocommerce_cart_shipping_method_full_label", "ghtk_remove_shipping_label", 10, 2);
	function ghtk_remove_shipping_label($label, $method) {
		global $vncheckout_settings;
		if ($vncheckout_settings["remove_methob_title"]) {
			$label = preg_replace("/^.+:/", "", $label);
		}
		return $label;
	}
}
if ($vncheckout_settings["active_vnd2usd"]) {
	include "class-vncheckout-vnd-paypal-standard.php";
	new NTCO_vncheckout_VND_PayPal_Standard($vncheckout_settings["vnd_usd_rate"], $vncheckout_settings["vnd2usd_currency"]);
}
if ($vncheckout_settings["freeship_remove_other_methob"] && !function_exists("dwas_hide_shipping_when_free_is_available")) {
	function dwas_hide_shipping_when_free_is_available($rates) {
		$free = [];
		foreach ($rates as $rate_id => $rate) {
			if (in_array($rate->get_method_id(), apply_filters("ntco_freeshipping_methob", ["free_shipping", "ntco_freeshipping_by_paymentmethod"]))) {
				$free[$rate_id] = $rate;
				return !empty($free) ? $free : $rates;
			}
		}
	}
	add_filter("woocommerce_package_rates", "dwas_hide_shipping_when_free_is_available", 100);
}
add_filter("formatted_woocommerce_price", "vn_checkout_formatted_woocommerce_price_func", 10, 2);
add_filter("woocommerce_currency_symbol", "vn_checkout_woocommerce_currency_symbol_func", 10, 2);
function ghtk_woocommerce_checkout_update_customer($customer, $data) {
	$customer->set_billing_country("VN");
	$customer->set_shipping_country("VN");
	$customer->save();
}
function vn_checkout_formatted_woocommerce_price_func($formatted_price, $price) {
	global $vncheckout_settings;
	if (!$vncheckout_settings["convert_price_text"]) {
		return $formatted_price;
	}
	if ($price < 1000) {
		return $formatted_price;
	}
	$str = "";
	$f = number_format($price);
	$r = explode(",", $f);
	count($r);
	switch (count($r)) {
		case 4:
			$str = $r[0] . "tỷ";
			if ((int) $r[1]) {
				$str .= $r[1];
			}
			break;
		case 3:
			$str = $r[0] . "tr";
			if ((int) $r[1]) {
				$str .= $r[1];
			}
			break;
		case 2:
			$str = $r[0] . "k";
			if ((int) $r[1]) {
				$str .= $r[1];
			}
			break;
	}
	return apply_filters("ntco_formatted_woocommerce_price", $str, $r);
}
function vn_checkout_woocommerce_currency_symbol_func($currency_symbol, $currency) {
	global $vncheckout_settings;
	if (!$vncheckout_settings["convert_price_text"]) {
		return $currency_symbol;
	}
	switch ($currency) {
		case "VND":
			$currency_symbol = "";
			break;
	}
	return $currency_symbol;
}
