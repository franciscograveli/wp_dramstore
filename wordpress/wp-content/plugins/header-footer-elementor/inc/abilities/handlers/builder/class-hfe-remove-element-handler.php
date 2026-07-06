<?php
/**
 * Remove Element Handler.
 *
 * Removes a widget or container from any Elementor post by element ID.
 * Unified handler replacing template-builder/remove.
 *
 * @package header-footer-elementor
 * @since 2.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class HFE_Remove_Element_Handler
 *
 * Implements HFE_Ability_Handler for the builder/remove-element ability.
 *
 * @since 2.9.0
 */
class HFE_Remove_Element_Handler implements HFE_Ability_Handler {

	use HFE_Abilities_Helpers;

	/**
	 * Get the ability name.
	 *
	 * @since 2.9.0
	 *
	 * @return string Ability name without plugin prefix.
	 */
	public function get_name() {
		return 'builder-remove-element';
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
			'label'               => __( 'Remove Element', 'header-footer-elementor' ),
			'description'         => __( 'Removes a widget or container from any Elementor post by element ID.', 'header-footer-elementor' ),
			'category'            => 'hfe-templates',
			'permission_callback' => function () {
				return current_user_can( 'edit_posts' );
			},
			'input_schema'        => [
				'type'       => 'object',
				'required'   => [ 'post_id', 'element_id' ],
				'properties' => [
					'post_id'    => [
						'type'        => 'integer',
						'description' => __( 'Any Elementor-enabled post ID (page, post, or HFE template).', 'header-footer-elementor' ),
					],
					'element_id' => [
						'type'        => 'string',
						'description' => __( 'Element ID to remove.', 'header-footer-elementor' ),
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
					'destructive'  => true,
					'idempotent'   => true,
					'instructions' => 'For removing a single element from an existing post. Use get-structure first to find the element ID. Warning: removing a container removes all its children. If you need to rebuild the layout, use builder/build instead.',
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

		$element_id = sanitize_text_field( $input['element_id'] ?? '' );

		if ( empty( $element_id ) ) {
			return new \WP_Error(
				'hfe_missing_element_id',
				__( 'Element ID is required.', 'header-footer-elementor' ),
				[ 'status' => 400 ]
			);
		}

		$loaded = $this->load_post( $input['post_id'] ?? $input['template_id'] ?? 0 );

		if ( is_wp_error( $loaded ) ) {
			return $loaded;
		}

		$elements = HFE_Element_Helpers::remove_element( $loaded['elements'], $element_id );

		if ( is_wp_error( $elements ) ) {
			return $elements;
		}

		$saved = HFE_Element_Helpers::save_elementor_data( $loaded['post']->ID, $elements );

		if ( is_wp_error( $saved ) ) {
			return $saved;
		}

		return [
			'success' => true,
			'message' => sprintf(
				/* translators: 1: Element ID, 2: Post title */
				__( 'Removed element %1$s from "%2$s".', 'header-footer-elementor' ),
				$element_id,
				$loaded['post']->post_title
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
