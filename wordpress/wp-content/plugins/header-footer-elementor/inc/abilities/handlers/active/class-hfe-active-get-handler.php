<?php
/**
 * Active Get Handler.
 *
 * Unified handler that returns all active templates (header, footer,
 * before-footer) in a single call. Replaces the individual
 * active/get-header, active/get-footer, active/get-before-footer,
 * and active/get-all abilities.
 *
 * @package header-footer-elementor
 * @since 2.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class HFE_Active_Get_Handler
 *
 * Implements HFE_Ability_Handler for the unified active/get ability.
 *
 * @since 2.9.0
 */
class HFE_Active_Get_Handler implements HFE_Ability_Handler {

	/**
	 * Get the ability name.
	 *
	 * @since 2.9.0
	 *
	 * @return string Ability name without plugin prefix.
	 */
	public function get_name() {
		return 'active-get';
	}

	/**
	 * Get the wp_register_ability() args array.
	 *
	 * Does NOT include execute_callback — the registry sets that automatically.
	 *
	 * @since 2.9.0
	 *
	 * @return array Ability registration args.
	 */
	public function get_registration_args() {
		$template_schema = [
			'type'       => [ 'object', 'null' ],
			'properties' => [
				'template_id' => [ 'type' => 'integer' ],
				'title'       => [ 'type' => 'string' ],
			],
		];

		return [
			'label'               => __( 'Get All Active Templates', 'header-footer-elementor' ),
			'description'         => __( 'Returns all active templates (header, footer, before-footer) in one call.', 'header-footer-elementor' ),
			'category'            => 'hfe-active-templates',
			'permission_callback' => function () {
				return current_user_can( 'read' );
			},
			'input_schema'        => [
				'type'       => 'object',
				'properties' => (object) [],
			],
			'output_schema'       => [
				'type'       => 'object',
				'properties' => [
					'header'        => $template_schema,
					'footer'        => $template_schema,
					'before_footer' => $template_schema,
				],
			],
			'meta'                => [
				'annotations'  => [
					'readonly'     => true,
					'destructive'  => false,
					'idempotent'   => true,
					'instructions' => 'Returns all active templates (header, footer, before-footer) in one call.',
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
	 * @param array $input Validated input parameters (unused).
	 * @return array All active templates keyed by slot.
	 */
	public function execute( $input ) {
		return [
			'header'        => $this->resolve_template( 'type_header' ),
			'footer'        => $this->resolve_template( 'type_footer' ),
			'before_footer' => $this->resolve_template( 'type_before_footer' ),
		];
	}

	/**
	 * Resolve a template by its HFE settings type key.
	 *
	 * @since 2.9.0
	 *
	 * @param string $type Template type: type_header, type_footer, or type_before_footer.
	 * @return array|null Template data with template_id and title, or null if none active.
	 */
	private function resolve_template( $type ) {
		$template_id = \Header_Footer_Elementor::get_settings( $type, '' );

		if ( empty( $template_id ) ) {
			return null;
		}

		return [
			'template_id' => (int) $template_id,
			'title'       => get_the_title( $template_id ),
		];
	}
}
