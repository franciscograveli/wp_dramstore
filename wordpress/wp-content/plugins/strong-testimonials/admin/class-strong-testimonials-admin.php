<?php


class Strong_Testimonials_Admin {

	/**
	 * Holds the class object.
	 *
	 * @since 3.0.3
	 *
	 * @var object
	 */
	public static $instance;

	/**
	 * WPMTST_Admin_Helpers constructor.
	 *
	 * @since 3.0.3
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'register_script' ) );
		add_action( 'in_admin_header', array( $this, 'page_header' ) );
		add_filter( 'views_edit-wpm-testimonial', array( $this, 'add_onboarding_view' ), 20, 1 );
	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @return object The Strong_Testimonials_Admin object.
	 *
	 * @since 3.0.3
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Strong_Testimonials_Admin ) ) {
			self::$instance = new Strong_Testimonials_Admin();
		}

		return self::$instance;
	}

	/**
	 * Display the ST Admin Page Header
	 *
	 * @param bool $extra_class
	 *
	 * @since 3.0.3
	 */
	public static function page_header() {
		$current_screen = get_current_screen();

		$allowed_bases = array( 'wpm-testimonial_page_testimonial-settings', 'wpm-testimonial_page_strong-testimonials-extensions' );
		$show_header   = in_array( $current_screen->base, $allowed_bases, true );

		if ( ! apply_filters( 'wpmtst_page_header', $show_header, $current_screen ) ) {
			return;
		}

		wp_enqueue_script( 'wpmtst-header' );
		wp_enqueue_style( 'wpmtst-header' );

		?>
		<div id="wpmtst-admin-header-root"></div>
		<?php
	}

	public static function render_admin_tabs() {
		$screen = get_current_screen();

		$screen_to_tab = array(
			'edit'                                                => 'all-testimonials',
			'wpm-testimonial_page_testimonial-settings'           => 'testimonial-settings',
			'wpm-testimonial_page_strong-testimonials-extensions' => 'strong-testimonials-extensions',
		);
		$active_tab = isset( $screen_to_tab[ $screen->base ] ) ? $screen_to_tab[ $screen->base ] : '';

		$tabs = apply_filters(
			'wpmtst_admin_tabs',
			array(
				'all-testimonials'               => array(
					'label' => esc_html__( 'All Testimonials', 'strong-testimonials' ),
					'url'   => admin_url( 'edit.php?post_type=wpm-testimonial' ),
				),
				'strong-testimonials-extensions' => array(
					'label' => esc_html__( 'Extensions', 'strong-testimonials' ),
					'url'   => admin_url( 'edit.php?post_type=wpm-testimonial&page=strong-testimonials-extensions' ),
				),
			)
		);

		echo '<div class="wpmtst-nav-wrapper"><div class="wpmtst-nav-tab-wrapper wp-clearfix">';
		foreach ( $tabs as $slug => $tab ) {
			$active = $active_tab === $slug ? ' wpmtst-nav-tab-active' : '';
			printf(
				'<a href="%s" class="wpmtst-nav-tab%s">%s</a>',
				esc_url( $tab['url'] ),
				esc_attr( $active ),
				esc_html( $tab['label'] )
			);
		}
		echo '</div></div>';
	}

	public function register_script() {
		$plugin_version = get_option( 'wpmtst_plugin_version' );

		/**
		 * Header
		 *
		 * @since 3.0
		 */
		$header_asset_file = WPMTST_DIR . 'assets/dist/header/index.asset.php';
		$header_asset      = file_exists( $header_asset_file )
			? require $header_asset_file
			: array(
				'version' => $plugin_version,
			);

		wp_register_script(
			'wpmtst-header',
			WPMTST_ASSETS_JS . 'header/index.js',
			$header_asset['dependencies'],
			$header_asset['version'],
			true
		);

		wp_register_style(
			'wpmtst-header',
			WPMTST_ASSETS_JS . 'header/index.css',
			array(),
			$header_asset['version']
		);
	}

	public function add_onboarding_view( $views ) {
		$current_screen = get_current_screen();

		if ( 'wpm-testimonial' !== $current_screen->post_type || ! in_array( $current_screen->base, array( 'edit', 'wpm-testimonial_page_testimonial-settings', 'wpm-testimonial_page_strong-testimonials-extensions' ), true ) ) {
			return $views;
		}

		$query = new WP_Query(
			array(
				'post_type'   => 'wpm-testimonial',
				'post_status' => array(
					'publish',
					'future',
					'trash',
					'draft',
					'inherit',
					'pending',
				),
			)
		);

		self::render_admin_tabs();

		if ( ! $query->have_posts() ) {
				global $wp_list_table;
				$wp_list_table = new WPMTST_Onboarding();

				return array();
		}

		return $views;
	}
}

Strong_Testimonials_Admin::get_instance();