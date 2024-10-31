<?php
/**
* Plugin Name: woo_point
* Plugin URI: https://www.yourwebsiteurl.com/
* Description: This is the very first plugin I ever created.
* Version: 1.0
* Author: WOO_POINT
* Author URI: http://yourwebsiteurl.com/
**/

if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
if(is_admin())
    {
        new Paulund_Wp_List_Table();
    }

    
class Paulund_Wp_List_Table
{
    /**
     * Constructor will create the menu item
     */
    public $plugin_path;
    public $plugin_url;

    public function __construct()
    {
        $this->plugin_path = plugin_dir_path( dirname( __FILE__, 1 ) ).'woo_point';
        $this->plugin_url = plugin_dir_url( dirname( __FILE__ ) ).'woo_point';
        add_action( 'admin_menu', array($this, 'themeslug_enqueue_style') );
        add_action( 'admin_menu', array($this, 'add_menu_example_list_table_page' ));
        add_action('admin_enqueue_scripts', array($this, 'load_media_files'));
    }
    function themeslug_enqueue_style() {
        wp_enqueue_style( 'add_point_style', $this->plugin_url . '/assets/styles/styles.css' );
        wp_enqueue_script( 'add_point_script', $this->plugin_url . '/assets/scripts/scripts.js' );
    }

    function load_media_files() {
        wp_enqueue_media();
    }
    
    /**
     * Menu item will allow us to load the page to display the table
     */
    public function add_menu_example_list_table_page()
    {
        $this->theme_options_panel();
    }
    public function theme_options_panel(){
        add_menu_page('Tích Điểm', 'Tích Điểm', 'manage_options', 'tich-diem',  array($this, 'wps_theme_func'));

      }
       
    function wps_theme_func(){
        return require_once( "$this->plugin_path/templates/admin.php" );

      }
    public function wps_theme_func_settings(){
              echo '<div class="wrap"><div id="icon-options-general" class="icon32"><br></div>
              <h2>Settings</h2></div>';
      }
      public  function wps_theme_func_tich_diem(){
              echo '<div class="wrap"><div id="icon-options-general" class="icon32"><br></div>
              <h2>FAQ</h2></div>';
      }
    /**
     * Display the list table page
     *
     * @return Void
     */
   
}




function pluginprefix_setup_db(){
    // Function change serialized
    set_time_limit(-1);
    global $wpdb;
    try{
        if(!function_exists('dbDelta')) {
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        }
        $ptbd_table_name = $wpdb->prefix . 'woo_rank';
        dbDelta("SET GLOBAL TIME_ZONE = '+07:00';");

        if ($wpdb->get_var("SHOW TABLES LIKE '". $ptbd_table_name ."'"  ) != $ptbd_table_name ) {

            $sql  = 'CREATE TABLE '.$ptbd_table_name.'(
            id INT AUTO_INCREMENT,
            imageurl VARCHAR(255)  NULL,
            name VARCHAR(255) CHARACTER SET utf8mb4  NOT NULL,
            minimum_spending INT NOT NULL,
            price_sale_off INT NOT NULL,
            is_limit INT DEFAULT 0,
            price_sale_off_max INT NOT NULL,
                    PRIMARY KEY(id))';
            dbDelta($sql);
        }

        $ptbd_table_name = $wpdb->prefix . 'woo_setting';
        if ($wpdb->get_var("SHOW TABLES LIKE '". $ptbd_table_name ."'"  ) != $ptbd_table_name ) {

            $sql  = 'CREATE TABLE '.$ptbd_table_name.'(
            id INT AUTO_INCREMENT,
            amount_spent INT NOT NULL,
            points_converted_to_money INT NOT NULL,
                    PRIMARY KEY(id))';
            dbDelta($sql);
        }

        $ptbd_table_name = $wpdb->prefix . 'woo_history_user_point';
        if ($wpdb->get_var("SHOW TABLES LIKE '". $ptbd_table_name ."'"  ) != $ptbd_table_name ) {

            $sql  = 'CREATE TABLE '.$ptbd_table_name.'(
            id BIGINT AUTO_INCREMENT,
            user_id BIGINT NOT NULL,
            total_order INT NOT NULL,
            order_id INT NULL,
            point INT NOT NULL,
            minimum_spending INT  NULL,
            points_converted_to_money INT  NULL,
            status INT DEFAULT 1, 
            create_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP  ,

                    PRIMARY KEY(id))';
                    //status =1 (them) =2  (tru)
            dbDelta($sql);
        }
    } catch (\Exception $ex) {
    }
}

/**
 * Activate the plugin.
 */
function pluginprefix_activate() { 
    // Trigger our function that registers the custom post type plugin.
    pluginprefix_setup_db(); 
    
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
register_activation_hook( __FILE__, 'pluginprefix_activate' );
// register_deactivation_hook(
// 	__FILE__,
// 	'pluginprefix_deactivate'
// );

function my_custom_update_wc_order_status_function($order_id, $order) {
    // Check if the order type is 'shop_order'
 
    if ($order->get_type() === 'shop_order') {
        global $wpdb;
        $prefix = $wpdb->prefix;
        $history = $wpdb->get_results("SELECT * FROM ".$prefix."woo_history_user_point WHERE (order_id = '".$order_id."' AND status = '3')");
        if($history){
            $id= $history[0]->id;
            $userId  = $order->data['customer_id'];
            $totalOrder = $wpdb->get_results("SELECT SUM(total_order) as total FROM ".$prefix."woo_history_user_point WHERE (user_id = '".$userId."' AND status = '1')")[0]->total;
            $totalOrder = ($totalOrder)?$totalOrder :0;
            $checkRankBefore = $wpdb->get_results("SELECT * FROM ".$prefix."woo_rank WHERE (minimum_spending <= '".$totalOrder."') ORDER BY minimum_spending DESC LIMIT 1");

            $wpdb->query($wpdb->prepare("UPDATE ".$prefix."woo_history_user_point SET status=1 WHERE id=$id"));
            $totalOrder = $wpdb->get_results("SELECT SUM(total_order) as total FROM ".$prefix."woo_history_user_point WHERE (user_id = '".$userId."' AND status = '1')")[0]->total;
            $totalOrder = ($totalOrder)?$totalOrder :0;
            $checkRankAfter = $wpdb->get_results("SELECT * FROM ".$prefix."woo_rank WHERE (minimum_spending <= '".$totalOrder."') ORDER BY minimum_spending DESC LIMIT 1");
            if($checkRankBefore && $checkRankAfter && $checkRankBefore[0]->id != $checkRankAfter[0]->id){
                $date = date('Y-m-d H:i:s');
                $code = generateRandomString(8);
                $priceSaleOff = $checkRankAfter[0]->price_sale_off;
                $text = 'Voucher cho '.$checkRankAfter[0]->name.'. Ưu đãi '.$priceSaleOff;
                $addVoucher = $wpdb->query($wpdb->prepare("INSERT INTO ".$prefix."posts (`post_author`, `post_date`, `post_date_gmt`, `post_title`, `post_excerpt`, `post_status`, `comment_status`, `ping_status`, `post_name`, `post_modified`, `post_modified_gmt`, `post_parent`, `post_type`) VALUES ('$userId','$date','$date','$code','$text','publish','closed','closed','$code','$date','$date','0','shop_coupon')"));
                $PostIdVoucher = $wpdb->insert_id;
                $arrayEmail = serialize([$order->data['billing']['email']]);
                $sqlAddMeta = "INSERT INTO ".$prefix."postmeta ( `post_id`, `meta_key`, `meta_value` ) VALUES ('$PostIdVoucher', 'discount_type', 'fixed_cart'), ('$PostIdVoucher', 'coupon_amount', '$priceSaleOff'), ('$PostIdVoucher', 'usage_limit', '1'), ('$PostIdVoucher', 'usage_limit_per_user', '1'), ('$PostIdVoucher', 'limit_usage_to_x_items', '0'), ('$PostIdVoucher', 'usage_count', '0'), ('$PostIdVoucher', 'customer_email', '$arrayEmail'), ('$PostIdVoucher', 'customer_user', '$userId')";
                $addMeta = $wpdb->query($wpdb->prepare($sqlAddMeta));
            }
        }
        // Your custom code to update something based on the WooCommerce order status change

    }
}
add_action('woocommerce_order_status_completed', 'my_custom_update_wc_order_status_function', 10, 4);

// add_action('woocommerce_order_status_processing', 'my_custom_update_wc_order_status_function', 10, 3);
function generateRandomString($length = 10) {
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[random_int(0, $charactersLength - 1)];
    }
    return $randomString;
}