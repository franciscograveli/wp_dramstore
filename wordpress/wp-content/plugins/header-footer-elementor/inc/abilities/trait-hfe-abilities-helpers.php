<?php
/**
 * Shared Abilities Helpers.
 *
 * Provides common utilities used across multiple ability classes:
 * template validation, theme compatibility resolution, and location normalization.
 *
 * @package header-footer-elementor
 * @since 2.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Trait HFE_Abilities_Helpers
 *
 * Shared helper methods for HFE ability classes.
 *
 * @since 2.9.0
 */
trait HFE_Abilities_Helpers {

	/**
	 * Validate that a post ID belongs to the elementor-hf CPT.
	 *
	 * @since 2.9.0
	 *
	 * @param int $post_id Post ID to validate.
	 * @return WP_Post|WP_Error The post object or error.
	 */
	protected function validate_template( $post_id ) {
		$post = get_post( $post_id );

		if ( ! $post || 'elementor-hf' !== $post->post_type ) {
			return new WP_Error(
				'hfe_invalid_template',
				__( 'Template not found or is not an HFE template.', 'header-footer-elementor' ),
				[ 'status' => 404 ]
			);
		}

		return $post;
	}

	/**
	 * Validate that a post ID belongs to any Elementor-enabled post type.
	 *
	 * Accepts pages, posts, HFE templates, and any custom post type that
	 * Elementor supports (configured in Elementor > Settings > General).
	 *
	 * @since 2.9.0
	 *
	 * @param int $post_id Post ID to validate.
	 * @return WP_Post|WP_Error The post object or error.
	 */
	protected function validate_elementor_post( $post_id ) {
		$post = get_post( $post_id );

		if ( ! $post ) {
			return new WP_Error(
				'hfe_post_not_found',
				__( 'Post not found.', 'header-footer-elementor' ),
				[ 'status' => 404 ]
			);
		}

		$is_supported = false;

		// Always accept HFE templates.
		if ( 'elementor-hf' === $post->post_type ) {
			$is_supported = true;
		} else {
			// Accept any post type Elementor supports.
			$supported_cpts = get_option( 'elementor_cpt_support', [ 'page', 'post' ] );

			if ( is_array( $supported_cpts ) && in_array( $post->post_type, $supported_cpts, true ) ) {
				$is_supported = true;
			} elseif ( 'builder' === get_post_meta( $post->ID, '_elementor_edit_mode', true ) ) {
				// Accept any post already edited with Elementor.
				$is_supported = true;
			}
		}

		if ( ! $is_supported ) {
			return new WP_Error(
				'hfe_unsupported_post_type',
				sprintf(
					/* translators: %s: post type slug */
					__( 'Post type "%s" is not supported by Elementor.', 'header-footer-elementor' ),
					$post->post_type
				),
				[ 'status' => 400 ]
			);
		}

		// Object-level authorization: a capability check on the ability is not
		// enough — verify the current user is allowed to edit THIS specific post.
		// `edit_post` is a meta capability that respects per-object ownership,
		// so an Author cannot tamper with pages/templates authored by others.
		if ( ! current_user_can( 'edit_post', $post->ID ) ) {
			return new WP_Error(
				'hfe_forbidden',
				__( 'You are not allowed to edit this post.', 'header-footer-elementor' ),
				[ 'status' => 403 ]
			);
		}

		return $post;
	}

	/**
	 * Resolve the current theme compatibility method.
	 *
	 * Reads the hfe_is_theme_supported and hfe_compatibility_option options
	 * to determine whether the theme uses native support, CSS override, or default fallback.
	 *
	 * @since 2.9.0
	 *
	 * @return string 'native', 'css-override', or 'default'.
	 */
	protected function resolve_compat_method() {
		$theme_supported = get_option( 'hfe_is_theme_supported', false );
		$compat_option   = get_option( 'hfe_compatibility_option', '1' );

		if ( $theme_supported ) {
			return 'native';
		}

		return '2' === $compat_option ? 'css-override' : 'default';
	}

	/**
	 * Normalize location meta into a consistent object structure.
	 *
	 * Post meta stores locations as { rule: [...], specific: [...] }.
	 * Ensures both keys exist with proper array values for schema compliance.
	 *
	 * @since 2.9.0
	 *
	 * @param mixed $locations Raw meta value.
	 * @return array Normalized location object with rule and specific keys.
	 */
	protected function normalize_locations( $locations ) {
		if ( empty( $locations ) || ! is_array( $locations ) ) {
			return [
				'rule'     => [],
				'specific' => [],
			];
		}

		return [
			'rule'     => ! empty( $locations['rule'] ) && is_array( $locations['rule'] )
				? array_values( $locations['rule'] )
				: [],
			'specific' => ! empty( $locations['specific'] ) && is_array( $locations['specific'] )
				? array_values( $locations['specific'] )
				: [],
		];
	}

	/**
	 * Guard write operations behind the AI Tools "allow modifications" setting.
	 *
	 * Shared by every handler that creates, edits, or deletes content so the
	 * check stays in one place. Returns boolean true when modifications are
	 * permitted; callers should bail with the returned WP_Error otherwise.
	 *
	 * @since 2.9.0
	 *
	 * @return true|WP_Error True when allowed, WP_Error (403) when disabled.
	 */
	protected function check_modifications_allowed() {
		$settings = get_option( 'uae_mcp_settings', [] );

		if ( empty( $settings['allow_modifications'] ) ) {
			return new \WP_Error(
				'hfe_modifications_disabled',
				__( 'Modifications disabled in AI Tools settings.', 'header-footer-elementor' ),
				[ 'status' => 403 ]
			);
		}

		return true;
	}
}
