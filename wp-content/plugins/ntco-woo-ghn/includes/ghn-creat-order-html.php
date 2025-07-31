<?php

defined("ABSPATH") or exit("No script kiddies please!");
$payment_methob = $order->get_payment_method();
echo "<tr>\r\n    <td>";
_e("Chọn cửa hàng/kho", "ntco-ghn");
echo "</td>\r\n    <td>\r\n        <select name=\"ghn_creatorder_hub\" id=\"ghn_creatorder_hub\">\r\n            <option class=\"\">";
_e("Chọn cửa hàng/kho", "ntco-ghn");
echo "</option>\r\n            ";
$all_hubs = $this->getHubs();
if (!empty($all_hubs) && is_array($all_hubs)) {
    foreach ($all_hubs as $hub) {
        $hubID = isset($hub["_id"]) ? $hub["_id"] : "";
        $Address = isset($hub["address"]) ? $hub["address"] : "";
        $status = isset($hub["status"]) ? $hub["status"] : 1;
        if ($status == 1) {
            echo "                    <option value=\"";
            echo $hubID;
            echo "\" ";
            selected($hubID, $HubID_Order);
            echo ">";
            echo "#" . $hubID . " - " . $Address;
            echo "</option>\r\n                    ";
        }
    }
}
echo "        </select><br>\r\n        <small>Phần này là tự động. Trong trường hợp thay đổi chi nhánh có thể sẽ làm phí vận chuyển thay đổi</small>\r\n    </td>\r\n</tr>\r\n<tr>\r\n    <td colspan=\"2\">\r\n        <div class=\"ntco_option_2col ghn_order_customerinfor\">\r\n            <div class=\"ntco_option_col ghn_more_wrap\">\r\n                <div class=\"ntco_option_col_title\">\r\n                    <strong>Thông tin khách hàng</strong>\r\n                    <small>Ẩn bớt</small>\r\n                </div>\r\n                <div class=\"ghn_customer_infor ghn_more_content\">\r\n                    <div class=\"ghn_customer_row\">\r\n                        <div class=\"ghn_customer_col\">\r\n                            ";
_e("Họ và tên", "ntco-ghn");
echo "                        </div>\r\n                        <div class=\"ghn_customer_col\">\r\n                            ";
echo $name;
echo "                        </div>\r\n                    </div>\r\n                    <div class=\"ghn_customer_row\">\r\n                        <div class=\"ghn_customer_col\">\r\n                            ";
_e("Số điện thoại", "ntco-ghn");
echo "                        </div>\r\n                        <div class=\"ghn_customer_col\">\r\n                            ";
echo $phone;
echo "                        </div>\r\n                    </div>\r\n                    <div class=\"ghn_customer_row\">\r\n                        <div class=\"ghn_customer_col\">\r\n                            ";
_e("Địa chỉ", "ntco-ghn");
echo "                        </div>\r\n                        <div class=\"ghn_customer_col\">\r\n                            ";
echo $address;
echo "                        </div>\r\n                    </div>\r\n                    <div class=\"ghn_customer_row\">\r\n                        <div class=\"ghn_customer_col\">\r\n                            ";
_e("Phường/Xã", "ntco-ghn");
echo "                        </div>\r\n                        <div class=\"ghn_customer_col\">\r\n                            ";
echo vn_checkout()->get_name_village($ward);
echo "                        </div>\r\n                    </div>\r\n                    <div class=\"ghn_customer_row\">\r\n                        <div class=\"ghn_customer_col\">\r\n                            ";
_e("Khu vực", "ntco-ghn");
echo "                        </div>\r\n                        <div class=\"ghn_customer_col\">\r\n                            ";
echo vn_checkout()->get_name_city($province) . " - " . vn_checkout()->get_name_district($district);
echo "                        </div>\r\n                    </div>\r\n                </div>\r\n            </div>\r\n            <div class=\"ntco_option_col ghn_more_wrap\">\r\n                <div class=\"ntco_option_col_title\">\r\n                    <strong>Thông tin sản phẩm</strong>\r\n                    <small>Ẩn bớt</small>\r\n                </div>\r\n                <div class=\"ghn_more_content\">\r\n                    <table class=\"prod_table\">\r\n                        <thead>\r\n                        <tr>\r\n                            <th>Tên sp</th>\r\n                            <th>Weight (";
echo get_option("woocommerce_weight_unit");
echo ")</th>\r\n                            <th>SL</th>\r\n                        </tr>\r\n                        </thead>\r\n                        <tbody>\r\n                        ";
$content_order = [];
if ($product_list && !is_wp_error($product_list) && !empty($product_list)) {
    foreach ($product_list as $product) {
        $content_order[] = $product["name"] . " x " . $product["quantity"];
        echo "                                <tr>\r\n                                    <td>";
        echo $product["name"];
        echo "</td>\r\n                                    <td>";
        echo $product["weight"];
        echo "</td>\r\n                                    <td>";
        echo $product["quantity"];
        echo "</td>\r\n                                </tr>\r\n                            ";
    }
    echo "                        ";
}
echo "                        </tbody>\r\n                    </table>\r\n                    <textarea name=\"ghn_contentOrder\" id=\"ghn_contentOrder\">";
echo esc_textarea(implode(" | ", $content_order));
echo "</textarea>\r\n                </div>\r\n            </div>\r\n        </div>\r\n    </td>\r\n</tr>\r\n<tr>\r\n    <td colspan=\"2\">\r\n        <div class=\"ntco_option_2col ghn_order_customerinfor\">\r\n            <div class=\"ntco_option_col\">\r\n                <strong>";
_e("Gói hàng", "ntco-ghn");
echo "</strong>\r\n                <table class=\"goihang_table\">\r\n                    <tbody>\r\n                    <tr>\r\n                        <td>";
_e("Mã đơn hệ thống", "ntco-ghn");
echo "</td>\r\n                        <td>\r\n                            <input type=\"text\" name=\"ghn_ExternalCode\" id=\"ghn_ExternalCode\" value=\"";
echo $order->get_id();
echo "\"/>\r\n                        </td>\r\n                    </tr>\r\n                    <tr>\r\n                        <td>";
_e("Giá trị gói hàng", "ntco-ghn");
echo "</td>\r\n                        <td>\r\n                            <input type=\"number\" name=\"ghn_InsuranceFee\" id=\"ghn_InsuranceFee\" data-total=\"";
echo ghn_class()->order_get_total($order);
echo "\" value=\"";
echo ghn_class()->order_get_total($order);
echo "\"/> ";
echo get_woocommerce_currency_symbol();
echo "\r\n                            <label><input type=\"checkbox\" id=\"ghn_khaigia\" value=\"1\" ";
echo $payment_methob && $payment_methob == "cod" ? "checked=\"checked\"" : "";
echo "> Khai giá hàng hóa</label>\r\n                        </td>\r\n                    </tr>\r\n                    <tr>\r\n                        <td>";
_e("Khối lượng", "ntco-ghn");
echo "</td>\r\n                        <td>\r\n                            ";
$all_weight = ghn_class()->convert_weight_to_gram(ghn_class()->get_order_weight($order));
echo "                            <input type=\"text\" name=\"ghn_order_weight\" id=\"ghn_order_weight\" value=\"";
echo $all_weight;
echo "\"> gram\r\n                        </td>\r\n                    </tr>\r\n                    <tr>\r\n                        <td>";
_e("Kích thước (cm)", "ntco-ghn");
echo "</td>\r\n                        <td class=\"input_inline\">\r\n                            ";
$all_width = ghn_class()->convert_dimension_to_cm(ghn_class()->get_order_weight($order, "width"));
echo "                            ";
$all_height = ghn_class()->convert_dimension_to_cm(ghn_class()->get_order_weight($order, "height"));
echo "                            ";
$all_length = ghn_class()->convert_dimension_to_cm(ghn_class()->get_order_weight($order, "length"));
echo "                            <input type=\"text\" name=\"ghn_order_length\" id=\"ghn_order_length\" value=\"";
echo $all_length;
echo "\"> dài\r\n                            <input type=\"text\" name=\"ghn_order_width\" id=\"ghn_order_width\" value=\"";
echo $all_width;
echo "\"> rộng\r\n                            <input type=\"text\" name=\"ghn_order_height\" id=\"ghn_order_height\" value=\"";
echo $all_height;
echo "\"> cao\r\n                        </td>\r\n                    </tr>\r\n                    <tr>\r\n                        <td>";
_e("Ghi chú bắt buộc", "ntco-ghn");
echo " <span class=\"required\">*</span></td>\r\n                        <td>\r\n                            ";
$ghn_ghichu = ghn_class()->get_options("ghn_ghichu");
echo "                            <select name=\"ghn_ghichu_required\" id=\"ghn_ghichu_required\">\r\n                                <option value=\"CHOXEMHANGKHONGTHU\" ";
selected("CHOXEMHANGKHONGTHU", $ghn_ghichu);
echo ">";
_e("Cho xem hàng, không cho thử", "ntco-ghn");
echo "</option>\r\n                                <option value=\"CHOTHUHANG\" ";
selected("CHOTHUHANG", $ghn_ghichu);
echo ">";
_e("Cho thử hàng", "ntco-ghn");
echo "</option>\r\n                                <option value=\"KHONGCHOXEMHANG\" ";
selected("KHONGCHOXEMHANG", $ghn_ghichu);
echo ">";
_e("Không cho xem hàng", "ntco-ghn");
echo "</option>\r\n                            </select>\r\n                        </td>\r\n                    </tr>\r\n                    <tr>\r\n                        <td>";
_e("Ca lấy hàng", "ntco-ghn");
echo "</td>\r\n                        <td>\r\n                            ";
$shift_date = ghn_api()->shift_date();
if ($shift_date) {
    $code = isset($shift_date["code"]) ? intval($shift_date["code"]) : "";
    $data = isset($shift_date["data"]) ? (array) $shift_date["data"] : [];
    if ($code == 200 && $data) {
        echo "                                    <select name=\"ghn_shift_date\" id=\"ghn_shift_date\">\r\n                                        <option value=\"\">Chọn ca lấy hàng. Không bắt buộc</option>\r\n                                        ";
        foreach ($data as $item) {
            $id = isset($item["id"]) ? intval($item["id"]) : "";
            $title = isset($item["title"]) ? sanitize_text_field($item["title"]) : "";
            echo "                                            <option value=\"";
            echo esc_attr($id);
            echo "\">";
            echo esc_attr($title);
            echo "</option>\r\n                                            ";
        }
        echo "                                    </select>\r\n                                    ";
    }
}
echo "                        </td>\r\n                    </tr>\r\n                    <tr>\r\n                        <td>";
_e("Ghi chú", "ntco-ghn");
echo "</td>\r\n                        <td><textarea name=\"ghn_ghichu\" id=\"ghn_ghichu\">";
echo esc_textarea($order->get_customer_note());
echo "</textarea></td>\r\n                    </tr>\r\n                    </tbody>\r\n                </table>\r\n            </div>\r\n            <div class=\"ntco_option_col\">\r\n                <div class=\"ghn_nguoithanhtoan\">\r\n                    ";
_e("Người thanh toán:", "ntco-ghn");
echo "                    <label><input type=\"radio\" name=\"ghn_PaymentTypeID\" class=\"ghn_PaymentTypeID\"  value=\"1\" checked=\"checked\"> ";
_e("Người gửi", "ntco-ghn");
echo "</label>\r\n                    <label><input type=\"radio\" name=\"ghn_PaymentTypeID\" class=\"ghn_PaymentTypeID\" value=\"2\"> ";
_e("Người nhận", "ntco-ghn");
echo "</label>\r\n                </div>\r\n                <div class=\"ghn_tienthuho\">\r\n                    ";
_e("Tiền thu hộ (COD):", "ntco-ghn");
echo "                    ";
$order_sub_total = ghn_class()->order_get_total($order);
$order_total = $order->get_total();
if ($payment_methob && $payment_methob != "cod" && !ghn_class()->get_options("order_insurance")) {
    $order_total = 0;
    $order_sub_total = 0;
}
echo "                    <input type=\"hidden\" id=\"payment_methob\" name=\"payment_methob\" value=\"";
echo $payment_methob;
echo "\">\r\n                    <input type=\"number\" name=\"ghn_tienthuho\" id=\"ghn_tienthuho\" data-total=\"";
echo $order_total;
echo "\" data-subtotal=\"";
echo $order_sub_total;
echo "\" value=\"";
echo $order_total;
echo "\"/> ";
echo get_woocommerce_currency_symbol();
echo "                </div>\r\n                <div class=\"ghn_makhuyenmai\" style=\"display: none\">\r\n                    ";
_e("Mã khuyến mại:", "ntco-ghn");
echo "                    <input type=\"text\" name=\"ghn_CouponCode\" id=\"ghn_CouponCode\"  value=\"\">\r\n                </div>\r\n                <hr>\r\n                <strong>Gói cước</strong>\r\n                ";
$payment_methob = $order->get_payment_method();
$rates = ghn_api()->order_findAvailableServices($order, $HubID_Order, ["weight" => $all_weight, "length" => $all_length, "width" => $all_width, "height" => $all_height, "CouponCode" => "", "InsuranceFee" => $payment_methob && $payment_methob != "cod" && !ghn_class()->get_options("order_insurance") ? 0 : (float) ghn_class()->order_get_total($order)]);
echo "                <div class=\"ghn_all_goicuoc\">\r\n                    ";
if ($rates && !empty($rates)) {
    foreach ($rates as $methob) {
        $ServiceID = isset($methob["service_id"]) ? intval($methob["service_id"]) : "";
        $ExpectedDeliveryTime = isset($methob["ExpectedDeliveryTime"]) ? date("d/m/Y", strtotime($methob["ExpectedDeliveryTime"])) : "";
        $Name = isset($methob["short_name"]) ? esc_attr($methob["short_name"]) : "";
        $ServiceFee = isset($methob["total"]) ? $methob["total"] : "";
        if ($ServiceID) {
            echo "                                <div class=\"ghn_all_goicuoc_list\">\r\n                                    <div class=\"ghn_all_goicuoc_col\">\r\n                                        <label><input type=\"radio\" name=\"ghn_services\" data-service=\"";
            echo esc_attr(json_encode($methob));
            echo "\" data-fee=\"";
            echo $ServiceFee;
            echo "\" value=\"";
            echo $ServiceID;
            echo "\" ";
            checked($ServiceID, $method_id);
            echo "> ";
            echo $Name . " - " . wc_price($ServiceFee);
            echo "</label>\r\n                                    </div>\r\n                                    ";
            if ($ExpectedDeliveryTime) {
                echo "<div class=\"ghn_all_goicuoc_col\">";
                _e("Dự kiến giao", "ntco-ghn");
                echo " ";
                echo $ExpectedDeliveryTime;
                echo "</div>";
            }
            echo "                                </div>\r\n                                ";
        }
    }
}
echo "                </div>\r\n                <hr>\r\n                <div class=\"ghn_nguoithanhtoan\">\r\n                    ";
_e("Gửi hàng tại điểm giao dịch:", "ntco-ghn");
echo "                    <label><input type=\"radio\" name=\"ghn_isPickAtStation\" class=\"ghn_isPickAtStation\"  value=\"1\"> ";
_e("Có", "ntco-ghn");
echo "</label>\r\n                    <label><input type=\"radio\" name=\"ghn_isPickAtStation\" class=\"ghn_isPickAtStation\" value=\"0\" checked=\"checked\"> ";
_e("Không", "ntco-ghn");
echo "</label>\r\n                </div>\r\n            </div>\r\n        </div>\r\n    </td>\r\n</tr>\r\n<tr>\r\n    <td colspan=\"2\" class=\"total_order_api\">\r\n        <div class=\"ntco_option_1col\">\r\n            <table>\r\n                <tbody>\r\n                    <tr class=\"total_cod_fee\">\r\n                        <th>Tiền thu hộ (COD):</th>\r\n                        <td></td>\r\n                    </tr>\r\n                    <tr class=\"total_service_fee\">\r\n                        <th>Phí vận chuyển:</th>\r\n                        <td></td>\r\n                    </tr>\r\n                    <tr class=\"total_insurance_fee\">\r\n                        <th>Phí khai giá (0.5%):</th>\r\n                        <td></td>\r\n                    </tr>\r\n                    <tr class=\"total_coupon_value\" style=\"display: none\">\r\n                        <th>Giảm giá:</th>\r\n                        <td></td>\r\n                    </tr>\r\n                    <tr class=\"total_order_api_total\">\r\n                        <th>Tổng phí vận chuyển:</th>\r\n                        <td></td>\r\n                    </tr>\r\n                </tbody>\r\n            </table>\r\n        </div>\r\n    </td>\r\n</tr>";
