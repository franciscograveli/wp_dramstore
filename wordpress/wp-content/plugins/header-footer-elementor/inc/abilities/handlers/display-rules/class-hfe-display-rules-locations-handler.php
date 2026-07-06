<?php
/**
 * Display Rules Locations Handler.
 *
 * Returns all available targeting locations for display rules,
 * grouped by category (Basic, Post, Page, etc.).
 *
 * @package header-footer-elementor
 * @since 2.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class HFE_Display_Rules_Locations_Handler
 *
 * Implements HFE_Ability_Handler for the display-rules/get-locations ability.
 *
 * @since 2.9.0
 */
class HFE_Display_Rules_Locations_Handler implements HFE_Ability_Handler {

	use HFE_Abilities_Helpers;

	/**
	 * Get the ability name.
	 *
	 * @since 2.9.0
	 *
	 * @return string Ability name without plugin prefix.
	 */
	public function get_name() {
		return 'display-rules-get-locations';
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
			'label'               => __( 'Get Available Locations', 'header-footer-elementor' ),
			'description'         => __( 'Lists all available targeting locations for display rules.', 'header-footer-elementor' ),
			'category'            => 'hfe-display-rules',
			'permission_callback' => function () {
				return current_user_can( 'edit_posts' );
			},
			'input_schema'        => [
				'type'       => 'object',
				'properties' => (object) [],
			],
			'output_schema'       => [
				'type'       => 'object',
				'properties' => [
					'groups' => [
						'type'  => 'array',
						'items' => [
							'type'       => 'object',
							'properties' => [
								'group'     => [ 'type' => 'string' ],
								'locations' => [
									'type'  => 'array',
									'items' => [
										'type'       => 'object',
										'properties' => [
											'type'  => [ 'type' => 'string' ],
											'label' => [ 'type' => 'string' ],
										],
									],
								],
							],
						],
					],
				],
				'required'   => [ 'groups' ],
			],
			'meta'                => [
				'annotations'  => [
					'readonly'     => true,
					'destructive'  => false,
					'idempotent'   => true,
					'instructions' => 'Use to show available targeting options when setting display rules.',
				],
				'show_in_rest' => true,
				'mcp'          => [ 'public' => true ],
			],
		];
	}

	/**
	 * Execute the ability.
	 *
	 * Transforms the grouped location selections from Astra_Target_Rules_Fields
	 * into a structured array of groups with type/label pairs.
	 *
	 * @since 2.9.0
	 *
	 * @param array $input Validated input parameters (unused).
	 * @return array Array of location groups.
	 */
	public function execute( $input ) {
		$selections = \HFE\Lib\Astra_Target_Rules_Fields::get_location_selections();
		$result     = [];

		foreach ( $selections as $group_key => $group ) {
			$locations = [];

			if ( ! empty( $group['value'] ) && is_array( $group['value'] ) ) {
				foreach ( $group['value'] as $type => $label ) {
					$locations[] = [
						'type'  => $type,
						'label' => $label,
					];
				}
			}

			$result[] = [
				'group'     => isset( $group['label'] ) ? $group['label'] : $group_key,
				'locations' => $locations,
			];
		}

		return [
			'groups' => $result,
		];
	}
}
