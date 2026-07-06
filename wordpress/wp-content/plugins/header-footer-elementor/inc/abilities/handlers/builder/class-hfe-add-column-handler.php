<?php
/**
 * Add Column Handler.
 *
 * Adds a column to an existing section or container in any Elementor post.
 * Automatically redistributes column sizes to fit.
 * Unified handler replacing template-builder/add-column.
 *
 * @package header-footer-elementor
 * @since 2.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class HFE_Add_Column_Handler
 *
 * Implements HFE_Ability_Handler for the builder/add-column ability.
 *
 * @since 2.9.0
 */
class HFE_Add_Column_Handler implements HFE_Ability_Handler {

	use HFE_Abilities_Helpers;

	/**
	 * Get the ability name.
	 *
	 * @since 2.9.0
	 *
	 * @return string Ability name without plugin prefix.
	 */
	public function get_name() {
		return 'builder-add-column';
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
			'label'               => __( 'Add Column to Section', 'header-footer-elementor' ),
			'description'         => __( 'Adds a column to an existing section or container in any Elementor post. Automatically redistributes column sizes to fit.', 'header-footer-elementor' ),
			'category'            => 'hfe-templates',
			'permission_callback' => function () {
				return current_user_can( 'edit_posts' );
			},
			'input_schema'        => [
				'type'       => 'object',
				'required'   => [ 'post_id', 'section_id' ],
				'properties' => [
					'post_id'     => [
						'type'        => 'integer',
						'description' => __( 'Any Elementor-enabled post ID (page, post, or HFE template).', 'header-footer-elementor' ),
					],
					'section_id'  => [
						'type'        => 'string',
						'description' => __( 'Section or container element ID to add column to.', 'header-footer-elementor' ),
					],
					'auto_resize' => [
						'type'        => 'boolean',
						'default'     => true,
						'description' => __( 'If true, automatically redistributes all column sizes evenly.', 'header-footer-elementor' ),
					],
				],
			],
			'output_schema'       => [
				'type'       => 'object',
				'properties' => [
					'success'   => [ 'type' => 'boolean' ],
					'column_id' => [ 'type' => 'string' ],
					'message'   => [ 'type' => 'string' ],
				],
			],
			'meta'                => [
				'annotations'  => [
					'readonly'     => false,
					'destructive'  => false,
					'idempotent'   => false,
					'instructions' => 'For adding a column to an existing section. Do NOT use this to build multi-column layouts from scratch -- use builder/build instead, which creates sections with the correct column structure in one call. Only use add-column for incremental changes.',
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

		$section_id  = sanitize_text_field( $input['section_id'] ?? '' );
		$auto_resize = isset( $input['auto_resize'] ) ? (bool) $input['auto_resize'] : true;

		if ( empty( $section_id ) ) {
			return new \WP_Error(
				'hfe_missing_section_id',
				__( 'section_id is required.', 'header-footer-elementor' ),
				[ 'status' => 400 ]
			);
		}

		$result = HFE_Element_Helpers::add_column_to_section(
			$loaded['elements'],
			$section_id,
			0,
			$auto_resize
		);

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		$saved = HFE_Element_Helpers::save_elementor_data( $loaded['post']->ID, $result['elements'] );

		if ( is_wp_error( $saved ) ) {
			return $saved;
		}

		return [
			'success'   => true,
			'column_id' => $result['column_id'],
			'message'   => sprintf(
				/* translators: 1: Section ID, 2: Post title, 3: Column ID */
				__( 'Added column to section %1$s in "%2$s". Use builder/insert-widget with position {"inside": "%3$s"} to add widgets.', 'header-footer-elementor' ),
				$section_id,
				$loaded['post']->post_title,
				$result['column_id']
			),
		];
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
