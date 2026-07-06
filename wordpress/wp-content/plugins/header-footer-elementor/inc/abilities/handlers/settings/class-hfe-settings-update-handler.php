<?php
/**
 * Settings Update Handler.
 *
 * Updates a specific plugin setting with per-key value validation.
 *
 * @package header-footer-elementor
 * @since 2.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class HFE_Settings_Update_Handler
 *
 * Implements HFE_Ability_Handler for the settings/update ability.
 *
 * @since 2.9.0
 */
class HFE_Settings_Update_Handler implements HFE_Ability_Handler {

	/**
	 * Whitelisted settings and their allowed values.
	 *
	 * @var array
	 */
	private $valid_values = [
		'hfe_compatibility_option' => [ '1', '2' ],
		'uae_usage_optin'          => [ 'yes', 'no', '' ],
	];

	/**
	 * Get the ability name.
	 *
	 * @since 2.9.0
	 *
	 * @return string Ability name without plugin prefix.
	 */
	public function get_name() {
		return 'settings-update';
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
			'label'               => __( 'Update Plugin Setting', 'header-footer-elementor' ),
			'description'         => __( 'Update a specific plugin setting. Only safe, non-sensitive settings are allowed.', 'header-footer-elementor' ),
			'category'            => 'hfe-settings',
			'permission_callback' => function () {
				return current_user_can( 'manage_options' );
			},
			'input_schema'        => [
				'type'       => 'object',
				'required'   => [ 'setting_key', 'value' ],
				'properties' => [
					'setting_key' => [
						'type'        => 'string',
						'enum'        => [ 'hfe_compatibility_option', 'uae_usage_optin' ],
						'description' => __( 'Setting key to update.', 'header-footer-elementor' ),
					],
					'value'       => [
						'type'        => 'string',
						'description' => __( 'New value for the setting.', 'header-footer-elementor' ),
					],
				],
			],
			'output_schema'       => [
				'type'       => 'object',
				'properties' => [
					'success'     => [ 'type' => 'boolean' ],
					'setting_key' => [ 'type' => 'string' ],
					'old_value'   => [ 'type' => 'string' ],
					'new_value'   => [ 'type' => 'string' ],
				],
			],
			'meta'                => [
				'annotations'  => [
					'readonly'     => false,
					'destructive'  => false,
					'idempotent'   => true,
					'instructions' => 'Always confirm the setting name and new value with the user before updating. Show the current value first.',
				],
				'show_in_rest' => true,
				'mcp'          => [ 'public' => true ],
			],
		];
	}

	/**
	 * Execute the ability.
	 *
	 * Validates the setting key against the whitelist and the value against
	 * per-key allowed values before updating.
	 *
	 * @since 2.9.0
	 *
	 * @param array $input Input with setting_key and value.
	 * @return array|WP_Error Result or error.
	 */
	public function execute( $input ) {
		if ( ! isset( $input['setting_key'] ) || ! isset( $input['value'] ) ) {
			return new WP_Error(
				'hfe_missing_params',
				__( 'Both setting_key and value are required.', 'header-footer-elementor' ),
				[ 'status' => 400 ]
			);
		}

		$key = sanitize_key( $input['setting_key'] );

		if ( ! isset( $this->valid_values[ $key ] ) ) {
			return new WP_Error(
				'hfe_invalid_setting',
				/* translators: %s: setting key */
				sprintf( __( 'Setting "%s" is not in the allowed list.', 'header-footer-elementor' ), $key ),
				[ 'status' => 400 ]
			);
		}

		$old_value = get_option( $key, '' );
		$new_value = sanitize_text_field( $input['value'] );

		if ( ! in_array( $new_value, $this->valid_values[ $key ], true ) ) {
			return new WP_Error(
				'hfe_invalid_value',
				/* translators: 1: setting key, 2: allowed values */
				sprintf(
					__( 'Invalid value for "%1$s". Allowed: %2$s', 'header-footer-elementor' ),
					$key,
					implode( ', ', $this->valid_values[ $key ] )
				),
				[ 'status' => 400 ]
			);
		}

		update_option( $key, $new_value );

		return [
			'success'     => true,
			'setting_key' => $key,
			'old_value'   => is_string( $old_value ) ? $old_value : '',
			'new_value'   => $new_value,
		];
	}
}
