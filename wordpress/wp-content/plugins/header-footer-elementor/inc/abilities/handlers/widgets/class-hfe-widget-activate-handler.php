<?php
/**
 * Widget Activate Handler.
 *
 * Enables a specific widget by slug.
 *
 * @package header-footer-elementor
 * @since 2.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class HFE_Widget_Activate_Handler
 *
 * Implements HFE_Ability_Handler for the widgets/activate ability.
 *
 * @since 2.9.0
 */
class HFE_Widget_Activate_Handler implements HFE_Ability_Handler {

	/**
	 * Get the ability name.
	 *
	 * @since 2.9.0
	 *
	 * @return string Ability name without plugin prefix.
	 */
	public function get_name() {
		return 'widgets-activate';
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
			'label'               => __( 'Activate Widget', 'header-footer-elementor' ),
			'description'         => __( 'Enable a specific widget by slug.', 'header-footer-elementor' ),
			'category'            => 'hfe-widgets',
			'permission_callback' => function () {
				return current_user_can( 'manage_options' );
			},
			'input_schema'        => [
				'type'       => 'object',
				'required'   => [ 'widget_slug' ],
				'properties' => [
					'widget_slug' => [
						'type'        => 'string',
						'description' => __( 'Widget slug to activate (e.g., site-logo, navigation-menu).', 'header-footer-elementor' ),
					],
				],
			],
			'output_schema'       => [
				'type'       => 'object',
				'properties' => [
					'success'     => [ 'type' => 'boolean' ],
					'widget_slug' => [ 'type' => 'string' ],
					'message'     => [ 'type' => 'string' ],
				],
			],
			'meta'                => [
				'annotations'  => [
					'readonly'     => false,
					'destructive'  => false,
					'idempotent'   => true,
					'instructions' => 'Confirm the widget name with the user before activating. Use widgets/list first if the slug is unknown.',
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
	 * @param array $input Input with widget_slug.
	 * @return array|WP_Error Result or error.
	 */
	public function execute( $input ) {
		$slug   = sanitize_text_field( $input['widget_slug'] );
		$widget = $this->find_widget_by_slug( $slug );

		if ( ! $widget ) {
			return new WP_Error(
				'hfe_invalid_widget',
				/* translators: %s: widget slug */
				sprintf( __( 'Widget "%s" not found.', 'header-footer-elementor' ), $slug ),
				[ 'status' => 404 ]
			);
		}

		$saved_widgets = get_option( '_hfe_widgets', [] );
		$saved_widgets[ $widget['class_name'] ] = $widget['class_name'];
		update_option( '_hfe_widgets', $saved_widgets );

		return [
			'success'     => true,
			'widget_slug' => $slug,
			'message'     => __( 'Widget activated.', 'header-footer-elementor' ),
		];
	}

	/**
	 * Find a widget config entry by its slug.
	 *
	 * Widget config is keyed by class name, so this reverse-lookups by slug.
	 *
	 * @since 2.9.0
	 *
	 * @param string $slug Widget slug (e.g., 'retina', 'site-logo').
	 * @return array|null Array with 'class_name' and 'data' keys, or null if not found.
	 */
	private function find_widget_by_slug( $slug ) {
		$widgets = \HFE\WidgetsManager\Base\Widgets_Config::get_widget_list();

		foreach ( $widgets as $class_name => $data ) {
			if ( isset( $data['slug'] ) && $data['slug'] === $slug ) {
				return [
					'class_name' => $class_name,
					'data'       => $data,
				];
			}
		}

		return null;
	}
}
