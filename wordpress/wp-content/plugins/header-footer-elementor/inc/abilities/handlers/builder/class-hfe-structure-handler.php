<?php
/**
 * Structure Handler.
 *
 * Returns the Elementor element tree of any Elementor-enabled post.
 * Unified handler replacing template-builder/get-structure and page-builder/get-structure.
 *
 * @package header-footer-elementor
 * @since 2.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class HFE_Structure_Handler
 *
 * Implements HFE_Ability_Handler for the builder/get-structure ability.
 *
 * @since 2.9.0
 */
class HFE_Structure_Handler implements HFE_Ability_Handler {

	use HFE_Abilities_Helpers;

	/**
	 * Get the ability name.
	 *
	 * @since 2.9.0
	 *
	 * @return string Ability name without plugin prefix.
	 */
	public function get_name() {
		return 'builder-get-structure';
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
			'label'               => __( 'Get Post Structure', 'header-footer-elementor' ),
			'description'         => __( 'Returns the full Elementor element tree of any Elementor-enabled post -- containers, widgets, IDs, types, and setting keys.', 'header-footer-elementor' ),
			'category'            => 'hfe-templates',
			'permission_callback' => function () {
				return current_user_can( 'edit_posts' );
			},
			'input_schema'        => [
				'type'       => 'object',
				'required'   => [ 'post_id' ],
				'properties' => [
					'post_id' => [
						'type'        => 'integer',
						'description' => __( 'Any Elementor-enabled post ID (page, post, or HFE template).', 'header-footer-elementor' ),
					],
					'full'    => [
						'type'        => 'boolean',
						'description' => __( 'If true, return complete settings. Default false returns only setting keys for brevity.', 'header-footer-elementor' ),
						'default'     => false,
					],
				],
			],
			'output_schema'       => [
				'type'       => 'object',
				'properties' => [
					'post_id'     => [ 'type' => 'integer' ],
					'post_title'  => [ 'type' => 'string' ],
					'post_type'   => [ 'type' => 'string' ],
					'layout_mode' => [ 'type' => 'string' ],
					'elements'    => [ 'type' => 'array' ],
				],
			],
			'meta'                => [
				'annotations'  => [
					'readonly'     => true,
					'destructive'  => false,
					'idempotent'   => true,
					'instructions' => 'Use this to inspect any Elementor post before making changes. Returns the element tree with IDs and types. If the post needs a full rebuild, use builder/build. For incremental edits, use the element IDs with update-widget/remove-element/move-element. Use full=true to see all widget settings values.',
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
		$loaded = $this->load_post( $input['post_id'] ?? $input['template_id'] ?? 0 );

		if ( is_wp_error( $loaded ) ) {
			return $loaded;
		}

		$full = ! empty( $input['full'] );

		return [
			'post_id'     => $loaded['post']->ID,
			'post_title'  => $loaded['post']->post_title,
			'post_type'   => $loaded['post']->post_type,
			'layout_mode' => HFE_Element_Helpers::is_container_active() ? 'container' : 'section',
			'elements'    => $full
				? $loaded['elements']
				: HFE_Element_Helpers::simplify_tree( $loaded['elements'] ),
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
