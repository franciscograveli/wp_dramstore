<?php
/**
 * This file include all plugin functions
 *
 * @package YITH WooCommerce Product Slider Carousel\Functions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<?php

if ( ! function_exists( 'ywcps_animations_list' ) ) {
	/**
	 * Return the available animations
	 *
	 * @author YITH <plugins@yithemes.com>
	 * @since 1.0.0
	 * @return array
	 */
	function ywcps_animations_list() {

		$animations = array(
			'Fading Entrances' => array( 'fadeIn', 'fadeInDown', 'fadeInDownBig', 'fadeInLeft', 'fadeInLeftBig', 'fadeInRight', 'fadeInRightBig', 'fadeInUp', 'fadeInUpBig' ),
			'Fading Exits'     => array( 'fadeOut', 'fadeOutDown', 'fadeOutDownBig', 'fadeInLeft', 'fadeInLeftBig', 'fadeInRight', 'fadeInRightBig', 'fadeInUp', 'fadeInUpBig' ),

		);

		return apply_filters( 'ywcps_animate_styles', $animations );
	}
}

if ( ! function_exists( 'YITH_Product_Slider_Type' ) ) {
	/**
	 * Return the unique instance of the class
	 *
	 * @since 1.0.0
	 * @return YITH_Product_Slider_Type|YITH_Product_Slider_Type_Premium
	 */
	function YITH_Product_Slider_Type() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName

		if ( ! defined( 'YWCPS_PREMIUM' ) ) {
			return YITH_Product_Slider_Type::get_instance();
		} else {
			return YITH_Product_Slider_Type_Premium::get_instance();
		}
	}
}

if ( ! function_exists( 'ywcps_add_gutenberg_block' ) ) {
	/**
	 * Register the plugin in gutenberg
	 *
	 */
	function ywcps_add_gutenberg_block() {

		global $YWC_Product_Slider; // phpcs:ignore WordPress.NamingConventions.ValidVariableName
		$all_sliders = $YWC_Product_Slider->get_productslider(); // phpcs:ignore WordPress.NamingConventions.ValidVariableName

		$options = array();

		foreach ( $all_sliders as $slider ) {
			$options[ $slider['value'] ] = $slider['text'];
		}
		$current_option = current( array_keys( $options ) );

		$block = array(
			'yith-wc-productslider' => array(
				'title'          => _x( 'Product Slider Carousel', '[gutenberg]: product slider carousel', 'yith-woocommerce-product-slider-carousel' ),
				'description'    => __( 'Show your Product Slider in sidebar!', 'yith-woocommerce-product-slider-carousel' ),
				'shortcode_name' => 'yith_wc_productslider',
				'do_shortcode'   => false,
				'keywords'       => array( __( 'Product Slider Carousel', 'yith-woocommerce-product-slider-carousel' ) ),
				'attributes'     => array(
					'id' => array(
						'type'    => 'select',
						'label'   => __( 'Slider', 'yith-woocommerce-product-slider-carousel' ),
						'options' => $options,
						'default' => $current_option,
					),
				),
			),
		);

		yith_plugin_fw_gutenberg_add_blocks( $block );
	}
}
