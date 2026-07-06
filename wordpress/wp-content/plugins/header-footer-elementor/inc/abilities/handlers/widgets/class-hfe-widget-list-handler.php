<?php
/**
 * Widget List Handler.
 *
 * Returns all available widgets with their status and metadata.
 *
 * @package header-footer-elementor
 * @since 2.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class HFE_Widget_List_Handler
 *
 * Implements HFE_Ability_Handler for the widgets/list ability.
 *
 * @since 2.9.0
 */
class HFE_Widget_List_Handler implements HFE_Ability_Handler {

	/**
	 * Get the ability name.
	 *
	 * @since 2.9.0
	 *
	 * @return string Ability name without plugin prefix.
	 */
	public function get_name() {
		return 'widgets-list';
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
			'label'               => __( 'List Widgets', 'header-footer-elementor' ),
			'description'         => __( 'Lists all available widgets with their enabled/disabled status, slug, title, and category.', 'header-footer-elementor' ),
			'category'            => 'hfe-widgets',
			'permission_callback' => function () {
				return current_user_can( 'manage_options' );
			},
			'input_schema'        => [
				'type'       => 'object',
				'properties' => (object) [],
			],
			'output_schema'       => [
				'type'       => 'object',
				'properties' => [
					'widgets' => [
						'type'  => 'array',
						'items' => [
							'type'       => 'object',
							'properties' => [
								'slug'        => [ 'type' => 'string' ],
								'class_name'  => [ 'type' => 'string' ],
								'title'       => [ 'type' => 'string' ],
								'description' => [ 'type' => 'string' ],
								'is_active'   => [ 'type' => 'boolean' ],
								'is_pro'      => [ 'type' => 'boolean' ],
								'category'    => [ 'type' => 'string' ],
								'icon'        => [ 'type' => 'string' ],
								'doc_url'     => [ 'type' => 'string' ],
								'demo_url'    => [ 'type' => 'string' ],
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
					'instructions' => 'Use this to show the user all available widgets and their activation status. Helpful before activating or deactivating widgets.',
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
	 * @param array $input Unused input parameters.
	 * @return array Array of widget data objects.
	 */
	public function execute( $input ) {
		$widgets = \HFE\WidgetsManager\Base\HFE_Helper::get_widget_options();
		$result  = [];

		foreach ( $widgets as $class_name => $data ) {
			$result[] = [
				'slug'        => isset( $data['slug'] ) ? $data['slug'] : '',
				'class_name'  => $class_name,
				'title'       => isset( $data['title'] ) ? $data['title'] : '',
				'description' => isset( $data['description'] ) ? $data['description'] : '',
				'is_active'   => ! empty( $data['is_activate'] ),
				'is_pro'      => ! empty( $data['is_pro'] ),
				'category'    => isset( $data['category'] ) ? $data['category'] : '',
				'icon'        => isset( $data['icon'] ) ? $data['icon'] : '',
				'doc_url'     => isset( $data['doc_url'] ) ? $data['doc_url'] : '',
				'demo_url'    => isset( $data['demo_url'] ) ? $data['demo_url'] : '',
			];
		}

		return [
			'widgets' => $result,
			'count'   => count( $result ),
		];
	}
}
