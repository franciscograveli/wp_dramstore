<?php
/**
 * Extension Toggle Handler.
 *
 * Enables or disables a specific extension by slug.
 *
 * @package header-footer-elementor
 * @since 2.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class HFE_Extension_Toggle_Handler
 *
 * Implements HFE_Ability_Handler for the extensions/toggle ability.
 *
 * @since 2.9.0
 */
class HFE_Extension_Toggle_Handler implements HFE_Ability_Handler {

	/**
	 * Get the ability name.
	 *
	 * @since 2.9.0
	 *
	 * @return string Ability name without plugin prefix.
	 */
	public function get_name() {
		return 'extensions-toggle';
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
			'label'               => __( 'Toggle Extension', 'header-footer-elementor' ),
			'description'         => __( 'Enable or disable a specific extension.', 'header-footer-elementor' ),
			'category'            => 'hfe-extensions',
			'permission_callback' => function () {
				return current_user_can( 'manage_options' );
			},
			'input_schema'        => [
				'type'       => 'object',
				'required'   => [ 'extension_slug', 'active' ],
				'properties' => [
					'extension_slug' => [
						'type'        => 'string',
						'enum'        => [ 'scroll-to-top', 'reading-progress-bar' ],
						'description' => __( 'Extension slug.', 'header-footer-elementor' ),
					],
					'active'         => [
						'type'        => 'boolean',
						'description' => __( 'True to enable, false to disable.', 'header-footer-elementor' ),
					],
				],
			],
			'output_schema'       => [
				'type'       => 'object',
				'properties' => [
					'success'        => [ 'type' => 'boolean' ],
					'extension_slug' => [ 'type' => 'string' ],
					'is_active'      => [ 'type' => 'boolean' ],
				],
			],
			'meta'                => [
				'annotations'  => [
					'readonly'     => false,
					'destructive'  => false,
					'idempotent'   => true,
					'instructions' => 'Confirm with the user which extension to enable or disable before toggling.',
				],
				'show_in_rest' => true,
				'mcp'          => [ 'public' => true ],
			],
		];
	}

	/**
	 * Execute the ability.
	 *
	 * Reverse-looks up the class name from the slug and updates the
	 * _hfe_widgets option to enable or disable the extension.
	 *
	 * @since 2.9.0
	 *
	 * @param array $input Input with extension_slug and active boolean.
	 * @return array|WP_Error Result or error.
	 */
	public function execute( $input ) {
		$slug   = sanitize_text_field( $input['extension_slug'] );
		$active = (bool) $input['active'];

		// Reverse-lookup class name from slug.
		$extensions = \HFE\WidgetsManager\Extensions_Loader::get_extensions_list();
		$class_name = array_search( $slug, $extensions, true );

		if ( false === $class_name ) {
			return new WP_Error(
				'hfe_invalid_extension',
				/* translators: %s: extension slug */
				sprintf( __( 'Extension "%s" not found.', 'header-footer-elementor' ), $slug ),
				[ 'status' => 404 ]
			);
		}

		$saved_widgets = get_option( '_hfe_widgets', [] );

		if ( $active ) {
			$saved_widgets[ $class_name ] = $class_name;
		} else {
			$saved_widgets[ $class_name ] = 'disabled';
		}

		update_option( '_hfe_widgets', $saved_widgets );

		return [
			'success'        => true,
			'extension_slug' => $slug,
			'is_active'      => $active,
		];
	}
}
