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
//screen affilate

// C:\xampp8.1\htdocs\trasua-wp\wp-content\plugins\woocommerce\includes\wc-account-functions.php