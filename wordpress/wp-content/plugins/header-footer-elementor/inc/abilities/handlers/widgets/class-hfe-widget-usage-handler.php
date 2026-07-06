<?php
/**
 * Widget Usage Handler.
 *
 * Returns site-wide usage counts for HFE widgets across all Elementor content.
 *
 * @package header-footer-elementor
 * @since 2.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class HFE_Widget_Usage_Handler
 *
 * Implements HFE_Ability_Handler for the widgets/get-usage ability.
 *
 * @since 2.9.0
 */
class HFE_Widget_Usage_Handler implements HFE_Ability_Handler {

	/**
	 * Get the ability name.
	 *
	 * @since 2.9.0
	 *
	 * @return string Ability name without plugin prefix.
	 */
	public function get_name() {
		return 'widgets-get-usage';
	}

	/**
	 * Get the wp_register_ability() args array.
	 *
	 * Does NOT include execute_callback -- the registry sets that automatically.
	 *
	 * @since 2.9.0
	 *
	 * @return array Ability registration args.
	 */
	public function get_registration_args() {
		return [
			'label'               => __( 'Get Widget Usage Map', 'header-footer-elementor' ),
			'description'         => __( 'Returns site-wide usage counts for HFE widgets across all Elementor content (pages, posts, and templates).', 'header-footer-elementor' ),
			'category'            => 'hfe-widgets',
			'permission_callback' => function () {
				return current_user_can( 'manage_options' );
			},
			'input_schema'        => [
				'type'       => 'object',
				'properties' => [
					'widget_slug' => [
						'type'        => 'string',
						'description' => __( 'Optional. Filter to a single widget slug.', 'header-footer-elementor' ),
					],
				],
			],
			'output_schema'       => [
				'type'       => 'object',
				'properties' => [
					'widgets' => [
						'type'  => 'array',
						'items' => [
							'type'       => 'object',
							'properties' => [
								'widget_slug' => [ 'type' => 'string' ],
								'title'       => [ 'type' => 'string' ],
								'is_active'   => [ 'type' => 'boolean' ],
								'usage_count' => [ 'type' => 'integer' ],
							],
						],
					],
					'count'   => [ 'type' => 'integer' ],
				],
				'required'   => [ 'widgets' ],
			],
			'meta'                => [
				'annotations'  => [
					'readonly'     => true,
					'destructive'  => false,
					'idempotent'   => true,
					'instructions' => 'Use this to check which widgets are actively used on the site before deactivating any widget.',
				],
				'show_in_rest' => true,
				'mcp'          => [ 'public' => true ],
			],
		];
	}

	/**
	 * Execute the ability.
	 *
	 * Uses Elementor's Usage Module to scan all Elementor content site-wide
	 * (pages, posts, templates) and returns usage counts for HFE widgets.
	 *
	 * @since 2.9.0
	 *
	 * @param array $input Optional input with widget_slug filter.
	 * @return array|WP_Error Array of widget usage objects or error.
	 */
	public function execute( $input ) {
		$filter_slug = ! empty( $input['widget_slug'] ) ? sanitize_text_field( $input['widget_slug'] ) : '';

		// Site-wide usage via Elementor's Usage Module.
		$used_widgets = \HFE\WidgetsManager\Base\HFE_Helper::get_used_widget();

		// Get all known HFE widgets for metadata.
		$widget_options = \HFE\WidgetsManager\Base\HFE_Helper::get_widget_options();
		$hfe_slugs      = [];

		foreach ( $widget_options as $class_name => $data ) {
			if ( ! empty( $data['slug'] ) ) {
				$hfe_slugs[ $data['slug'] ] = [
					'title'     => isset( $data['title'] ) ? $data['title'] : '',
					'is_active' => ! empty( $data['is_activate'] ),
				];
			}
		}

		// If filtering by slug, validate it exists.
		if ( '' !== $filter_slug && ! isset( $hfe_slugs[ $filter_slug ] ) ) {
			return new WP_Error(
				'hfe_invalid_widget',
				/* translators: %s: widget slug */
				sprintf( __( 'Widget "%s" not found.', 'header-footer-elementor' ), $filter_slug ),
				[ 'status' => 404 ]
			);
		}

		$result     = [];
		$slugs_list = '' !== $filter_slug ? [ $filter_slug ] : array_keys( $hfe_slugs );

		foreach ( $slugs_list as $slug ) {
			$widget_info = isset( $hfe_slugs[ $slug ] ) ? $hfe_slugs[ $slug ] : [
				'title'     => $slug,
				'is_active' => false,
			];

			$result[] = [
				'widget_slug' => $slug,
				'title'       => $widget_info['title'],
				'is_active'   => $widget_info['is_active'],
				'usage_count' => isset( $used_widgets[ $slug ] ) ? (int) $used_widgets[ $slug ] : 0,
			];
		}

		return [
			'widgets' => $result,
			'count'   => count( $result ),
		];
	}
}
