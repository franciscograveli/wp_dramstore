<?php
/**
 * Template Restore Handler.
 *
 * Restores a trashed HFE template back to its previous status.
 *
 * @package header-footer-elementor
 * @since 2.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class HFE_Template_Restore_Handler
 *
 * Implements HFE_Ability_Handler for the templates/restore ability.
 *
 * @since 2.9.0
 */
class HFE_Template_Restore_Handler implements HFE_Ability_Handler {

	use HFE_Abilities_Helpers;

	/**
	 * Get the ability name.
	 *
	 * @since 2.9.0
	 *
	 * @return string Ability name without plugin prefix.
	 */
	public function get_name() {
		return 'templates-restore';
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
			'label'               => __( 'Restore Template from Trash', 'header-footer-elementor' ),
			'description'         => __( 'Restores a trashed HFE template back to its previous status (typically draft).', 'header-footer-elementor' ),
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
						'description' => __( 'The trashed template post ID to restore.', 'header-footer-elementor' ),
					],
				],
			],
			'output_schema'       => [
				'type'       => 'object',
				'properties' => [
					'success'     => [ 'type' => 'boolean' ],
					'template_id' => [ 'type' => 'integer' ],
					'new_status'  => [ 'type' => 'string' ],
					'message'     => [ 'type' => 'string' ],
				],
			],
			'meta'                => [
				'annotations'  => [
					'readonly'     => false,
					'destructive'  => false,
					'idempotent'   => true,
					'instructions' => 'Confirm with the user which trashed template to restore. The template will return to draft status.',
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
	 * @return array|WP_Error Result or error.
	 */
	public function execute( $input ) {
		$template_id = absint( $input['template_id'] );
		$post        = get_post( $template_id );

		if ( ! $post || 'elementor-hf' !== $post->post_type ) {
			return new WP_Error(
				'hfe_invalid_template',
				__( 'Template not found or is not an HFE template.', 'header-footer-elementor' ),
				[ 'status' => 404 ]
			);
		}

		if ( 'trash' !== $post->post_status ) {
			return new WP_Error(
				'hfe_not_trashed',
				__( 'Template is not in trash.', 'header-footer-elementor' ),
				[ 'status' => 400 ]
			);
		}

		$result = wp_untrash_post( $template_id );

		if ( ! $result ) {
			return new WP_Error(
				'hfe_restore_failed',
				__( 'Failed to restore template from trash.', 'header-footer-elementor' ),
				[ 'status' => 500 ]
			);
		}

		// Refresh post to get restored status.
		$restored_post = get_post( $template_id );

		return [
			'success'     => true,
			'template_id' => $template_id,
			'new_status'  => $restored_post->post_status,
			'message'     => __( 'Template restored from trash.', 'header-footer-elementor' ),
		];
	}
}
