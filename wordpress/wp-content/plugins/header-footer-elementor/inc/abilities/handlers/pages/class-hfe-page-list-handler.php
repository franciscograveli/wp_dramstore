<?php
/**
 * Page List Handler.
 *
 * Lists Elementor-edited pages and posts with filtering options.
 *
 * @package header-footer-elementor
 * @since 2.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class HFE_Page_List_Handler
 *
 * Implements HFE_Ability_Handler for the pages/list ability.
 *
 * @since 2.9.0
 */
class HFE_Page_List_Handler implements HFE_Ability_Handler {

	use HFE_Abilities_Helpers;

	/**
	 * Get the ability name.
	 *
	 * @since 2.9.0
	 *
	 * @return string Ability name without plugin prefix.
	 */
	public function get_name() {
		return 'pages-list';
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
			'label'               => __( 'List Pages', 'header-footer-elementor' ),
			'description'         => __( 'Lists Elementor-edited pages and posts with optional filtering by type, status, and search.', 'header-footer-elementor' ),
			'category'            => 'hfe-pages',
			'permission_callback' => function () {
				return current_user_can( 'edit_posts' );
			},
			'input_schema'        => [
				'type'       => 'object',
				'properties' => [
					'post_type' => [
						'type'        => 'string',
						'enum'        => [ 'page', 'post', 'any' ],
						'default'     => 'page',
						'description' => __( 'Filter by post type. Use "any" for all supported types.', 'header-footer-elementor' ),
					],
					'status'    => [
						'type'        => 'string',
						'enum'        => [ 'publish', 'draft', 'any' ],
						'default'     => 'publish',
						'description' => __( 'Filter by post status.', 'header-footer-elementor' ),
					],
					'per_page'  => [
						'type'        => 'integer',
						'default'     => 20,
						'description' => __( 'Number of results per page. Maximum 100.', 'header-footer-elementor' ),
					],
					'search'    => [
						'type'        => 'string',
						'description' => __( 'Search by title.', 'header-footer-elementor' ),
					],
				],
			],
			'output_schema'       => [
				'type'       => 'object',
				'properties' => [
					'pages' => [
						'type'  => 'array',
						'items' => [
							'type'       => 'object',
							'properties' => [
								'id'                 => [ 'type' => 'integer' ],
								'title'              => [ 'type' => 'string' ],
								'post_type'          => [ 'type' => 'string' ],
								'status'             => [ 'type' => 'string' ],
								'edit_url'           => [ 'type' => 'string' ],
								'elementor_edit_url' => [ 'type' => 'string' ],
								'view_url'           => [ 'type' => 'string' ],
								'modified_date'      => [ 'type' => 'string' ],
							],
						],
					],
					'total' => [ 'type' => 'integer' ],
				],
			],
			'meta'                => [
				'annotations'  => [
					'readonly'     => true,
					'destructive'  => false,
					'idempotent'   => true,
					'instructions' => 'Lists pages and posts edited with Elementor. Use to show the user their content or find a specific page before updating.',
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
	 * @return array List of pages and total count.
	 */
	public function execute( $input ) {
		$post_type = ! empty( $input['post_type'] ) ? sanitize_text_field( $input['post_type'] ) : 'page';
		$status    = ! empty( $input['status'] ) ? sanitize_text_field( $input['status'] ) : 'publish';
		$per_page  = ! empty( $input['per_page'] ) ? absint( $input['per_page'] ) : 20;
		$per_page  = min( $per_page, 100 );

		// Build post type argument.
		if ( 'any' === $post_type ) {
			$supported_cpts = get_option( 'elementor_cpt_support', [ 'page', 'post' ] );
			$query_types    = is_array( $supported_cpts ) ? $supported_cpts : [ 'page', 'post' ];
		} else {
			$query_types = [ $post_type ];
		}

		// Exclude elementor-hf CPT — those are templates, not pages.
		$query_types = array_diff( $query_types, [ 'elementor-hf' ] );

		$args = [
			'post_type'      => $query_types,
			'post_status'    => 'any' === $status ? [ 'publish', 'draft' ] : $status,
			'posts_per_page' => $per_page,
			'meta_query'     => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				[
					'key'   => '_elementor_edit_mode',
					'value' => 'builder',
				],
			],
		];

		if ( ! empty( $input['search'] ) ) {
			$args['s'] = sanitize_text_field( $input['search'] );
		}

		$query = new \WP_Query( $args );
		$pages = [];

		foreach ( $query->posts as $post ) {
			$pages[] = [
				'id'                 => $post->ID,
				'title'              => get_the_title( $post->ID ),
				'post_type'          => $post->post_type,
				'status'             => $post->post_status,
				'edit_url'           => admin_url( 'post.php?post=' . $post->ID . '&action=edit' ),
				'elementor_edit_url' => admin_url( 'post.php?post=' . $post->ID . '&action=elementor' ),
				'view_url'           => get_permalink( $post->ID ),
				'modified_date'      => $post->post_modified,
			];
		}

		return [
			'pages' => $pages,
			'total' => (int) $query->found_posts,
		];
	}
}
