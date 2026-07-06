<?php // phpcs:ignore WordPress.Files.FileName
/**
 * This class register the plugin shortcode
 *
 * @package YITH WooCommerce Product Slider Carousel\Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'YITH_Product_Slider_Shortcode' ) ) {
	/**
	 * The shortcode class
	 */
	class YITH_Product_Slider_Shortcode {

		/**
		 * Show the shortcode
		 *
		 * @author YITH <plugins@yithemes.com>
		 * @since 1.0.0
		 * @param array $atts The shortcode attributes.
		 * @param mixed $content The content.
		 */
		public static function print_product_slider( $atts, $content = null ) {

			global $woocommerce_loop;

			$default_attrs = array();

			$atts         = shortcode_atts( $default_attrs, $atts );
			$extra_params = array(

				'is_rtl'         => 'yes' === get_option( 'ywcps_check_rtl' ) ? 'true' : 'false',
				'posts_per_page' => get_option( 'ywcps_n_posts_per_page' ), // phpcs:ignore WordPress.WP.PostsPerPage.posts_per_page_posts_per_page
				'categories'     => get_option( 'ywcps_categories', false ),
				'product_type'   => get_option( 'ywcps_product_type' ),
				'title'          => get_option( 'ywcps_title' ),
				'n_items'        => get_option( 'ywcps_image_per_row' ),
				'order_by'       => get_option( 'ywcps_order_by' ),
				'order'          => get_option( 'ywcps_order_type' ),
				'is_loop'        => 'yes' === get_option( 'ywcps_check_loop' ) ? 'true' : 'false',
				'page_speed'     => get_option( 'ywcps_pagination_speed' ),
				'auto_play'      => get_option( 'ywcps_auto_play' ),
				'stop_hov'       => 'yes' === get_option( 'ywcps_stop_hover' ) ? 'true' : 'false',
				'show_nav'       => 'yes' === get_option( 'ywcps_show_navigation' ) ? 'true' : 'false',
				'anim_in'        => get_option( 'ywcps_animate_in' ),
				'anim_out'       => get_option( 'ywcps_animate_out' ),
				'anim_speed'     => get_option( 'ywcps_animation_speed' ),
				'show_dot_nav'   => 'yes' === get_option( 'ywcps_show_dot_navigation' ) ? 'true' : 'false',
				'show_title'     => 'yes' === get_option( 'ywcps_show_title' ),
			);

			$atts         = array_merge( $extra_params, $atts );
			$atts['atts'] = $extra_params;

			$old_woocommerce_loop = $woocommerce_loop;
			/**
			* Since 1.0.5
			*
			* @param $woocommerce_loop mixed Woocommerce loop global
			* @param $plugin_slug string Current plugin slug
			*/

			$woocommerce_loop = apply_filters( 'yith_customize_product_carousel_loop', $woocommerce_loop, YWCPS_SLUG );
			ob_start();
			wc_get_template( 'product_slider_view.php', $atts, '', YWCPS_TEMPLATE_PATH );
			$template = ob_get_contents();
			ob_end_clean();
			$woocommerce_loop = $old_woocommerce_loop;

			return apply_filters( 'yith_wcpsl_productslider_html', $template, array(), true );

		}

	}
}
add_shortcode( 'yith_wc_productslider', array( 'YITH_Product_Slider_Shortcode', 'print_product_slider' ) );
