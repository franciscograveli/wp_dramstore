<?php
/**
 * This file configure the plugin option
 *
 * @package YITH WooCommerce Product Slider Carousel\Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

global $YWC_Product_Slider; // phpcs:ignore WordPress.NamingConventions.ValidVariableName
$animations_list = ywcps_animations_list();
$slider          = $YWC_Product_Slider->get_productslider();// phpcs:ignore WordPress.NamingConventions.ValidVariableName

$animations_in = array();
foreach ( $animations_list['Fading Entrances'] as $value ) {
	$animations_in[ $value ] = $value;
}
$animations_out = array();
foreach ( $animations_list['Fading Exits'] as $value ) {
	$animations_out[ $value ] = $value;
}

$settings = array(

	'settings' => array(

		'section_product_slider_settings' => array(
			'name' => __( 'General Settings', 'yith-woocommerce-product-slider-carousel' ),
			'type' => 'title',
			'id'   => 'ywcps_section_general_start',
		),

		'n_posts_per_page'                => array(
			'name'      => __( 'Product to show', 'yith-woocommerce-product-slider-carousel' ),
			'type'      => 'yith-field',
			'yith-type' => 'number',
			'id'        => 'ywcps_n_posts_per_page',
			'desc_tip'  => __( 'This option lets you choose the number of products you want to show. -1 for all', 'yith-woocommerce-product-slider-carousel' ),
			'min'       => -1,
			'max'       => 99,
			'std'       => 15,
		),

		'check_rtl'                       => array(
			'name'      => __( 'Enable Rtl support', 'yith-woocommerce-product-slider-carousel' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'id'        => 'ywcps_check_rtl',
			'default'   => 'no',
			'std'       => 'no',
		),
		'ywcps_check_loop'                => array(
			'name'      => __( 'Loop slider', 'yith-woocommerce-product-slider-carousel' ),
			'desc'      => __( 'Choose if you want your slider to scroll products continuously', 'yith-woocommerce-product-slider-carousel' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'std'       => 'no',
			'default'   => 'no',
			'id'        => 'ywcps_check_loop',
		),

		'ywcps_pagination_speed'          => array(
			'name'      => __( 'Pagination Speed', 'yith-woocommerce-product-slider-carousel' ),
			'desc_tip'  => __( 'Pagination speed in milliseconds', 'yith-woocommerce-product-slider-carousel' ),
			'type'      => 'yith-field',
			'yith-type' => 'text',
			'std'       => '800',
			'default'   => '800',
			'id'        => 'ywcps_pagination_speed',
		),


		'ywcps_auto_play'                 => array(
			'name'      => __( 'AutoPlay', 'yith-woocommerce-product-slider-carousel' ),
			'desc_tip'  => __( 'Insert the autoplay value in milliseconds, enter 0 to disable it', 'yith-woocommerce-product-slider-carousel' ),
			'type'      => 'yith-field',
			'yith-type' => 'text',
			'std'       => '5000',
			'default'   => '5000',
			'id'        => 'ywcps_auto_play',
		),

		'ywcps_stop_hover'                => array(
			'name'      => __( 'Stop on Hover', 'yith-woocommerce-product-slider-carousel' ),
			'desc'      => __( 'Stop autoplay on mouse hover', 'yith-woocommerce-product-slider-carousel' ),
			'type'      => 'yith-field',
			'yith-type' => 'checkbox',
			'std'       => 'no',
			'default'   => 'no',
			'id'        => 'ywcps_stop_hover',
		),

		'ywcps_show_navigation'           => array(
			'name'      => __( 'Show Navigation', 'yith-woocommerce-product-slider-carousel' ),
			'desc'      => __( 'Display "prev" and "next" button', 'yith-woocommerce-product-slider-carousel' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'std'       => 'no',
			'default'   => 'no',
			'id'        => 'ywcps_show_navigation',
		),

		'ywcps_show_dot_navigation'       => array(
			'name'      => __( 'Show Dots Navigation', 'yith-woocommerce-product-slider-carousel' ),
			'desc'      => __( 'Show or Hide dots navigation', 'yith-woocommerce-product-slider-carousel' ),
			'type'      => 'yith-field',
			'yith-type' => 'checkbox',
			'std'       => 'no',
			'default'   => 'no',
			'id'        => 'ywcps_show_dot_navigation',
		),

		'ywcps_animate_in'                => array(
			'name'      => __( 'Animation IN', 'yith-woocommerce-product-slider-carousel' ),
			'desc_tip'  => __( 'Choose entrance animation for a new slide.*Animation functions work only if there is just one item in the slider and only in browsers that support perspective property', 'yith-woocommerce-product-slider-carousel' ),
			'type'      => 'yith-field',
			'yith-type' => 'select',
			'options'   => $animations_in,
			'id'        => 'ywcps_animate_in',
		),
		'ywcps_animate_out'               => array(
			'name'      => __( 'Animation OUT', 'yith-woocommerce-product-slider-carousel' ),
			'desc_tip'  => __( 'Choose exit animation for a slide. *Animation functions work only if there is just one item in the slider and only in browsers that support perspective property', 'yith-woocommerce-product-slider-carousel' ),
			'type'      => 'yith-field',
			'yith-type' => 'select',
			'options'   => $animations_out,
			'id'        => 'ywcps_animate_out',
		),

		'ywcps_animation_speed'           => array(
			'name'      => __( 'Animation Speed', 'yith-woocommerce-product-slider-carousel' ),
			'desc_tip'  => __( 'Enter animation duration in milliseconds', 'yith-woocommerce-product-slider-carousel' ),
			'type'      => 'yith-field',
			'yith-type' => 'text',
			'std'       => 450,
			'default'   => 450,
			'id'        => 'ywcps_animation_speed',
		),

		'general_settings_end'            => array(
			'type' => 'sectionend',
			'id'   => 'ywcps_section_general_end',
		),

		'section_product_slider_content'  => array(
			'name' => __( 'Content Setting', 'yith-woocommerce-product-slider-carousel' ),
			'type' => 'title',
			'id'   => 'ywcps_section_content_start',
		),
		'ywcps_title'                     => array(
			'name'              => __( 'Title', 'yith-woocommerce-product-slider-carousel' ),
			'type'              => 'yith-field',
			'yith-type'         => 'text',
			'id'                => 'ywcps_title',
			'placeholder'       => __( 'Enter Product Slider Title', 'yith-woocommerce-product-slider-carousel' ),
			'custom_attributes' => 'required',
			'default'           => 'Free Slider',
			'std'               => 'Free Slider',
		),

		'ywcps_show_title'                => array(
			'name'      => __( 'Show Title', 'yith-woocommerce-product-slider-carousel' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'std'       => 'yes',
			'default'   => 'yes',
			'id'        => 'ywcps_show_title',
		),
		'ywcps_categories'                => array(

			'name'        => __( 'Choose Product Category', 'yith-woocommerce-product-slider-carousel' ),
			'placeholder' => __( 'Choose product categories', 'yith-woocommerce-product-slider-carousel' ),
			'desc'        => __( 'Leave this field empty if you want all categories to be shown in the slider', 'yith-woocommerce-product-slider-carousel' ),
			'type'        => 'yith-field',
			'yith-type'   => 'ajax-terms',
			'data'        => array(
				'taxonomy'   => 'product_cat',
				'term_field' => 'slug',
				'show_id'    => true,
			),
			'multiple'    => true,
			'id'          => 'ywcps_categories',
			'default'     => '',
		),

		'ywcps_layout_type'               => array(
			'name'      => __( 'Slider Template', 'yith-woocommerce-product-slider-carousel' ),
			'desc_tip'  => __( 'Choose template for Product Slider', 'yith-woocommerce-product-slider-carousel' ),
			'type'      => 'yith-field',
			'yith-type' => 'select',
			'options'   => array(
				'default' => 'WooCommerce Loop',
			),
			'std'       => 'default',
			'id'        => 'ywcps_layout_type',
		),

		'ywcps_image_per_row'             => array(
			'name'      => __( 'Images per row', 'yith-woocommerce-product-slider-carousel' ),
			'desc'      => '',
			'type'      => 'yith-field',
			'yith-type' => 'number',
			'min'       => 1,
			'max'       => 6,
			'std'       => 4,
			'default'   => 4,
			'id'        => 'ywcps_image_per_row',
		),

		'ywcps_order_by'                  => array(
			'name'      => __( 'Order By', 'yith-woocommerce-product-slider-carousel' ),
			'type'      => 'yith-field',
			'yith-type' => 'radio',
			'desc'      => '',
			'options'   => array(
				'name'  => __( 'Name', 'yith-woocommerce-product-slider-carousel' ),
				'price' => __( 'Price', 'yith-woocommerce-product-slider-carousel' ),
				'date'  => __( 'Date', 'yith-woocommerce-product-slider-carousel' ),
			),
			'id'        => 'ywcps_order_by',
		),

		'ywcps_order_type'                => array(
			'name'      => __( 'Order Type', 'yith-woocommerce-product-slider-carousel' ),
			'type'      => 'yith-field',
			'yith-type' => 'radio',
			'desc'      => '',
			'options'   => array(
				'asc'  => 'ASC',
				'desc' => 'DESC',
			),
			'id'        => 'ywcps_order_type',
		),

		'ywcps_info_shortcode'            => array(
			'name'              => __( 'Use this shortcode in your pages ', 'yith-woocommerce-product-slider-carousel' ),
			'type'              => 'yith-field',
			'yith-type'         => 'copy-to-clipboard',
			//'custom_attributes' => 'readonly',
			'id'                => 'ywcps_info_shortcode',
			'default'           => '[yith_wc_productslider id=' . $slider[0]['value'] . ']',
			'std'               => '[yith_wc_productslider id=' . $slider[0]['value'] . ']',
			'css'               => 'width:30%',
		),

		'content_settings_end'            => array(
			'type' => 'sectionend',
			'id'   => 'ywcps_section_content_end',
		),



	),

);

return apply_filters( 'ywsfl_general_settings', $settings );
