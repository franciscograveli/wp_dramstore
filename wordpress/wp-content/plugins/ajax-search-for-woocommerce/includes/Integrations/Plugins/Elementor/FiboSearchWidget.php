<?php

namespace DgoraWcas\Integrations\Plugins\Elementor;

use Elementor\Controls_Manager;
use Elementor\Widget_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * FiboSearchWidget Class
 */
class FiboSearchWidget extends Widget_Base {
	public function __construct( $data = [], $args = null ) {
		parent::__construct( $data, $args );
		$this->enqueue_elementor_preview_styles();
	}

	private function enqueue_elementor_preview_styles() {
		add_action(
			'elementor/preview/enqueue_styles',
			function () {
				ob_start();
				?>
			<style>
				/**
				 * ╭───────────────────────────────────────╮
				 * | Icon on mobile, search bar on desktop |
				 * ╰───────────────────────────────────────╯
				 */
				.elementor-widget-fibosearch .dgwt-wcas-search-wrapp.dgwt-wcas-layout-icon-flexible,
				.elementor-widget-fibosearch .dgwt-wcas-search-wrapp.dgwt-wcas-layout-icon-flexible .dgwt-wcas-search-form {
					max-width: 600px;
				}

				.elementor-widget-fibosearch .dgwt-wcas-search-wrapp.dgwt-wcas-layout-icon-flexible .dgwt-wcas-icon-preloader,
				.elementor-widget-fibosearch .dgwt-wcas-search-wrapp.dgwt-wcas-layout-icon-flexible-inv .dgwt-wcas-icon-preloader {
					display: none;
				}

				.elementor-widget-fibosearch .dgwt-wcas-search-wrapp.dgwt-wcas-layout-icon-flexible .dgwt-wcas-search-form {
					opacity: 1;
				}

				/**
				 * ╭───────────────────────────────────────╮
				 * | Icon on desktop, search bar on mobile |
				 * ╰───────────────────────────────────────╯
				 */
				.elementor-widget-fibosearch .dgwt-wcas-search-wrapp.dgwt-wcas-layout-icon-flexible-inv .dgwt-wcas-search-icon {
					display: block;
				}

				.elementor-widget-fibosearch .dgwt-wcas-search-wrapp.dgwt-wcas-layout-icon-flexible-inv .dgwt-wcas-search-form {
					display: none;
				}

				/* -------------------------------------- */
				/* Responsive (mobile)                    */
				/* -------------------------------------- */
				@media (max-width: 767px) {
					/**
					 * ╭───────────────────────────────────────╮
					 * | Icon on mobile, search bar on desktop |
					 * ╰───────────────────────────────────────╯
					 */
					.elementor-widget-fibosearch .dgwt-wcas-search-wrapp.dgwt-wcas-layout-icon-flexible .dgwt-wcas-search-form {
						opacity: 0;
						display: none;
					}

					.elementor-widget-fibosearch .dgwt-wcas-search-wrapp.dgwt-wcas-layout-icon-flexible .dgwt-wcas-search-icon {
						display: block;
						margin: 0 auto;
					}

					/**
					 * ╭───────────────────────────────────────╮
					 * | Icon on desktop, search bar on mobile |
					 * ╰───────────────────────────────────────╯
					 */
					.elementor-widget-fibosearch .dgwt-wcas-search-wrapp.dgwt-wcas-layout-icon-flexible-inv .dgwt-wcas-search-form {
						display: block;
						opacity: 1;
						max-width: 600px;
					}

					.elementor-widget-fibosearch .dgwt-wcas-search-wrapp.dgwt-wcas-layout-icon-flexible-inv .dgwt-wcas-search-icon {
						display: none;
					}

					.elementor-widget-fibosearch .dgwt-wcas-search-wrapp.dgwt-wcas-layout-icon-flexible-inv,
					.elementor-widget-fibosearch .dgwt-wcas-search-wrapp.dgwt-wcas-layout-icon-flexible-inv .dgwt-wcas-search-form {
						max-width: 600px;
					}
				}
			</style>
				<?php
				$css_with_style = ob_get_clean();
				$css            = str_replace( [ '<style>', '</style>' ], '', $css_with_style );

				wp_register_style( 'fibosearch-elementor-editor-only', false, [], DGWT_WCAS_VERSION );
				wp_enqueue_style( 'fibosearch-elementor-editor-only' );
				wp_add_inline_style( 'fibosearch-elementor-editor-only', $css );
			}
		);
	}

	public function get_name(): string {
		return 'fibosearch';
	}

	public function get_title(): string {
		return esc_html__( 'FiboSearch', 'ajax-search-for-woocommerce' );
	}

	public function get_icon(): string {
		return 'fibosearchicon-fibosearch';
	}

	public function get_categories(): array {
		return [ 'woocommerce-elements' ];
	}

	public function get_keywords(): array {
		return [ 'fibo', 'search', 'fibosearch' ];
	}


	public function get_custom_help_url(): string {
		// TODO Sprecyzować link do strony z opisem dla Elementora.
		return 'https://fibosearch.com/documentation/';
	}

	protected function register_controls() {
		$this->start_controls_section(
			'content_section',
			[
				'label' => esc_html__( 'Appearance', 'ajax-search-for-woocommerce' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'layout',
			[
				'type'    => Controls_Manager::SELECT,
				'label'   => esc_html__( 'Layout', 'ajax-search-for-woocommerce' ),
				'options' => [
					'default'           => esc_html__( 'Default', 'ajax-search-for-woocommerce' ),
					'classic'           => esc_html__( 'Search bar', 'ajax-search-for-woocommerce' ),
					'icon'              => esc_html__( 'Search icon', 'ajax-search-for-woocommerce' ),
					'icon-flexible'     => esc_html__( 'Icon on mobile, search bar on desktop', 'ajax-search-for-woocommerce' ),
					'icon-flexible-inv' => esc_html__( 'Icon on desktop, search bar on mobile', 'ajax-search-for-woocommerce' ),
				],
				'default' => 'default',
			]
		);

		$this->add_control(
			'mobile_overlay',
			[
				'type'  => Controls_Manager::SWITCHER,
				'label' => esc_html__( 'Overlay on mobile', 'ajax-search-for-woocommerce' ),
			]
		);

		$this->end_controls_section();
	}

	/**
	 * @return void
	 */
	protected function render() {
		$params = '';

		// Layout.
		$layout = $this->get_settings_for_display( 'layout' );
		if ( in_array( $layout, [ 'classic', 'icon', 'icon-flexible', 'icon-flexible-inv' ] ) ) {
			$params .= ' layout="' . $layout . '"';
		}

		// Overlay on mobile.
		$mobile_overlay = $this->get_settings_for_display( 'mobile_overlay' );
		if ( $mobile_overlay === 'yes' ) {
			$params .= ' mobile_overlay="1"';
		}

		echo do_shortcode( '[fibosearch' . $params . ']' );
	}
}
