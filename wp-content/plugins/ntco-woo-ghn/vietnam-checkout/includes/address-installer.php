<?php

defined("ABSPATH") or exit;
VNAddress_Install::init();
class VNAddress_Install {
    private static $current_db_version = "";
    private static $db_version = "1.8";
    public static function init() {
        self::$current_db_version = get_option("vnaddress_db_version");
        if (version_compare(self::$current_db_version, self::$db_version, "<")) {
            add_action("admin_notices", ["VNAddress_Install", "admin_notice"]);
        }
        add_action("wp_ajax_vnaddress_create_table", ["VNAddress_Install", "ajax_create_tables"]);
    }
    public static function admin_notice() {
        $ajax_nonce = wp_create_nonce("vnadress_create_table");
        $class = "notice notice-alt notice-warning notice-error";
        $title = "<h2 class=\"notice-title\">Cập nhật quan trọng!</h2>";
        $message = "Ấn \"Cập nhật database\" để cập nhật lại thông tin địa giới hành chính mới nhất";
        $btn = "<p><button type=\"button\" class=\"button-primary button_create_tables\" data-nonce=\"" . $ajax_nonce . "\">Cập nhật database</button></p>";
        printf("<div class=\"%1\$s\">%2\$s<p>%3\$s</p>%4\$s</div>", esc_attr($class), $title, $message, $btn);
    }
    private static function del_tables() {
        global $wpdb;
        $wpdb->query("DROP TABLE IF EXISTS " . $wpdb->prefix . "vnaddress_districts");
        $wpdb->query("DROP TABLE IF EXISTS " . $wpdb->prefix . "vnaddress_wards");
    }
    public static function ajax_create_tables() {
        check_ajax_referer("vnadress_create_table", "security");
        if (self::create_tables()) {
            wp_send_json_success("Cập nhật thành công!");
        } else {
            wp_send_json_error("Lỗi khi cập nhật. Vui lòng thử lại sau!");
        }
        exit;
    }
    public static function onactive_create_tables() {
        if (version_compare(self::$current_db_version, self::$db_version, "<")) {
            self::create_tables();
        }
    }
    public static function create_tables() {
        global $wpdb;
        $wpdb->hide_errors();
        require_once ABSPATH . "wp-admin/includes/upgrade.php";
        self::del_tables();
        $collate = "";
        if ($wpdb->has_cap("collation")) {
            $collate = $wpdb->get_charset_collate();
        }
        $tables = "\r\nCREATE TABLE " . $wpdb->prefix . "vnaddress_districts (\r\n  maqh varchar(25) NOT NULL,\r\n  name varchar(100) NOT NULL,\r\n  matp varchar(25) NOT NULL,\r\n  vt BIGINT UNSIGNED NULL,\r\n  ghn varchar(25) NULL,\r\n  vnpost BIGINT UNSIGNED NULL,\r\n  vnpostv2 varchar(25) NULL,\r\n  PRIMARY KEY (maqh)\r\n) " . $collate . ";\r\nCREATE TABLE " . $wpdb->prefix . "vnaddress_wards (\r\n  xaid varchar(25) NOT NULL,\r\n  name varchar(100) NOT NULL,\r\n  maqh varchar(30) NOT NULL,\r\n  vt BIGINT UNSIGNED NULL,\r\n  ghn varchar(25) NULL,\r\n  vnpost BIGINT UNSIGNED NULL,\r\n  vnpostv2 varchar(25) NULL,\r\n  PRIMARY KEY (xaid)\r\n) " . $collate . ";\r\n";
        dbDelta($tables);
        include VN_CHECKOUT_DIR . "cities/quan_huyen.php";
        include VN_CHECKOUT_DIR . "cities/xa_phuong_thitran.php";
        if (!$quan_huyen || !$xa_phuong_thitran) {
            return false;
        }
        $quanhuyen_args = [];
        foreach ($quan_huyen as $item) {
            if (!isset($item["vnpostv2"])) {
                $item["vnpostv2"] = "";
            }
            $quanhuyen_args[] = "(\"" . implode("\",\"", $item) . "\")";
        }
        $quanhuyen_text = implode(",", $quanhuyen_args);
        $wpdb->query("INSERT INTO " . $wpdb->prefix . "vnaddress_districts\r\n            (maqh, name, matp, vt, ghn, vnpost, vnpostv2)\r\n            VALUES " . $quanhuyen_text);
        $xa_phuong_args = [];
        foreach ($xa_phuong_thitran as $item) {
            if (!isset($item["vnpostv2"])) {
                $item["vnpostv2"] = "";
            }
            $xa_phuong_args[] = "(\"" . implode("\",\"", $item) . "\")";
        }
        $xa_phuong_text = implode(",", $xa_phuong_args);
        $wpdb->query("INSERT INTO " . $wpdb->prefix . "vnaddress_wards\r\n            (xaid, name, maqh, vt, ghn, vnpost, vnpostv2)\r\n            VALUES " . $xa_phuong_text);
        update_option("vnaddress_db_version", self::$db_version, false);
        return true;
    }
}
