<?php
/**
 * Class Strong_Testimonials_Menu_Settings
 */
class Strong_Testimonials_Menu_Settings {

	public static $callbacks;

	/**
	 * Strong_Testimonials_Menu_Settings constructor.
	 */
	public function __construct() {}

	/**
	 * Initialize.
	 */
	public static function init() {
		self::add_actions();
	}

	/**
	 * Add actions and filters.
	 */
	public static function add_actions() {
		add_filter( 'wpmtst_submenu_pages', array( __CLASS__, 'add_submenu' ) );
	}

	/**
	 * Add submenu page.
	 *
	 * @param $pages
	 *
	 * @return mixed
	 */
	public static function add_submenu( $pages ) {
		$pages[30] = self::get_submenu();
		return $pages;
	}

	/**
	 * Return submenu page parameters.
	 *
	 * @return array
	 */
	public static function get_submenu() {
		return array(
			'page_title' => esc_html__( 'Settings', 'strong-testimonials' ),
			'menu_title' => esc_html__( 'Settings', 'strong-testimonials' ),
			'capability' => 'strong_testimonials_options',
			'menu_slug'  => 'testimonial-settings',
			'function'   => array( __CLASS__, 'settings_page' ),
		);
	}

	/**
	 * Render the React-based settings page root element.
	 */
	public static function settings_page() {
		if ( ! current_user_can( 'strong_testimonials_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'strong-testimonials' ) );
		}
		echo '<div id="wpmtst-general-settings-app"></div>';
	}
}

Strong_Testimonials_Menu_Settings::init();
