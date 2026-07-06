<?php
/**
 * Template Create Handler.
 *
 * Creates a new HFE template with Elementor meta initialized.
 *
 * @package header-footer-elementor
 * @since 2.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class HFE_Template_Create_Handler
 *
 * Implements HFE_Ability_Handler for the templates/create ability.
 *
 * @since 2.9.0
 */
class HFE_Template_Create_Handler implements HFE_Ability_Handler {

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
		return 'templates-create';
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
			'label'               => __( 'Create Template', 'header-footer-elementor' ),
			'description'         => __( 'Creates a new HFE template. Returns the new template ID and Elementor edit URL.', 'header-footer-elementor' ),
			'category'            => 'hfe-templates',
			'permission_callback' => function () {
				return current_user_can( 'manage_options' );
			},
			'input_schema'        => [
				'type'       => 'object',
				'required'   => [ 'title', 'type' ],
				'properties' => [
					'title'  => [
						'type'        => 'string',
						'minLength'   => 1,
						'maxLength'   => 200,
						'description' => __( 'Template title.', 'header-footer-elementor' ),
					],
					'type'   => [
						'type'        => 'string',
						'enum'        => self::VALID_TYPES,
						'description' => __( 'Template type.', 'header-footer-elementor' ),
					],
					'status' => [
						'type'        => 'string',
						'enum'        => [ 'publish', 'draft' ],
						'default'     => 'publish',
						'description' => __( 'Post status.', 'header-footer-elementor' ),
					],
				],
			],
			'output_schema'       => [
				'type'       => 'object',
				'properties' => [
					'id'                 => [ 'type' => 'integer' ],
					'title'              => [ 'type' => 'string' ],
					'type'               => [ 'type' => 'string' ],
					'edit_url'           => [ 'type' => 'string' ],
					'elementor_edit_url' => [ 'type' => 'string' ],
					'shortcode'          => [ 'type' => 'string' ],
				],
			],
			'meta'                => [
				'annotations'  => [
					'readonly'     => false,
					'destructive'  => false,
					'idempotent'   => false,
					'instructions' => 'After creating, use template-builder/build-template to design the template content programmatically. Then call template-builder/regenerate-css. Finally, use display-rules/update to set where the template appears (e.g., basic-global for site-wide). Also provide the Elementor edit URL in case the user wants to refine manually.',
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
	 * @return array|WP_Error New template data or error.
	 */
	public function execute( $input ) {
		$title  = sanitize_text_field( $input['title'] );
		$type   = sanitize_text_field( $input['type'] );
		$status = ! empty( $input['status'] ) ? sanitize_text_field( $input['status'] ) : 'publish';

		if ( ! in_array( $type, self::VALID_TYPES, true ) ) {
			return new WP_Error( 'hfe_invalid_type', __( 'Invalid template type.', 'header-footer-elementor' ), [ 'status' => 400 ] );
		}

		if ( ! in_array( $status, [ 'publish', 'draft' ], true ) ) {
			return new WP_Error( 'hfe_invalid_status', __( 'Invalid post status.', 'header-footer-elementor' ), [ 'status' => 400 ] );
		}

		$post_id = wp_insert_post(
			[
				'post_type'   => 'elementor-hf',
				'post_title'  => $title,
				'post_status' => $status,
			]
		);

		if ( is_wp_error( $post_id ) ) {
			return new WP_Error(
				'hfe_create_failed',
				__( 'Failed to create template.', 'header-footer-elementor' ),
				[ 'status' => 500 ]
			);
		}

		update_post_meta( $post_id, 'ehf_template_type', $type );

		// Set Elementor meta so the post is recognized as an Elementor document.
		// Without these, "Edit with Elementor" button won't appear and rendering fails.
		update_post_meta( $post_id, '_elementor_edit_mode', 'builder' );
		update_post_meta( $post_id, '_elementor_data', '[]' );

		if ( defined( 'ELEMENTOR_VERSION' ) ) {
			update_post_meta( $post_id, '_elementor_version', ELEMENTOR_VERSION );
		}

		return [
			'id'                 => $post_id,
			'title'              => $title,
			'type'               => $type,
			'edit_url'           => admin_url( 'post.php?post=' . $post_id . '&action=edit' ),
			'elementor_edit_url' => admin_url( 'post.php?post=' . $post_id . '&action=elementor' ),
			'shortcode'          => '[hfe_template id="' . $post_id . '"]',
		];
	}
}
