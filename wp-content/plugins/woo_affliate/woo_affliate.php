<?php

/**
 * Plugin Name: woo_affliate
 * Plugin URI: https://www.yourwebsiteurl.com/
 * Description: This is the very first plugin I ever created.
 * Version: 1.0
 * Author: WOO_AFFLIATE
 * Author URI: http://yourwebsiteurl.com/
 **/

defined('ABSPATH') or die('Hey, you can\t access this file, you silly human!');

if (is_admin()) {
  new Woo_Affliate();
}

class Woo_Affliate
{
  public $plugin_path;
  public $plugin_url;

  public function __construct()
  {
    $this->plugin_path = plugin_dir_path(dirname(__FILE__, 1)) . 'woo_affliate';
    $this->plugin_url = plugin_dir_url(dirname(__FILE__)) . 'woo_affliate';
    add_action('admin_menu', array($this, 'themeslug_enqueue_style'));
    add_action('admin_menu', array($this, 'add_menu_option'));
  }

  function themeslug_enqueue_style()
  {
    wp_enqueue_style('add_affliate_style', $this->plugin_url . '/assets/styles/affliate-styles.css');
    wp_enqueue_script('add_affliate_script', $this->plugin_url . '/assets/scripts/affliate-scripts.js');
  }

  public function add_menu_option()
  {
    $this->plugin_option();
  }

  public function plugin_option()
  {
    add_menu_page('Hoa Hồng', 'Hoa Hồng', 'manage_options', 'hoa-hong',  array($this, 'admin_template'));
  }

  function admin_template()
  {
    return require_once("$this->plugin_path/templates/admin.php");
  }
}
function plugin_setup_db()
{
  // Function change serialized
  set_time_limit(-1);
  global $wpdb;
  try {
    if (!function_exists('dbDelta')) {
      require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    }
    $prefix = $wpdb->prefix;

    $users = $wpdb->get_results("SELECT * FROM " . $prefix . "users ");
    foreach ($users as $user) {

      $check = get_user_meta($user->ID, 'code_invite', true);
      if (!$check) {
        add_user_meta($user->ID, 'code_invite', generateRandomString2());
      }
    }



    $ptbd_table_name = $wpdb->prefix . 'woo_history_share_link';
    if ($wpdb->get_var("SHOW TABLES LIKE '" . $ptbd_table_name . "'") != $ptbd_table_name) {
      dbDelta("SET GLOBAL TIME_ZONE = '+07:00';");
      $sql  = 'CREATE TABLE ' . $ptbd_table_name . '(
          id BIGINT AUTO_INCREMENT,
          user_id BIGINT NOT NULL,
          user_parent BIGINT NOT NULL,
          product INT NULL,
          status INT DEFAULT 1, 
          create_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP  ,

                  PRIMARY KEY(id))';
      //status =1 (them) =2  (tru)
      dbDelta($sql);
    }

    $ptbd_table_name = $wpdb->prefix . 'woo_history_user_commission';
    if ($wpdb->get_var("SHOW TABLES LIKE '" . $ptbd_table_name . "'") != $ptbd_table_name) {
      dbDelta("SET GLOBAL TIME_ZONE = '+07:00';");
      $sql  = 'CREATE TABLE ' . $ptbd_table_name . '(
          id BIGINT AUTO_INCREMENT,
          user_id BIGINT NOT NULL,
          user_parent BIGINT  NULL,
          product_id INT NULL,
          total_order INT NOT NULL,
          order_id INT NULL,
          commission INT DEFAULT 0,
          commission_level2 INT DEFAULT 0,
          minimum_spending INT  NULL,
          date VARCHAR(255)  NULL,
          month VARCHAR(255)  NULL,
          year VARCHAR(255)  NULL,
          payment_method TEXT  NULL,
          status INT DEFAULT 1, 
          create_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP  ,

                  PRIMARY KEY(id))';
      //status =1 (them) =2  (tru)
      dbDelta($sql);
    }
    $ptbd_table_name = $wpdb->prefix . 'otp_code';
    if ($wpdb->get_var("SHOW TABLES LIKE '" . $ptbd_table_name . "'") != $ptbd_table_name) {
      dbDelta("SET GLOBAL TIME_ZONE = '+07:00';");
      $sql  = 'CREATE TABLE ' . $ptbd_table_name . '(
          id BIGINT AUTO_INCREMENT,
          otp INT NOT NULL,
          sdt VARCHAR(255)  NOT NULL,
          status INT DEFAULT 1, 
          time BIGINT NOT NULL, 
          create_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP  ,

                  PRIMARY KEY(id))';
      //status =1 (them) =2  (tru)
      dbDelta($sql);
    }
  } catch (\Exception $ex) {
  }
}
function active_plugin()
{
  flush_rewrite_rules();
  plugin_setup_db();
}

register_activation_hook(__FILE__, 'active_plugin');


function aff_update_wc_order_status_function($order_id, $order)
{
  // Check if the order type is 'shop_order'
  
  if ($order->get_type() === 'shop_order') {
    global $wpdb;
    $prefix = $wpdb->prefix;
    $history = $wpdb->get_results("SELECT * FROM " . $prefix . "woo_history_user_commission WHERE (order_id = '" . $order_id . "' AND status = '3')");
    if ($history) {

      $id = $history[0]->id;
      $wpdb->query($wpdb->prepare("UPDATE " . $prefix . "woo_history_user_commission SET status=1 WHERE id=$id"));

    }
    // Your custom code to update something based on the WooCommerce order status change

  }
}
add_action('woocommerce_order_status_completed', 'aff_update_wc_order_status_function', 10, 4);
function my_custom_user_registration_action($user_id)
{
  // Code to run after user registers
  // Example: Add custom user meta
  add_user_meta($user_id, 'code_invite', generateRandomString2());

  // Example: Send a welcome email
  $user_info = get_userdata($user_id);
  $email = $user_info->user_email;
  wp_mail($email, 'Welcome to Our Site!', 'Thank you for joining!');
}

add_action('user_register', 'my_custom_user_registration_action', 10, 1);

function generateRandomString2($length = 10)
{
  $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
  $charactersLength = strlen($characters);
  $randomString = '';
  for ($i = 0; $i < $length; $i++) {
    $randomString .= $characters[rand(0, $charactersLength - 1)];
  }
  return $randomString;
}
add_action('woocommerce_edit_account_form', 'add_custom_code_field_to_account');
function add_custom_code_field_to_account()
{
  $user_id = get_current_user_id();
  $userParent =  get_user_meta($user_id, 'user_parent', true); 
  $userCodeParent =  get_user_meta($userParent, 'code_invite', true);

  ?>

  <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
    <label for="code">Nhập mã bạn được giới thiệu &nbsp;<span class="required"></span></label>
    <input type="text" name="code" class="woocommerce-Input woocommerce-Input--email input-text" <?= ($userParent) ? "disabled" : "" ?> value="<?= $userCodeParent ?>" />
    <span><em><?php esc_html_e('Bạn chỉ được nhập mã giới thiệu 1 lần duy nhất', 'woocommerce'); ?></em></span>
  </p>
<?php
}
add_action('woocommerce_save_account_details', 'save_custom_code_field_to_account');
function save_custom_code_field_to_account($user_id)
{
  // Check if the 'code' field is set
  if (isset($_POST['code'])) {
    // Sanitize and save the custom code field
    $userParent =  get_user_meta($user_id, 'user_parent', true);
    if (empty($userParent)) {
      global $wpdb;
      $userCode =  get_user_meta($user_id, 'code_invite', true);
      if ($userCode != $_POST['code']) {
        $result = $wpdb->get_var(
          $wpdb->prepare(
            "SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = %s AND meta_value = %s",
            'code_invite',
            $_POST['code']
          )
        );
       
        if ($result > 0) {
          update_user_meta($user_id, 'user_parent', $result);
        } else {
          wc_add_notice("<span style='color:red'>Mã mời không tồn tại</span>", 'error');
        }
      }else{
        wc_add_notice("<span style='color:red'>Không thể nhập mã mời của bạn</span>", 'error');

      }

      
    }
  }
}

add_action('woocommerce_new_order', 'custom_action_affilate_on_new_order', 10, 1);

function custom_action_affilate_on_new_order($order_id) {
    $order = wc_get_order($order_id);
    $total = $order->total;
    $userId = $order->data['customer_id'];
    $userParent =  get_user_meta($userId, 'user_parent', true);
    $configAff = get_option('woo_aff_setting');
    global $wpdb;
    $prefix = $wpdb->prefix;
    
    if ($userParent && $configAff) {
      $commissions = $total * $configAff / 100;

      $date =  date('d');
      $month =  date('m');
      $year =  date('Y');
      $re = $wpdb->query($wpdb->prepare("INSERT INTO ".$prefix."woo_history_user_commission (`order_id`, `total_order`, `user_id`, `user_parent`, `product_id`, `commission`, `commission_level2` , `minimum_spending`, `date`, `month`, `year`, `status`) VALUES ('$order_id','$total','$userId','$userParent','','$commissions',0,'$total','$date','$month','$year','3')"));
    
    }


  
    // Các hành động khi tạo đơn hàng mới
}
// Example usage: