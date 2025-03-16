<?php

if (!defined("ABSPATH")) {
    exit("No script kiddies please!");
}


echo '<form method="post" action="options.php" novalidate="novalidate" class="ntco_options_style">';
settings_fields($this->_optionGroup);
$flra_options = wp_parse_args(get_option($this->_optionName), $this->_defaultOptions);

echo '<h2>' . __("GHN API", "ntco-ghn") . '</h2>';
echo '<table class="form-table">
        <tbody>
            <tr>
                <th scope="row"><label for="token_key">' . __("Môi trường", "ntco-ghn") . '</label></th>
                <td>
                    <label><input type="radio" name="' . $this->_optionName . '[moitruong]" value="product" ' . checked("product", $flra_options["moitruong"], false) . '/> Product - Tài khoản tại khachhang.ghn.vn</label><br>
                    <label><input type="radio" name="' . $this->_optionName . '[moitruong]" value="test" ' . checked("test", $flra_options["moitruong"], false) . '/> Test - Tài khoản tại 5sao.ghn.dev</label>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="token_key">' . __("Token Key", "ntco-ghn") . '<span class="ntco_require">*</span></label></th>
                <td>
                    <input type="text" name="' . $this->_optionName . '[token_key]" value="' . $flra_options["token_key"] . '" id="token_key"/><br>
                    <small>' . sprintf(__("Lấy token key <a href=\"%s\" target=\"_blank\">tại đây</a>", "ntco-ghn"), "https://khachhang.ghn.vn/account") . '</small>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="token_key">' . __("Ghi chú bắt buộc", "ntco-ghn") . '<span class="ntco_require">*</span></label></th>
                <td>
                    <select name="' . $this->_optionName . '[ghn_ghichu]" id="ghn_ghichu">
                        <option value="CHOXEMHANGKHONGTHU" ' . selected("CHOXEMHANGKHONGTHU", $flra_options["ghn_ghichu"], false) . '>' . __("Cho xem hàng, không cho thử", "ntco-ghn") . '</option>
                        <option value="CHOTHUHANG" ' . selected("CHOTHUHANG", $flra_options["ghn_ghichu"], false) . '>' . __("Cho thử hàng", "ntco-ghn") . '</option>
                        <option value="KHONGCHOXEMHANG" ' . selected("KHONGCHOXEMHANG", $flra_options["ghn_ghichu"], false) . '>' . __("Không cho thử hàng", "ntco-ghn") . '</option>
                    </select><br>
                    <small>' . __("Ghi chú bắt buộc khi đăng đơn lên GHN", "ntco-ghn") . '</small>
                </td>
            </tr>
            <tr style="display:none;">
                <th scope="row"><label for="ghn_aff_id">' . __("Mã liên kết", "ntco-ghn") . '</label></th>
                <td>
                    <input type="text" name="' . $this->_optionName . '[ghn_aff_id]" value="' . $flra_options["ghn_aff_id"] . '" id="ghn_aff_id"/><br>
                    <small>' . __("Không bắt buộc. Nếu là đối tác hãy liên hệ với GHN để có mã này.", "ntco-ghn") . '</small>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="tracking_page">' . __("Tracking", "ntco-ghn") . '</label></th>
                <td>';
$args = ["selected" => $flra_options["tracking_page"], "name" => $this->_optionName . "[tracking_page]", "show_option_none" => __("Chọn một trang", "ntco-ghn")];
wp_dropdown_pages($args);
echo '<br>Chọn page hiển thị tracking. Nội dung page hãy nhập shortcode này [ghn_tracking]<br>
                    Sau khi chọn page và lưu cài đặt hãy <a href="' . admin_url("options-permalink.php") . '" title="">vào đây</a> để cập nhật lại permalink.
                </td>
            </tr>
            <tr style=" display: none; ">
                <th scope="row"><label for="token_key">' . __("Debug", "ntco-ghn") . '</label></th>
                <td>
                    <label><input type="checkbox" name="' . $this->_optionName . '[debug]" value="1" ' . checked(1, $flra_options["debug"], false) . '/> Bật Debug</label><br>
                    <small>Xem debug tại đây: <a href="' . NTCO_GHNV2_URL . 'ghn_log.txt" target="_blank" rel="nofollow">' . NTCO_GHNV2_URL . 'ghn_log.txt</a>. <strong style="color: red;">Hãy tắt tính năng này khi không cần check lỗi</strong> </small>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="order_items">' . __("Danh sách sp trong đơn hàng", "ntco-ghn") . '</label></th>
                <td>
                    <label><input type="checkbox" name="' . $this->_optionName . '[order_items]" value="1" ' . checked(1, $flra_options["order_items"], false) . '/> Hiển thị chi tiết các sp trong đơn hàng khi up lên GHN</label><br>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="order_insurance">' . __("Khai giá hàng hoá", "ntco-ghn") . '</label></th>
                <td>
                    <label><input type="checkbox" name="' . $this->_optionName . '[order_insurance]" value="1" ' . checked(1, $flra_options["order_insurance"], false) . '/> Khai giá hàng hoá để hưởng bảo hiểm nếu trường hợp mất hàng, bể hàng sẽ đền theo giá trị của đơn hàng</label><br>
                    <small>Nếu không check thì với đơn hàng COD mặc định vẫn có khai giá hàng hoá. Nếu check thì tất cả các đơn dù bank hay COD vẫn có áp dụng khai giá hàng hoá</small>
                </td>
            </tr>
        </tbody>
    </table>';
echo '<div style=" display: none; ">';
echo '<h2>' . __("Email thông báo", "ntco-ghn") . '</h2>';
echo '<p>Email thông báo tới khách hàng sau khi đăng đơn lên GHN</p>
    <p>
        Các giá trị sử dụng trong nội dung và tiêu đề của email:<br>
        {site_title}: Tên website<br>
        {order_id}: ID đơn trên web<br>
        {ghn_id}: Mã đơn trên GHN<br>
        {estimated_deliver}: Dự kiến giao hàng<br>
        {ghn_mess}: Tin nhắn từ GHN<br>
        {ghn_tracking_link}: Link tracking trên GHN<br>
        {site_tracking_link}: Link tracking trên website
    </p>';
echo '<table class="form-table">
        <tbody>
            <tr>
                <th scope="row"><label for="email_active">' . __("Kích hoạt", "ntco-ghn") . '</label></th>
                <td>
                    <label style="margin-right: 10px;"><input type="radio" name="' . $this->_optionName . '[email_active]" value="0" ' . checked(0, $flra_options["email_active"], false) . '/> Không</label>
                    <label><input type="radio" name="' . $this->_optionName . '[email_active]" value="1" ' . checked(1, $flra_options["email_active"], false) . '/> Có</label>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="email_title">' . __("Tiêu đề email", "ntco-ghn") . '<span class="ntco_require">*</span></label></th>
                <td>
                    <input type="text" name="' . $this->_optionName . '[email_title]" value="' . $flra_options["email_title"] . '" id="email_title"/>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="email_content">' . __("Nội dung email", "ntco-ghn") . '<span class="ntco_require">*</span></label></th>
                <td>';
$settings = ["textarea_name" => $this->_optionName . "[email_content]"];
wp_editor($flra_options["email_content"], "email_content", $settings);
echo '                </td>
            </tr>
        </tbody>
    </table>';
echo '</div>';
do_settings_sections($this->_optionGroup, "default");
submit_button();
echo '</form>';
