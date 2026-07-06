<?php
/**
 * Builder Undo Handler.
 *
 * Reverts the most recent AI builder change on a post by restoring the snapshot
 * captured automatically before that change (see HFE_Element_Helpers::save_elementor_data()).
 *
 * @package header-footer-elementor
 * @since 2.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class HFE_Undo_Handler
 *
 * Implements HFE_Ability_Handler for the builder/undo ability.
 *
 * @since 2.9.0
 */
class HFE_Undo_Handler implements HFE_Ability_Handler {

	use HFE_Abilities_Helpers;

	/**
	 * Ability name.
	 *
	 * @since 2.9.0
	 *
	 * @return string
	 */
	public function get_name() {
		return 'builder-undo';
	}

	/**
	 * Registration args.
	 *
	 * @since 2.9.0
	 *
	 * @return array
	 */
	public function get_registration_args() {
		return [
			'label'               => __( 'Undo Last Builder Change', 'header-footer-elementor' ),
			'description'         => __( 'Reverts the most recent AI builder change on a post by restoring the snapshot taken automatically just before that change. Keeps a single level of undo; for older versions use Elementor revision history.', 'header-footer-elementor' ),
			'category'            => 'hfe-pages',
			'permission_callback' => function () {
				return current_user_can( 'edit_posts' );
			},
			'input_schema'        => [
				'type'       => 'object',
				'required'   => [ 'post_id' ],
				'properties' => [
					'post_id' => [
						'type'        => 'integer',
						'description' => __( 'The post, page, or HFE template ID to undo the last change on.', 'header-footer-elementor' ),
					],
				],
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
					'idempotent'   => false,
					'instructions' => 'Use when the user says the last builder edit was a mistake. Restores the single most recent change only. Confirm the post_id with the user first. If no snapshot exists, tell the user to use Elementor\'s revision history instead.',
				],
				'show_in_rest' => true,
				'mcp'          => [ 'public' => true ],
			],
		];
	}

	/**
	 * Execute: restore the last pre-edit snapshot.
	 *
	 * @since 2.9.0
	 *
	 * @param array $input Validated input.
	 * @return array|WP_Error Result or error.
	 */
	public function execute( $input ) {
		$post_id = absint( $input['post_id'] ?? $input['template_id'] ?? 0 );

		// Object-level authorization + Elementor post validation (shared trait).
		$post = $this->validate_elementor_post( $post_id );

		if ( is_wp_error( $post ) ) {
			return $post;
		}

		$snapshot = get_post_meta( $post->ID, HFE_Element_Helpers::UNDO_SNAPSHOT_META, true );

		if ( empty( $snapshot ) || empty( $snapshot['data'] ) ) {
			return new WP_Error(
				'hfe_no_undo_snapshot',
				__( 'No recent change is available to undo for this post. Try Elementor revision history.', 'header-footer-elementor' ),
				[ 'status' => 404 ]
			);
		}

		// Restore the previous data directly (raw write) and refresh caches.
		update_post_meta( $post->ID, '_elementor_data', wp_slash( $snapshot['data'] ) );
		HFE_Element_Helpers::clear_elementor_cache( $post->ID );

		// Single level of undo: consume the snapshot so it cannot be applied twice.
		delete_post_meta( $post->ID, HFE_Element_Helpers::UNDO_SNAPSHOT_META );

		return [
			'success' => true,
			'message' => sprintf(
				/* translators: %s: post title */
				__( 'Reverted the last change on "%s".', 'header-footer-elementor' ),
				$post->post_title
			),
		];
	}
}
