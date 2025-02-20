<?php

/**
 * Plugin Name: woo_branch
 * Plugin URI: https://www.yourwebsiteurl.com/
 * Description: This is the very first plugin I ever created.
 * Version: 1.0
 * Author: WOO_branch
 * Author URI: http://yourwebsiteurl.com/
 **/

if (! class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}
if (is_admin()) {
    new Branch_Wp_List_Table();
}


class Branch_Wp_List_Table
{
    /**
     * Constructor will create the menu item
     */
    public $plugin_path;
    public $plugin_url;

    public function __construct()
    {
        $this->plugin_path = plugin_dir_path(dirname(__FILE__, 1)) . 'woo_branch';
        $this->plugin_url = plugin_dir_url(dirname(__FILE__)) . 'woo_branch';
        add_action('admin_menu', array($this, 'themeslug_enqueue_style'));
        add_action('admin_menu', array($this, 'add_menu_example_list_table_page'));
        add_action('admin_enqueue_scripts', array($this, 'load_media_files'));
    }
    function themeslug_enqueue_style()
    {
        wp_enqueue_style('add_branch_style', $this->plugin_url . '/assets/styles/select2.min.css');
        wp_enqueue_script('add_branch_script', $this->plugin_url . '/assets/scripts/select2.min.js');
        wp_enqueue_script('add_default_script', $this->plugin_url . '/assets/scripts/scripts.js');
    }

    function load_media_files()
    {
        wp_enqueue_media();
    }

    /**
     * Menu item will allow us to load the page to display the table
     */
    public function add_menu_example_list_table_page()
    {
        $this->theme_options_panel();
    }
    public function theme_options_panel()
    {

        add_submenu_page(
            'woocommerce',
            __('Địa Chỉ Cửa Hàng', 'woocommerce'),
            __('Địa Chỉ Cửa Hàng', 'woocommerce'),
            'manage_woocommerce',
            'store-locations',
            array($this, 'render_store_locations_page')
        );
    }


    // Hiển thị trang cài đặt địa chỉ cửa hàng
    function render_store_locations_page()
    {
        if (!current_user_can('manage_woocommerce')) {
            wp_die(__('Bạn không có quyền truy cập vào trang này.', 'woocommerce'));
        }

        // Lấy danh sách địa chỉ
        $store_addresses = get_option('custom_store_addresses', []);

        // Lưu dữ liệu khi form được submit
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && check_admin_referer('store_locations_nonce')) {
            if (isset($_POST['custom_store_addresses'])) {
                $store_addresses = array_map('sanitize_text_field', $_POST['custom_store_addresses']);
                update_option('custom_store_addresses', $store_addresses);
                echo '<div class="updated notice"><p>Địa chỉ cửa hàng đã được lưu thành công!</p></div>';
            }
        }
?>
    <style>
        .select2-container {
            width: 100%;
        }
    </style>
        <div class="wrap">
            <h1><?php _e('Danh Sách Địa Chỉ Cửa Hàng', 'woocommerce'); ?></h1>
            <form method="post" action="" id="store-locations-table">
                <?php wp_nonce_field('store_locations_nonce'); ?>
                <?php
                if (!empty($store_addresses)) {
                    foreach ($store_addresses as $address) {
                ?>
                        <table class="form-table">
                            <tbody>
                                <tr>
                                    <td>
                                        <input type="text" name="custom_store_addresses[]" value="<?php echo esc_attr($address); ?>" placeholder="Nhập địa chỉ cửa hàng" />
                                    </td>
                                    <td>
                                        <button type="button" class="remove-address button">Xóa</button>
                                    </td>
                                </tr>

                            </tbody>
                        </table>

                <?php
                    }
                }
                ?>

                <p>
                    <button type="button" id="add-store-address" class="button">+ Thêm địa chỉ</button>
                </p>
                <p>
                    <button type="submit" class="button-primary"><?php _e('Lưu Địa Chỉ', 'woocommerce'); ?></button>
                </p>
            </form>
        </div>


<?php
    }

    function wps_theme_func()
    {
        return require_once("$this->plugin_path/templates/admin.php");
    }
    public function wps_theme_func_settings()
    {
        echo '<div class="wrap"><div id="icon-options-general" class="icon32"><br></div>
              <h2>Settings</h2></div>';
    }
    public  function wps_theme_func_tich_diem()
    {
        echo '<div class="wrap"><div id="icon-options-general" class="icon32"><br></div>
              <h2>FAQ</h2></div>';
    }


    /**
     * Display the list table page
     *
     * @return Void
     */
}




function pluginprefix_setup_db1()
{
    // Function change serialized
    set_time_limit(-1);
    global $wpdb;

    try {
        if (!function_exists('dbDelta')) {
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        }
        $ptbd_table_name = $wpdb->prefix . 'woo_branchs';

        dbDelta("SET GLOBAL TIME_ZONE = '+07:00';");

        if ($wpdb->get_var("SHOW TABLES LIKE '" . $ptbd_table_name . "'") != $ptbd_table_name) {

            $sql  = 'CREATE TABLE ' . $ptbd_table_name . '(
            id INT AUTO_INCREMENT,
            city INT  NOT NULL,
            district INT  NOT NULL,
            ward INT  NOT NULL,
            address VARCHAR(255) CHARACTER SET utf8mb4  NOT NULL,
            status INT DEFAULT 1,
                    PRIMARY KEY(id))';
            dbDelta($sql);
        }
    } catch (\Exception $ex) {
        echo '<pre>';
        print_r($ex);
        echo '</pre>';
        die;
    }
}

/**
 * Activate the plugin.
 */
function pluginprefix_activate1()
{
    // Trigger our function that registers the custom post type plugin.
    pluginprefix_setup_db1();

    // Clear the permalinks after the post type has been registered.
    flush_rewrite_rules();
}

// /**
//  * DeActivate the plugin.
//  */
// function pluginprefix_deactivate() { 
//     // Trigger our function that registers the custom post type plugin.
//     pluginprefix_unsetup_db(); 
//     // Clear the permalinks after the post type has been registered.
//     flush_rewrite_rules(); 
// }
register_activation_hook(__FILE__, 'pluginprefix_activate1');
add_action('rest_api_init', function () {
    register_rest_route('v1', '/city', array(
        'methods'  => 'GET',
        'callback' => 'getCity', // Gọi callback từ một class
        'permission_callback' => '__return_true'
    ));
    register_rest_route('v1', '/phuong', array(
        'methods'  => 'POST',
        'callback' => 'phuong', // Gọi callback từ một class
        'permission_callback' => '__return_true'

        // 'permission_callback' => function () {
        //     return current_user_can('edit_posts');
        // }
    ));
    register_rest_route('v1', '/quan', array(
        'methods'  => 'POST',
        'callback' => 'quan', // Gọi callback từ một class
        'permission_callback' => '__return_true'

        // 'permission_callback' => function () {
        //     return current_user_can('edit_posts');
        // }
    ));
});
function getCity()
{

    $city = file_get_contents(ABSPATH . 'wp-content/plugins/woo_branch/assets/data/tinh_tp.json');
    $city = json_decode($city);
    $listCity = [];
    foreach ($city as $id => $val) {
        $json['id'] = $id;
        $json['name'] = $val->name_with_type;
        $listCity[] = $json;
    }
    return new WP_REST_Response(array(
        'status' => 'success',
        'message' => 'API WordPress hoạt động!',
        'data' => $listCity
    ), 200);
}
function phuong(WP_REST_Request $request)
{
    try {
        $data = sanitize_text_field($request->get_param('parent'));

        if (!$data) {
            return new WP_REST_Response(array(
                'status' => 'error',
                'message' => 'Vui lòng nhập quận',
                'data' => ''
            ), 500);
        } else {
            $phuong = file_get_contents(ABSPATH . "wp-content/plugins/woo_branch/assets/data/xa-phuong/$data.json");
            $phuong = json_decode($phuong);
            
            $listPhuong = [];
            foreach ($phuong as $id => $val) {
                $json['id'] = $id;
                $json['name'] = $val->name_with_type;
                $listPhuong[] = $json;
            }
            
            return new WP_REST_Response(array(
                'status' => 'success',
                'message' => 'API WordPress hoạt động!',
                'data' => $listPhuong
            ), 200);
        }
    } catch (\Throwable $th) {
        //throw $th;
        return new WP_REST_Response(array(
            'status' => 'error',
            'message' => 'Lỗi lấy phường',
            'data' => ''
        ), 500);
    }
}
function quan(WP_REST_Request $request)
{
    try {
        $data = sanitize_text_field($request->get_param('parent'));

        if (!$data) {
            return new WP_REST_Response(array(
                'status' => 'error',
                'message' => 'Vui lòng nhập thành phố',
                'data' => ''
            ), 500);
        } else {
          
            $quan = file_get_contents(ABSPATH . "wp-content/plugins/woo_branch/assets/data/quan-huyen/$data.json") ;
            $quan = json_decode($quan);
            $listQuan = [];
            foreach ($quan as $id => $val) {
                $json['id'] = $id;
                $json['name'] = $val->name_with_type;
                $listQuan[] = $json;
            }
            return new WP_REST_Response(array(
                'status' => 'success',
                'message' => 'API WordPress hoạt động!',
                'data' => $listQuan
            ), 200);
        }
    } catch (\Throwable $th) {
        //throw $th;
        return new WP_REST_Response(array(
            'status' => 'error',
            'message' => 'Lỗi lấy quận',
            'data' => ''
        ), 500);
    }
}
