<?php // phpcs:ignore WordPress.Files.FileName
/**
 * This is the slider with WooCommerce layout
 *
 * @package YITH WooCommerce Product Slider Carousel\Templates
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<?php

global $wpdb, $woocommerce, $woocommerce_loop;

$hide_cart = isset( $hide_add_to_cart ) ? $hide_add_to_cart : false;
$hideprice = isset( $hide_price ) ? $hide_price : false;
$products  = new WP_Query( $query_args );

$i    = 0;
$cols = '';

$priorities = array(
	'hide_cart'  => -1,
	'hide_price' => -1,
);

if ( $hide_cart ) {
	$priorities['hide_cart'] = has_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart' );

	if ( ! $priorities['hide_cart']  ) {
		remove_action( 'woocommerce_template_loop_add_to_cart', $priorities['hide_cart'] );
		add_filter( 'woocommerce_loop_add_to_cart_link', '__return_empty_string', 10 );
	}
}

if ( $hideprice ) {

	$priorities['hide_price'] = has_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price' );

	if ( ! $priorities['hide_price'] ) {
		remove_action( 'woocommerce_template_loop_price', $priorities['hide_price'] );
		add_filter( 'woocommerce_get_price_html', '__return_empty_string', 10 );

	}
}

$extra_class = apply_filters( 'ywcps_add_classes_in_slider', array() );

$extra_class = implode( ' ', $extra_class );
$cols        = ( isset( $woocommerce_loop['columns'] ) ) ? $woocommerce_loop['columns'] : 6; // fix $woocommerce_loop['columns'] empty

if ( $products->have_posts() ) :

	$data_attributes = array(
		'n_items'      => $n_items,
		'is_loop'      => $is_loop,
		'pag_speed'    => $page_speed,
		'auto_play'    => $auto_play,
		'stop_hov'     => $stop_hov,
		'show_nav'     => $show_nav,
		'is_rtl'       => $is_rtl,
		'anim_in'      => $anim_in,
		'anim_out'     => $anim_out,
		'anim_speed'   => $anim_speed,
		'show_dot_nav' => $show_dot_nav,
		'columns'      => $cols,
	);
	$data_attributes = yith_plugin_fw_html_data_to_string( $data_attributes );
	?>
	<div class="woocommerce">
		<?php
		if ( $show_title ) {
			echo '<h3>' . esc_html( $title ) . '</h3>';
		}
		?>
		<div class="ywcps-wrapper" <?php echo $data_attributes; //phpcs:ignore WordPress.Security.EscapeOutput ?>>
		   <div class="ywcps-slider <?php echo esc_attr( $extra_class ); ?>" style="visibility:hidden;">
		   		<div class="row">
		   			<ul class="ywcps-products products ywcps_products_slider">
						<?php
							while ( $products->have_posts() ) :
								$products->the_post();
								wc_get_template( 'content-product.php' );
								$i ++;
							endwhile; // end of the loop.
							?>
		   			</ul>
		   		</div>
		   </div>
		   <div class="ywcps-nav">
				<div id="nav_prev_def_free" class="ywcps-nav-prev"><span id="default_prev"></span></div>
				<div id="nav_next_def_free" class="ywcps-nav-next"><span id="default_next"></span></div>
			</div>
		</div>
		<div class="es-carousel-clear"></div>	
	</div>

	<?php
endif;

if ( $hide_cart && ! $priorities['hide_cart'] ) {
	add_action( 'woocommerce_template_loop_add_to_cart', $priorities['hide_cart'] );
	remove_filter( 'woocommerce_loop_add_to_cart_link', '__return_empty_string', 10 );
}

if ( $hideprice && ! $priorities['hide_price'] ) {
	add_action( 'woocommerce_template_loop_price', $priorities['hide_price'] );
	remove_filter( 'woocommerce_get_price_html', '__return_empty_string', 10 );
}

wp_reset_query();
wp_reset_postdata();


