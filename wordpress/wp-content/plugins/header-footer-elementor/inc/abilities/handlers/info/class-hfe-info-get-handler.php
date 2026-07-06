<?php
/**
 * Info Get Handler.
 *
 * Unified handler merging info/get-version, info/get-status, and info/get-hooks
 * into a single info/get ability. Returns version, health, templates, widgets,
 * theme compatibility, extensions, and optionally hooks data.
 *
 * @package header-footer-elementor
 * @since 2.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class HFE_Info_Get_Handler
 *
 * Implements HFE_Ability_Handler for the unified info/get ability.
 *
 * @since 2.9.0
 */
class HFE_Info_Get_Handler implements HFE_Ability_Handler {

	use HFE_Abilities_Helpers;

	/**
	 * Get the ability name.
	 *
	 * @since 2.9.0
	 *
	 * @return string Ability name without plugin prefix.
	 */
	public function get_name() {
		return 'info-get';
	}

	/**
	 * Get the full wp_register_ability() args array.
	 *
	 * The execute_callback is set automatically by the registry.
	 *
	 * @since 2.9.0
	 *
	 * @return array Ability registration args.
	 */
	public function get_registration_args() {
		return [
			'label'               => __( 'Get Plugin Info', 'header-footer-elementor' ),
			'description'         => __( 'Returns unified plugin info: version, health, templates, widgets, theme compatibility, extensions, and optionally hooks.', 'header-footer-elementor' ),
			'category'            => 'hfe-info',
			'permission_callback' => function () {
				return current_user_can( 'manage_options' );
			},
			'input_schema'        => [
				'type'       => 'object',
				'properties' => [
					'include_hooks' => [
						'type'        => 'boolean',
						'default'     => false,
						'description' => __( 'When true, includes the hooks list in the response.', 'header-footer-elementor' ),
					],
				],
			],
			'output_schema'       => [
				'type'       => 'object',
				'properties' => [
					'plugin_version'       => [ 'type' => 'string' ],
					'elementor_version'    => [ 'type' => 'string' ],
					'elementor_compatible' => [ 'type' => 'boolean' ],
					'active_widgets'       => [ 'type' => 'integer' ],
					'total_widgets'        => [ 'type' => 'integer' ],
					'total_templates'      => [ 'type' => 'integer' ],
					'is_pro_active'        => [ 'type' => 'boolean' ],
					'wordpress_version'    => [ 'type' => 'string' ],
					'theme'                => [
						'type'       => 'object',
						'properties' => [
							'name'          => [ 'type' => 'string' ],
							'is_supported'  => [ 'type' => 'boolean' ],
							'compat_method' => [ 'type' => 'string' ],
						],
					],
					'templates'            => [
						'type'       => 'object',
						'properties' => [
							'total'          => [ 'type' => 'integer' ],
							'headers'        => [ 'type' => 'integer' ],
							'footers'        => [ 'type' => 'integer' ],
							'before_footers' => [ 'type' => 'integer' ],
							'custom_blocks'  => [ 'type' => 'integer' ],
						],
					],
					'widgets'              => [
						'type'       => 'object',
						'properties' => [
							'total'  => [ 'type' => 'integer' ],
							'active' => [ 'type' => 'integer' ],
						],
					],
					'extensions'           => [
						'type'       => 'object',
						'properties' => [
							'scroll_to_top'        => [ 'type' => 'boolean' ],
							'reading_progress_bar' => [ 'type' => 'boolean' ],
						],
					],
					'hooks'                => [
						'type'       => 'object',
						'properties' => [
							'filters' => [
								'type'  => 'array',
								'items' => [
									'type'       => 'object',
									'properties' => [
										'name'        => [ 'type' => 'string' ],
										'return_type' => [ 'type' => 'string' ],
										'description' => [ 'type' => 'string' ],
									],
								],
							],
							'actions' => [
								'type'  => 'array',
								'items' => [
									'type'       => 'object',
									'properties' => [
										'name'        => [ 'type' => 'string' ],
										'description' => [ 'type' => 'string' ],
									],
								],
							],
						],
					],
				],
			],
			'meta'                => [
				'annotations'  => [
					'readonly'     => true,
					'destructive'  => false,
					'idempotent'   => true,
					'instructions' => 'Use this as the primary info tool. Returns version, health, templates, widgets, theme compat, and extensions. Pass include_hooks=true when the user asks about hooks.',
				],
				'show_in_rest' => true,
				'mcp'          => [ 'public' => true ],
			],
		];
	}

	/**
	 * Execute the info/get ability.
	 *
	 * @since 2.9.0
	 *
	 * @param array $input Validated input parameters.
	 * @return array Unified plugin info data.
	 */
	public function execute( $input ) {
		global $wp_version;

		$include_hooks = ! empty( $input['include_hooks'] );

		// Version and compatibility data.
		$elementor_version    = defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : '';
		$elementor_compatible = '' !== $elementor_version && version_compare( $elementor_version, '3.5.0', '>=' );

		// Widget counts.
		$widget_counts = $this->get_widget_counts();

		// Template counts by type.
		$templates = get_posts(
			[
				'post_type'      => 'elementor-hf',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'no_found_rows'  => true,
			]
		);

		$type_counts = [
			'type_header'        => 0,
			'type_footer'        => 0,
			'type_before_footer' => 0,
			'custom'             => 0,
		];

		foreach ( $templates as $template_id ) {
			$type = get_post_meta( $template_id, 'ehf_template_type', true );
			if ( isset( $type_counts[ $type ] ) ) {
				++$type_counts[ $type ];
			} else {
				++$type_counts['custom'];
			}
		}

		// Extension status.
		$saved_widgets = get_option( '_hfe_widgets', [] );
		$scroll_active = ! empty( $saved_widgets['Scroll_To_Top'] ) && 'disabled' !== $saved_widgets['Scroll_To_Top'];
		$bar_active    = ! empty( $saved_widgets['Reading_Progress_Bar'] ) && 'disabled' !== $saved_widgets['Reading_Progress_Bar'];

		$result = [
			'plugin_version'       => defined( 'HFE_VER' ) ? HFE_VER : '',
			'elementor_version'    => $elementor_version,
			'elementor_compatible' => $elementor_compatible,
			'active_widgets'       => $widget_counts['active'],
			'total_widgets'        => $widget_counts['total'],
			'total_templates'      => count( $templates ),
			'is_pro_active'        => defined( 'UAEL_PRO' ) && UAEL_PRO,
			'wordpress_version'    => $wp_version,
			'theme'                => [
				'name'          => get_template(),
				'is_supported'  => (bool) get_option( 'hfe_is_theme_supported', false ),
				'compat_method' => $this->resolve_compat_method(),
			],
			'templates'            => [
				'total'          => count( $templates ),
				'headers'        => $type_counts['type_header'],
				'footers'        => $type_counts['type_footer'],
				'before_footers' => $type_counts['type_before_footer'],
				'custom_blocks'  => $type_counts['custom'],
			],
			'widgets'              => [
				'total'  => $widget_counts['total'],
				'active' => $widget_counts['active'],
			],
			'extensions'           => [
				'scroll_to_top'        => $scroll_active,
				'reading_progress_bar' => $bar_active,
			],
		];

		if ( $include_hooks ) {
			$result['hooks'] = $this->get_hooks_data();
		}

		return $result;
	}

	/**
	 * Get active and total widget counts.
	 *
	 * @since 2.9.0
	 *
	 * @return array { active: int, total: int }
	 */
	private function get_widget_counts() {
		$widget_options = \HFE\WidgetsManager\Base\HFE_Helper::get_widget_options();
		$active         = 0;
		$total          = is_array( $widget_options ) ? count( $widget_options ) : 0;

		if ( is_array( $widget_options ) ) {
			foreach ( $widget_options as $widget ) {
				if ( ! empty( $widget['is_activate'] ) ) {
					++$active;
				}
			}
		}

		return [
			'active' => $active,
			'total'  => $total,
		];
	}

	/**
	 * Get hooks data (filters and actions).
	 *
	 * @since 2.9.0
	 *
	 * @return array { filters: array, actions: array }
	 */
	private function get_hooks_data() {
		return [
			'filters' => [
				[
					'name'        => 'hfe_header_enabled',
					'return_type' => 'bool',
					'description' => __( 'Enable/disable header rendering.', 'header-footer-elementor' ),
				],
				[
					'name'        => 'hfe_footer_enabled',
					'return_type' => 'bool',
					'description' => __( 'Enable/disable footer rendering.', 'header-footer-elementor' ),
				],
				[
					'name'        => 'hfe_before_footer_enabled',
					'return_type' => 'bool',
					'description' => __( 'Enable/disable before-footer rendering.', 'header-footer-elementor' ),
				],
				[
					'name'        => 'get_hfe_header_id',
					'return_type' => 'int|false',
					'description' => __( 'Filter header template post ID.', 'header-footer-elementor' ),
				],
				[
					'name'        => 'get_hfe_footer_id',
					'return_type' => 'int|false',
					'description' => __( 'Filter footer template post ID.', 'header-footer-elementor' ),
				],
				[
					'name'        => 'get_hfe_before_footer_id',
					'return_type' => 'int|false',
					'description' => __( 'Filter before-footer template ID.', 'header-footer-elementor' ),
				],
				[
					'name'        => 'enable_hfe_render_header',
					'return_type' => 'bool',
					'description' => __( 'Conditionally skip header output.', 'header-footer-elementor' ),
				],
				[
					'name'        => 'enable_hfe_render_footer',
					'return_type' => 'bool',
					'description' => __( 'Conditionally skip footer output.', 'header-footer-elementor' ),
				],
				[
					'name'        => 'enable_hfe_render_before_footer',
					'return_type' => 'bool',
					'description' => __( 'Conditionally skip before-footer output.', 'header-footer-elementor' ),
				],
				[
					'name'        => 'hfe_render_template_id',
					'return_type' => 'int',
					'description' => __( 'Filter template ID before render.', 'header-footer-elementor' ),
				],
				[
					'name'        => 'hfe_show_preview_notice',
					'return_type' => 'bool',
					'description' => __( 'Control preview notice visibility.', 'header-footer-elementor' ),
				],
			],
			'actions' => [
				[
					'name'        => 'hfe_header',
					'description' => __( 'Fires inside header wrapper.', 'header-footer-elementor' ),
				],
				[
					'name'        => 'hfe_footer_before',
					'description' => __( 'Fires before footer section.', 'header-footer-elementor' ),
				],
				[
					'name'        => 'hfe_footer',
					'description' => __( 'Fires inside footer wrapper.', 'header-footer-elementor' ),
				],
				[
					'name'        => 'hfe_render_admin_page_content',
					'description' => __( 'Render admin page body.', 'header-footer-elementor' ),
				],
			],
		];
	}
}
