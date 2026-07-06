<?php
/**
 * Widget Types Handler.
 *
 * Lists all widget types available for insertion into Elementor posts.
 * Unified handler replacing template-builder/list-widget-types.
 *
 * @package header-footer-elementor
 * @since 2.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class HFE_Widget_Types_Handler
 *
 * Implements HFE_Ability_Handler for the builder/list-widget-types ability.
 *
 * @since 2.9.0
 */
class HFE_Widget_Types_Handler implements HFE_Ability_Handler {

	use HFE_Abilities_Helpers;

	/**
	 * Get the ability name.
	 *
	 * @since 2.9.0
	 *
	 * @return string Ability name without plugin prefix.
	 */
	public function get_name() {
		return 'builder-list-widget-types';
	}

	/**
	 * Get the wp_register_ability() args array.
	 *
	 * @since 2.9.0
	 *
	 * @return array Ability registration args.
	 */
	public function get_registration_args() {
		return [
			'label'               => __( 'List Available Widget Types', 'header-footer-elementor' ),
			'description'         => __( 'Lists all widget types that can be inserted. RECOMMENDED — Header: site-logo, navigation-menu, hfe-search-button, hfe-cart, hfe-menu. Footer: copyright, retina (logo), social-icons, hfe-menu. Pages: heading, text-editor, image, button, hfe-infocard, hfe-counter, spacer, divider.', 'header-footer-elementor' ),
			'category'            => 'hfe-widgets',
			'permission_callback' => function () {
				return current_user_can( 'edit_posts' );
			},
			'input_schema'        => [
				'type'       => 'object',
				'properties' => [
					'source' => [
						'type'        => 'string',
						'enum'        => [ 'all', 'header-footer-elementor', 'elementor', 'ultimate-addons-for-elementor' ],
						'default'     => 'all',
						'description' => __( 'Filter by source plugin.', 'header-footer-elementor' ),
					],
				],
			],
			'output_schema'       => [
				'type'       => 'object',
				'properties' => [
					'widget_types' => [
						'type'  => 'array',
						'items' => [
							'type'       => 'object',
							'properties' => [
								'slug'   => [ 'type' => 'string' ],
								'title'  => [ 'type' => 'string' ],
								'source' => [ 'type' => 'string' ],
							],
						],
					],
					'count'        => [ 'type' => 'integer' ],
				],
				'required'   => [ 'widget_types' ],
			],
			'meta'                => [
				'annotations'  => [
					'readonly'     => true,
					'destructive'  => false,
					'idempotent'   => true,
					'instructions' => 'Use this to discover available widget types before building layouts. The slug is what you pass as widget_type. RECOMMENDED WIDGETS BY TEMPLATE TYPE: Header -- site-logo, navigation-menu, hfe-search-button, button, social-icons, hfe-cart, hfe-menu. Footer -- copyright, retina (logo), hfe-menu, social-icons, heading, icon-list. Custom blocks -- any Elementor widget (heading, image, button, text-editor, icon-box, etc.). Call builder/get-schema with type=widget on any widget to discover its content + style settings before building.',
				],
				'show_in_rest' => true,
				'mcp'          => [ 'public' => true ],
			],
		];
	}

	/**
	 * Execute the ability.
	 *
	 * @since 2.9.0
	 *
	 * @param array $input Validated input parameters.
	 * @return array List of widget types.
	 */
	public function execute( $input ) {
		$widgets = HFE_Element_Helpers::get_allowed_widget_types();
		$source  = sanitize_text_field( $input['source'] ?? 'all' );

		if ( 'all' !== $source ) {
			$widgets = array_values(
				array_filter(
					$widgets,
					function ( $w ) use ( $source ) {
						return $w['source'] === $source;
					}
				)
			);
		}

		return [
			'widget_types' => $widgets,
			'count'        => count( $widgets ),
		];
	}
}
