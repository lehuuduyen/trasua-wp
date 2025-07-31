<?php

defined("ABSPATH") or exit("No script kiddies please!");



echo '<div class="hubs_action">
    <a href="#" class="button button-primary ntco_ghn_synchub" data-nonce="' . wp_create_nonce("ghn_action_nonce_action") . '">' .
    __("Đồng bộ kho hàng từ GHN", "ntco-ghn") . '</a>
    <span class="spinner"></span>
    <a href="https://khachhang.ghn.vn/store" target="_blank" style="color: red;">Quản lý kho tại đây</a>
</div>';

$all_hubs = ghn_api()->getHubs();

if ($all_hubs && !empty($all_hubs)) {
    $ghn_main_hub = get_option("ghn_main_hub");
    wp_nonce_field("action_nonce_update", "nonce_update");

    echo '<div class="ntco_option_2col ntco_options_style">';

    foreach ($all_hubs as $hub) {
        $HubID = isset($hub["_id"]) ? $hub["_id"] : "";
        $Address = isset($hub["address"]) ? $hub["address"] : "";
        $ContactName = isset($hub["name"]) ? $hub["name"] : "";
        $ContactPhone = isset($hub["phone"]) ? $hub["phone"] : "";
        $WardID = isset($hub["ward_code"]) ? $hub["ward_code"] : "";
        $DistrictID = isset($hub["district_id"]) ? $hub["district_id"] : "";
        $Email = isset($hub["Email"]) ? $hub["Email"] : "";
        $IsMain = isset($hub["IsMain"]) ? $hub["IsMain"] : "";
        $status = isset($hub["status"]) ? $hub["status"] : 1;

        echo '<div class="ntco_option_col status_' . $status . '">
            <div class="ntco_option_box">
                <table class="ntco_hubs_table widefat" cellspacing="0">
                    <thead>
                        <tr>
                            <th colspan="2">
                                <h2 class="hubs_title">' . __("Kho", "ntco-ghn") . ' #' . $HubID . '</h2>';
        echo $status == 1 ? '<span style="color: green">Đang kích hoạt</span>' : '<span style="color: red">Ngừng kích hoạt</span>';
        echo '<a  style=" display: none; " href="#" class="khuvuc_banhang" data-hubid="' . $HubID . '" title="' . __("Chọn tỉnh/quận huyện mà cửa hàng/kho này sẽ giao hàng.", "ntco-ghn") . '">' .
            __("Khu vực bán hàng", "ntco-ghn") . '</a>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>' . __("Mã kho", "ntco-ghn") . '</td>
                            <td>' . $HubID . '<input class="hub_HubID" data-name="HubID" type="hidden" value="' . $HubID . '"></td>
                        </tr>
                        <tr>
                            <td>' . __("Tên người liên hệ", "ntco-ghn") . '</td>
                            <td><input type="text" readonly="readonly" class="hub_ContactName" data-name="ContactName" name="ContactName_' . $HubID . '" value="' . $ContactName . '"></td>
                        </tr>
                        <tr>
                            <td>' . __("Số điện thoại liên hệ", "ntco-ghn") . '</td>
                            <td><input type="text" readonly="readonly" class="hub_ContactPhone" data-name="ContactPhone" name="ContactPhone_' . $HubID . '" value="' . $ContactPhone . '"></td>
                        </tr>
                        <tr>
                            <td>' . __("Địa chỉ", "ntco-ghn") . '</td>
                            <td><input type="text" readonly="readonly" class="hub_Address" data-name="Address" name="Address_' . $HubID . '" value="' . $Address . '"></td>
                        </tr>
                        <tr style="display: none;">
                            <td>' . __("Email", "ntco-ghn") . '</td>
                            <td><input type="text" class="hub_Email" data-name="Email" name="Email_' . $HubID . '" value="' . $Email . '"></td>
                        </tr>
                        <tr>
                            <td>' . __("Khu vực", "ntco-ghn") . '</td>
                            <td>' . vn_checkout()->get_name_city($this->get_ghn_district_name($DistrictID, "matp")) . " - " . $this->get_ghn_district_name($DistrictID) . '</td>
                        </tr>
                        <tr>
                            <td>' . __("Phường xã", "ntco-ghn") . '</td>
                            <td>' . $this->get_ghn_ward_name($WardID) . '</td>
                        </tr>
                        <tr>
                            <td>' . __("Kho chính", "ntco-ghn") . '</td>
                            <td><label><input type="checkbox" onclick="selectOnlyThis(this)" class="hub_IsMain pick_ismain_onlyone" data-name="IsMain" name="ismain_' . $HubID . '" value="' . $HubID . '" ' . checked($ghn_main_hub, $HubID, false) . '> Đặt làm kho chính</label></td>
                        </tr>
                    </tbody>
                    <tfoot style="display: none;">
                        <tr>
                            <td colspan="2" class="text_alignright">
                                <a href="#" class="button button-primary ntco_ghn_updatehubs">' . __("Cập nhật thông tin", "ntco-ghn") . '</a>
                                <span class="spinner"></span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>';
    }
    echo '</div>';

    $hub_district = get_option(ghn_api()->_allhubs);
    foreach ($all_hubs as $hub) {
        $HubID = isset($hub["_id"]) ? $hub["_id"] : "";
        $this_hub_district = isset($hub_district[$HubID]) ? $hub_district[$HubID] : [];

        echo '<div id="hub_district_' . $HubID . '" class="ghn_popup_style">
            <div class="khuvu_banhang_wrap ntco_options_style">
                <div class="ntco_option_box">
                    <table class="ntco_hubs_table widefat" cellspacing="0">
                        <thead>
                            <tr>
                                <th colspan="2">
                                    <h2>' . sprintf(__("Khu vực bán hàng của kho #%s", "ntco-ghn"), $HubID) . '</h2>
                                    <input class="search_city" placeholder="' . __("Tìm nhanh theo tên", "ntco-ghn") . '">
                                    <a href="#" class="ntco_float_right ntco_checkbox_all">' . __("Chọn/Bỏ toàn bộ", "ntco-ghn") . '</a>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="2">
                                    <div class="ghn_all_state_checkbox">';
        $states = ghn_class()->get_states();
        asort($states);
        foreach ($states as $k => $v) {
            echo '<div class="ghn_all_state_checkbox_item">
                                                <label><input type="checkbox" name="hubs_district_' . $HubID . '" value="' . $k . '" ' . (in_array($k, $this_hub_district) ? 'checked="checked"' : '') . '> ' . $v . '</label>
                                            </div>';
        }
        echo '</div>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2" class="text_alignright">
                                    <div class="ghn_msg"></div>
                                    <a href="#" class="button button-primary ntco_float_right ntco_ghn_addhubdistrict" data-hubid="' . $HubID . '">' . __("Lưu", "ntco-ghn") . '</a>
                                    <a href="#" class="button close_popup ntco_float_right">' . __("Đóng", "ntco-ghn") . '</a>
                                    <span class="spinner"></span>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>';
    }
} else {
    echo '<div class="nonce_hubs">' . __("Chưa có kho hàng nào", "ntco-ghn") . '</div>';
}
