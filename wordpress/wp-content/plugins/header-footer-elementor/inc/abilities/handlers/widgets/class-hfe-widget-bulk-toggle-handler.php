<?php
/**
 * Widget Bulk Toggle Handler.
 *
 * Activates or deactivates all widgets in a single operation.
 * Replaces the old bulk-activate and bulk-deactivate abilities.
 *
 * @package header-footer-elementor
 * @since 2.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class HFE_Widget_Bulk_Toggle_Handler
 *
 * Implements HFE_Ability_Handler for the widgets/bulk-toggle ability.
 *
 * @since 2.9.0
 */
class HFE_Widget_Bulk_Toggle_Handler implements HFE_Ability_Handler {

	/**
	 * Get the ability name.
	 *
	 * @since 2.9.0
	 *
	 * @return string Ability name without plugin prefix.
	 */
	public function get_name() {
		return 'widgets-bulk-toggle';
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
			'label'               => __( 'Bulk Toggle All Widgets', 'header-footer-elementor' ),
			'description'         => __( 'Activate or deactivate all widgets at once. When activating, Pro widgets are skipped. When deactivating, all widgets are disabled which may break the frontend.', 'header-footer-elementor' ),
			'category'            => 'hfe-widgets',
			'permission_callback' => function () {
				return current_user_can( 'manage_options' );
			},
			'input_schema'        => [
				'type'       => 'object',
				'required'   => [ 'action' ],
				'properties' => [
					'action' => [
						'type'        => 'string',
						'enum'        => [ 'activate', 'deactivate' ],
						'description' => __( 'Whether to activate or deactivate all widgets.', 'header-footer-elementor' ),
					],
				],
			],
			'output_schema'       => [
				'type'       => 'object',
				'properties' => [
					'success' => [ 'type' => 'boolean' ],
					'action'  => [ 'type' => 'string' ],
					'count'   => [ 'type' => 'integer' ],
				],
			],
			'meta'                => [
				'annotations'  => [
					'readonly'     => false,
					'destructive'  => true,
					'idempotent'   => true,
					'instructions' => 'Warn user before deactivating all. Activating skips Pro widgets.',
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
	 * @param array $input Input with action ('activate' or 'deactivate').
	 * @return array Result with action performed and count of affected widgets.
	 */
	public function execute( $input ) {
		$action        = sanitize_text_field( $input['action'] );
		$widgets       = \HFE\WidgetsManager\Base\Widgets_Config::get_widget_list();
		$saved_widgets = get_option( '_hfe_widgets', [] );
		$count         = 0;

		if ( 'activate' === $action ) {
			foreach ( $widgets as $class_name => $data ) {
				if ( ! empty( $data['is_pro'] ) ) {
					continue;
				}
				$saved_widgets[ $class_name ] = $class_name;
				++$count;
			}
		} else {
			foreach ( $widgets as $class_name => $data ) {
				$saved_widgets[ $class_name ] = 'disabled';
				++$count;
			}
		}

		update_option( '_hfe_widgets', $saved_widgets );

		return [
			'success' => true,
			'action'  => $action,
			'count'   => $count,
		];
	}
}
