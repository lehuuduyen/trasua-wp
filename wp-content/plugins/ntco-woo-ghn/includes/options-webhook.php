<?php

defined("ABSPATH") or exit("No script kiddies please!");

echo '<h2>' . __("Cài đặt đường dẫn (Webhook)", "ntco-ghn") . '</h2>
<p>Hãy gửi url bên dưới cho GHN để họ cấu hình Webhook nhé</p>';

$token_key = ghn_class()->get_options("token_key");

echo '<form method="post" action="options.php" novalidate="novalidate" class="ntco_options_style ntco_input_full ghn_webhook_url">';

$webhook_field = ghn_api()->_ghn_webhook;
$webhook_field_group = ghn_api()->_ghn_webhook_group;
settings_fields($webhook_field_group);
$options = wp_parse_args(get_option($webhook_field), ghn_api()->_defaultWebhookOptions);
$webhook_url = $options["webhook_url"];
$webhook_hash = $options["webhook_hash"];
$register = true;

if (!$webhook_url || !$webhook_hash) {
    $webhook_hash = wp_generate_password(24, false);
    $webhook_url = ghn_api()->_ghn_webhook_action . $webhook_hash;
    $register = false;
}

echo '<div class="webhook_mess"></div>
    <table class="form-table">
        <tbody>
            <tr>
                <th scope="row"><label for="license_key">' . __("Đường dẫn cập nhật trạng thái tự động (Webhook URL)", "ntco-ghn") . '</label></th>
                <td>
                    <input type="text" readonly name="' . $webhook_field . '[webhook_url]" data-webhookaction="' . esc_url(ghn_api()->_ghn_webhook_action) . '" value="' . esc_url($webhook_url) . '" id="webhook_url"/> <br>
                    <input type="hidden" readonly name="' . $webhook_field . '[webhook_hash]" value="' . $webhook_hash . '" id="webhook_hash"/>
                    <a href="javascript:void(0)" class="dhn_change_webhook_url">' . __("Làm mới Webhook URL", "ntco-ghn") . '</a>
                </td>
            </tr>
        </tbody>
    </table>';

do_settings_sections($webhook_field_group, "default");

echo '<p style="display: none">
        <input type="button" name="webhook_submit" id="webhook_submit" data-nonce="' . wp_create_nonce("webhook_nonce") . '" class="button button-primary ghn_register_webhook" value="' . __("Lưu Webhook URL", "ntco-ghn") . '">
    </p>';

submit_button();

echo '</form>';
