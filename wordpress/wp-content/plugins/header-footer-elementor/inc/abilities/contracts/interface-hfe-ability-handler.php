<?php
/**
 * Ability Handler Interface.
 *
 * Contract for all ability handler classes. Each handler encapsulates
 * one logical ability: its WP Abilities API registration and execution.
 *
 * @package header-footer-elementor
 * @since 2.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Interface HFE_Ability_Handler
 *
 * @since 2.9.0
 */
interface HFE_Ability_Handler {

	/**
	 * Get the ability name (e.g., 'builder/insert-widget').
	 *
	 * The registry prepends the plugin prefix ('uae/')
	 * when registering with wp_register_ability().
	 *
	 * @return string Ability name without plugin prefix.
	 */
	public function get_name();

	/**
	 * Get the full wp_register_ability() args array.
	 *
	 * Must include: label, description, category, permission_callback,
	 * input_schema, output_schema, meta.
	 *
	 * The execute_callback is set automatically by the registry.
	 *
	 * @return array Ability registration args.
	 */
	public function get_registration_args();

	/**
	 * Execute the ability.
	 *
	 * @param array $input Validated input parameters.
	 * @return array|WP_Error Result data or error.
	 */
	public function execute( $input );
}
