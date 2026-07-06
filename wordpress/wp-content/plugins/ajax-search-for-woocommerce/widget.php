<?php
/**
 * Exit if accessed directly.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( class_exists( 'WC_Widget' ) ) {

	add_action(
		'widgets_init',
		function () {
			register_widget( 'DGWT_WCAS_Search_Widget' );
		}
	);

	class DGWT_WCAS_Search_Widget extends WC_Widget {

		/**
		 * Constructor
		 */
		public function __construct() {

			$this->widget_cssclass    = 'woocommerce dgwt-wcas-widget';
			$this->widget_description = __( 'AJAX (live) search form for WooCommerce', 'ajax-search-for-woocommerce' );
			$this->widget_id          = 'dgwt_wcas_ajax_search';
			$this->widget_name        = __( 'FiboSearch bar', 'ajax-search-for-woocommerce' );
			$this->settings           = [
				'title'          => [
					'type'  => 'text',
					'std'   => '',
					'label' => __( 'Title', 'ajax-search-for-woocommerce' ),
				],
				'layout'         => [
					'type'    => 'select',
					'std'     => 'default',
					'options' => [
						'default'           => __( 'Default', 'ajax-search-for-woocommerce' ),
						'classic'           => __( 'Search bar', 'ajax-search-for-woocommerce' ),
						'icon'              => __( 'Search icon', 'ajax-search-for-woocommerce' ),
						'icon-flexible'     => __( 'Icon on mobile, search bar on desktop', 'ajax-search-for-woocommerce' ),
						'icon-flexible-inv' => __( 'Icon on desktop, search bar on mobile', 'ajax-search-for-woocommerce' ),
					],
					'label'   => __( 'Layout', 'ajax-search-for-woocommerce' ),
				],
				'mobile_overlay' => [
					'type'  => 'checkbox',
					'std'   => '',
					'label' => __( 'Overlay on mobile', 'ajax-search-for-woocommerce' ),
				],
			];

			parent::__construct();
		}

		/**
		 * Outputs the content of the widget
		 *
		 * @param array $args
		 * @param array $instance
		 */
		public function widget( $args, $instance ) {
			ob_start();

			$this->widget_start( $args, $instance );
			$params = '';

			// Layout

			if ( ! empty( $instance['layout'] ) && in_array( $instance['layout'], [ 'classic', 'icon', 'icon-flexible', 'icon-flexible-inv' ] ) ) {
				$params .= ' layout="' . $instance['layout'] . '"';
			}

			// Overlay on mobile
			if ( ! empty( $instance['mobile_overlay'] ) && $instance['mobile_overlay'] === 'on' ) {
				$params .= ' mobile_overlay="1"';
			}

			echo do_shortcode( '[fibosearch' . $params . ']' );

			$this->widget_end( $args );

			$html = ob_get_clean();
			//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo apply_filters( 'dgwt/wcas/widget/html', $html, $args, $instance );
		}
	}
}
