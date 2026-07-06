<?php
/**
 * Page Create Handler.
 *
 * Creates a new Elementor-ready page or post with optional template settings.
 *
 * @package header-footer-elementor
 * @since 2.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class HFE_Page_Create_Handler
 *
 * Implements HFE_Ability_Handler for the pages/create ability.
 *
 * @since 2.9.0
 */
class HFE_Page_Create_Handler implements HFE_Ability_Handler {

	use HFE_Abilities_Helpers;

	/**
	 * Valid page templates.
	 *
	 * @var array
	 */
	const VALID_TEMPLATES = [ 'elementor_header_footer', 'elementor_canvas', 'default' ];

	/**
	 * Get the ability name.
	 *
	 * @since 2.9.0
	 *
	 * @return string Ability name without plugin prefix.
	 */
	public function get_name() {
		return 'pages-create';
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
			'label'               => __( 'Create Page', 'header-footer-elementor' ),
			'description'         => __( 'Creates a new Elementor-ready page or post with optional page template.', 'header-footer-elementor' ),
			'category'            => 'hfe-pages',
			'permission_callback' => function () {
				return current_user_can( 'manage_options' );
			},
			'input_schema'        => [
				'type'       => 'object',
				'required'   => [ 'title' ],
				'properties' => [
					'title'         => [
						'type'        => 'string',
						'description' => __( 'The page or post title.', 'header-footer-elementor' ),
					],
					'post_type'     => [
						'type'        => 'string',
						'enum'        => [ 'page', 'post' ],
						'default'     => 'page',
						'description' => __( 'Post type to create.', 'header-footer-elementor' ),
					],
					'status'        => [
						'type'        => 'string',
						'enum'        => [ 'publish', 'draft' ],
						'default'     => 'draft',
						'description' => __( 'Initial post status.', 'header-footer-elementor' ),
					],
					'page_template' => [
						'type'        => 'string',
						'enum'        => self::VALID_TEMPLATES,
						'default'     => 'default',
						'description' => __( 'Elementor page template: elementor_header_footer, elementor_canvas, or default.', 'header-footer-elementor' ),
					],
				],
			],
			'output_schema'       => [
				'type'       => 'object',
				'properties' => [
					'id'                 => [ 'type' => 'integer' ],
					'title'              => [ 'type' => 'string' ],
					'post_type'          => [ 'type' => 'string' ],
					'page_template'      => [ 'type' => 'string' ],
					'edit_url'           => [ 'type' => 'string' ],
					'elementor_edit_url' => [ 'type' => 'string' ],
					'view_url'           => [ 'type' => 'string' ],
				],
			],
			'meta'                => [
				'annotations'  => [
					'readonly'     => false,
					'destructive'  => false,
					'idempotent'   => false,
					'instructions' => 'Creates a new page/post ready for Elementor editing. Confirm title and settings with user before creating.',
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
		$modifications_check = $this->check_modifications_allowed();

		if ( is_wp_error( $modifications_check ) ) {
			return $modifications_check;
		}

		$post_type = ! empty( $input['post_type'] ) ? sanitize_text_field( $input['post_type'] ) : 'page';
		$status    = ! empty( $input['status'] ) ? sanitize_text_field( $input['status'] ) : 'draft';
		$title     = sanitize_text_field( $input['title'] );
		$template  = ! empty( $input['page_template'] ) ? sanitize_text_field( $input['page_template'] ) : 'default';

		// Validate post type against Elementor's supported CPTs.
		$supported_cpts = get_option( 'elementor_cpt_support', [ 'page', 'post' ] );

		if ( ! is_array( $supported_cpts ) || ! in_array( $post_type, $supported_cpts, true ) ) {
			return new \WP_Error(
				'hfe_unsupported_post_type',
				sprintf(
					/* translators: %s: post type slug */
					__( 'Post type "%s" is not supported by Elementor.', 'header-footer-elementor' ),
					$post_type
				),
				[ 'status' => 400 ]
			);
		}

		// Validate template value.
		if ( ! in_array( $template, self::VALID_TEMPLATES, true ) ) {
			$template = 'default';
		}

		$post_id = wp_insert_post(
			[
				'post_title'  => $title,
				'post_type'   => $post_type,
				'post_status' => $status,
			],
			true
		);

		if ( is_wp_error( $post_id ) ) {
			return $post_id;
		}

		// Set Elementor edit mode meta so the page is recognized by Elementor.
		update_post_meta( $post_id, '_elementor_edit_mode', 'builder' );
		update_post_meta( $post_id, '_elementor_data', '[]' );
		update_post_meta( $post_id, '_elementor_version', defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : '' );

		// Set page template if not default.
		if ( 'default' !== $template ) {
			update_post_meta( $post_id, '_wp_page_template', $template );
		}

		return [
			'id'                 => $post_id,
			'title'              => $title,
			'post_type'          => $post_type,
			'page_template'      => $template,
			'edit_url'           => admin_url( 'post.php?post=' . $post_id . '&action=edit' ),
			'elementor_edit_url' => admin_url( 'post.php?post=' . $post_id . '&action=elementor' ),
			'view_url'           => get_permalink( $post_id ),
		];
	}
}
