<?php
/**
 * Update Widget Handler.
 *
 * Updates settings for an existing widget element in any Elementor post.
 * Performs a partial merge -- only provided settings are changed.
 * Unified handler replacing template-builder/update.
 *
 * @package header-footer-elementor
 * @since 2.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class HFE_Update_Widget_Handler
 *
 * Implements HFE_Ability_Handler for the builder/update-widget ability.
 *
 * @since 2.9.0
 */
class HFE_Update_Widget_Handler implements HFE_Ability_Handler {

	use HFE_Abilities_Helpers;

	/**
	 * Get the ability name.
	 *
	 * @since 2.9.0
	 *
	 * @return string Ability name without plugin prefix.
	 */
	public function get_name() {
		return 'builder-update-widget';
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
			'label'               => __( 'Update Widget Settings', 'header-footer-elementor' ),
			'description'         => __( 'Updates settings for an existing widget element in any Elementor post. Performs a partial merge -- only provided settings are changed.', 'header-footer-elementor' ),
			'category'            => 'hfe-templates',
			'permission_callback' => function () {
				return current_user_can( 'edit_posts' );
			},
			'input_schema'        => [
				'type'       => 'object',
				'required'   => [ 'post_id', 'element_id', 'settings' ],
				'properties' => [
					'post_id'    => [
						'type'        => 'integer',
						'description' => __( 'Any Elementor-enabled post ID (page, post, or HFE template).', 'header-footer-elementor' ),
					],
					'element_id' => [
						'type'        => 'string',
						'description' => __( 'Element ID to update (from get-structure).', 'header-footer-elementor' ),
					],
					'settings'   => [
						'type'        => 'string',
						'description' => __( 'JSON string of the settings to update. You MUST call builder/get-schema first to discover the exact setting keys for the widget type. Then pass a JSON object string with only the keys you want to change. Example for hfe-infocard: {"infocard_title":"New Title","infocard_description":"New text","infocard_button_text":"Click Me"}. Example for heading: {"heading_title":"New Heading"}. Example for text-editor: {"editor":"<p>New content</p>"}.', 'header-footer-elementor' ),
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
					'idempotent'   => true,
					'instructions' => 'For changing settings on an existing widget (e.g., text, colors, menu selection). Use get-structure first to find the element ID. If you need to change the entire layout, use builder/build instead.',
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

		// Settings can arrive as JSON string (from Angie) or array (from MCP Adapter).
		$raw_settings = $input['settings'] ?? [];

		if ( is_string( $raw_settings ) ) {
			$settings = json_decode( $raw_settings, true );

			if ( JSON_ERROR_NONE !== json_last_error() || ! is_array( $settings ) ) {
				$settings = [];
			}
		} else {
			$settings = is_array( $raw_settings ) ? $raw_settings : [];
		}

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

		$elements = $loaded['elements'];

		// When settings is empty, return the widget's current settings so the AI knows the correct keys.
		if ( empty( $settings ) ) {
			$element = HFE_Element_Helpers::find_element( $elements, $element_id );

			if ( ! $element ) {
				return new \WP_Error(
					'hfe_element_not_found',
					__( 'Element not found in post.', 'header-footer-elementor' ),
					[ 'status' => 404 ]
				);
			}

			$widget_type      = $element['widgetType'] ?? $element['elType'] ?? 'unknown';
			$current_settings = $element['settings'] ?? [];
			$setting_keys     = array_keys( $current_settings );

			return new \WP_Error(
				'hfe_empty_settings',
				sprintf(
					/* translators: 1: widget type, 2: comma-separated setting keys */
					__( 'No settings provided. This is a "%1$s" widget. Available setting keys: %2$s. Retry with the correct keys in the settings object.', 'header-footer-elementor' ),
					$widget_type,
					implode( ', ', array_slice( $setting_keys, 0, 15 ) )
				),
				[ 'status' => 400 ]
			);
		}

		// Find and update the element in the tree.
		$updated = $this->update_element_settings( $elements, $element_id, $settings );

		if ( ! $updated['found'] ) {
			return new \WP_Error(
				'hfe_element_not_found',
				__( 'Element not found in post.', 'header-footer-elementor' ),
				[ 'status' => 404 ]
			);
		}

		$saved = HFE_Element_Helpers::save_elementor_data( $loaded['post']->ID, $updated['elements'] );

		if ( is_wp_error( $saved ) ) {
			return $saved;
		}

		return [
			'success' => true,
			'message' => sprintf(
				/* translators: 1: Element ID, 2: Post title */
				__( 'Updated element %1$s in "%2$s".', 'header-footer-elementor' ),
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

	/**
	 * Recursively find and update element settings (partial merge).
	 *
	 * @since 2.9.0
	 *
	 * @param array  $elements   Element tree.
	 * @param string $element_id Target element ID.
	 * @param array  $settings   Settings to merge.
	 * @return array Result with 'found' bool and 'elements' array.
	 */
	private function update_element_settings( $elements, $element_id, $settings ) {
		foreach ( $elements as $index => $element ) {
			if ( isset( $element['id'] ) && $element['id'] === $element_id ) {
				$existing = isset( $elements[ $index ]['settings'] ) && is_array( $elements[ $index ]['settings'] )
					? $elements[ $index ]['settings']
					: [];

				$elements[ $index ]['settings'] = array_merge( $existing, $settings );

				return [
					'found'    => true,
					'elements' => $elements,
				];
			}

			if ( ! empty( $element['elements'] ) ) {
				$child_result = $this->update_element_settings( $element['elements'], $element_id, $settings );

				if ( $child_result['found'] ) {
					$elements[ $index ]['elements'] = $child_result['elements'];

					return [
						'found'    => true,
						'elements' => $elements,
					];
				}
			}
		}

		return [
			'found'    => false,
			'elements' => $elements,
		];
	}
}
