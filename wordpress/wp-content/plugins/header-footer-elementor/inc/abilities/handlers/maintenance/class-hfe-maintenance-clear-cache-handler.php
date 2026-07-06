<?php
/**
 * Maintenance Clear Cache Handler.
 *
 * Clears the Elementor CSS cache globally.
 *
 * @package header-footer-elementor
 * @since 2.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class HFE_Maintenance_Clear_Cache_Handler
 *
 * Implements HFE_Ability_Handler for the maintenance/clear-cache ability.
 *
 * @since 2.9.0
 */
class HFE_Maintenance_Clear_Cache_Handler implements HFE_Ability_Handler {

	/**
	 * Get the ability name.
	 *
	 * @since 2.9.0
	 *
	 * @return string Ability name without plugin prefix.
	 */
	public function get_name() {
		return 'maintenance-clear-cache';
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
			'label'               => __( 'Clear Elementor Cache', 'header-footer-elementor' ),
			'description'         => __( 'Clears the Elementor CSS cache globally to regenerate all stylesheets.', 'header-footer-elementor' ),
			'category'            => 'hfe-maintenance',
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
					'success' => [ 'type' => 'boolean' ],
					'message' => [ 'type' => 'string' ],
				],
			],
			'meta'                => [
				'annotations'  => [
					'readonly'     => false,
					'destructive'  => false,
					'idempotent'   => true,
					'instructions' => 'Use this after making design or template changes to regenerate Elementor CSS files. Safe to call multiple times.',
				],
				'show_in_rest' => true,
				'mcp'          => [ 'public' => true ],
			],
		];
	}

	/**
	 * Execute the ability.
	 *
	 * Clears the Elementor CSS cache via the files manager.
	 *
	 * @since 2.9.0
	 *
	 * @param array $input Unused input parameters.
	 * @return array|WP_Error Result or error.
	 */
	public function execute( $input ) {
		if ( ! class_exists( '\Elementor\Plugin' ) ) {
			return new WP_Error(
				'hfe_elementor_not_active',
				__( 'Elementor is not active.', 'header-footer-elementor' ),
				[ 'status' => 500 ]
			);
		}

		\Elementor\Plugin::$instance->files_manager->clear_cache();

		return [
			'success' => true,
			'message' => __( 'Elementor CSS cache cleared successfully.', 'header-footer-elementor' ),
		];
	}
}
