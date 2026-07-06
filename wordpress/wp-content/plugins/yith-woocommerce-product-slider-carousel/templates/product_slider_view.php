<?php // phpcs:ignore WordPress.Files.FileName
/**
 * File that get all information about a Slider
 *
 * @package YITH WooCommerce Product Slider Carousel\Templates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$suffix  = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
$path_js = file_exists( YWCPS_ASSETS_PATH . 'js/yith_product_slider_custom.js' ) ? YWCPS_ASSETS_URL . 'js/yith_product_slider_custom.js' : YWCPS_ASSETS_URL . 'js/yith_product_slider' . $suffix . '.js';
wp_register_script( 'yith_wc_product_slider', $path_js, array( 'jquery' ), YWCPS_VERSION, true );

wp_enqueue_style( 'fontawesome' );
wp_enqueue_style( 'owl-carousel-style' );
wp_enqueue_style( 'yith-animate' );
wp_enqueue_style( 'yith-product-slider-style' );
wp_enqueue_script( 'owl-carousel' );
wp_enqueue_script( 'yith_wc_product_slider' );

$query_args = array(
	'posts_per_page' => $posts_per_page,
	'post_type'      => 'product',
	'post_status'    => 'publish',
);


if ( isset( $categories ) && ! empty( $categories ) ) {

	if ( is_array( $categories ) ) {
		$categories = implode( ',', $categories );
	}

	$query_args['product_cat'] = $categories;
}
	$order    = strtoupper( $order ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.OverrideProhibited
	$order_by = strtolower( trim( $order_by ) );

switch ( $order_by ) {


	case 'date':
		if ( ! isset( $query_args['orderby'] ) ) {
			$query_args['orderby'] = 'date';
			$query_args['order']   = $order;
		}
		break;

	case 'price':
			$query_args['meta_key'] = '_price';
			$query_args['orderby']  = 'meta_value_num';
			$query_args['order']    = $order;
		break;
	case 'name':
		if ( ! isset( $query_args['orderby'] ) ) {
			$query_args['orderby'] = 'title';
			$query_args['order']   = $order;
		}
		break;
}


$atts['query_args'] = $query_args;

wc_get_template( 'product_slider_view_default.php', $atts, '', YWCPS_TEMPLATE_PATH );
