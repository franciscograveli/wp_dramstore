<?php
/**
 * Shortcode Render Handler.
 *
 * Renders an HFE template shortcode and returns its HTML output.
 *
 * @package header-footer-elementor
 * @since 2.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class HFE_Shortcode_Render_Handler
 *
 * Implements HFE_Ability_Handler for the shortcode/render ability.
 *
 * @since 2.9.0
 */
class HFE_Shortcode_Render_Handler implements HFE_Ability_Handler {

	use HFE_Abilities_Helpers;

	/**
	 * Get the ability name.
	 *
	 * @since 2.9.0
	 *
	 * @return string Ability name without plugin prefix.
	 */
	public function get_name() {
		return 'shortcode-render';
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
			'label'               => __( 'Render Template Shortcode', 'header-footer-elementor' ),
			'description'         => __( 'Render an HFE template shortcode and return its HTML output.', 'header-footer-elementor' ),
			'category'            => 'hfe-shortcodes',
			'permission_callback' => function () {
				return current_user_can( 'manage_options' );
			},
			'input_schema'        => [
				'type'       => 'object',
				'required'   => [ 'template_id' ],
				'properties' => [
					'template_id' => [
						'type'        => 'integer',
						'description' => __( 'Template post ID to render.', 'header-footer-elementor' ),
					],
				],
			],
			'output_schema'       => [
				'type'       => 'object',
				'properties' => [
					'html'        => [ 'type' => 'string' ],
					'template_id' => [ 'type' => 'integer' ],
					'title'       => [ 'type' => 'string' ],
				],
			],
			'meta'                => [
				'annotations'  => [
					'readonly'     => true,
					'destructive'  => false,
					'idempotent'   => true,
					'instructions' => 'Use this to preview a template\'s rendered HTML output. The output may be large for complex templates.',
				],
				'show_in_rest' => true,
				'mcp'          => [ 'public' => true ],
			],
		];
	}

	/**
	 * Execute the ability.
	 *
	 * Validates the template and renders its shortcode output.
	 *
	 * @since 2.9.0
	 *
	 * @param array $input Input with template_id.
	 * @return array|WP_Error Rendered HTML or error.
	 */
	public function execute( $input ) {
		$template_id = absint( $input['template_id'] );
		$validation  = $this->validate_template( $template_id );

		if ( is_wp_error( $validation ) ) {
			return $validation;
		}

		$html = do_shortcode( '[hfe_template id="' . $template_id . '"]' );

		return [
			'html'        => $html,
			'template_id' => $template_id,
			'title'       => get_the_title( $template_id ),
		];
	}
}
