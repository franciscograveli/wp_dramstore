<?php
/**
 * Settings Get Handler.
 *
 * Returns all plugin-level settings.
 *
 * @package header-footer-elementor
 * @since 2.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class HFE_Settings_Get_Handler
 *
 * Implements HFE_Ability_Handler for the settings/get ability.
 *
 * @since 2.9.0
 */
class HFE_Settings_Get_Handler implements HFE_Ability_Handler {

	/**
	 * Get the ability name.
	 *
	 * @since 2.9.0
	 *
	 * @return string Ability name without plugin prefix.
	 */
	public function get_name() {
		return 'settings-get';
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
			'label'               => __( 'Get Plugin Settings', 'header-footer-elementor' ),
			'description'         => __( 'Get all plugin-level settings.', 'header-footer-elementor' ),
			'category'            => 'hfe-settings',
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
					'is_theme_supported'   => [ 'type' => 'boolean' ],
					'compatibility_option' => [ 'type' => 'string' ],
					'analytics_optin'      => [ 'type' => 'string' ],
				],
			],
			'meta'                => [
				'annotations'  => [
					'readonly'     => true,
					'destructive'  => false,
					'idempotent'   => true,
					'instructions' => 'Use this to show the user current plugin settings before making any changes with settings/update.',
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
	 * @return array Plugin settings.
	 */
	public function execute( $input ) {
		return [
			'is_theme_supported'   => (bool) get_option( 'hfe_is_theme_supported', false ),
			'compatibility_option' => get_option( 'hfe_compatibility_option', '1' ),
			'analytics_optin'      => get_option( 'uae_usage_optin', '' ),
		];
	}
}
