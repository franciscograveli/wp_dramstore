<?php
/**
 * Add Section Handler.
 *
 * Adds a structural layout element (section or container) to any Elementor post.
 * Unified handler replacing template-builder/add-section.
 *
 * @package header-footer-elementor
 * @since 2.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class HFE_Add_Section_Handler
 *
 * Implements HFE_Ability_Handler for the builder/add-section ability.
 *
 * @since 2.9.0
 */
class HFE_Add_Section_Handler implements HFE_Ability_Handler {

	use HFE_Abilities_Helpers;

	/**
	 * Get the ability name.
	 *
	 * @since 2.9.0
	 *
	 * @return string Ability name without plugin prefix.
	 */
	public function get_name() {
		return 'builder-add-section';
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
			'label'               => __( 'Add Section/Container', 'header-footer-elementor' ),
			'description'         => __( 'Adds a structural layout element to any Elementor post. On legacy sites: creates a section with columns. On container sites: creates a flex container with child containers. Supports inner sections and multi-column layouts.', 'header-footer-elementor' ),
			'category'            => 'hfe-templates',
			'permission_callback' => function () {
				return current_user_can( 'edit_posts' );
			},
			'input_schema'        => [
				'type'       => 'object',
				'required'   => [ 'post_id' ],
				'properties' => [
					'post_id'  => [
						'type'        => 'integer',
						'description' => __( 'Any Elementor-enabled post ID (page, post, or HFE template).', 'header-footer-elementor' ),
					],
					'columns'  => [
						'type'        => 'array',
						'items'       => [ 'type' => 'integer' ],
						'default'     => [ 100 ],
						'description' => __( 'Array of column size percentages. Must sum to 100. E.g., [50, 50] for two equal columns, [33, 34, 33] for three columns, [25, 75] for sidebar layout.', 'header-footer-elementor' ),
					],
					'is_inner' => [
						'type'        => 'boolean',
						'default'     => false,
						'description' => __( 'If true, creates an inner section (must be placed inside a column). On container sites, creates a nested container.', 'header-footer-elementor' ),
					],
					'position' => [
						'description' => __( 'Where to insert: "append" (default), "prepend", { "after": "element_id" }, { "before": "element_id" }, { "inside": "element_id" } (for inner sections inside a column/container).', 'header-footer-elementor' ),
						'default'     => 'append',
					],
				],
			],
			'output_schema'       => [
				'type'       => 'object',
				'properties' => [
					'success'     => [ 'type' => 'boolean' ],
					'section_id'  => [ 'type' => 'string' ],
					'column_ids'  => [
						'type'  => 'array',
						'items' => [ 'type' => 'string' ],
					],
					'layout_mode' => [ 'type' => 'string' ],
					'message'     => [ 'type' => 'string' ],
				],
			],
			'meta'                => [
				'annotations'  => [
					'readonly'     => false,
					'destructive'  => false,
					'idempotent'   => false,
					'instructions' => 'For adding ONE section to an existing post. Do NOT use this with insert-widget to build layouts step by step -- use builder/build instead, which creates sections, columns, and widgets in one call. Only use add-section for incremental structural changes to an already-built post.',
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
		$allowed = $this->check_modifications_allowed();

		if ( is_wp_error( $allowed ) ) {
			return $allowed;
		}

		$loaded = $this->load_post( $input['post_id'] ?? $input['template_id'] ?? 0 );

		if ( is_wp_error( $loaded ) ) {
			return $loaded;
		}

		$columns  = isset( $input['columns'] ) && is_array( $input['columns'] ) ? array_map( 'absint', $input['columns'] ) : [ 100 ];
		$is_inner = ! empty( $input['is_inner'] );
		$position = $input['position'] ?? 'append';

		// Validate column sizes sum to 100.
		$total = array_sum( $columns );
		if ( 100 !== $total ) {
			return new \WP_Error(
				'hfe_invalid_column_sizes',
				/* translators: %d: total of column sizes */
				sprintf( __( 'Column sizes must sum to 100. Got %d.', 'header-footer-elementor' ), $total ),
				[ 'status' => 400 ]
			);
		}

		// Create the structural element.
		$section = HFE_Element_Helpers::create_layout( $columns, $is_inner );

		// Collect column/child IDs for the response.
		$column_ids = [];
		if ( ! empty( $section['elements'] ) ) {
			foreach ( $section['elements'] as $child ) {
				$column_ids[] = $child['id'];
			}
		}

		// For inner sections: if position is append/prepend, target a column.
		if ( $is_inner && is_string( $position ) && in_array( $position, [ 'append', 'prepend' ], true ) ) {
			$path = self::find_inner_target( $loaded['elements'] );
			if ( null !== $path ) {
				$position = [ 'inside' => $path ];
			}
		}

		// Insert into post.
		if ( is_string( $position ) && in_array( $position, [ 'append', 'prepend' ], true ) ) {
			// Top-level section: add directly to the element tree root.
			if ( 'prepend' === $position ) {
				array_unshift( $loaded['elements'], $section );
			} else {
				$loaded['elements'][] = $section;
			}
			$elements = $loaded['elements'];
		} else {
			// Relative or inside positioning.
			$elements = HFE_Element_Helpers::insert_element( $loaded['elements'], $section, $position );
		}

		if ( is_wp_error( $elements ) ) {
			return $elements;
		}

		$saved = HFE_Element_Helpers::save_elementor_data( $loaded['post']->ID, $elements );

		if ( is_wp_error( $saved ) ) {
			return $saved;
		}

		$type_label = $is_inner
			? __( 'inner section', 'header-footer-elementor' )
			: ( HFE_Element_Helpers::is_container_active() ? __( 'container', 'header-footer-elementor' ) : __( 'section', 'header-footer-elementor' ) );

		return [
			'success'     => true,
			'section_id'  => $section['id'],
			'column_ids'  => $column_ids,
			'layout_mode' => HFE_Element_Helpers::is_container_active() ? 'container' : 'section',
			'message'     => sprintf(
				/* translators: 1: Element type, 2: Number of columns, 3: Post title */
				__( 'Added %1$s with %2$d column(s) to "%3$s". Use column_ids to insert widgets into specific columns.', 'header-footer-elementor' ),
				$type_label,
				count( $columns ),
				$loaded['post']->post_title
			),
		];
	}

	/**
	 * Find the ID of the first column/container suitable for placing an inner section.
	 *
	 * @since 2.9.0
	 *
	 * @param array $elements Element tree.
	 * @return string|null Column/container ID or null.
	 */
	private static function find_inner_target( $elements ) {
		foreach ( $elements as $element ) {
			$type = $element['elType'] ?? '';

			if ( 'column' === $type || 'container' === $type ) {
				return $element['id'];
			}

			if ( ! empty( $element['elements'] ) ) {
				$found = self::find_inner_target( $element['elements'] );
				if ( null !== $found ) {
					return $found;
				}
			}
		}

		return null;
	}

	/**
	 * Validate and load an Elementor-enabled post.
	 *
	 * @since 2.9.0
	 *
	 * @param int $post_id Post ID.
	 * @return array|WP_Error Array with 'post' and 'elements', or error.
	 */
	private function load_post( $post_id ) {
		$post = $this->validate_elementor_post( absint( $post_id ) );

		if ( is_wp_error( $post ) ) {
			return $post;
		}

		$elements = HFE_Element_Helpers::parse_elementor_data( $post->ID );

		if ( is_wp_error( $elements ) ) {
			return $elements;
		}

		return [
			'post'     => $post,
			'elements' => $elements,
		];
	}
}
