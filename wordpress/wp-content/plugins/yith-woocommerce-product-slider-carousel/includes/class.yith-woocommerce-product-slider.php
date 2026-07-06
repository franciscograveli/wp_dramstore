<?php // phpcs:ignore WordPress.Files.FileName
/**
 * This class manage all plugin features.
 *
 * @package YITH WooCommerce Product Slider Carousel\Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'YITH_WooCommerce_Product_Slider' ) ) {
	/**
	 * The main class
	 */
	class YITH_WooCommerce_Product_Slider {
		/**
		 * The unquie instance of the class
		 *
		 * @var YITH_WooCommerce_Product_Slider
		 */
		protected static $_instance;
		/**
		 * The instance of YIT Plugin panel
		 *
		 * @var YIT_Plugin_Panel_WooCommerce
		 */
		protected $_panel = null;
			/**
			 * The plugin panel name
			 *
			 * @var string
			 */
		protected $_panel_page = 'yith_wc_product_slider';

		/**
		 * Product slider instance
		 *
		 * @var YITH_Product_Slider_Type
		 */
		protected $product_slider;

			/**
			 * The premium template name
			 *
			 * @var string
			 */
		protected $_premium = 'premium.php';

		/**
		 * Check if wc version is 2.7
		 *
		 * @var bool
		 */
		public $is_wc_2_7;

		/**
		 * The construct
		 *
		 * @author YITH <plugins@yithemes.com>
		 * @since 1.0.0
		 */
		public function __construct() {
			// Load Plugin Framework.
			$this->product_slider = YITH_Product_Slider_Type();

			// Add action links.
			add_filter(
				'plugin_action_links_' . plugin_basename( YWCPS_DIR . '/' . basename( YWCPS_FILE ) ),
				array(
					$this,
					'action_links',
				)
			);
			// Add row meta.
			add_filter( 'yith_show_plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 5 );
			// Add menu  field under YITH_PLUGIN.
			add_action( 'yith_wc_product_slider_carousel_premium', array( $this, 'premium_tab' ) );
			add_action( 'admin_menu', array( $this, 'add_product_slider_carousel_menu' ), 5 );

			add_action( 'wp_enqueue_scripts', array( $this, 'include_style_script' ) );

			if ( is_admin() ) {
				add_action( 'plugins_loaded', 'ywcps_add_gutenberg_block', 20 );
				add_action( 'current_screen', array( $this, 'add_shortcodes_button' ) );
			}

			$this->is_wc_2_7 = version_compare( WC()->version, '2.7.0', '>=' );

			add_action( 'before_woocommerce_init', array( $this, 'declare_wc_features_support' ) );

		}
		/**
		 * Return the unique instance of the class
		 *
		 * @since 1.0.0
		 * @return YITH_WooCommerce_Product_Slider
		 */
		public static function get_instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}

		/**
		 * Add the action links to plugin admin page
		 *
		 * @param array $links | links plugin array.
		 * @since    1.0
		 * @return array
		 * @use plugin_action_links_{$plugin_file_name}
		 */
		public function action_links( $links ) {
			$is_premium = defined( 'YWCPS_INIT' );

			$links = yith_add_action_links( $links, $this->_panel_page, $is_premium );

			return $links;
		}

		/**
		 * Plugin_row_meta.
		 *
		 * Add the action links to plugin admin page.
		 *
		 * @param array  $new_row_meta_args The new plugin meta.
		 * @param array  $plugin_meta The plugin meta.
		 * @param string $plugin_file The filename of plugin.
		 * @param array  $plugin_data The plugin data.
		 * @param string $status The plugin status.
		 * @param string $init_file The filename of plugin.
		 * @return   array
		 * @since    1.0
		 * @use plugin_row_meta
		 */
		public function plugin_row_meta( $new_row_meta_args, $plugin_meta, $plugin_file, $plugin_data, $status, $init_file = 'YWCPS_FREE_INIT' ) {
			if ( defined( $init_file ) && constant( $init_file ) === $plugin_file ) {
				$new_row_meta_args['slug'] = YWCPS_SLUG;
			}

			return $new_row_meta_args;
		}


		/**
		 * Premium Tab Template
		 *
		 * Load the premium tab template on admin page
		 *
		 * @since   1.0.0
		 */
		public function premium_tab() {
			$premium_tab_template = YWCPS_TEMPLATE_PATH . '/admin/' . $this->_premium;
			if ( file_exists( $premium_tab_template ) ) {
				include_once $premium_tab_template;
			}
		}

		/**
		 * Add a panel under YITH Plugins tab
		 *
		 * @return   void
		 * @since    1.0
		 * @use     /Yit_Plugin_Panel class
		 * @see      plugin-fw/lib/yit-plugin-panel.php
		 */
		public function add_product_slider_carousel_menu() {
			if ( ! empty( $this->_panel ) ) {
				return;
			}

			$admin_tabs = array(
				'settings' => __( 'Settings', 'yith-woocommerce-product-slider-carousel' ),
			);

			if ( ! defined( 'YWCPS_PREMIUM' ) ) {
				$admin_tabs['premium'] = __( 'Premium Version', 'yith-woocommerce-product-slider-carousel' );
			} else {
				$admin_tabs['layout1'] = __( 'Layout 1', 'yith-woocommerce-product-slider-carousel' );
				$admin_tabs['layout2'] = __( 'Layout 2', 'yith-woocommerce-product-slider-carousel' );
				$admin_tabs['layout3'] = __( 'Layout 3', 'yith-woocommerce-product-slider-carousel' );

			}

			$args = array(
				'create_menu_page' => true,
				'parent_slug'      => '',
				'plugin_slug'      => YWCPS_SLUG,
				'page_title'       => 'YITH WooCommerce Product Slider Carousel',
				'menu_title'       => 'Product Slider Carousel',
				'capability'       => 'manage_options',
				'parent'           => '',
				'class'            => yith_set_wrapper_class(),
				'parent_page'      => 'yith_plugin_panel',
				'page'             => $this->_panel_page,
				'admin-tabs'       => $admin_tabs,
				'options-path'     => YWCPS_DIR . '/plugin-options',
				'is_free'          => true,
			);

			$this->_panel = new YIT_Plugin_Panel_WooCommerce( $args );
		}


		/**
		 * Include style and script
		 *
		 * @since 1.0.0
		 */
		public function include_style_script() {
			wp_register_style( 'fontawesome', YWCPS_ASSETS_URL . 'css/font-awesome.min.css', array(), YWCPS_VERSION );
			wp_register_style( 'yith-animate', YWCPS_ASSETS_URL . 'css/animate.css', array(), YWCPS_VERSION );
			wp_register_style( 'yith-product-slider-style', YWCPS_ASSETS_URL . 'css/product_slider_style.css', array(), YWCPS_VERSION );

			if ( defined( 'YIT' ) ) {
				$add_inline_style = '.ywcps-slider{visibility:visible!important;}';
				wp_add_inline_style( 'yith-product-slider-style', $add_inline_style );

			} else {
				wp_register_style( 'owl-carousel-style', YWCPS_ASSETS_URL . 'css/owl.css', array(), '2.2.1' );
				wp_register_script( 'owl-carousel', YWCPS_ASSETS_URL . 'js/owl.carousel.js', array( 'jquery' ), '2.2.1', true );

			}
		}


		/**
		 * Add shortcode button
		 *
		 * Add shortcode button to TinyMCE editor, adding filter on mce_external_plugins
		 *
		 * @param WP_Screen $current_screen The current screen.
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public function add_shortcodes_button( $current_screen ) {

			if ( ! is_null( $current_screen ) && ! in_array( $current_screen->post_type, array( 'post', 'page' ), true ) ) {
				if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) ) {
					return;
				}
				if ( get_user_option( 'rich_editing' ) == 'true' ) {
					add_action( 'media_buttons', array( &$this, 'ywcps_media_buttons_context' ) );
					add_action( 'admin_print_footer_scripts', array( &$this, 'ywcps_add_quicktags' ) );
					add_action( 'admin_action_print_shortcode_popup', array( $this, 'print_shortcode_popup' ) );
					add_filter( 'mce_external_plugins', array( $this, 'add_shortcodes_tinymce_plugin' ) );
					add_filter( 'mce_buttons', array( $this, 'register_shortcodes_button' ) );
				}
				// register shortcodes to WPBackery Visual Composer.
				add_action( 'vc_before_init', array( $this, 'register_vc_shortcodes' ) );
			}
		}

		/**
		 * Add shortcode plugin
		 *
		 * Add a script to TinyMCE script list
		 *
		 * @param array $plugin_array containing TinyMCE script list.
		 *
		 * @return array The edited array containing TinyMCE script list
		 * @since 1.0.0
		 */
		public function add_shortcodes_tinymce_plugin( $plugin_array ) {
			$plugin_array['ywcps_shortcode'] = YWCPS_ASSETS_URL . 'js/tinymce.js';

			return $plugin_array;
		}

		/**
		 * Register the plugin shortcode in mce buttons
		 *
		 * @since 1.0.0
		 * @param array $buttons the mce buttons.
		 * @return array
		 */
		public function register_shortcodes_button( $buttons ) {
			array_push( $buttons, '|', 'ywcps_shortcode' );

			return $buttons;
		}


		/**
		 * The markup of shortcode
		 *
		 * @since   1.0.0
		 */
		public function ywcps_media_buttons_context() {

			global $post_ID, $temp_ID;// phpcs:ignore WordPress.NamingConventions.ValidVariableName

			$query_args = array(
				'post_id'   => (int) ( 0 == $post_ID ? $temp_ID : $post_ID ), // phpcs:ignore WordPress.NamingConventions.ValidVariableName
				'action'    => 'print_shortcode_popup',
				'KeepThis'  => true,
				'TB_iframe' => true,

			);
			$lightbox_url = esc_url( add_query_arg( $query_args, admin_url( 'admin.php' ) ) );

			$out = sprintf( '<a id="ywcps_shortcode" style="display:none" href="%s" class="hide-if-no-js thickbox" title="%s"></a>', $lightbox_url, __( 'Add YITH WooCommerce Product Slider Carousel shortcode', 'yith-woocommerce-product-slider-carousel' ) );

			echo $out; //phpcs:ignore WordPress.Security.EscapeOutput

		}
		/**
		 * Add the shortcode popup
		 *
		 * @since 1.0.0
		 */
		public function print_shortcode_popup() {

			require_once YWCPS_DIR . '/templates/admin/lightbox.php';
		}

		/**
		 * Add quicktags to visual editor
		 *
		 * @since   1.0.0
		 */
		public function ywcps_add_quicktags() {

			?>
			<script type="text/javascript">

				if (window.QTags !== undefined) {
					QTags.addButton('ywcps_shortcode', 'add ywcps shortcode', function () {
						jQuery('#ywcps_shortcode').click()
					});
				}


			</script>
			<?php
		}

		/**
		 * Register product slider carousel shortcode to visual composer
		 *
		 * @since 1.0.6
		 */
		public function register_vc_shortcodes() {

			$all_sliders = $this->get_productslider();

			$options = array();

			foreach ( $all_sliders as $key => $value ) {
				$options[ "{$value['text']}" ] = $value['value'];
			}

			$vc_map_params = apply_filters(
				'yith_wcps_vc_shortcodes_params',
				array(

					'yith_wc_productslider' => array(
						'name'        => __( 'YITH WooCommerce Product Slider Carousel', 'yith-woocommerce-product-slider-carousel' ),
						'base'        => 'yith_wc_productslider',
						'description' => __( 'Add Product Slider', 'yith-woocommerce-product-slider-carousel' ),
						'category'    => __( 'Product Slider Carousel', 'yith-woocommerce-product-slider-carousel' ),
						'params'      => array(
							array(
								'type'        => 'dropdown',
								'holder'      => '',
								'heading'     => __( 'Choose a Product Slider', 'yith-woocommerce-product-slider-carousel' ),
								'param_name'  => 'id',
								'value'       => $options,
								'description' => __( 'Choose to show empty terms or not', 'yith-wcbr' ),
							),
							array(
								'type'       => 'textfield',
								'holder'     => '',
								'param_name' => 'z_index',
								'heading'    => __( 'Z-Index', 'yith-woocommerce-product-slider-carousel' ),
							),
						),

					),

				)
			);

			if ( ! empty( $vc_map_params ) && function_exists( 'vc_map' ) ) {
				foreach ( $vc_map_params as $params ) {
					vc_map( $params );
				}
			}
		}

		/**
		 * Return all product slider
		 *
		 * @since 1.0.0
		 * @used include_style_script
		 * @return array
		 */
		public function get_productslider() {

			global $wpdb;
			$slider_free_id = $wpdb->get_var( "SELECT ID FROM {$wpdb->posts} JOIN {$wpdb->postmeta} ON {$wpdb->posts}.ID = {$wpdb->postmeta}.post_id   WHERE post_type = 'yith_wcps_type' AND {$wpdb->postmeta}.meta_key ='_ywcps_free_slider_id' " );

			return array(
				array(
					'text'  => get_option( 'ywcps_title' ),
					'value' => $slider_free_id,
				),
			);

		}
		/***
         * Declare support for WooCommerce features.
         */
        public function declare_wc_features_support() {
            if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
                \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', YWCPS_FREE_INIT, true );
            }
        }

	}
}
