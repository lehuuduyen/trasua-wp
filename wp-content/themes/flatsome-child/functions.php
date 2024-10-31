<?php
// Add custom Theme Functions here
function remove_acf_menu(){
  global $current_user;
  
    remove_menu_page( 'edit.php?post_type=acf-field-group' );
  
}
add_action( 'admin_menu', 'remove_acf_menu', 100 );

/* Code ẩn menu active license Flatsome */
/* Có mã bản quyền thì xóa đi để active */
function pm_disable_fl_update( $value ) {
    if ( isset( $value ) && is_object( $value ) ) {
        unset( $value->response['flatsome'] );
    }
    return $value;
}
add_action( 'init', 'pm_disable_fl_update' );
function remove_fl_update_server() {
remove_filter( 'pre_set_site_transient_update_themes', 'flatsome_get_update_info', 1, 999999 );
remove_filter( 'pre_set_transient_update_themes', 'flatsome_get_update_info', 1, 999999 );
}
add_action( 'init', 'remove_fl_update_server' );

add_action('admin_enqueue_scripts', 'ds_admin_theme_style');
add_action('login_enqueue_scripts', 'ds_admin_theme_style');
function ds_admin_theme_style() 
{
        echo '<style>#health-check-issues-critical, .flatsome-panel .about-text a.button, .flatsome-panel .nav-tab-wrapper, .flatsome-panel .panel.flatsome-panel, #wp-admin-bar-flatsome_panel-default > li:nth-child(5) ,#wp-admin-bar-flatsome_panel-default > li:nth-child(7) , li#toplevel_page_flatsome-panel .wp-submenu > li:nth-child(3), li#toplevel_page_flatsome-panel .wp-submenu > li:nth-child(5), li#toplevel_page_flatsome-panel .wp-submenu > li:nth-child(9),  .health-check-accordion-block-flatsome_registration, #flatsome-notice, ul#wp-admin-bar-root-default li#wp-admin-bar-flatsome-activate , ul li#wp-admin-bar-flatsome_panel_license, #toplevel_page_flatsome-panel ul.wp-submenu.wp-submenu-wrap > li:nth-child(2), #toplevel_page_flatsome-panel ul.wp-submenu.wp-submenu-wrap > li:nth-child(3), .woocommerce-store-alerts, .updated.woocommerce-message, #yith-license-notice{ display: none !important; }</style>';
    
}
add_action( 'init', 'remove_fl_action' );
function remove_fl_action()
{
global $wp_filter;
remove_action( 'admin_notices', 'flatsome_status_check_admin_notice' );
remove_action( 'admin_notices', 'flatsome_maintenance_admin_notice' );
}
add_action( 'wp_head', 'ds_admin_theme_style');

function disable_plugin_updates( $value ) {
  if ( isset($value) && is_object($value) ) {
    if ( isset( $value->response['advanced-custom-fields-pro/acf.php'] ) ) {
      unset( $value->response['advanced-custom-fields-pro/acf.php'] );
    }
    
if ( isset( $value->response['yith-woocommerce-ajax-product-filter-premium/init.php'] ) ) {
      unset( $value->response['yith-woocommerce-ajax-product-filter-premium/init.php'] );
    }  
 
    if ( isset( $value->response['woocommerce-tm-extra-product-options/tm-woo-extra-product-options.php'] ) ) {
      unset( $value->response['woocommerce-tm-extra-product-options/tm-woo-extra-product-options.php'] );
    } 
  }
  return $value;
}
add_filter( 'site_transient_update_plugins', 'disable_plugin_updates',999 );

add_filter( 'woocommerce_admin_features', function( $features ) {
  return array_values(
    array_filter( $features, function($feature) {
      return ! in_array( $feature, [ 'marketing', 'analytics', 'analytics-dashboard', 'analytics-dashboard/customizable' ] );
    } ) 
  );
} );
add_filter( 'woocommerce_marketing_menu_items', '__return_empty_array' );
add_filter( 'woocommerce_admin_disabled', '__return_true' );
add_filter( 'woocommerce_allow_marketplace_suggestions', '__return_false', 999 );
add_filter( 'woocommerce_helper_suppress_admin_notices', '__return_true' );
add_action('wp_print_styles', 'jltwp_adminify_remove_dashicons', 100);

/** Remove Dashicons from Admin Bar for non logged in users **/
function jltwp_adminify_remove_dashicons()
{
    if (!is_admin_bar_showing() && !is_customize_preview()) {
        wp_dequeue_style('dashicons');
        wp_deregister_style('dashicons');
    }
}
function remove_query_strings() {
   if(!is_admin()) {
       add_filter('script_loader_src', 'remove_query_strings_split', 15);
       add_filter('style_loader_src', 'remove_query_strings_split', 15);
   }
}


/* Code ẩn menu active license Flatsome */
/* Có mã bản quyền thì xóa đi để active */


function mh_load_theme_style() {
	
	//disable zxcvbn.min.js in wordpress
	if ( !is_user_logged_in() ) {
	wp_dequeue_script('wc-password-strength-meter');
    wp_deregister_script('wc-password-strength-meter');
	}
	/* Add Font Awesome */
	wp_deregister_script('font-awesome');
	wp_deregister_style('font-awesome');
	wp_register_style( 'font-awesome', get_stylesheet_directory_uri() . '/font-awesome/css/font-awesome.min.css', false, false );
	wp_enqueue_style( 'font-awesome' );
	 wp_enqueue_script('flatsome-minicart-refresh', get_stylesheet_directory_uri() . '/flatsome-minicart-refresh.js' , array('jquery'), '20230524-3', true);	
	
	if(!is_page('menu')){
		wp_register_style( 'contactusar', get_stylesheet_directory_uri() . '/cta.css', false, false );
	     wp_enqueue_style( 'contactusar' );
			 wp_enqueue_script('contactusarjs', get_stylesheet_directory_uri() . '/contactus.js' , array('jquery'), '20230920-3', true);	
	}
 
	
}
add_action( 'wp_enqueue_scripts', 'mh_load_theme_style', 99998 );
function fnisHTML($string){
 return $string != strip_tags($string) ? true:false;
}
 add_filter( 'the_content','change_h5_h3_post_title', 9999 );
function change_h5_h3_post_title( $content ){
	
	if ( is_feed() || is_preview() )
      return $content;

	$matches = array();
preg_match_all( "/(<h5(.*class=['\"](.*)post-title(.*)['\"].*)*>)+/is", $content, $matches );

    $search = array();
    $replace = array();
    
  foreach ( $matches[0] as $imgHTML ) {    
      $replaceHTML = '';
  	 $replaceHTML = preg_replace( '/<h5/is', '<h3', $imgHTML );
      $replaceHTML = preg_replace( '/<\/h5>/is', '</h3>', $replaceHTML );
  
  	 array_push( $search, $imgHTML );
     array_push( $replace, $replaceHTML );
  }
   $search = array_unique( $search );
    $replace = array_unique( $replace );

   $content = str_replace( $search, $replace, $content );
	
	
	$content = fnSetTittle($content);
	return $content;
}
function fnSetTittle($content){
	
  return preg_replace('/\<a([^<]*)(?!title)>([^<]+)\<\/a/isu', '<a${1} title="${2}">${2}</a',$content);
}
function custom_mini_cart() { 
	

               echo '<div id="minicar_box" class="minicar_box"><div class="minicart_title">
                    <strong>GIỎ HÀNG</strong><div class="minicart_title_button"><a href="javascript:void(0);" class="minicart_title_button_clear">Xóa tất cả</a><a href="javascript:void(0);"  class="minicart_title_close">Đóng</a></div>
                </div><div id="trasua_minicart"></div></div>';
           

}
add_shortcode( 'custom-mini-cart', 'custom_mini_cart' );
add_filter( 'woocommerce_widget_cart_item_quantity', 'add_minicart_quantity_fields', 10, 3 );
function add_minicart_quantity_fields( $html, $cart_item, $cart_item_key ) {
    $product_price = apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $cart_item['data'] ), $cart_item, $cart_item_key );

    return woocommerce_quantity_input( array('input_value' => $cart_item['quantity']), $cart_item['data'], false ) . $product_price;
}

function action_woocommerce_widget_shopping_cart_before_buttons(  ) { 
 ?>
<form class="checkout_coupon devvn_coupon_wrap" method="post">
			<div class="coupon"><input type="text" name="coupon_code" class="input-text" id="coupon_code" value="" placeholder="Mã ưu đãi" /> <input type="submit" class="is-form expand" name="apply_coupon" value="<?php esc_attr_e( 'Áp dụng', 'woocommerce' ); ?>" />
				<?php do_action( 'woocommerce_cart_coupon' ); ?>
			</div>
</form>
<?php
}; 
add_action( 'woocommerce_widget_shopping_cart_before_buttons', 'action_woocommerce_widget_shopping_cart_before_buttons', 10, 0 ); 
add_action("wp_ajax_load_minicart", "load_minicart");
add_action("wp_ajax_nopriv_load_minicart", "load_minicart");

function load_minicart() {
	 
	 woocommerce_mini_cart();
	  $count_items =  WC()->cart->cart_contents_count; 
	  $cart_subtotal = number_format(WC()->cart->subtotal, 0, ',', '.');
	  $cart_total = number_format(WC()->cart->total, 0, ',', '.');
	  $coupon_applied = '';
	  $discount_total = '';
	  if (!empty(WC()->cart->get_applied_coupons()))
      {
	      $coupon_applied = WC()->cart->get_applied_coupons()[0];
		  $getCPDetails =  new WC_Coupon($coupon_applied);
          $discount_total  =  number_format($getCPDetails->amount, 0, ',', '.');
	  }
	  echo '<input id="mt_count_items" type="hidden" value="'. $count_items  .'" />';
	   echo '<input id="mt_cart_subtotal" type="hidden" value="'.  $cart_subtotal  .'" />';
	  echo '<input id="mt_cart_total" type="hidden" value="'.  $cart_total  .'" />';
	  echo '<input id="mt_applied_coupons" type="hidden" value="'.  $coupon_applied  .'" />';
	   echo '<input id="mt_discount_amount" type="hidden" value="'.  $discount_total  .'" />';
	 die();
}
function ajax_remove_item_cart() {
	  $cart_item_key = $_POST['hash'];
	 if($cart_item_key)
	 {
       WC()->cart->remove_cart_item($cart_item_key);
      
     } 
	  echo 'true';
	  die();
}
add_action('wp_ajax_remove_item_cart', 'ajax_remove_item_cart');
add_action('wp_ajax_nopriv_remove_item_cart', 'ajax_remove_item_cart');
function ajax_remove_coupon_cart() {
	
	  $coupon = htmlspecialchars($_POST['coupon'], ENT_QUOTES | ENT_HTML5, $encoding);
	  
	//WC()->session->__unset( 'wc_notices' );
	 
        WC()->cart->remove_coupon( $coupon );
        WC()->cart->calculate_totals();
     
	   $result_code =  array("success"=> array(array("notice"=>"Ưu đãi đã được gỡ bỏ.")));
	    echo json_encode($result_code);
	  die();
}
add_action('wp_ajax_ajax_remove_coupon_cart', 'ajax_remove_coupon_cart');
add_action('wp_ajax_nopriv_ajax_remove_coupon_cart', 'ajax_remove_coupon_cart');

function ajax_empty_cart() {
	  
	  try {
	  WC()->session->__unset( 'wc_notices' );
      WC()->cart->empty_cart();
	  WC()->session->set('cart', array());
	  
	   $error_code =  array("success"=> array(array("notice"=>"Đã Xóa Tất Cả Trong Giỏ Hàng")));
	    echo json_encode($error_code);
	  }
	  catch (Exception $e) {
		  $error_code =  array("error"=> array(array("notice"=>json_encode($text, JSON_UNESCAPED_UNICODE))));

  

         echo json_encode($error_code);
      }
	  
	  die();
}
add_action('wp_ajax_ajax_empty_cart', 'ajax_empty_cart');
add_action('wp_ajax_nopriv_ajax_empty_cart', 'ajax_empty_cart');


function ajax_apply_coupon_cart() 
{
	 $coupon = htmlspecialchars($_POST['coupon'], ENT_QUOTES | ENT_HTML5, $encoding);
	 if($coupon)
	 {
		 if ( WC()->cart->has_discount( $coupon ) )
		 {
			 echo 'Đã áp dụng mã ưu đãi này';
		 }
		 else{
			     WC()->session->__unset( 'wc_notices' );
				 if(WC()->cart->apply_coupon( $coupon ))
				 {
					   WC()->cart->calculate_totals();
					   echo json_encode(WC()->session->get( 'wc_notices'));
				 }
			     else{
					 echo json_encode(WC()->session->get( 'wc_notices'));
				 }
			 
			
		 }
       
        
     } 
	 else{
		 echo 'Không thể áp dụng mã này';
	 }
	  die();
}
add_action('wp_ajax_ajax_apply_coupon_cart', 'ajax_apply_coupon_cart');
add_action('wp_ajax_nopriv_ajax_apply_coupon_cart', 'ajax_apply_coupon_cart');

function ajax_qty_cart() {

    // Set item key as the hash found in input.qty's name
    $cart_item_key = $_POST['hash'];

    // Get the array of values owned by the product we're updating
    $threeball_product_values = WC()->cart->get_cart_item( $cart_item_key );

    // Get the quantity of the item in the cart
    $threeball_product_quantity = apply_filters( 'woocommerce_stock_amount_cart_item', apply_filters( 'woocommerce_stock_amount', preg_replace( "/[^0-9\.]/", '', filter_var($_POST['quantity'], FILTER_SANITIZE_NUMBER_INT)) ), $cart_item_key );

    // Update cart validation
    $passed_validation  = apply_filters( 'woocommerce_update_cart_validation', true, $cart_item_key, $threeball_product_values, $threeball_product_quantity );

    // Update the quantity of the item in the cart
    if ( $passed_validation ) {
        WC()->cart->set_quantity( $cart_item_key, $threeball_product_quantity, true );
    }
    $count_items =  WC()->cart->cart_contents_count; 
	 $cart_subtotal = WC()->cart->subtotal;
	 $cart_total = WC()->cart->total;
	if($cart_subtotal == $cart_total){
    echo '<strong>Tổng ('. $count_items .' món):</strong> <span class="woocommerce-Price-amount amount"><bdi>'. number_format(WC()->cart->total, 0, ',', '.') .'<span class="woocommerce-Price-currencySymbol">₫</span></bdi></span>';
	}
	else{
		 echo '<strong>Tổng ('. $count_items .' món):</strong> <span class="price"><del aria-hidden="true"><span class="woocommerce-Price-amount amount"><bdi>'. number_format($cart_subtotal, 0, ',', '.') .'<span class="woocommerce-Price-currencySymbol">₫</span></bdi></span></del> <ins><span class="woocommerce-Price-amount amount"><bdi>'. number_format($cart_total, 0, ',', '.') .'<span class="woocommerce-Price-currencySymbol">₫</span></bdi></span></ins></span>';
	}
    
    die();

}

add_action('wp_ajax_qty_cart_update', 'ajax_qty_cart');
add_action('wp_ajax_nopriv_qty_cart_update', 'ajax_qty_cart');

function isures_custom_quantity_field_archive()
{
 
    global $product;
    
		 echo '<div class="isures-custom--qty_wrap product_vari">';
         echo '<a title="Chọn mua" href="#quick-view" data-prod="'. $product->get_id() .'" class="quick-view primary is-small add_to_cart_archive mb-0 button is-outline" rel="nofollow">Chọn mua</a>';
        
        echo '</div>';
	
}
 
//add_action('woocommerce_after_shop_loop_item', 'isures_custom_quantity_field_archive', 10);

add_action('flatsome_product_box_after','isures_custom_quantity_field_archive_shortocde');
function isures_custom_quantity_field_archive_shortocde()
{
	
	 global $product;
    
		 echo '<div class="isures-custom--qty_wrap product_vari add_to_cart_archive_shortocde">';
    
        echo '<a title="Chọn mua" class="my-quick-view" data-prod="'. $product->get_id() .'" href="javascript:void(0);"><svg width="30" height="30" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                                    <circle fill="none" stroke="#000" stroke-width="1.1" cx="9.5" cy="9.5" r="9"></circle>
                                                    <line fill="none" stroke="#000" x1="9.5" y1="5" x2="9.5" y2="14"></line>
                                                    <line fill="none" stroke="#000" x1="5" y1="9.5" x2="14" y2="9.5"></line>
                                                </svg></a>';
        // echo '<a title="Chọn mua" href="#quick-view" data-prod="'. $product->get_id() .'" class="quick-view quick-view-2 primary is-small add_to_cart_archive mb-0 button is-outline" rel="nofollow">Chọn mua</a>';
	   
        echo '</div>';
	
}
if( function_exists('acf_add_options_page') ) {
	
	acf_add_options_page(array(
		'page_title' 	=> 'Cấu hình khác',
		'menu_title'	=> 'Cấu hình khác',
		'menu_slug' 	=> 'cau-hinh-khac',
		'capability'	=> 'install_plugins',
		'redirect'		=> false
	));
	
	
	
}
function register_themecomplete_css()
{
   wp_register_style( 'themecomplete-epo', '/wp-content/plugins/woocommerce-tm-extra-product-options/assets/css/epo.min.css', false, false );
	wp_enqueue_style('themecomplete-epo');
	
  // wp_register_script( 'themecomplete-epo', '/wp-content/plugins/woocommerce-tm-extra-product-options/assets/js/epo.min.js', false, true );
	//wp_enqueue_script('themecomplete-epo');
}
add_action('wp_enqueue_scripts', 'register_themecomplete_css');
function fnMenuOrder(){
	$page_id = get_queried_object_id();
    
	ob_start();
	
	
	$html = '';
	$html .= '<div class="danh-mucsp"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" id="Layer_1" style="enable-background:new 0 0 512 512;" version="1.1" viewBox="0 0 512 512" xml:space="preserve" class="isures-open"><g><g><path class="st0" d="M388.3,201.9h-196c-9.9,0-17.9-9-17.9-20s8-20,17.9-20h196c9.9,0,17.9,9,17.9,20S398.2,201.9,388.3,201.9z" /></g><circle class="st0" cx="131.2" cy="181.9" r="25.5"/><g><path class="st0" d="M388.3,276h-196c-9.9,0-17.9-9-17.9-20s8-20,17.9-20h196c9.9,0,17.9,9,17.9,20S398.2,276,388.3,276z"/></g><circle class="st0" cx="131.2" cy="256" r="25.5"/><g><path class="st0" d="M388.3,350.1h-196c-9.9,0-17.9-9-17.9-20s8-20,17.9-20h196c9.9,0,17.9,9,17.9,20S398.2,350.1,388.3,350.1z"/></g><circle class="st0" cx="131.2" cy="330.1" r="25.5"/></g></svg>
	<div class="isures-close"><svg width="30" height="30" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <line fill="none" stroke="#000" stroke-width="1.1" x1="1" y1="1" x2="13" y2="13"></line>
                    <line fill="none" stroke="#000" stroke-width="1.1" x1="13" y1="1" x2="1" y2="13"></line>
                </svg></div>
	</div><div class="menuorder_cart_icons"><svg xmlns="http://www.w3.org/2000/svg" fill="none" height="27" viewBox="0 0 30 27" width="30"><path d="M1.39999 1.70001H6.60001" stroke="#4F4F4F" stroke-linecap="round" stroke-miterlimit="10" stroke-width="1"></path><path d="M6.60001 1.70001L11 18.9" stroke="#4F4F4F" stroke-linecap="round" stroke-miterlimit="10" stroke-width="1"></path><path d="M11.8 18.9H28.3" stroke="#4F4F4F" stroke-linecap="round" stroke-miterlimit="10" stroke-width="1"></path><path d="M13.8 25.7C15.4569 25.7 16.8 24.3569 16.8 22.7C16.8 21.0432 15.4569 19.7 13.8 19.7C12.1431 19.7 10.8 21.0432 10.8 22.7C10.8 24.3569 12.1431 25.7 13.8 25.7Z" stroke="#4F4F4F" stroke-linecap="round" stroke-miterlimit="10" stroke-width="1"></path><path d="M25.3 25.7C26.9568 25.7 28.3 24.3569 28.3 22.7C28.3 21.0432 26.9568 19.7 25.3 19.7C23.6431 19.7 22.3 21.0432 22.3 22.7C22.3 24.3569 23.6431 25.7 25.3 25.7Z" stroke="#4F4F4F" stroke-linecap="round" stroke-miterlimit="10" stroke-width="1"></path><path d="M25.7 14.6H11.3C10.7 14.6 10.1 14.2 10 13.6L8.1 6.90001C7.9 6.00001 8.49999 5.20001 9.39999 5.20001H27.5C28.4 5.20001 29.1 6.10001 28.8 6.90001L26.9 13.6C26.9 14.2 26.4 14.6 25.7 14.6Z" stroke="#4F4F4F" stroke-linecap="round" stroke-miterlimit="10" stroke-width="1"></path></svg></div>';
	$html_menu = '';
	$html_menu_mobile = '';
	$html_menu_top = '';
	$html_cats = '';
	$logo = '<a class="mt_menuorder_logo" href="'. get_site_url() .'"><img src="'.get_field('logo','option') .'" /></a>';
	$html_search = '<div class="menuorder-box-header">'. $logo .'
                    <div class="menuorder-searchbox">
                        <input type="text" placeholder="Tìm kiếm sản phẩm..." class="search-menuorder">
                        <button type="submit"><svg id="Capa_1" enable-background="new 0 0 551.13 551.13" height="512" viewBox="0 0 551.13 551.13" width="512" xmlns="http://www.w3.org/2000/svg"><path d="m551.13 526.776-186.785-186.785c30.506-36.023 49.003-82.523 49.003-133.317 0-113.967-92.708-206.674-206.674-206.674s-206.674 92.707-206.674 206.674 92.707 206.674 206.674 206.674c50.794 0 97.294-18.497 133.317-49.003l186.785 186.785s24.354-24.354 24.354-24.354zm-344.456-147.874c-94.961 0-172.228-77.267-172.228-172.228s77.267-172.228 172.228-172.228 172.228 77.267 172.228 172.228-77.267 172.228-172.228 172.228z"></path></svg></button>
                    </div>
                </div>';
if( have_rows('danh_muc_san_pham','option') ):
	$html .= '[section label="SP" bg_color="rgb(229, 228, 230)" padding="12px"][row style="small" class="menu_trasua_row"]';
	
    $html_menu .= '<div class="mt_menuorder_box"><ul class="menuorder-cat">';
	$html_menu_mobile .= '<ul class="isures-order--cat_items">';
	$html_menu_top .= '<ul class="isures-order--cat_item">';
    $dem = 0;
    while( have_rows('danh_muc_san_pham','option') ) : the_row();
         $hinh_anh = get_sub_field('hinh_anh');
	     $img_atts = wp_get_attachment_image_src($hinh_anh, 'thumbnail');
         $danh_muc = get_sub_field('danh_muc');
         $html_menu .= '<li ><a id="danh_muc_' . $danh_muc->term_id . '" class="'.($dem ==0?'active':'').'" href="#danh_muc_' . $danh_muc->term_id . '"><img src="' . $img_atts[0] . '" /> <span>' . $danh_muc->name . '</span> <span class="isures-count--item_cate">('. $danh_muc->count.')</span></a></li>';
	     $html_menu_top .= '<li data-link="danh_muc_' . $danh_muc->term_id . '" data-index="'. $dem .'" class="'.($dem ==0?'active':'').'" ><a id="danh_muc_' . $danh_muc->term_id . '" class="isures-menuorder--product_order" href="#danh_muc_' . $danh_muc->term_id . '"><img src="' . $img_atts[0] . '" /> <span>' . $danh_muc->name . '</span> <span class="isures-count--item_cate">('. $danh_muc->count.')</span></a></li>';
	     $html_cats .= '[row_inner class="box-collapse-producs"][col_inner span__sm="12" class="pb-0"]';
	     $html_cats .= '[scroll_to title="'. $danh_muc->name .'" link="danh_muc_'. $danh_muc->term_id .'" bullet="false"]';
	     $html_cats .= '<div class="menuorder-list-prod-title"><h4>'. $danh_muc->name .'</h4></div>';
	
         $html_cats .= '[ux_products products="-1" style="vertical" type="row" columns="1" equalize_box="0" cat="'. $danh_muc->term_id .'" image_width="40" image_size="medium" text_pos="top" text_align="right"]';
	     $html_cats .= '[/col_inner][/row_inner]';
	     $html_menu_mobile .='<li><a href="#danh_muc_' . $danh_muc->term_id . '" class="isures-menuorder--product_order">
                                                        
                            <span>' . $danh_muc->name . '</span>
                            <span class="isures-count--item_cate">('. $danh_muc->count.')</span>
                        </a>
                    </li>';
	     $dem++;
    endwhile;
	
	$html_menu .='</ul></div>';
	$html_menu_mobile .= '</ul>';
	$html_menu_top .= '</ul>';
	$html .= '[col span="12" span__sm="12" class="col_top_menu_trasua"]'. $html_menu_top .'[/col]';
	$html .= '[col span="3" span__sm="12" divider="0" sticky="true" visibility="hide-for-medium"]' . $html_menu .'[/col]';
	$html .= '[col span="5" span__sm="12" class="column_menutrasua"]' . $html_search. $html_cats . '[/col]';
	$html .= '[col span="4" span__sm="12" class="right_col_sticky"][custom-mini-cart /][/col]';
	$html .= '[/row][/section]';
	echo do_shortcode($html.$html_menu_mobile);
	echo '<div class="isures-loading--order_wrap active">
                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" style="margin:auto;display:block;" width="200px" height="200px" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid">
<circle cx="50" cy="50" fill="none" stroke="#FC921C" stroke-width="10" r="35" stroke-dasharray="164.93361431346415 56.97787143782138">
  <animateTransform attributeName="transform" type="rotate" repeatCount="indefinite" dur="1s" values="0 50 50;360 50 50" keyTimes="0;1"></animateTransform>
</circle>
</svg>
            </div>';
	echo '<style>';
	echo '.isures-loading--order_wrap {
    position: fixed;
    width: 100%;
    height: 100%;
    left: 0;
    top: 0;
    background: rgba(255, 255, 255, .95);
    z-index: 9;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
     visibility: hidden;
    z-index: 999999;
}

.isures-loading--order_wrap.active {
    opacity: 1;
    visibility: visible;
}

.isures-loading--order_wrap svg {
    width: 139px;
}';
	echo '.page-id-'.$page_id.' .woocommerce-message { display : none;} ';
	echo '@media (max-width: 549px){';
	echo '.page-id-'.$page_id.'{
padding-top: 52px;
padding-bottom: 50px;

}
.page-id-'.$page_id.' #header, .page-id-'.$page_id.' #footer, .page-id-'.$page_id.' .menu_order_icon{
  display: none !important;
}';
	echo '}';
	echo '</style>';
else :
 
endif;
	$contents = ob_get_contents();
	ob_end_clean();


	return $contents;
}
add_shortcode('MenuOrder','fnMenuOrder');

//Chỉ hoạt động trên web demo trasua.muathemewp.com, web của khách không hiển các thẻ meta này, đừng  'no nắng', không thích thì xóa đi
function fnShowDescriptionMeta(){
	
	if(strpos(get_site_url(),"trasua.muathemewp.com") && is_front_page()){
		?>
   
	<meta name="description" content="Theme Wordpress bán trà sữa, thức ăn nhanh cao cấp - Milktea - Giao diện website tuyệt đẹp và đặc biệt chuyên nghiệp với tính năng menu order" class="yoast-seo-meta-tag" />
	<meta name="title" content="Theme Wordpress bán trà sữa, thức ăn nhanh cao cấp - Milktea" class="yoast-seo-meta-tag" />
	
	<meta property="og:locale" content="vi_VN" class="yoast-seo-meta-tag" />
	<meta property="og:type" content="article" class="yoast-seo-meta-tag" />
	<meta property="og:title" content="Theme Wordpress bán trà sữa, thức ăn nhanh cao cấp - Milktea" class="yoast-seo-meta-tag" />
	<meta property="og:description" content="Theme Wordpress bán trà sữa, thức ăn nhanh cao cấp - Milktea - Giao diện website tuyệt đẹp và đặc biệt chuyên nghiệp với tính năng menu order" class="yoast-seo-meta-tag" />
	<meta property="og:url" content="https://muatheme.com/san-pham/theme-wordpress-ban-tra-sua-thuc-an-nhanh-cao-cap-milktea/" class="yoast-seo-meta-tag" />
	<meta property="og:site_name" content="Mua Theme Wordpress Giá Rẻ" class="yoast-seo-meta-tag" />
	<meta property="article:publisher" content="https://www.facebook.com/muathemewp" class="yoast-seo-meta-tag" />
	<meta property="article:modified_time" content="2021-10-18T08:06:45+00:00" class="yoast-seo-meta-tag" />
	<meta property="og:image" content="https://muatheme.com/wp-content/uploads/2021/10/theme-wordpress-tra-sua.jpg" class="yoast-seo-meta-tag" />
	<meta property="og:image:width" content="1024" class="yoast-seo-meta-tag" />
	<meta property="og:image:height" content="542" class="yoast-seo-meta-tag" />
	<meta property="og:image:alt" content="Theme Wordpress bán trà sữa, thức ăn nhanh cao cấp - Milktea" class="yoast-seo-meta-tag" />
<meta property="og:image:type" content="image/jpeg" class="yoast-seo-meta-tag" />
	<meta name="twitter:card" content="summary_large_image" class="yoast-seo-meta-tag" />
	<meta name="twitter:title" content="Theme Wordpress bán trà sữa, thức ăn nhanh cao cấp - Milktea" class="yoast-seo-meta-tag" />
	<meta name="twitter:description" content="Theme Wordpress bán trà sữa, thức ăn nhanh cao cấp - Milktea - Giao diện website tuyệt đẹp và đặc biệt chuyên nghiệp với tính năng menu order" class="yoast-seo-meta-tag" />
	<meta name="twitter:image" content="https://muatheme.com/wp-content/uploads/2021/10/theme-wordpress-tra-sua.jpg" class="yoast-seo-meta-tag" />
	<meta name="twitter:label1" content="Ước tính thời gian đọc" class="yoast-seo-meta-tag" />
	<meta name="twitter:data1" content="1 phút" class="yoast-seo-meta-tag" />
	<script type="application/ld+json" class="yoast-schema-graph">{"@context":"https://schema.org","@graph":[{"@type":"WebPage","@id":"https://muatheme.com/san-pham/theme-wordpress-ban-tra-sua-thuc-an-nhanh-cao-cap-milktea/","url":"https://muatheme.com/san-pham/theme-wordpress-ban-tra-sua-thuc-an-nhanh-cao-cap-milktea/","name":"Theme Wordpress bán trà sữa, thức ăn nhanh cao cấp - Milktea","isPartOf":{"@id":"https://muatheme.com/#website"},"primaryImageOfPage":{"@id":"https://muatheme.com/san-pham/theme-wordpress-ban-tra-sua-thuc-an-nhanh-cao-cap-milktea/#primaryimage"},"image":{"@id":"https://muatheme.com/san-pham/theme-wordpress-ban-tra-sua-thuc-an-nhanh-cao-cap-milktea/#primaryimage"},"thumbnailUrl":"https://muatheme.com/wp-content/uploads/2021/10/theme-wordpress-tra-sua.jpg","datePublished":"2021-10-18T07:00:05+00:00","dateModified":"2021-10-18T08:06:45+00:00","description":"Theme Wordpress bán trà sữa, thức ăn nhanh cao cấp - Milktea - Giao diện website tuyệt đẹp và đặc biệt chuyên nghiệp với tính năng menu order","breadcrumb":{"@id":"https://muatheme.com/san-pham/theme-wordpress-ban-tra-sua-thuc-an-nhanh-cao-cap-milktea/#breadcrumb"},"inLanguage":"vi","potentialAction":[{"@type":"ReadAction","target":["https://muatheme.com/san-pham/theme-wordpress-ban-tra-sua-thuc-an-nhanh-cao-cap-milktea/"]}]},{"@type":"ImageObject","inLanguage":"vi","@id":"https://muatheme.com/san-pham/theme-wordpress-ban-tra-sua-thuc-an-nhanh-cao-cap-milktea/#primaryimage","url":"https://muatheme.com/wp-content/uploads/2021/10/theme-wordpress-tra-sua.jpg","contentUrl":"https://muatheme.com/wp-content/uploads/2021/10/theme-wordpress-tra-sua.jpg","width":1024,"height":542},{"@type":"BreadcrumbList","@id":"https://muatheme.com/san-pham/theme-wordpress-ban-tra-sua-thuc-an-nhanh-cao-cap-milktea/#breadcrumb","itemListElement":[{"@type":"ListItem","position":1,"name":"Trang chủ","item":"https://muatheme.com/"},{"@type":"ListItem","position":2,"name":"Mua theme WordPress giá rẻ uy tín","item":"https://muatheme.com/shop/"},{"@type":"ListItem","position":3,"name":"Theme Wordpress"}]},{"@type":"WebSite","@id":"https://muatheme.com/#website","url":"https://muatheme.com/","name":"Mua Theme Wordpress Giá Rẻ","description":"Chuyên bán theme WP Đẹp. Uy tín, Chất lượng","potentialAction":[{"@type":"SearchAction","target":{"@type":"EntryPoint","urlTemplate":"https://muatheme.com/?s={search_term_string}"},"query-input":"required name=search_term_string"}],"inLanguage":"vi"}]}</script>
	<!-- / Yoast SEO plugin. -->

	<?php }
}
add_action( 'wp_head' ,'fnShowDescriptionMeta',5);

function fnCategoriesHome(){
	$page_id = get_queried_object_id();
    
	ob_start();
	if( have_rows('danh_muc_san_pham_home','option') ):
	echo '<div class="row  equalize-box row-small tch-categories ">';
	$dem = 0;
    while( have_rows('danh_muc_san_pham_home','option') ) : the_row();
        $hinh_anh = get_sub_field('hinh_anh');
	    $tieu_de = get_sub_field('tieu_de');
	    $link = get_sub_field('link');
    ?>
        <div class="col">
			<div class="col-inner">
				<a data-index="<?php echo $dem; ?>" title="<?php echo esc_attr($tieu_de); ?>" href="<?php echo $link; ?>"><div class="tch-category-card__image"><img src="<?php echo $hinh_anh; ?>" alt="<?php echo esc_attr($tieu_de); ?>"></div>
				<div class="tch-category-card__content"><h3><?php echo $tieu_de; ?></h3></div></a>
			</div>
        </div> 
    <?php
	$dem++;
    endwhile;
	echo '</div>';
else :

endif;
	$contents = ob_get_contents();
	ob_end_clean();
    return $contents;
}
add_shortcode('categoryhome','fnCategoriesHome');