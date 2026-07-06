<?php
/**
 * Template Duplicate Handler.
 *
 * Creates a copy of an existing template with all settings and Elementor content.
 *
 * @package header-footer-elementor
 * @since 2.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class HFE_Template_Duplicate_Handler
 *
 * Implements HFE_Ability_Handler for the templates/duplicate ability.
 *
 * @since 2.9.0
 */
class HFE_Template_Duplicate_Handler implements HFE_Ability_Handler {

	use HFE_Abilities_Helpers;

	/**
	 * Get the ability name.
	 *
	 * @since 2.9.0
	 *
	 * @return string Ability name without plugin prefix.
	 */
	public function get_name() {
		return 'templates-duplicate';
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
			'label'               => __( 'Duplicate Template', 'header-footer-elementor' ),
			'description'         => __( 'Creates a copy of an existing template with all settings and Elementor content.', 'header-footer-elementor' ),
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
						'description' => __( 'Source template post ID.', 'header-footer-elementor' ),
					],
					'title'       => [
						'type'        => 'string',
						'description' => __( 'Title for duplicate. Default: "Copy of {original}".', 'header-footer-elementor' ),
					],
				],
			],
			'output_schema'       => [
				'type'       => 'object',
				'properties' => [
					'id'                 => [ 'type' => 'integer' ],
					'title'              => [ 'type' => 'string' ],
					'type'               => [ 'type' => 'string' ],
					'edit_url'           => [ 'type' => 'string' ],
					'elementor_edit_url' => [ 'type' => 'string' ],
					'shortcode'          => [ 'type' => 'string' ],
				],
			],
			'meta'                => [
				'annotations'  => [
					'readonly'     => false,
					'destructive'  => false,
					'idempotent'   => false,
					'instructions' => 'Confirm with the user which template to duplicate and what title to use for the copy.',
				],
				'show_in_rest' => true,
				'mcp'          => [ 'public' => true ],
			],
		];
	}

	/**
	 * Execute the ability.
	 *
	 * Uses raw meta copy to preserve Elementor data integrity.
	 *
	 * @since 2.9.0
	 *
	 * @param array $input Validated input parameters.
	 * @return array|WP_Error New template data or error.
	 */
	public function execute( $input ) {
		$template_id = absint( $input['template_id'] );
		$source_post = $this->validate_template( $template_id );

		if ( is_wp_error( $source_post ) ) {
			return $source_post;
		}

		/* translators: %s: Original template title. */
		$title = ! empty( $input['title'] )
			? sanitize_text_field( $input['title'] )
			: sprintf( __( 'Copy of %s', 'header-footer-elementor' ), $source_post->post_title );

		$new_post_id = wp_insert_post(
			[
				'post_type'   => 'elementor-hf',
				'post_title'  => $title,
				'post_status' => 'draft',
				'post_author' => get_current_user_id(),
			]
		);

		if ( is_wp_error( $new_post_id ) ) {
			return new WP_Error(
				'hfe_create_failed',
				__( 'Failed to create duplicate template.', 'header-footer-elementor' ),
				[ 'status' => 500 ]
			);
		}

		// Raw meta copy for Elementor data integrity.
		$this->copy_post_meta_raw( $template_id, $new_post_id );

		// Clear Elementor generated CSS for the duplicate.
		delete_post_meta( $new_post_id, '_elementor_css' );

		if ( class_exists( '\Elementor\Plugin' ) ) {
			\Elementor\Plugin::$instance->files_manager->clear_cache();
		}

		$type = get_post_meta( $new_post_id, 'ehf_template_type', true );

		return [
			'id'                 => $new_post_id,
			'title'              => $title,
			'type'               => $type,
			'edit_url'           => admin_url( 'post.php?post=' . $new_post_id . '&action=edit' ),
			'elementor_edit_url' => admin_url( 'post.php?post=' . $new_post_id . '&action=elementor' ),
			'shortcode'          => '[hfe_template id="' . $new_post_id . '"]',
		];
	}

	/**
	 * Raw copy of all post meta from one post to another.
	 *
	 * Uses direct database insert to preserve Elementor serialized data.
	 * Mirrors the approach in HFE_Post_Duplicator::copy_post_meta_raw().
	 *
	 * @since 2.9.0
	 *
	 * @param int $source_id Source post ID.
	 * @param int $target_id Target post ID.
	 * @return void
	 */
	private function copy_post_meta_raw( $source_id, $target_id ) {
		global $wpdb;

		$meta_rows = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"SELECT meta_key, meta_value FROM {$wpdb->postmeta} WHERE post_id = %d",
				$source_id
			)
		);

		if ( empty( $meta_rows ) ) {
			return;
		}

		$skip_keys = [ '_edit_lock', '_edit_last', '_wp_old_slug' ];

		foreach ( $meta_rows as $meta ) {
			if ( in_array( $meta->meta_key, $skip_keys, true ) ) {
				continue;
			}

			$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
				$wpdb->postmeta,
				[
					'post_id'    => $target_id,
					'meta_key'   => $meta->meta_key, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
					'meta_value' => $meta->meta_value, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
				],
				[ '%d', '%s', '%s' ]
			);
		}
	}
}
