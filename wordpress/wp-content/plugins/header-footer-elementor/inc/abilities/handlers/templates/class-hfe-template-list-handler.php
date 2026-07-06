<?php
/**
 * Template List Handler.
 *
 * Lists all HFE templates with optional type and status filtering.
 *
 * @package header-footer-elementor
 * @since 2.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class HFE_Template_List_Handler
 *
 * Implements HFE_Ability_Handler for the templates/list ability.
 *
 * @since 2.9.0
 */
class HFE_Template_List_Handler implements HFE_Ability_Handler {

	use HFE_Abilities_Helpers;

	/**
	 * Valid template types.
	 *
	 * @var array
	 */
	const VALID_TYPES = [ 'type_header', 'type_footer', 'type_before_footer', 'custom' ];

	/**
	 * Get the ability name.
	 *
	 * @since 2.9.0
	 *
	 * @return string Ability name without plugin prefix.
	 */
	public function get_name() {
		return 'templates-list';
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
			'label'               => __( 'List Templates', 'header-footer-elementor' ),
			'description'         => __( 'Lists all HFE templates with their type, status, display conditions, and edit URLs.', 'header-footer-elementor' ),
			'category'            => 'hfe-templates',
			'permission_callback' => function () {
				return current_user_can( 'edit_posts' );
			},
			'input_schema'        => [
				'type'       => 'object',
				'properties' => [
					'type'   => [
						'type'        => 'string',
						'enum'        => self::VALID_TYPES,
						'description' => __( 'Filter by template type.', 'header-footer-elementor' ),
					],
					'status' => [
						'type'        => 'string',
						'enum'        => [ 'publish', 'draft', 'trash' ],
						'default'     => 'publish',
						'description' => __( 'Filter by post status.', 'header-footer-elementor' ),
					],
				],
			],
			'output_schema'       => [
				'type'       => 'object',
				'properties' => [
					'templates' => [
						'type'  => 'array',
						'items' => $this->get_template_item_schema(),
					],
					'count'     => [ 'type' => 'integer' ],
				],
				'required'   => [ 'templates' ],
			],
			'meta'                => [
				'annotations'  => [
					'readonly'     => true,
					'destructive'  => false,
					'idempotent'   => true,
					'instructions' => 'Use this to show the user an overview of all their templates. Can filter by type (header, footer, before-footer, custom) and status (publish, draft, trash).',
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
	 * @return array Array of template data objects.
	 */
	public function execute( $input ) {
		$allowed_statuses = [ 'publish', 'draft', 'trash' ];
		$status           = ! empty( $input['status'] ) ? sanitize_text_field( $input['status'] ) : 'publish';

		if ( ! in_array( $status, $allowed_statuses, true ) ) {
			$status = 'publish';
		}

		$args = [
			'post_type'      => 'elementor-hf',
			'post_status'    => $status,
			'posts_per_page' => -1,
			'no_found_rows'  => true,
		];

		if ( ! empty( $input['type'] ) ) {
			$args['meta_query'] = [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				[
					'key'   => 'ehf_template_type',
					'value' => sanitize_text_field( $input['type'] ),
				],
			];
		}

		$posts     = get_posts( $args );
		$templates = [];

		foreach ( $posts as $post ) {
			$templates[] = $this->build_template_data( $post->ID );
		}

		return [
			'templates' => $templates,
			'count'     => count( $templates ),
		];
	}

	/**
	 * Build a template data array from a post ID.
	 *
	 * @since 2.9.0
	 *
	 * @param int  $post_id       Post ID.
	 * @param bool $include_dates Whether to include created/modified dates.
	 * @return array Template data.
	 */
	private function build_template_data( $post_id, $include_dates = false ) {
		$post  = get_post( $post_id );
		$type  = get_post_meta( $post_id, 'ehf_template_type', true );
		$roles = get_post_meta( $post_id, 'ehf_target_user_roles', true );

		$data = [
			'id'                 => $post_id,
			'title'              => get_the_title( $post_id ),
			'type'               => $type,
			'type_label'         => $this->get_type_label( $type ),
			'status'             => $post->post_status,
			'edit_url'           => admin_url( 'post.php?post=' . $post_id . '&action=edit' ),
			'elementor_edit_url' => admin_url( 'post.php?post=' . $post_id . '&action=elementor' ),
			'shortcode'          => '[hfe_template id="' . $post_id . '"]',
			'include_locations'  => $this->normalize_locations( get_post_meta( $post_id, 'ehf_target_include_locations', true ) ),
			'exclude_locations'  => $this->normalize_locations( get_post_meta( $post_id, 'ehf_target_exclude_locations', true ) ),
			'user_roles'         => ! empty( $roles ) && is_array( $roles ) ? array_values( $roles ) : [],
		];

		if ( $include_dates ) {
			$data['created_date']  = $post->post_date;
			$data['modified_date'] = $post->post_modified;
		}

		return $data;
	}

	/**
	 * Get a human-readable label for a template type.
	 *
	 * @since 2.9.0
	 *
	 * @param string $type Template type meta value.
	 * @return string Human-readable label.
	 */
	private function get_type_label( $type ) {
		$labels = [
			'type_header'        => __( 'Header', 'header-footer-elementor' ),
			'type_footer'        => __( 'Footer', 'header-footer-elementor' ),
			'type_before_footer' => __( 'Before Footer', 'header-footer-elementor' ),
			'custom'             => __( 'Custom Block', 'header-footer-elementor' ),
		];

		return isset( $labels[ $type ] ) ? $labels[ $type ] : $type;
	}

	/**
	 * Shared output schema for template list items.
	 *
	 * @since 2.9.0
	 *
	 * @return array JSON Schema for a template object.
	 */
	private function get_template_item_schema() {
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
			'type'       => 'object',
			'properties' => [
				'id'                 => [ 'type' => 'integer' ],
				'title'              => [ 'type' => 'string' ],
				'type'               => [ 'type' => 'string' ],
				'type_label'         => [ 'type' => 'string' ],
				'status'             => [ 'type' => 'string' ],
				'edit_url'           => [ 'type' => 'string' ],
				'elementor_edit_url' => [ 'type' => 'string' ],
				'shortcode'          => [ 'type' => 'string' ],
				'include_locations'  => $location_schema,
				'exclude_locations'  => $location_schema,
				'user_roles'         => [
					'type'  => 'array',
					'items' => [ 'type' => 'string' ],
				],
			],
		];
	}
}
