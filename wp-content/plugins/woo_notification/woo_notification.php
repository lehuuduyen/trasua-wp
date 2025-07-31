<?php

/**
 * Plugin Name: woo_notification
 * Plugin URI: https://www.yourwebsiteurl.com/
 * Description: This is the very first plugin I ever created.
 * Version: 1.0 ExponentPushToken[YjVosiEOAvr2jlwznM8tNd]
 * Author: WOO_notification
 * Author URI: http://yourwebsiteurl.com/
 **/
if (isset($_POST['submitNotification'])) {


    global $wpdb;
    $prefix = $wpdb->prefix;

    $link = $_POST['link'];
    $content = $_POST['content'];
    $prefix = $wpdb->prefix; // Get the table prefix

    // Insert Data
    $wpdb->query(
        $wpdb->prepare(
            "INSERT INTO " . $prefix . "woo_notification (link, content) VALUES (%s, %s)",
            $link,
            $content
        )
    );

    // Get the last inserted ID
    $idNoti = $wpdb->insert_id;

    $listUser = $wpdb->get_results("SELECT ID FROM " . $prefix . "users");


    // check thêm thông báo user
    foreach ($listUser as $key => $val) {
        $wpdb->query($wpdb->prepare("INSERT INTO " . $prefix . "woo_user_notification  (user_id,notification_id) VALUES ('" . $val->ID . "','" . $idNoti . "'); "));
    }
    $query = "SELECT DISTINCT token FROM {$prefix}woo_user_key_notification";
    $listToken = $wpdb->get_col($query);
  
    foreach ($listToken as  $token) {
        $wpdb->query($wpdb->prepare("INSERT INTO " . $prefix . "woo_send_notification  (token,content) VALUES ('" . $token . "','" . $content . "'); "));
    }
    // check Gửi thông báo




}
if (! class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}
if (is_admin()) {
    new Notification_Wp_List_Table();
}


class Notification_Wp_List_Table
{
    /**
     * Constructor will create the menu item
     */
    public $plugin_path;
    public $plugin_url;

    public function __construct()
    {
        $this->plugin_path = plugin_dir_path(dirname(__FILE__, 1)) . 'woo_notification';
        $this->plugin_url = plugin_dir_url(dirname(__FILE__)) . 'woo_notification';
        add_action('admin_menu', array($this, 'themeslug_enqueue_style'));
        add_action('admin_menu', array($this, 'add_menu_example_list_table_page'));
        add_action('admin_enqueue_scripts', array($this, 'load_media_files'));
    }
    function themeslug_enqueue_style()
    {
        wp_enqueue_style('add_notification', $this->plugin_url . '/assets/styles/select2.min.css');
        wp_enqueue_style('datatables-css', $this->plugin_url . '/assets/styles/jquery.dataTables.min.css');
        wp_enqueue_script('datatables-js', $this->plugin_url . '/assets/scripts/jquery.dataTables.min.js');
        wp_enqueue_style('add_notification', $this->plugin_url . '/assets/styles/styles.css');
        wp_enqueue_script('add_notification', $this->plugin_url . '/assets/scripts/select2.min.js');
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

        add_menu_page(
            'Thông báo',
            "Thông báo",
            'manage_options',
            'notification',
            array($this, 'render_store_locations_page')
        );
    }


    // Hiển thị trang cài đặt địa chỉ cửa hàng
    function render_store_locations_page()
    {




?>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
        <div style="margin:20px 0">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal" data-bs-whatever="@mdo">Thêm</button>

        </div>

        <!-- Modal -->
        <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Tạo thông báo</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="post" action="" id="store-notification-table">

                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="recipient-name" class="col-form-label">Link:</label>
                                <input type="text" name="link" class="form-control" id="recipient-name">
                            </div>
                            <div class="mb-3">
                                <label for="message-text" class="col-form-label">Message:</label>
                                <textarea class="form-control" name="content" id="message-text"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" name="submitNotification" class="btn btn-primary">Gửi thông báo</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
        <?php
        global $wpdb;
        $notifications = $wpdb->get_results("SELECT id,link,content FROM wp_woo_notification ORDER BY id DESC");
        ?>
        <table id="usersTable" class="display">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Link</th>
                    <th>content</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($notifications as $notification) : ?>
                    <tr>
                        <td><?php echo esc_html($notification->id); ?></td>
                        <td><?php echo esc_html($notification->link); ?></td>
                        <td><?php echo esc_html($notification->content); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <script>
            jQuery(document).ready(function($) {
                $('#usersTable').DataTable({
                    "paging": true,
                    "searching": true,
                    "ordering": true,
                    "pageLength": 5
                });
            });
        </script>

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




function pluginprefix_setup_db2()
{
    // Function change serialized
    set_time_limit(-1);
    global $wpdb;

    try {
        if (!function_exists('dbDelta')) {
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        }
        $ptbd_table_name = $wpdb->prefix . 'woo_notification';

        dbDelta("SET GLOBAL TIME_ZONE = '+07:00';");
        if ($wpdb->get_var("SHOW TABLES LIKE '" . $ptbd_table_name . "'") != $ptbd_table_name) {

            $sql  = 'CREATE TABLE ' . $ptbd_table_name . '(
            id INT AUTO_INCREMENT,
            link VARCHAR(255)   NULL,
            content text CHARACTER SET utf8mb4   NULL,
            status INT DEFAULT 1,
            PRIMARY KEY(id))';
            dbDelta($sql);
        }
        
        $ptbd_table_name = $wpdb->prefix . 'woo_user_notification';

        dbDelta("SET GLOBAL TIME_ZONE = '+07:00';");
        if ($wpdb->get_var("SHOW TABLES LIKE '" . $ptbd_table_name . "'") != $ptbd_table_name) {

            $sql  = 'CREATE TABLE ' . $ptbd_table_name . '(
            id BIGINT AUTO_INCREMENT,
            user_id INT  ,
            notification_id INT,
            status INT DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

            PRIMARY KEY(id))';
            dbDelta($sql);
        }
        $ptbd_table_name = $wpdb->prefix . 'woo_user_key_notification';

        dbDelta("SET GLOBAL TIME_ZONE = '+07:00';");
        if ($wpdb->get_var("SHOW TABLES LIKE '" . $ptbd_table_name . "'") != $ptbd_table_name) {

            $sql  = 'CREATE TABLE ' . $ptbd_table_name . '(
            id BIGINT AUTO_INCREMENT,
            user_id INT  ,
            token VARCHAR(255),
            status INT DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

            PRIMARY KEY(id))';
            dbDelta($sql);
        }
        $ptbd_table_name = $wpdb->prefix . 'woo_send_notification';

        dbDelta("SET GLOBAL TIME_ZONE = '+07:00';");
        if ($wpdb->get_var("SHOW TABLES LIKE '" . $ptbd_table_name . "'") != $ptbd_table_name) {

            $sql  = 'CREATE TABLE ' . $ptbd_table_name . '(
            id BIGINT AUTO_INCREMENT,
            token VARCHAR(255),
            content text CHARACTER SET utf8mb4   NULL,
            status INT DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
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
function pluginprefix_activate2()
{
    // Trigger our function that registers the custom post type plugin.
    pluginprefix_setup_db2();

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
register_activation_hook(__FILE__, 'pluginprefix_activate2');
