<?php
/**
 * Widget Deactivate Unused Handler.
 *
 * Scans site-wide Elementor usage and deactivates widgets not found
 * in any published content. Skips extensions (Scroll_To_Top, Reading_Progress_Bar).
 *
 * @package header-footer-elementor
 * @since 2.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class HFE_Widget_Deactivate_Unused_Handler
 *
 * Implements HFE_Ability_Handler for the widgets/deactivate-unused ability.
 *
 * @since 2.9.0
 */
class HFE_Widget_Deactivate_Unused_Handler implements HFE_Ability_Handler {

	/**
	 * Get the ability name.
	 *
	 * @since 2.9.0
	 *
	 * @return string Ability name without plugin prefix.
	 */
	public function get_name() {
		return 'widgets-deactivate-unused';
	}

	/**
	 * Get the wp_register_ability() args array.
	 *
	 * Does NOT include execute_callback -- the registry sets that automatically.
	 *
	 * @since 2.9.0
	 *
	 * @return array Ability registration args.
	 */
	public function get_registration_args() {
		return [
			'label'               => __( 'Deactivate Unused Widgets', 'header-footer-elementor' ),
			'description'         => __( 'Disable widgets not used in any published Elementor content (pages, posts, or templates).', 'header-footer-elementor' ),
			'category'            => 'hfe-widgets',
			'permission_callback' => function () {
				return current_user_can( 'manage_options' );
			},
			'input_schema'        => [
				'type'       => 'object',
				'properties' => (object) [],
			],
			'output_schema'       => [
				'type'       => 'object',
				'properties' => [
					'success'           => [ 'type' => 'boolean' ],
					'deactivated'       => [
						'type'  => 'array',
						'items' => [ 'type' => 'string' ],
					],
					'deactivated_count' => [ 'type' => 'integer' ],
				],
			],
			'meta'                => [
				'annotations'  => [
					'readonly'     => false,
					'destructive'  => true,
					'idempotent'   => true,
					'instructions' => 'Before running, use widgets/get-usage to show the user which widgets will be deactivated. Ask for confirmation before proceeding.',
				],
				'show_in_rest' => true,
				'mcp'          => [ 'public' => true ],
			],
		];
	}

	/**
	 * Execute the ability.
	 *
	 * Cross-references widget usage in Elementor data across all published
	 * content, then deactivates any active widget not found in use.
	 * Skips extensions (Scroll_To_Top, Reading_Progress_Bar) as they are
	 * site-wide features not tied to individual content.
	 *
	 * @since 2.9.0
	 *
	 * @param array $input Unused input parameters.
	 * @return array Result with list of deactivated widget slugs.
	 */
	public function execute( $input ) {
		$used_widgets  = \HFE\WidgetsManager\Base\HFE_Helper::get_used_widget();
		$widget_list   = \HFE\WidgetsManager\Base\Widgets_Config::get_widget_list();
		$saved_widgets = get_option( '_hfe_widgets', [] );
		$deactivated   = [];

		foreach ( $widget_list as $class_name => $widget ) {
			$slug = isset( $widget['slug'] ) ? $widget['slug'] : '';

			// Skip extensions (Scroll_To_Top, Reading_Progress_Bar) -- site-wide features.
			if ( 'Scroll_To_Top' === $class_name || 'Reading_Progress_Bar' === $class_name ) {
				continue;
			}

			// If widget is not used anywhere on the site, deactivate it.
			if ( '' !== $slug && ! isset( $used_widgets[ $slug ] ) ) {
				$saved_widgets[ $class_name ] = 'disabled';
				$deactivated[]                = $slug;
			}
		}

		if ( ! empty( $deactivated ) ) {
			$saved_widgets = array_map( 'esc_attr', $saved_widgets );
			\HFE\WidgetsManager\Base\HFE_Helper::update_admin_settings_option( '_hfe_widgets', $saved_widgets );
		}

		return [
			'success'           => true,
			'deactivated'       => $deactivated,
			'deactivated_count' => count( $deactivated ),
		];
	}
}
