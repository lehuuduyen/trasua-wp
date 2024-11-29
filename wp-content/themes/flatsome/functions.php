<?php

/**
 * Flatsome functions and definitions
 *
 * @package flatsome
 */

require get_template_directory() . '/inc/init.php';

flatsome()->init();

/**
 * It's not recommended to add any custom code here. Please use a child theme
 * so that your customizations aren't lost during updates.
 *
 * Learn more here: https://developer.wordpress.org/themes/advanced-topics/child-themes/
 */
add_action('wp_head', function () {

    echo '<meta name="robots" content="noindex">';
    echo '<meta name="googlebot" content="noindex">';
});

//screen point
add_action('init', 'custom_woocommerce_endpoint');

function custom_woocommerce_endpoint()
{
    add_rewrite_endpoint('point', EP_ROOT | EP_PAGES);
}
add_action('woocommerce_account_point_endpoint', 'custom_page_content');

function custom_page_content()
{
    $current_page    = empty($current_page) ? 1 : absint($current_page);
    global $wpdb;
    $prefix = $wpdb->prefix;

    $history = $wpdb->get_results("SELECT * FROM " . $prefix . "woo_history_user_point WHERE (user_id = '" . get_current_user_id() . "' AND status = '2')");


    wc_get_template(
        'myaccount/point.php',
        array(
            'current_page'    => absint($current_page),
            'listPoint' => $history,
            'has_orders'      => 0 < count($history),
            'wp_button_class' => wc_wp_theme_get_element_class_name('button') ? ' ' . wc_wp_theme_get_element_class_name('button') : '',
        )
    );
}

add_filter('woocommerce_account_menu_items', 'custom_page_menu_item');

function custom_page_menu_item($items)
{
    $items['point'] = 'Point';
    return $items;
}
//screen point
//screen affilate
add_action('init', 'custom_woocommerce_aff_endpoint');

function custom_woocommerce_aff_endpoint()
{
    add_rewrite_endpoint('affilate', EP_ROOT | EP_PAGES);
}
add_action('woocommerce_account_affilate_endpoint', 'custom_page_affilate_content');

function custom_page_affilate_content()
{
    $current_page    = empty($current_page) ? 1 : absint($current_page);
    global $wpdb;
    $prefix = $wpdb->prefix;

    $history = $wpdb->get_results("SELECT * FROM " . $prefix . "woo_history_user_commission WHERE (user_parent = '" . get_current_user_id() . "' AND status = '1' OR status = '2') ORDER BY create_at;");


    wc_get_template(
        'myaccount/affilate.php',
        array(
            'current_page'    => absint($current_page),
            'listAffilate' => $history,
            'has_orders'      => 0 < count($history),
            'wp_button_class' => wc_wp_theme_get_element_class_name('button') ? ' ' . wc_wp_theme_get_element_class_name('button') : '',
        )
    );
}

add_filter('woocommerce_account_menu_items', 'custom_page_menu_item_affilate');

function custom_page_menu_item_affilate($items)
{
    $items['affilate'] = 'Affilate';
    return $items;
}

add_filter('woocommerce_register_shop_order_post_statuses', 'bbloomer_register_custom_order_status');

function bbloomer_register_custom_order_status($order_statuses)
{

    // Status must start with “wc-”
    $order_statuses['wc-process-shipper'] = array(
        'label' => 'Chờ giao hàng',
        'public' => false,
        'exclude_from_search' => false,
        'show_in_admin_all_list' => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop('Chờ giao hàng (%s)', 'Chờ giao hàng (%s)')
    );
    return $order_statuses;
}
add_filter('bulk_actions-edit-shop_order', 'bbloomer_get_custom_order_status_bulk');

function bbloomer_get_custom_order_status_bulk($bulk_actions)
{
    // Note: “mark_” must be there instead of “wc”
    $bulk_actions['mark_process-shipper'] = 'Change status to custom status';
    return $bulk_actions;
}
add_action('woocommerce_thankyou', 'bbloomer_thankyou_change_order_status');

function bbloomer_thankyou_change_order_status($order_id)
{
    if (! $order_id) return;
    $order = wc_get_order($order_id);

    // Status without the “wc-” prefix
    $order->update_status('process-shipper');
    $order->add_order_note('Đơn hàng đã được chuyển sang trạng thái Chờ giao hàng.');

}
// Thêm trạng thái vào WooCommerce
function add_custom_status_to_woocommerce($order_statuses)
{
    $new_statuses = array();

    foreach ($order_statuses as $key => $status) {
        $new_statuses[$key] = $status;
        if ('wc-processing' === $key) {
            $new_statuses['wc-waiting-for-shipment'] = 'Chờ giao hàng';
        }
    }

    return $new_statuses;
}
add_filter('wc_order_statuses', 'add_custom_status_to_woocommerce');



//screen affilate

// C:\xampp8.1\htdocs\trasua-wp\wp-content\plugins\woocommerce\includes\wc-account-functions.php