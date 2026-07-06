<?php
/**
 * Display Rules Update Handler.
 *
 * Merged handler for updating display rules and user role targeting.
 * Replaces the separate display-rules/update and display-rules/set-user-roles abilities.
 *
 * @package header-footer-elementor
 * @since 2.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class HFE_Display_Rules_Update_Handler
 *
 * Implements HFE_Ability_Handler for the display-rules/update ability.
 *
 * @since 2.9.0
 */
class HFE_Display_Rules_Update_Handler implements HFE_Ability_Handler {

	use HFE_Abilities_Helpers;

	/**
	 * Get the ability name.
	 *
	 * @since 2.9.0
	 *
	 * @return string Ability name without plugin prefix.
	 */
	public function get_name() {
		return 'display-rules-update';
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
		$location_schema = [
			'type'       => 'object',
			'properties' => [
				'rule'     => [
					'type'  => 'array',
					'items' => [ 'type' => 'string' ],
				],
				'specific' => [
					'type'  => 'array',
					'items' => [ 'type' => 'string' ],
				],
			],
		];

		return [
			'label'               => __( 'Update Display Rules', 'header-footer-elementor' ),
			'description'         => __( 'Set include/exclude locations and user role targeting for a template. Merges the old update and set-user-roles abilities.', 'header-footer-elementor' ),
			'category'            => 'hfe-display-rules',
			'permission_callback' => function () {
				return current_user_can( 'manage_options' );
			},
			'input_schema'        => [
				'type'       => 'object',
				'required'   => [ 'template_id' ],
				'properties' => [
					'template_id'       => [
						'type'        => 'integer',
						'description' => __( 'Template post ID.', 'header-footer-elementor' ),
					],
					'include_locations' => array_merge(
						$location_schema,
						[ 'description' => __( 'Include rules: { "rule": ["basic-global", ...], "specific": ["post-123", ...] }', 'header-footer-elementor' ) ]
					),
					'exclude_locations' => array_merge(
						$location_schema,
						[ 'description' => __( 'Exclude rules. Same structure as include_locations.', 'header-footer-elementor' ) ]
					),
					'user_roles'        => [
						'type'        => 'array',
						'items'       => [ 'type' => 'string' ],
						'description' => __( 'Role slugs: logged-in, logged-out, administrator, editor, author, contributor, subscriber.', 'header-footer-elementor' ),
					],
				],
			],
			'output_schema'       => [
				'type'       => 'object',
				'properties' => [
					'success'     => [ 'type' => 'boolean' ],
					'template_id' => [ 'type' => 'integer' ],
				],
			],
			'meta'                => [
				'annotations'  => [
					'readonly'     => false,
					'destructive'  => false,
					'idempotent'   => true,
					'instructions' => 'Confirm target locations and user roles with user. This replaces current rules. Pass user_roles to set role-based targeting.',
				],
				'show_in_rest' => true,
				'mcp'          => [ 'public' => true ],
			],
		];
	}

	/**
	 * Execute the ability.
	 *
	 * Validates the template, then conditionally updates include locations,
	 * exclude locations, and user roles based on which inputs are provided.
	 *
	 * @since 2.9.0
	 *
	 * @param array $input Validated input parameters.
	 * @return array|WP_Error Result data or error.
	 */
	public function execute( $input ) {
		$template_id = absint( $input['template_id'] );
		$validation  = $this->validate_template( $template_id );

		if ( is_wp_error( $validation ) ) {
			return $validation;
		}

		if ( isset( $input['include_locations'] ) ) {
			$include = $this->sanitize_locations( $input['include_locations'] );
			update_post_meta( $template_id, 'ehf_target_include_locations', $include );
		}

		if ( isset( $input['exclude_locations'] ) ) {
			$exclude = $this->sanitize_locations( $input['exclude_locations'] );
			update_post_meta( $template_id, 'ehf_target_exclude_locations', $exclude );
		}

		if ( isset( $input['user_roles'] ) ) {
			$roles       = array_map( 'sanitize_text_field', $input['user_roles'] );
			$valid_roles = array_merge(
				[ 'logged-in', 'logged-out' ],
				array_keys( wp_roles()->get_names() )
			);
			$invalid = array_diff( $roles, $valid_roles );

			if ( ! empty( $invalid ) ) {
				return new \WP_Error(
					'hfe_invalid_roles',
					/* translators: %s: comma-separated invalid role slugs */
					sprintf( __( 'Invalid role(s): %s', 'header-footer-elementor' ), implode( ', ', $invalid ) ),
					[ 'status' => 400 ]
				);
			}

			update_post_meta( $template_id, 'ehf_target_user_roles', $roles );
		}

		return [
			'success'     => true,
			'template_id' => $template_id,
		];
	}

	/**
	 * Sanitize a location rules array.
	 *
	 * Location rules are stored as: { rule: ['basic-global', ...], specific: ['post-123', ...] }
	 *
	 * @since 2.9.0
	 *
	 * @param mixed $locations Raw location rules.
	 * @return array Sanitized location rules with rule and specific keys.
	 */
	private function sanitize_locations( $locations ) {
		if ( ! is_array( $locations ) ) {
			return [
				'rule'     => [],
				'specific' => [],
			];
		}

		$sanitized = [
			'rule'     => [],
			'specific' => [],
		];

		if ( ! empty( $locations['rule'] ) && is_array( $locations['rule'] ) ) {
			$sanitized['rule'] = array_values( array_map( 'sanitize_text_field', $locations['rule'] ) );
		}

		if ( ! empty( $locations['specific'] ) && is_array( $locations['specific'] ) ) {
			$sanitized['specific'] = array_values( array_map( 'sanitize_text_field', $locations['specific'] ) );
		}

		// HFE's native format requires the literal 'specifics' token in `rule`
		// whenever specific targets are present. The admin metabox renderer
		// ( Astra_Target_Rules_Fields::generate_target_rule_selector() ) and the
		// frontend condition parser both iterate `rule` first and only read
		// `specific` for entries equal to 'specifics'. Without the token, the
		// saved targets are orphaned -- unreadable by the editor and ignored on
		// the frontend. Auto-add it so callers that pass only `specific` still
		// produce a valid, readable rule set.
		if ( ! empty( $sanitized['specific'] ) && ! in_array( 'specifics', $sanitized['rule'], true ) ) {
			$sanitized['rule'][] = 'specifics';
		}

		return $sanitized;
	}
}
