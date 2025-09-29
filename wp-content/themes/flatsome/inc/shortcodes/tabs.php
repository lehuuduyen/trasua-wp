<?php
// [tabgroup]
function ux_tabgroup( $params, $content = null, $tag = '' ) {
	$GLOBALS['tabs'] = array();
	$GLOBALS['tab_count'] = 0;
	$i = 1;

	extract(shortcode_atts(array(
		'id' => 'panel-'.rand(),
		'title' => '',
		'style' => 'line',
		'align' => 'left',
		'class' => '',
		'visibility' => '',
		'type' => '', // horizontal, vertical
		'nav_style' => 'uppercase',
		'nav_size' => 'normal',
		'history' => 'false',
		'event' => '',
	), $params));
	if($tag == 'tabgroup_vertical'){
		$type = 'vertical';
	}

	$content = do_shortcode( $content );

	$wrapper_class[] = 'tabbed-content';
	if ( $class ) $wrapper_class[] = $class;
  if ( $visibility ) $wrapper_class[] = $visibility;

	$classes[] = 'nav';

	if($style) $classes[] = 'nav-'.$style;
	if($type == 'vertical') $classes[] = 'nav-vertical';
	if($nav_style) $classes[] = 'nav-'.$nav_style;
	if($nav_size) $classes[] = 'nav-size-'.$nav_size;
	if($align) $classes[] = 'nav-'.$align;
	if($event) $classes[] = 'active-on-' . $event;


	$classes = implode(' ', $classes);

	$return = '';

	if( is_array( $GLOBALS['tabs'] )){
	    if( have_rows('danh_muc_san_pham','option') ):
    
        while( have_rows('danh_muc_san_pham','option') ) : the_row();
         $hinh_anh = get_sub_field('hinh_anh');
	     $img_atts = wp_get_attachment_image_src($hinh_anh, 'thumbnail');
        $danh_muc = get_sub_field('danh_muc');
        $hinh_anh = $img_atts[0];
	    $tieu_de = $danh_muc->name;
	    $link = $danh_muc->slug ;
	    	if ( ! empty( $tab['anchor'] ) ) {
				$id = $link;
				$anchor = $link;
			} else {
				$id = $tieu_de ? flatsome_to_dashed( $tieu_de ) : wp_rand();
				$anchor = $link;
			}
			$active = $i == 1 ? ' active' : ''; // Set first tab active by default.
			$tabs[] = '<li id="tab-'.$id.'" class="tab'.$active.' has-icon" role="presentation"><a href="#'.$anchor.'"'.($key != 1 ? ' tabindex="-1"' : '').' role="tab" aria-selected="'.($key == 1 ? 'true' : 'false').'" aria-controls="tab_'.$id.'"><span>' . wp_kses_post( $tieu_de ) . '</span></a></li>';
			
			$html = '<div id="tab_'.$id.'" class="panel'.$active.' entry-content" role="tabpanel" aria-labelledby="tab-'.$id.'"><div class="row  equalize-box large-columns-4 medium-columns-3 small-columns-2 row-small">';
			
			
			
			
			
			$args = array(
                'post_type'      => 'product',
                'posts_per_page' => 9,
                'tax_query'      => array(
                    array(
                        'taxonomy' => 'product_cat',   // taxonomy của WooCommerce
                        'field'    => 'slug',          // có thể dùng 'id' hoặc 'slug'
                        'terms'    => $link,       // slug category
                    ),
                ),
            );
            $products = new WP_Query($args);
            
            if ($products->have_posts()) :
                while ($products->have_posts()) : $products->the_post();
                    global $product;
                    $titleProduct =get_the_title() ;
                    $priceProduct =  $product->get_price_html(); // giá
                    $image_id  = $product->get_image_id();
                    $image_url = wp_get_attachment_image_url( $image_id );
                    $slug = $product->get_slug();

                    $html .='<div class="col">
		<div class="col-inner">

			<div class="badge-container absolute left top z-1">

			</div>
			<div class="product-small box has-hover box-normal box-text-bottom">
				<div class="box-image">
					<div class="image-cover" style="padding-top:100%;">
						<a href="/san-pham/'.$slug.'" aria-label="'.$titleProduct.'">
							<img decoding="async" width="300" height="300"
          src="' . esc_url( $image_url ) . '" 
          alt="' . esc_attr( $titleProduct ) . '" 
          class="attachment-medium size-medium" /></a>
					</div>
					<div class="image-tools top right show-on-hover">
					</div>
					<div class="image-tools grid-tools text-center hide-for-small bottom hover-slide-in show-on-hover">
					</div>
				</div>

				<div class="box-text text-left" style="height: 103.969px;">
					<div class="title-wrapper">
						<p class="name product-title woocommerce-loop-product__title" style="height: 23.0312px;"><a
								href="/san-pham/'.$slug.'/"
								class="woocommerce-LoopProduct-link woocommerce-loop-product__link"
								title="Sữa Tươi Đào Matcha">'.$titleProduct.'</a></p>
					</div>
					<div class="price-wrapper" style="height: 17px;">
						<span class="price"><span class="woocommerce-Price-amount amount"><bdi>'.$priceProduct.'
					</div>
					<div class="isures-custom--qty_wrap product_vari add_to_cart_archive_shortocde"><a title="Chọn mua"
							class="my-quick-view" data-prod="1060" href="javascript:void(0);"><svg width="30"
								height="30" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
								<circle fill="none" stroke="#000" stroke-width="1.1" cx="9.5" cy="9.5" r="9"></circle>
								<line fill="none" stroke="#000" x1="9.5" y1="5" x2="9.5" y2="14"></line>
								<line fill="none" stroke="#000" x1="5" y1="9.5" x2="14" y2="9.5"></line>
							</svg></a></div>
				</div>
			</div>
		</div>
	</div>';
                    
                    
                    
                    
                    
                endwhile;
                wp_reset_postdata();
            endif;
            
            
            
            $html .='</div></div>';
			$panes[] = $html;
			$i++;
	    
	    
	    
        endwhile;
        endif;


			if($title) $title = '<h4 class="uppercase text-' . esc_attr( $align ) . '">' . wp_kses_post( $title ) . '</h4>';
			$return = '
		<div class="' . esc_attr( implode( ' ', $wrapper_class ) ) . '">
			'.$title.'
			<ul class="' . esc_attr( $classes ) . '" role="tablist">'.implode( "\n", $tabs ).'</ul><div class="tab-panels">'.implode( "\n", $panes ).'</div></div>';
	}


	return $return;
}

function ux_tab( $params, $content = null) {
	extract(shortcode_atts(array(
			'title' => '',
			'anchor' => ''
	), $params));

	$x = $GLOBALS['tab_count'];
	$GLOBALS['tabs'][ $x ] = array( 'title' => $title, 'anchor' => $anchor, 'content' => $content );
	$GLOBALS['tab_count']++;
}


add_shortcode('tabgroup', 'ux_tabgroup');
add_shortcode('tabgroup_vertical', 'ux_tabgroup');
add_shortcode('tab', 'ux_tab' );
