<?php
/**
 * Template Delete Handler.
 *
 * Moves an HFE template to trash.
 *
 * @package header-footer-elementor
 * @since 2.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class HFE_Template_Delete_Handler
 *
 * Implements HFE_Ability_Handler for the templates/delete ability.
 *
 * @since 2.9.0
 */
class HFE_Template_Delete_Handler implements HFE_Ability_Handler {

	use HFE_Abilities_Helpers;

	/**
	 * Get the ability name.
	 *
	 * @since 2.9.0
	 *
	 * @return string Ability name without plugin prefix.
	 */
	public function get_name() {
		return 'templates-delete';
	}

	/**
	 * Get the wp_register_ability() args array.
	 *
	 * @since 2.9.0
	 *
	 * @return array Ability registration args.
	 */
	public function get_registration_args() {
		return [
			'label'               => __( 'Delete Template', 'header-footer-elementor' ),
			'description'         => __( 'Moves an HFE template to trash. Does not permanently delete.', 'header-footer-elementor' ),
			'category'            => 'hfe-templates',
			'permission_callback' => function () {
				return current_user_can( 'manage_options' );
			},
			'input_schema'        => [
				'type'       => 'object',
				'required'   => [ 'template_id' ],
				'properties' => [
					'template_id' => [
						'type'        => 'integer',
						'description' => __( 'The template post ID to trash.', 'header-footer-elementor' ),
					],
				],
			],
			'output_schema'       => [
				'type'       => 'object',
				'properties' => [
					'success'     => [ 'type' => 'boolean' ],
					'template_id' => [ 'type' => 'integer' ],
					'message'     => [ 'type' => 'string' ],
				],
			],
			'meta'                => [
				'annotations'  => [
					'readonly'     => false,
					'destructive'  => true,
					'idempotent'   => true,
					'instructions' => 'Always ask the user to confirm before trashing a template. State the template name and ID before proceeding.',
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
	 * @param array $input Validated input parameters.
	 * @return array|WP_Error Success data or error.
	 */
	public function execute( $input ) {
		$template_id = absint( $input['template_id'] );
		$validation  = $this->validate_template( $template_id );

		if ( is_wp_error( $validation ) ) {
			return $validation;
		}

		$result = wp_trash_post( $template_id );

		if ( ! $result ) {
			return new WP_Error(
				'hfe_update_failed',
				__( 'Failed to trash the template.', 'header-footer-elementor' ),
				[ 'status' => 500 ]
			);
		}

		return [
			'success'     => true,
			'template_id' => $template_id,
			'message'     => __( 'Template moved to trash.', 'header-footer-elementor' ),
		];
	}
}
