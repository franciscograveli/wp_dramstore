<?php
/**
 * Page Restore Handler.
 *
 * Restores a trashed page or post back to its previous status.
 *
 * @package header-footer-elementor
 * @since 2.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class HFE_Page_Restore_Handler
 *
 * Implements HFE_Ability_Handler for the pages/restore ability.
 *
 * @since 2.9.0
 */
class HFE_Page_Restore_Handler implements HFE_Ability_Handler {

	use HFE_Abilities_Helpers;

	/**
	 * Get the ability name.
	 *
	 * @since 2.9.0
	 *
	 * @return string Ability name without plugin prefix.
	 */
	public function get_name() {
		return 'pages-restore';
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
			'label'               => __( 'Restore Page', 'header-footer-elementor' ),
			'description'         => __( 'Restores a trashed page or post back to its previous status.', 'header-footer-elementor' ),
			'category'            => 'hfe-pages',
			'permission_callback' => function () {
				return current_user_can( 'manage_options' );
			},
			'input_schema'        => [
				'type'       => 'object',
				'required'   => [ 'post_id' ],
				'properties' => [
					'post_id' => [
						'type'        => 'integer',
						'description' => __( 'The trashed page or post ID to restore.', 'header-footer-elementor' ),
					],
				],
			],
			'output_schema'       => [
				'type'       => 'object',
				'properties' => [
					'success'    => [ 'type' => 'boolean' ],
					'post_id'    => [ 'type' => 'integer' ],
					'new_status' => [ 'type' => 'string' ],
					'message'    => [ 'type' => 'string' ],
				],
			],
			'meta'                => [
				'annotations'  => [
					'readonly'     => false,
					'destructive'  => false,
					'idempotent'   => true,
					'instructions' => 'Restores a trashed page/post to its previous status. Only works on posts currently in the trash.',
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
		$post_id = absint( $input['post_id'] );
		$post    = get_post( $post_id );

		if ( ! $post ) {
			return new \WP_Error(
				'hfe_post_not_found',
				__( 'Post not found.', 'header-footer-elementor' ),
				[ 'status' => 404 ]
			);
		}

		if ( 'elementor-hf' === $post->post_type ) {
			return new \WP_Error(
				'hfe_use_template_api',
				__( 'Use templates/delete for HFE templates.', 'header-footer-elementor' ),
				[ 'status' => 400 ]
			);
		}

		if ( 'trash' !== $post->post_status ) {
			return new \WP_Error(
				'hfe_not_trashed',
				__( 'Post is not in the trash.', 'header-footer-elementor' ),
				[ 'status' => 400 ]
			);
		}

		// Restoring is an edit of this specific post, so require its edit cap.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return new \WP_Error(
				'hfe_forbidden',
				__( 'You are not allowed to restore this post.', 'header-footer-elementor' ),
				[ 'status' => 403 ]
			);
		}

		$result = wp_untrash_post( $post_id );

		if ( ! $result ) {
			return new \WP_Error(
				'hfe_restore_failed',
				__( 'Failed to restore post from trash.', 'header-footer-elementor' ),
				[ 'status' => 500 ]
			);
		}

		$restored_post = get_post( $post_id );

		return [
			'success'    => true,
			'post_id'    => $post_id,
			'new_status' => $restored_post->post_status,
			'message'    => sprintf(
				/* translators: %s: post title */
				__( '"%s" restored from trash.', 'header-footer-elementor' ),
				get_the_title( $post_id )
			),
		];
	}
}
