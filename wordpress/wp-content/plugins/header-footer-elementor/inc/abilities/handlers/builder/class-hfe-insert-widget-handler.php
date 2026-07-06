<?php
/**
 * Insert Widget Handler.
 *
 * Inserts a single widget into an existing Elementor post layout.
 * Unified handler replacing template-builder/insert.
 *
 * @package header-footer-elementor
 * @since 2.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class HFE_Insert_Widget_Handler
 *
 * Implements HFE_Ability_Handler for the builder/insert-widget ability.
 *
 * @since 2.9.0
 */
class HFE_Insert_Widget_Handler implements HFE_Ability_Handler {

	use HFE_Abilities_Helpers;

	/**
	 * Get the ability name.
	 *
	 * @since 2.9.0
	 *
	 * @return string Ability name without plugin prefix.
	 */
	public function get_name() {
		return 'builder-insert-widget';
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
			'label'               => __( 'Insert Widget', 'header-footer-elementor' ),
			'description'         => __( 'Inserts a single widget into an existing Elementor post. KEY WIDGET SLUGS — Header: site-logo, navigation-menu, hfe-search-button, hfe-cart. Footer: copyright, retina (logo), social-icons. Pages: heading, text-editor, image, button, hfe-infocard, hfe-counter. Use list-widget-types to discover all available slugs.', 'header-footer-elementor' ),
			'category'            => 'hfe-templates',
			'permission_callback' => function () {
				return current_user_can( 'edit_posts' );
			},
			'input_schema'        => [
				'type'       => 'object',
				'required'   => [ 'post_id', 'widget_type' ],
				'properties' => [
					'post_id'     => [
						'type'        => 'integer',
						'description' => __( 'Any Elementor-enabled post ID (page, post, or HFE template).', 'header-footer-elementor' ),
					],
					'widget_type' => [
						'type'        => 'string',
						'description' => __( 'Widget type slug (e.g. "navigation-menu", "site-logo", "heading"). Use builder/list-widget-types to see available types.', 'header-footer-elementor' ),
					],
					'settings'    => [
						'type'        => 'string',
						'description' => __( 'JSON string of widget settings. Use builder/get-schema to discover the keys. Example: {"heading_title":"My Heading","align":"center"}. Pass empty string or "{}" for defaults.', 'header-footer-elementor' ),
						'default'     => '{}',
					],
					'position'    => [
						'description' => __( 'Where to insert: "append" (default), "prepend", a number for index position (0=first, 1=second, etc.), or { "after": "element_id" }, { "before": "element_id" }, { "inside": "container_id" }.', 'header-footer-elementor' ),
						'default'     => 'append',
					],
				],
			],
			'output_schema'       => [
				'type'       => 'object',
				'properties' => [
					'success'    => [ 'type' => 'boolean' ],
					'element_id' => [ 'type' => 'string' ],
					'message'    => [ 'type' => 'string' ],
				],
			],
			'meta'                => [
				'annotations'  => [
					'readonly'     => false,
					'destructive'  => false,
					'idempotent'   => false,
					'instructions' => 'For adding a SINGLE widget to an existing layout. Do NOT use this repeatedly to build a layout from scratch -- use builder/build instead, which creates the entire layout in one call. Only use insert-widget for incremental additions to an already-built post. First use get-structure to find the right position, then insert.',
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

		$widget_type = sanitize_text_field( $input['widget_type'] ?? '' );

		if ( empty( $widget_type ) ) {
			return new \WP_Error(
				'hfe_missing_widget_type',
				__( 'Widget type is required.', 'header-footer-elementor' ),
				[ 'status' => 400 ]
			);
		}

		// Validate widget type is allowed.
		if ( ! HFE_Element_Helpers::is_widget_allowed( $widget_type ) ) {
			$requirements = HFE_Element_Helpers::check_widget_requirements( $widget_type );

			if ( ! empty( $requirements['required_plugin'] ) && ! $requirements['is_active'] ) {
				return new \WP_Error(
					'hfe_widget_requires_plugin',
					/* translators: %s: Plugin name */
					sprintf( __( 'This widget requires %s.', 'header-footer-elementor' ), $requirements['required_plugin'] ),
					[ 'status' => 400 ]
				);
			}

			return new \WP_Error(
				'hfe_widget_not_allowed',
				/* translators: %s: Widget type slug */
				sprintf( __( 'Widget type "%s" is not recognized. Use builder/list-widget-types to see available types.', 'header-footer-elementor' ), $widget_type ),
				[ 'status' => 400 ]
			);
		}

		$loaded = $this->load_post( $input['post_id'] ?? $input['template_id'] ?? 0 );

		if ( is_wp_error( $loaded ) ) {
			return $loaded;
		}

		$raw_settings = $input['settings'] ?? '{}';

		if ( is_string( $raw_settings ) ) {
			$settings = json_decode( $raw_settings, true );

			if ( JSON_ERROR_NONE !== json_last_error() || ! is_array( $settings ) ) {
				$settings = [];
			}
		} else {
			$settings = is_array( $raw_settings ) ? $raw_settings : [];
		}
		$position = $input['position'] ?? 'append';

		// Build the widget element.
		$widget = HFE_Element_Helpers::build_widget_element( $widget_type, $settings );

		// Insert into the element tree.
		$elements = HFE_Element_Helpers::insert_element( $loaded['elements'], $widget, $position );

		if ( is_wp_error( $elements ) ) {
			return $elements;
		}

		// Save back.
		$saved = HFE_Element_Helpers::save_elementor_data( $loaded['post']->ID, $elements );

		if ( is_wp_error( $saved ) ) {
			return $saved;
		}

		return [
			'success'     => true,
			'element_id'  => $widget['id'],
			'layout_mode' => HFE_Element_Helpers::is_container_active() ? 'container' : 'section',
			'message'     => sprintf(
				/* translators: 1: Widget type, 2: Post title */
				__( 'Inserted %1$s widget into "%2$s".', 'header-footer-elementor' ),
				$widget_type,
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
