<?php
/**
 * Template Get Handler.
 *
 * Returns full details for a specific HFE template including dates.
 *
 * @package header-footer-elementor
 * @since 2.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class HFE_Template_Get_Handler
 *
 * Implements HFE_Ability_Handler for the templates/get ability.
 *
 * @since 2.9.0
 */
class HFE_Template_Get_Handler implements HFE_Ability_Handler {

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
		return 'templates-get';
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
			'label'               => __( 'Get Template Details', 'header-footer-elementor' ),
			'description'         => __( 'Returns full details for a specific template including type, display rules, and shortcode.', 'header-footer-elementor' ),
			'category'            => 'hfe-templates',
			'permission_callback' => function () {
				return current_user_can( 'edit_posts' );
			},
			'input_schema'        => [
				'type'       => 'object',
				'required'   => [ 'template_id' ],
				'properties' => [
					'template_id' => [
						'type'        => 'integer',
						'description' => __( 'The template post ID.', 'header-footer-elementor' ),
					],
				],
			],
			'output_schema'       => $this->get_template_item_schema(),
			'meta'                => [
				'annotations'  => [
					'readonly'     => true,
					'destructive'  => false,
					'idempotent'   => true,
					'instructions' => 'Use this to retrieve full details of a specific template before performing any update or delete operation.',
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
	 * @return array|WP_Error Template data or error.
	 */
	public function execute( $input ) {
		$template_id = absint( $input['template_id'] );
		$validation  = $this->validate_template( $template_id );

		if ( is_wp_error( $validation ) ) {
			return $validation;
		}

		return $this->build_template_data( $template_id, true );
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
				'created_date'       => [ 'type' => 'string' ],
				'modified_date'      => [ 'type' => 'string' ],
			],
		];
	}
}
