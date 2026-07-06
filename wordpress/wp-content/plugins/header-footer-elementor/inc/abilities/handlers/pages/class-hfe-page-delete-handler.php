<?php
/**
 * Page Delete Handler.
 *
 * Moves an Elementor page or post to the trash.
 *
 * @package header-footer-elementor
 * @since 2.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class HFE_Page_Delete_Handler
 *
 * Implements HFE_Ability_Handler for the pages/delete ability.
 *
 * @since 2.9.0
 */
class HFE_Page_Delete_Handler implements HFE_Ability_Handler {

	use HFE_Abilities_Helpers;

	/**
	 * Get the ability name.
	 *
	 * @since 2.9.0
	 *
	 * @return string Ability name without plugin prefix.
	 */
	public function get_name() {
		return 'pages-delete';
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
			'label'               => __( 'Delete Page', 'header-footer-elementor' ),
			'description'         => __( 'Moves an Elementor page or post to the trash. Does not permanently delete.', 'header-footer-elementor' ),
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
						'description' => __( 'The page or post ID to trash.', 'header-footer-elementor' ),
					],
				],
			],
			'output_schema'       => [
				'type'       => 'object',
				'properties' => [
					'success' => [ 'type' => 'boolean' ],
					'post_id' => [ 'type' => 'integer' ],
					'message' => [ 'type' => 'string' ],
				],
			],
			'meta'                => [
				'annotations'  => [
					'readonly'     => false,
					'destructive'  => true,
					'idempotent'   => true,
					'instructions' => 'Moves page/post to trash. Ask user to confirm. Does not permanently delete.',
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

		$validation = $this->validate_elementor_post( $post_id );

		if ( is_wp_error( $validation ) ) {
			return $validation;
		}

		// Deleting requires the delete capability for this specific post, not
		// merely edit (which validate_elementor_post checks).
		if ( ! current_user_can( 'delete_post', $post_id ) ) {
			return new \WP_Error(
				'hfe_forbidden',
				__( 'You are not allowed to delete this post.', 'header-footer-elementor' ),
				[ 'status' => 403 ]
			);
		}

		$result = wp_trash_post( $post_id );

		if ( ! $result ) {
			return new \WP_Error(
				'hfe_trash_failed',
				__( 'Failed to move post to trash.', 'header-footer-elementor' ),
				[ 'status' => 500 ]
			);
		}

		return [
			'success' => true,
			'post_id' => $post_id,
			'message' => sprintf(
				/* translators: %s: post title */
				__( '"%s" moved to trash.', 'header-footer-elementor' ),
				get_the_title( $post_id )
			),
		];
	}
}
