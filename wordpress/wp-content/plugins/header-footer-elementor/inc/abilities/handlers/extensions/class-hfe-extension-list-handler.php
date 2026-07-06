<?php
/**
 * Extension List Handler.
 *
 * Returns all available extensions with their status and metadata.
 *
 * @package header-footer-elementor
 * @since 2.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class HFE_Extension_List_Handler
 *
 * Implements HFE_Ability_Handler for the extensions/list ability.
 *
 * @since 2.9.0
 */
class HFE_Extension_List_Handler implements HFE_Ability_Handler {

	/**
	 * Get the ability name.
	 *
	 * @since 2.9.0
	 *
	 * @return string Ability name without plugin prefix.
	 */
	public function get_name() {
		return 'extensions-list';
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
			'label'               => __( 'List Extensions', 'header-footer-elementor' ),
			'description'         => __( 'Lists available extensions (Scroll to Top, Reading Progress Bar) and their status.', 'header-footer-elementor' ),
			'category'            => 'hfe-extensions',
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
					'extensions' => [
						'type'  => 'array',
						'items' => [
							'type'       => 'object',
							'properties' => [
								'slug'        => [ 'type' => 'string' ],
								'title'       => [ 'type' => 'string' ],
								'description' => [ 'type' => 'string' ],
								'is_active'   => [ 'type' => 'boolean' ],
							],
						],
					],
					'count'      => [ 'type' => 'integer' ],
				],
				'required'   => [ 'extensions' ],
			],
			'meta'                => [
				'annotations'  => [
					'readonly'     => true,
					'destructive'  => false,
					'idempotent'   => true,
					'instructions' => 'Use this to show the user available extensions and their current activation status.',
				],
				'show_in_rest' => true,
				'mcp'          => [ 'public' => true ],
			],
		];
	}

	/**
	 * Execute the ability.
	 *
	 * Pulls extension metadata from Widgets_Config and activation status
	 * from HFE_Helper::is_widget_active().
	 *
	 * @since 2.9.0
	 *
	 * @param array $input Unused input parameters.
	 * @return array Array of extension data objects.
	 */
	public function execute( $input ) {
		$extensions  = \HFE\WidgetsManager\Extensions_Loader::get_extensions_list();
		$all_widgets = \HFE\WidgetsManager\Base\Widgets_Config::get_widget_list();
		$result      = [];

		foreach ( $extensions as $class_name => $slug ) {
			$widget_data = isset( $all_widgets[ $class_name ] ) ? $all_widgets[ $class_name ] : [];

			$result[] = [
				'slug'        => $slug,
				'title'       => isset( $widget_data['title'] ) ? $widget_data['title'] : $class_name,
				'description' => isset( $widget_data['description'] ) ? $widget_data['description'] : '',
				'is_active'   => \HFE\WidgetsManager\Base\HFE_Helper::is_widget_active( $class_name ),
			];
		}

		return [
			'extensions' => $result,
			'count'      => count( $result ),
		];
	}
}
