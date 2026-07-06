<?php
/**
 * Page Update Status Handler.
 *
 * Updates the publish/draft status of an Elementor page or post.
 *
 * @package header-footer-elementor
 * @since 2.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class HFE_Page_Update_Status_Handler
 *
 * Implements HFE_Ability_Handler for the pages/update-status ability.
 *
 * @since 2.9.0
 */
class HFE_Page_Update_Status_Handler implements HFE_Ability_Handler {

	use HFE_Abilities_Helpers;

	/**
	 * Get the ability name.
	 *
	 * @since 2.9.0
	 *
	 * @return string Ability name without plugin prefix.
	 */
	public function get_name() {
		return 'pages-update-status';
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
			'label'               => __( 'Update Page Status', 'header-footer-elementor' ),
			'description'         => __( 'Publishes or unpublishes (drafts) an Elementor page or post.', 'header-footer-elementor' ),
			'category'            => 'hfe-pages',
			'permission_callback' => function () {
				return current_user_can( 'manage_options' );
			},
			'input_schema'        => [
				'type'       => 'object',
				'required'   => [ 'post_id', 'status' ],
				'properties' => [
					'post_id' => [
						'type'        => 'integer',
						'description' => __( 'The page or post ID.', 'header-footer-elementor' ),
					],
					'status'  => [
						'type'        => 'string',
						'enum'        => [ 'publish', 'draft' ],
						'description' => __( 'The new status: publish or draft.', 'header-footer-elementor' ),
					],
				],
			],
			'output_schema'       => [
				'type'       => 'object',
				'properties' => [
					'success'    => [ 'type' => 'boolean' ],
					'post_id'    => [ 'type' => 'integer' ],
					'old_status' => [ 'type' => 'string' ],
					'new_status' => [ 'type' => 'string' ],
				],
			],
			'meta'                => [
				'annotations'  => [
					'readonly'     => false,
					'destructive'  => false,
					'idempotent'   => true,
					'instructions' => 'Changes a page/post between publish and draft status. Cannot be used on trashed posts — restore them first.',
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
	 * @return array|WP_Error Result data or error.
	 */
	public function execute( $input ) {
		$post_id    = absint( $input['post_id'] );
		$new_status = sanitize_text_field( $input['status'] );
		$validation = $this->validate_elementor_post( $post_id );

		if ( is_wp_error( $validation ) ) {
			return $validation;
		}

		$post       = $validation;
		$old_status = $post->post_status;

		if ( 'trash' === $old_status ) {
			return new \WP_Error(
				'hfe_post_trashed',
				__( 'Post is in the trash. Use pages/restore first.', 'header-footer-elementor' ),
				[ 'status' => 400 ]
			);
		}

		$result = wp_update_post(
			[
				'ID'          => $post_id,
				'post_status' => $new_status,
			],
			true
		);

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return [
			'success'    => true,
			'post_id'    => $post_id,
			'old_status' => $old_status,
			'new_status' => $new_status,
		];
	}
}
