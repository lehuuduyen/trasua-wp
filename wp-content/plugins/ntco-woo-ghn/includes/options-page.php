<?php

defined("ABSPATH") or exit("No script kiddies please!");

$current_tab = isset($_REQUEST["tab"]) ? esc_html($_REQUEST["tab"]) : "ghn";

echo '<div class="wrap">
    <h1 class="ghn_title">' . __("Giao hàng nhanh", "ntco-ghn") . '</h1>';

$ntco_note_done = (int) get_option("ntco_note_done");
$woocommerce_default_customer_address = get_option("woocommerce_default_customer_address");
$woocommerce_default_country = get_option("woocommerce_default_country");
$woocommerce_dimension_unit = get_option("woocommerce_dimension_unit");
$woocommerce_weight_unit = get_option("woocommerce_weight_unit");

if ($ntco_note_done < NTCO_GHNV2_NOTE_VERSION || $woocommerce_weight_unit != "g" || $woocommerce_dimension_unit != "cm" || !$woocommerce_default_country || false === strpos($woocommerce_default_country, "VN:") || $woocommerce_default_customer_address != "base") {
	echo '<div class="ntco_note">
        <fieldset>
            <legend>Những lưu ý để plugin hoạt động tốt nhất</legend>
            <ul>
                <li>Sản phẩm BẮT BUỘC phải thêm <strong>khối lượng hoặc kích thước</strong> (nếu là hàng to)</li>';

	if ($woocommerce_dimension_unit != "cm") {
		echo '<li>Kích thước sản phẩm để là <strong>cm</strong>. Thay đổi <a href="' . admin_url("admin.php?page=wc-settings&tab=products#woocommerce_dimension_unit") . '">tại đây</a></li>';
	}
	if ($woocommerce_weight_unit != "g") {
		echo '<li>Trọng lượng sản phẩm để là <strong>g</strong>. Thay đổi <a href="' . admin_url("admin.php?page=wc-settings&tab=products#woocommerce_weight_unit") . '">tại đây</a></li>';
	}

	if ($woocommerce_default_customer_address != "base") {
		echo '<li>Địa chỉ khách hàng mặc định hãy để là địa chỉ của hàng mặc định</li>';
	}

	if (!$woocommerce_default_country || false === strpos($woocommerce_default_country, "VN:")) {
		echo '<li>Hãy cài đặt địa chỉ "Quốc gia / Thành phố" cửa hàng <a href="' . admin_url("admin.php?page=wc-settings") . '" target="_blank">tại đây</a></li>';
	}

	echo '</ul>
            <hr>
            Nếu bạn đã thực hiện xong các chú ý trên hãy ấn <a href="javascript:void(0)" class="ntco_note_done" data-nonce="' . wp_create_nonce("nonce_note_done") . '">Đã xong</a> để ẩn thông báo này
        </fieldset>
    </div>';
}
//NTCO_2
echo '<h2 class="nav-tab-wrapper ntco-nav-tab-wrapper">
    <a href="?page=ntco-woo-ghn&tab=ghn" class="nav-tab ' . ($current_tab == "ghn" ? "nav-tab-active" : "") . '">' . __("Cài đặt GHN", "ntco-ghn") . '</a>
    <a href="?page=ntco-woo-ghn&tab=hubs" class="nav-tab ' . ($current_tab == "hubs" ? "nav-tab-active" : "") . '">' . __("Cửa hàng/Kho", "ntco-ghn") . '</a>
    <a href="?page=ntco-woo-ghn&tab=webhook" class="nav-tab ' . ($current_tab == "webhook" ? "nav-tab-active" : "") . '">' . __("Cập nhật trạng thái tự động", "ntco-ghn") . '</a>
</h2>';

switch ($current_tab) {
	case "ghn":
		include "options-generals.php";
		break;
	case "hubs":
		include "options-allhubs.php";
		break;
	case "webhook":
		include "options-webhook.php";
		break;
	default:
		echo "</div>";
}
