<?php

global $vncheckout_settings;

echo '<form method="post" action="options.php" novalidate="novalidate">';
settings_fields($this->_optionGroup);

echo '<h2>Checkout field</h2>
    <table class="form-table infor-shop">
        <tbody>
            <tr>
                <th scope="row"><label for="activeplugin">' . __("Ẩn mục phường/xã", "ntco-vn-checkout") . '</label></th>
                <td>
                    <label><input type="checkbox" name="' . $this->_optionName . '[active_village]" ' . checked("1", $vncheckout_settings["active_village"], false) . ' value="1" /> ' . __("Ẩn mục phường/xã", "ntco-vn-checkout") . '</label>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="required_village">' . __("KHÔNG bắt buộc nhập phường/xã", "ntco-vn-checkout") . '</label></th>
                <td>
                    <label><input type="checkbox" name="' . $this->_optionName . '[required_village]" ' . checked("1", $vncheckout_settings["required_village"], false) . ' value="1" /> ' . __("Không bắt buộc", "ntco-vn-checkout") . '</label>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="alepay_support">' . __("Show country/Last name", "ntco-vn-checkout") . '</label></th>
                <td>
                    <label><input type="checkbox" name="' . $this->_optionName . '[alepay_support]" ' . checked("1", $vncheckout_settings["alepay_support"], false) . ' value="1" /> ' . __("Show country/Last name", "ntco-vn-checkout") . '</label>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="enable_postcode">' . __("Show postcode for Viet Nam", "ntco-vn-checkout") . '</label></th>
                <td>
                    <label><input type="checkbox" name="' . $this->_optionName . '[enable_postcode]" ' . checked("1", $vncheckout_settings["enable_postcode"], false) . ' value="1" /> ' . __("Show postcode for Viet Nam", "ntco-vn-checkout") . '</label>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="show_postcode">' . __("Show postcode", "ntco-vn-checkout") . '</label></th>
                <td>
                    <label><input type="checkbox" name="' . $this->_optionName . '[show_postcode]" ' . checked("1", $vncheckout_settings["show_postcode"], false) . ' value="1" /> ' . __("Check vào để hiện trường postcode. Mặc định là ẩn", "ntco-vn-checkout") . '</label>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="active_orderstyle">' . __("Xưng hô", "ntco-vn-checkout") . '</label></th>
                <td>
                    <label><input type="checkbox" name="' . $this->_optionName . '[enable_gender]" ' . checked("1", $vncheckout_settings["enable_gender"], false) . ' value="1" /> ' . __("Hiển thị mục chọn cách xưng hô Anh/Chị", "ntco-vn-checkout") . '</label>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="not_required_email">' . __("KHÔNG bắt buộc nhập Email", "ntco-vn-checkout") . '</label> <span class="new_label">Mới</span></th>
                <td>
                    <label><input type="checkbox" name="' . $this->_optionName . '[not_required_email]" ' . checked("1", $vncheckout_settings["not_required_email"], false) . ' value="1" id="not_required_email"/> ' . __("Trường email sẽ KHÔNG bắt buộc phải nhập nữa", "ntco-vn-checkout") . '</label>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="load_address">' . __("Hình thức tải địa chỉ", "ntco-vn-checkout") . '</label> <span class="new_label">Mới</span></th>
                <td>
                    <label><input type="radio" name="' . $this->_optionName . '[load_address]" ' . esc_attr__(in_array($vncheckout_settings["load_address"], [1, 2]) ? 'checked="checked"' : '') . ' value="2"/> ' . __("Tải bằng file json. Tốc độ cực nhanh. Khuyến khích dùng cái này.", "ntco-vn-checkout") . '</label><br>
                    <label><input type="radio" name="' . $this->_optionName . '[load_address]" ' . checked("3", $vncheckout_settings["load_address"], false) . ' value="3"/> ' . __("Tải bằng admin-ajax.php. Tốc độ chậm hơn", "ntco-vn-checkout") . '</label><br>
                    <label><input type="radio" name="' . $this->_optionName . '[load_address]" ' . checked("4", $vncheckout_settings["load_address"], false) . ' value="4"/> ' . __("Tải bằng get-address.php trong plugin. Tốc độ rất nhanh. Nhưng nếu chặn thực thi php trong plugin sẽ không hoạt động được.", "ntco-vn-checkout") . '</label>
                </td>
            </tr>
        </tbody>
    </table>
    <h2>Cài đặt chung</h2>
    <table class="form-table">
        <tbody>';

if (function_exists("ntco_vietnam_shipping")) {
    echo '<tr>
            <th scope="row"><label for="shipping_to_village">' . __("Hỗ trợ tính phí ship tới phường/xã", "ntco-vn-checkout") . ' <span class="new_label">Mới</span></label></th>
            <td>
                <label><input type="checkbox" name="' . $this->_optionName . '[shipping_to_village]" ' . checked("1", $vncheckout_settings["shipping_to_village"], false) . ' value="1" /> ' . __("Có. Bật tính phí ship tới phường/xã", "ntco-vn-checkout") . '</label>
            </td>
        </tr>';
}

echo '<tr>
            <th scope="row"><label for="convert_price_text">' . __("Chuyển giá sang dạng chữ", "ntco-vn-checkout") . '</label></th>
            <td>
                <label><input type="checkbox" name="' . $this->_optionName . '[convert_price_text]" ' . checked("1", $vncheckout_settings["convert_price_text"], false) . ' value="1" id="convert_price_text"/> ' . __("Cho phép chuyển giá sang dạng chữ", "ntco-vn-checkout") . '</label><br>
                <small>Ví dụ:<br>
                    900đ => 900đ<br>
                    18.000đ => 18k<br>
                    18.200đ => 18k200<br>
                    18.200.000đ => 18tr200<br>
                    1.820.000.000đ => 1tỷ820
                </small>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="to_vnd">' . __("Chuyển ₫ sang VNĐ", "ntco-vn-checkout") . '</label></th>
            <td>
                <label><input type="checkbox" name="' . $this->_optionName . '[to_vnd]" ' . checked("1", $vncheckout_settings["to_vnd"], false) . ' value="1" id="to_vnd"/> ' . __("Cho phép chuyển sang VNĐ", "ntco-vn-checkout") . '</label><br>
                <small>Xem thêm <a href="http://levantoan.com/thay-doi-ky-hieu-tien-te-dong-viet-nam-trong-woocommerce-d-sang-vnd/" target="_blank">cách thiết lập đơn vị tiền tệ ₫ (Việt Nam đồng)</a></small>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="remove_methob_title">' . __("Loại bỏ tiêu đề vận chuyển", "ntco-vn-checkout") . '</label></th>
            <td>
                <label><input type="checkbox" name="' . $this->_optionName . '[remove_methob_title]" ' . checked("1", $vncheckout_settings["remove_methob_title"], false) . ' value="1" id="remove_methob_title"/> ' . __("Loại bỏ hoàn toàn tiêu đề của phương thức vận chuyển", "ntco-vn-checkout") . '</label>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="freeship_remove_other_methob">' . __("Ẩn phương thức khi có free-shipping", "ntco-vn-checkout") . '</label></th>
            <td>
                <label><input type="checkbox" name="' . $this->_optionName . '[freeship_remove_other_methob]" ' . checked("1", $vncheckout_settings["freeship_remove_other_methob"], false) . ' value="1" id="freeship_remove_other_methob"/> ' . __("Ẩn tất cả những phương thức vận chuyển khác khi có miễn phí vận chuyển", "ntco-vn-checkout") . '</label>
            </td>
        </tr>
        <tr class="ntco_pro">
            <th scope="row"><label for="khoiluong_quydoi">' . __("Số quy đổi", "ntco-vn-checkout") . '</label></th>
            <td>
                <input type="number" min="0" name="' . $this->_optionName . '[khoiluong_quydoi]" value="' . $vncheckout_settings["khoiluong_quydoi"] . '" id="khoiluong_quydoi"/> <br>
                <small>' . __("Thương số quy đổi. Mặc định theo GHTK là 6000", "ntco-vn-checkout") . '</small>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="active_vnd2usd">' . __("Kích hoạt chuyển đổi VNĐ sang USD", "ntco-vn-checkout") . '</label></th>
            <td>
                <label><input type="checkbox" name="' . $this->_optionName . '[active_vnd2usd]" ' . checked("1", $vncheckout_settings["active_vnd2usd"], false) . ' value="1" /> ' . __("Kích hoạt chuyển đổi VNĐ sang USD để có thể sử dụng paypal", "ntco-vn-checkout") . '</label>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="vnd_usd_rate">' . __("VNĐ quy đổi sang tiền", "ntco-vn-checkout") . '</label></th>
            <td>
                <select name="' . $this->_optionName . '[vnd2usd_currency]" id="vnd2usd_currency">';
$paypal_supported_currencies = ["AUD", "BRL", "CAD", "MXN", "NZD", "HKD", "SGD", "USD", "EUR", "JPY", "TRY", "NOK", "CZK", "DKK", "HUF", "ILS", "MYR", "PHP", "PLN", "SEK", "CHF", "TWD", "THB", "GBP", "RMB", "RUB"];
foreach ($paypal_supported_currencies as $currency) {
    echo '<option value="' . $currency . '" ' . selected(strtoupper($currency), $vncheckout_settings["vnd2usd_currency"], false) . '>' . $currency . '</option>';
}
echo '</select>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="vnd_usd_rate">' . __("Số quy đổi", "ntco-vn-checkout") . '</label></th>
            <td>
                <input type="number" min="0" name="' . $this->_optionName . '[vnd_usd_rate]" value="' . $vncheckout_settings["vnd_usd_rate"] . '" id="vnd_usd_rate"/> <br>
                <small>' . __("Tỷ giá quy đổi từ VNĐ", "ntco-vn-checkout") . '</small>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="active_orderstyle">' . __("Thay đổi giao diện trang đơn hàng", "ntco-vn-checkout") . '</label></th>
            <td>
                <label><input type="checkbox" name="' . $this->_optionName . '[active_orderstyle]" ' . checked("1", $vncheckout_settings["active_orderstyle"], false) . ' value="1" /> ' . __("Thay đổi giao diện trang danh sách đơn hàng", "ntco-vn-checkout") . '</label>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="enable_getaddressfromphone">' . __("Lấy địa chỉ tự động", "ntco-vn-checkout") . '</label></th>
            <td>
                <label><input type="checkbox" name="' . $this->_optionName . '[enable_getaddressfromphone]" ' . checked("1", $vncheckout_settings["enable_getaddressfromphone"], false) . ' value="1" /> ' . __("Lấy địa chỉ tự động", "ntco-vn-checkout") . '</label><br>
                <small>Chức năng này cho phép nhập SĐT để lấy địa chỉ đã có của khách hàng.</small>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="enable_recaptcha">' . __("Cấu hình ReCaptcha", "ntco-vn-checkout") . '</label></th>
            <td>
                <label><input type="checkbox" name="' . $this->_optionName . '[enable_recaptcha]" ' . checked("1", $vncheckout_settings["enable_recaptcha"], false) . ' value="1" /> ' . __("Sử dụng recaptcha", "ntco-vn-checkout") . ' V2</label><br>
                <small>Chức năng này sẽ hiện captcha khi lấy địa chỉ bằng số điện thoại đặt hàng trước đó. Tránh bot spam.</small>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="recaptcha_sitekey">' . __("Recaptcha Sitekey", "ntco-vn-checkout") . '</label></th>
            <td>
                <input type="text" name="' . $this->_optionName . '[recaptcha_sitekey]" value="' . $vncheckout_settings["recaptcha_sitekey"] . '" id="recaptcha_sitekey"/> <br>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="recaptcha_secretkey">' . __("Recaptcha Secretkey", "ntco-vn-checkout") . '</label></th>
            <td>
                <input type="text" name="' . $this->_optionName . '[recaptcha_secretkey]" value="' . $vncheckout_settings["recaptcha_secretkey"] . '" id="recaptcha_secretkey"/> <br>
            </td>
        </tr>';

if ($this->has_active("ghtk") || $this->has_active("viettel")) {
    echo '<tr>
            <th scope="row"><label for="hide_special_method">' . __("Ẩn phí ship GHTK và ViettelPost", "ntco-vn-checkout") . '</label> <span class="new_label">Mới</span></th>
            <td>
                <label><input type="checkbox" name="' . $this->_optionName . '[hide_special_method]" ' . checked("1", $vncheckout_settings["hide_special_method"], false) . ' value="1" id="hide_special_method"/> ' . __("Ẩn phí ship GHTK và Viettel khi có cài đặt các shipping method khác", "ntco-vn-checkout") . '</label>
            </td>
        </tr>';
}

echo '<tr>
            <th scope="row"><label for="hide_special_method">' . __("Làm tròn phí ship", "ntco-vn-checkout") . '</label> <span class="new_label">Mới</span></th>
            <td>
                <label><input type="checkbox" name="' . $this->_optionName . '[roundup_ship]" ' . checked("1", $vncheckout_settings["roundup_ship"], false) . ' value="1" id="roundup_ship"/> ' . __("Có làm tròn phí ship", "ntco-vn-checkout") . '</label><br>
                <small>Ví dụ: ' . wc_price(18050) . ' -> ' . wc_price(18000) . ' hoặc ' . wc_price(18503) . ' -> ' . wc_price(19000) . '</small>
            </td>
        </tr>';

do_settings_fields($this->_optionGroup, "default");

echo '        </tbody>
    </table>

    <h2>Công cụ.</h2>
    <table class="form-table infor-shop">
        <tbody>
            <tr>
                <th scope="row"><label for="send_shipid_active">' . __("Cập nhật quốc gia", "ntco-vn-checkout") . '</label></th>
                <td>
                    <button class="button update_country" type="button" data-nonce="' . wp_create_nonce("admin_ghtk_nonce_action") . '">Cập nhật quốc gia</button><span class="ajax_mess"></span><br>
                    <small>Cập nhật quốc gia VN cho toàn bộ user</small>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="send_shipid_active">' . __("Cập nhật dữ liệu địa chỉ", "ntco-vn-checkout") . '</label></th>
                <td>';
$ajax_nonce = wp_create_nonce("vnadress_create_table");
echo '<p><button type="button" class="button-primary button_create_tables" data-nonce="' . $ajax_nonce . '">Cập nhật database</button></p>
                </td>
            </tr>
        </tbody>
    </table>';

do_settings_sections($this->_optionGroup, "default");
submit_button();

echo '</form>';
