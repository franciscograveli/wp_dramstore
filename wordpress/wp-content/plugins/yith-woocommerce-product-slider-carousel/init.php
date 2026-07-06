<?php
/**
 * Plugin Name: YITH WooCommerce Product Slider Carousel
 * Plugin URI: https://yithemes.com/themes/plugins/yith-woocommerce-product-slider-carousel/
 * Description: <code><strong>YITH WooCommerce Product Slider Carousel</strong></code> allows you to create responsive product sliders that you can add to your pages through a shortcode. <a href ="https://yithemes.com">Get more plugins for your e-commerce shop on <strong>YITH</strong></a>
 * Version: 1.53.0
 * Author: YITH
 * Author URI: https://yithemes.com/
 * Text Domain: yith-woocommerce-product-slider-carousel
 * Domain Path: /languages/
 * WC requires at least: 10.5
 * WC tested up to: 10.7
 * Requires Plugins: woocommerce
 *
 * @author YITH <plugins@yithemes.com>
 * @package YITH WooCommerce Product Slider Carousel
 * @version 1.53.0
 */

/*
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
*/
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! function_exists( 'is_plugin_active' ) ) {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

/**
 * Show an error if WooCommerce isn't active.
 *
 * @since 1.0.0
 */
function yith_ywcps_install_woocommerce_admin_notice() {
	?>
		<div class="error">
			<p><?php esc_html_e( 'YITH WooCommerce Product Slider Carousel is enabled but not effective. It requires WooCommerce in order to work.', 'yith-woocommerce-product-slider-carousel' ); ?></p>
		</div>
	<?php
}
/**
 * Show an error if the premium version is active
 *
 * @since 1.0.0
 */
function yith_ywcps_install_free_admin_notice() {
	?>
		<div class="error">
			<p><?php esc_html_e( 'You can\'t activate the free version of YITH WooCommerce Product Slider Carousel while you are using the premium one.', 'yith-woocommerce-product-slider-carousel' ); ?></p>
		</div>
	<?php
}

if ( ! function_exists( 'yith_plugin_registration_hook' ) ) {
	require_once 'plugin-fw/yit-plugin-registration-hook.php';
}
register_activation_hook( __FILE__, 'yith_plugin_registration_hook' );


if ( ! defined( 'YWCPS_VERSION' ) ) {
	define( 'YWCPS_VERSION', '1.53.0' );
}

if ( ! defined( 'YWCPS_FREE_INIT' ) ) {
	define( 'YWCPS_FREE_INIT', plugin_basename( __FILE__ ) );
}

if ( ! defined( 'YWCPS_FILE' ) ) {
	define( 'YWCPS_FILE', __FILE__ );
}

if ( ! defined( 'YWCPS_DIR' ) ) {
	define( 'YWCPS_DIR', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'YWCPS_URL' ) ) {
	define( 'YWCPS_URL', plugins_url( '/', __FILE__ ) );
}

if ( ! defined( 'YWCPS_ASSETS_URL' ) ) {
	define( 'YWCPS_ASSETS_URL', YWCPS_URL . 'assets/' );
}

if ( ! defined( 'YWCPS_ASSETS_PATH' ) ) {
	define( 'YWCPS_ASSETS_PATH', YWCPS_DIR . 'assets/' );
}

if ( ! defined( 'YWCPS_TEMPLATE_PATH' ) ) {
	define( 'YWCPS_TEMPLATE_PATH', YWCPS_DIR . 'templates/' );
}

if ( ! defined( 'YWCPS_INC' ) ) {
	define( 'YWCPS_INC', YWCPS_DIR . 'includes/' );
}
if ( ! defined( 'YWCPS_SLUG' ) ) {
	define( 'YWCPS_SLUG', 'yith-woocommerce-product-slider-carousel' );
}

// Plugin Framework Loader.
if ( file_exists( plugin_dir_path( __FILE__ ) . 'plugin-fw/init.php' ) ) {
	require_once plugin_dir_path( __FILE__ ) . 'plugin-fw/init.php';
}

if ( ! function_exists( 'YITH_Product_Slider_Init' ) ) {
	/**
	 * Unique access to instance of YITH_Product_Slider class
	 *
	 * @since 1.0.3
	 */
	function YITH_Product_Slider_Init() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName

		if ( function_exists( 'yith_plugin_fw_load_plugin_textdomain' ) ) {
			yith_plugin_fw_load_plugin_textdomain( 'yith-woocommerce-product-slider-carousel', basename( dirname( __FILE__ ) ) . '/languages' );
		}
		// Load required classes and functions.
		require_once YWCPS_INC . 'functions.yith-product-slider.php';
		require_once YWCPS_INC . 'class.yith-product-slider-type.php';
		require_once YWCPS_INC . 'class.yith-product-slider-shortcode.php';
		require_once YWCPS_INC . 'class.yith-woocommerce-product-slider.php';

		global $YWC_Product_Slider; // phpcs:ignore WordPress.NamingConventions.ValidVariableName
		$YWC_Product_Slider = YITH_WooCommerce_Product_Slider::get_instance(); // phpcs:ignore WordPress.NamingConventions.ValidVariableName

	}
}

add_action( 'ywcps_init', 'YITH_Product_Slider_Init' );

if ( ! function_exists( 'yith_product_slider_carousel_install' ) ) {
	/**
	 * Install Product slider plugin
	 *
	 * @since 1.0.0
	 */
	function yith_product_slider_carousel_install() {

		if ( ! function_exists( 'WC' ) ) {
			add_action( 'admin_notices', 'yith_ywcps_install_woocommerce_admin_notice' );
		} elseif ( defined( 'YWCPS_PREMIUM' ) ) {
			add_action( 'admin_notices', 'yith_ywcps_install_free_admin_notice' );
			deactivate_plugins( plugin_basename( __FILE__ ) );
		} else {
			do_action( 'ywcps_init' );
		}
	}
}

add_action( 'plugins_loaded', 'yith_product_slider_carousel_install', 11 );
