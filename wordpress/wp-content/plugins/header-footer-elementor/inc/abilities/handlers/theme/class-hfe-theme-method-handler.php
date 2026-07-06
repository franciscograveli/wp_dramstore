<?php
/**
 * Theme Method Handler.
 *
 * Sets the theme compatibility method for unsupported themes.
 *
 * @package header-footer-elementor
 * @since 2.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class HFE_Theme_Method_Handler
 *
 * Implements HFE_Ability_Handler for the theme/set-method ability.
 *
 * @since 2.9.0
 */
class HFE_Theme_Method_Handler implements HFE_Ability_Handler {

	/**
	 * Get the ability name.
	 *
	 * @since 2.9.0
	 *
	 * @return string Ability name without plugin prefix.
	 */
	public function get_name() {
		return 'theme-set-method';
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
			'label'               => __( 'Set Theme Compatibility Method', 'header-footer-elementor' ),
			'description'         => __( 'Configure fallback method for unsupported themes.', 'header-footer-elementor' ),
			'category'            => 'hfe-theme-compat',
			'permission_callback' => function () {
				return current_user_can( 'manage_options' );
			},
			'input_schema'        => [
				'type'       => 'object',
				'required'   => [ 'method' ],
				'properties' => [
					'method' => [
						'type'        => 'string',
						'enum'        => [ 'default', 'css-override' ],
						'description' => __( 'Compatibility method: default or css-override.', 'header-footer-elementor' ),
					],
				],
			],
			'output_schema'       => [
				'type'       => 'object',
				'properties' => [
					'success' => [ 'type' => 'boolean' ],
					'method'  => [ 'type' => 'string' ],
				],
			],
			'meta'                => [
				'annotations'  => [
					'readonly'     => false,
					'destructive'  => false,
					'idempotent'   => true,
					'instructions' => 'Explain the available methods (default vs CSS override) and confirm the choice with the user before changing.',
				],
				'show_in_rest' => true,
				'mcp'          => [ 'public' => true ],
			],
		];
	}

	/**
	 * Execute the ability.
	 *
	 * Translates API values to stored values and updates the option.
	 *
	 * @since 2.9.0
	 *
	 * @param array $input Input with method string.
	 * @return array|WP_Error Result or error.
	 */
	public function execute( $input ) {
		$method        = sanitize_key( $input['method'] );
		$valid_methods = [ 'default', 'css-override' ];

		if ( ! in_array( $method, $valid_methods, true ) ) {
			return new WP_Error(
				'hfe_invalid_method',
				__( 'Invalid compatibility method.', 'header-footer-elementor' ),
				[ 'status' => 400 ]
			);
		}

		// Translate API values to stored values: 'default' => '1', 'css-override' => '2'.
		$stored = 'css-override' === $method ? '2' : '1';
		update_option( 'hfe_compatibility_option', $stored );

		return [
			'success' => true,
			'method'  => $method,
		];
	}
}
