<?php
/**
 * Class Strong_Testimonials_Addons
 *
 * @since 2.38
 */
class Strong_Testimonials_Addons {

	private $addons = array();

	public function __construct() {
		add_filter( 'wpmtst_submenu_pages', array( $this, 'add_submenu' ) );
	}

	/**
	 * Add submenu page.
	 *
	 * @param $pages
	 *
	 * @return mixed
	 */
	public function add_submenu( $pages ) {
		$pages[91] = $this->get_submenu();
		return $pages;
	}

	/**
	 * Return submenu page parameters.
	 *
	 * @return array
	 */
	public function get_submenu() {
		return array(
			'page_title' => esc_html__( 'Extensions', 'strong-testimonials' ),
			'menu_title' => esc_html__( 'Extensions', 'strong-testimonials' ),
			'capability' => 'strong_testimonials_options',
			'menu_slug'  => 'strong-testimonials-extensions',
			'function'   => array( $this, 'add_extensions_react_root' ),
		);
	}
	public function add_extensions_react_root() {
		echo '<div class="strong-testimonial-addons-container" id="strong-testimonial-addons"></div>';
	}
}

new Strong_Testimonials_Addons();
