<?php
/**
 * Template Update Handler.
 *
 * Updates template type and/or status in a single operation.
 * Merges the old update-type and update-status abilities.
 *
 * @package header-footer-elementor
 * @since 2.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class HFE_Template_Update_Handler
 *
 * Implements HFE_Ability_Handler for the templates/update ability.
 *
 * @since 2.9.0
 */
class HFE_Template_Update_Handler implements HFE_Ability_Handler {

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
		return 'templates-update';
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
			'label'               => __( 'Update Template', 'header-footer-elementor' ),
			'description'         => __( 'Updates a template\'s type and/or status. Provide type, status, or both.', 'header-footer-elementor' ),
			'category'            => 'hfe-templates',
			'permission_callback' => function () {
				return current_user_can( 'manage_options' );
			},
			'input_schema'        => [
				'type'       => 'object',
				'required'   => [ 'template_id' ],
				'properties' => [
					'template_id' => [
						'type'        => 'integer',
						'description' => __( 'Template post ID.', 'header-footer-elementor' ),
					],
					'type'        => [
						'type'        => 'string',
						'enum'        => self::VALID_TYPES,
						'description' => __( 'New template type.', 'header-footer-elementor' ),
					],
					'status'      => [
						'type'        => 'string',
						'enum'        => [ 'publish', 'draft' ],
						'description' => __( 'New post status.', 'header-footer-elementor' ),
					],
				],
			],
			'output_schema'       => [
				'type'       => 'object',
				'properties' => [
					'success'     => [ 'type' => 'boolean' ],
					'template_id' => [ 'type' => 'integer' ],
					'old_type'    => [ 'type' => 'string' ],
					'new_type'    => [ 'type' => 'string' ],
					'old_status'  => [ 'type' => 'string' ],
					'new_status'  => [ 'type' => 'string' ],
				],
			],
			'meta'                => [
				'annotations'  => [
					'readonly'     => false,
					'destructive'  => false,
					'idempotent'   => true,
					'instructions' => 'Updates template type and/or status. Provide type, status, or both. Confirm changes with the user before proceeding. Publishing a draft makes it live immediately.',
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
	 * @return array|WP_Error Update result or error.
	 */
	public function execute( $input ) {
		$template_id = absint( $input['template_id'] );
		$validation  = $this->validate_template( $template_id );

		if ( is_wp_error( $validation ) ) {
			return $validation;
		}

		$has_type   = ! empty( $input['type'] );
		$has_status = ! empty( $input['status'] );

		if ( ! $has_type && ! $has_status ) {
			return new WP_Error(
				'hfe_no_updates',
				__( 'Provide at least one of type or status to update.', 'header-footer-elementor' ),
				[ 'status' => 400 ]
			);
		}

		$old_type   = get_post_meta( $template_id, 'ehf_template_type', true );
		$old_status = $validation->post_status;

		// Prevent status change on trashed posts — use templates/restore instead.
		if ( $has_status && 'trash' === $old_status ) {
			return new WP_Error(
				'hfe_template_trashed',
				__( 'Cannot change status of a trashed template. Use templates/restore first.', 'header-footer-elementor' ),
				[ 'status' => 400 ]
			);
		}

		$new_type   = $old_type;
		$new_status = $old_status;

		// Update type if provided.
		if ( $has_type ) {
			$new_type = sanitize_text_field( $input['type'] );

			if ( ! in_array( $new_type, self::VALID_TYPES, true ) ) {
				return new WP_Error( 'hfe_invalid_type', __( 'Invalid template type.', 'header-footer-elementor' ), [ 'status' => 400 ] );
			}

			$updated = update_post_meta( $template_id, 'ehf_template_type', $new_type );

			if ( false === $updated && $old_type !== $new_type ) {
				return new WP_Error(
					'hfe_update_failed',
					__( 'Failed to update template type.', 'header-footer-elementor' ),
					[ 'status' => 500 ]
				);
			}
		}

		// Update status if provided.
		if ( $has_status ) {
			$new_status = sanitize_text_field( $input['status'] );

			if ( ! in_array( $new_status, [ 'publish', 'draft' ], true ) ) {
				return new WP_Error( 'hfe_invalid_status', __( 'Status must be publish or draft.', 'header-footer-elementor' ), [ 'status' => 400 ] );
			}

			if ( $old_status !== $new_status ) {
				$result = wp_update_post(
					[
						'ID'          => $template_id,
						'post_status' => $new_status,
					],
					true
				);

				if ( is_wp_error( $result ) ) {
					return new WP_Error(
						'hfe_update_failed',
						__( 'Failed to update template status.', 'header-footer-elementor' ),
						[ 'status' => 500 ]
					);
				}
			}
		}

		return [
			'success'     => true,
			'template_id' => $template_id,
			'old_type'    => $old_type,
			'new_type'    => $new_type,
			'old_status'  => $old_status,
			'new_status'  => $new_status,
		];
	}
}
