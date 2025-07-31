<?php

/**
 * Plugin Name: woo_branch
 * Plugin URI: https://www.yourwebsiteurl.com/
 * Description: This is the very first plugin I ever created.
 * Version: 1.0
 * Author: WOO_branch
 * Author URI: http://yourwebsiteurl.com/
 **/
if (isset($_POST['submitBranch'])) {


    global $wpdb;
    $prefix = $wpdb->prefix;

    $address = $_POST['address'];
    $city = $_POST['city'];
    $quan = $_POST['quan'];
    $phuong = $_POST['phuong'];
    $status = $_POST['status'];

    $update_address = $_POST['update_address'];
    $update_city = $_POST['update_city'];
    $update_quan = $_POST['update_quan'];
    $update_phuong = $_POST['update_phuong'];
    $update_status = $_POST['update_status'];

    foreach ($address as $key => $val) {
        if(empty($city[$key]) || empty($quan[$key]) || empty($phuong[$key]) || empty($val) ){
            $status[$key] = 2;
        }
        $wpdb->query($wpdb->prepare("INSERT INTO " . $prefix . "woo_branchs  (city, district, ward, address,status) VALUES ('" . $city[$key] . "','" . $quan[$key] . "','" . $phuong[$key] . "','" . $val . "','" . $status[$key] . "'); "));
    }

    // check update update_phuong
    foreach ($update_address as $id => $val) {
        if(empty($update_city[$id]) || empty($update_quan[$id]) || empty($update_phuong[$id]) || empty($val) ){
            $update_status[$id] = 2;
        }
        $wpdb->query($wpdb->prepare("UPDATE " . $prefix . "woo_branchs SET city='" . $update_city[$id] . "' , district='" . $update_quan[$id] . "' , ward='" . $update_phuong[$id] . "' , address='" . $val . "' , status='" . $update_status[$id] . "' WHERE id=$id"));
    }
}
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
        wp_enqueue_style('add_branch_style2', $this->plugin_url . '/assets/styles/styles.css');
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
        global $wpdb;
        $prefix = $wpdb->prefix;
        // Lấy danh sách địa chỉ
        $store_addresses = $wpdb->get_results("SELECT * FROM " . $prefix . "woo_branchs ");

?>
        <style>
            .select2-container {
                width: 100%;
            }
        </style>
        <div class="wrap">
            <h1><?php _e('Danh Sách Địa Chỉ Cửa Hàng', 'woocommerce'); ?></h1>

            <form method="post" action="" id="store-locations-table">
                <div class="ntco_option_2col ntco_options_style">
                    <p>
                        <button type="button" id="add-store-address" class="button">+ Thêm địa chỉ</button>
                    </p>
                    <p>
                        <button type="submit" name="submitBranch" class="button-primary"><?php _e('Lưu Địa Chỉ', 'woocommerce'); ?></button>
                    </p>
                    <?php
                    if (!empty($store_addresses)) {
                        $htmlCity = getCity();

                        foreach ($store_addresses as $address) {
                            $htmlQuan = [];

                            if (!empty($address->city)) {

                                $request = new WP_REST_Request();
                                $request->set_param('parent', $address->city);
                                $htmlQuan = quan($request);
                            }
                            $htmlPhuong = [];

                            if (!empty($address->district)) {

                                $request = new WP_REST_Request();
                                $request->set_param('parent', $address->district);
                                $htmlPhuong = phuong($request);
                            }

                    ?>
                            <div class="ntco_option_col ">
                                <div class="ntco_option_box">
                                    <table class=" tableAddress ntco_hubs_table widefat">
                                        <tbody>
                                            <tr>
                                                <th scope="row" class="titledesc">
                                                    <label for="woocommerce_default_country">Địa chỉ quán <span class="woocommerce-help-tip" tabindex="0"></span></label>
                                                </th>
                                                <td> <input class="form-control" name="update_address[<?= $address->id ?>]" value="<?= $address->address ?>" type="text"></input> </td>
                                            </tr>
                                            <tr>
                                                <th scope="row" class="titledesc">
                                                    <label for="woocommerce_default_country">Quốc gia / Thành phố <span class="woocommerce-help-tip" tabindex="0" aria-label="Quốc gia và thành phố nơi trụ sở cửa hàng của bạn."></span></label>
                                                </th>
                                                <td><select name="update_city[<?= $address->id ?>]" data-placeholder="Chọn khu vực…" aria-label="Quốc gia/Khu vực" class="select2-container changeCity" tabindex="-1" aria-hidden="true">
                                                        <option></option>
                                                        <?php
                                                        foreach ($htmlCity->data['data'] as $city) {
                                                        ?>
                                                            <option value="<?= $city['id'] ?>" <?= ($city['id'] == $address->city) ? "selected" : "" ?>> <?= $city['name'] ?> </option>
                                                        <?php
                                                        }

                                                        ?>
                                                    </select></td>
                                            </tr>
                                            <tr class="quan">
                                                <th scope="row" class="titledesc">
                                                    <label for="woocommerce_default_country"> Quận <span class="woocommerce-help-tip" tabindex="0" aria-label="Quốc gia và thành phố nơi trụ sở cửa hàng của bạn."></span></label>
                                                </th>
                                                <td><select name="update_quan[<?= $address->id ?>]" style="" data-placeholder="Chọn Quận…" aria-label="Quận" class="select2-container  changeQuan" tabindex="-1" aria-hidden="true">
                                                        <option></option>
                                                        <?php
                                                        foreach ($htmlQuan->data['data'] as $quan) {
                                                        ?>
                                                            <option value="<?= $quan['id'] ?>" <?= ($quan['id'] == $address->district) ? "selected" : "" ?>> <?= $quan['name'] ?> </option>
                                                        <?php
                                                        }

                                                        ?>
                                                    </select></td>
                                            </tr>

                                            <tr class="phuong">
                                                <th scope="row" class="titledesc">
                                                    <label for="woocommerce_default_country">Phường <span class="woocommerce-help-tip" tabindex="0" aria-label="Quốc gia và thành phố nơi trụ sở cửa hàng của bạn."></span></label>
                                                </th>
                                                <td><select name="update_phuong[<?= $address->id ?>]" style="" data-placeholder="Chọn Phường…" aria-label="Phường" class="select2-container  " tabindex="-1" aria-hidden="true">
                                                        <option></option>
                                                        <?php
                                                        foreach ($htmlPhuong->data['data'] as $phuong) {
                                                        ?>
                                                            <option value="<?= $phuong['id'] ?>" <?= ($phuong['id'] == $address->ward) ? "selected" : "" ?>> <?= $phuong['name'] ?> </option>
                                                        <?php
                                                        }

                                                        ?>
                                                    </select></td>
                                            </tr>
                                            <tr class="trang thai">
                                                <th scope="row" class="titledesc">
                                                    <label for="woocommerce_default_country">Trạng thái <span class="woocommerce-help-tip" tabindex="0"></span></label>
                                                </th>
                                                <td><select name="update_status[<?= $address->id ?>]" class="status_<?=$address->status?>" style="" data-placeholder="Chọn trạng thái" tabindex="-1" aria-hidden="true">
                                                        <option value="1" <?= (1 == $address->status) ? "selected" : "" ?>>Active</option>
                                                        <option value="2" <?= (2 == $address->status) ? "selected" : "" ?>>Deactive</option>
                                                    </select></td>
                                            </tr>
                                            <!-- 
                                <tr>
                                    <td><button type="button" class="remove-address button">Xóa</button></td>
                                </tr> -->
                                        </tbody>
                                    </table>

                                </div>
                            </div>


                    <?php
                        }
                    }
                    ?>

                </div>


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
            city VARCHAR(255)   NULL,
            district VARCHAR(255)   NULL,
            ward VARCHAR(255)   NULL,
            address VARCHAR(255) CHARACTER SET utf8mb4   NULL,
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

            $quan = file_get_contents(ABSPATH . "wp-content/plugins/woo_branch/assets/data/quan-huyen/$data.json");
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
