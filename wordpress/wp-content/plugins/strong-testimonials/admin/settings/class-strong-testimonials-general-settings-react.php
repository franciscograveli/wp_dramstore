<?php
/**
 * Class Strong_Testimonials_General_Settings_React
 *
 * Provides tabs, subtabs and field configuration for the React-based settings page.
 * Follows the same structural pattern as Modula's settings class.
 *
 * @since 3.2.23
 */
class Strong_Testimonials_General_Settings_React {

	// =========================================================================
	// Constants
	// =========================================================================

	/** WordPress option names */
	const OPTION_GENERAL       = 'wpmtst_options';
	const OPTION_ADVANCED      = 'strong_testimonials_advanced_settings';
	const OPTION_ROLES         = 'wpmtst_access_options';
	const OPTION_MAILCHIMP     = 'wpmtst_mailchimp_options';
	const OPTION_ASSIGNMENT    = 'wpmtst_assignment';
	const OPTION_PROPERTIES    = 'wpmtst_properties';
	const OPTION_REVIEW_MARKUP = 'wpmtst_review_markup';

	/** Field type identifiers (must match React FieldRenderer switch cases) */
	const FIELD_TYPE_TOGGLE    = 'toggle';
	const FIELD_TYPE_TEXT      = 'text';
	const FIELD_TYPE_NUMBER    = 'number';
	const FIELD_TYPE_COMBO     = 'combo';
	const FIELD_TYPE_PARAGRAPH = 'paragraph';
	const FIELD_TYPE_ROLE      = 'role';
	const FIELD_TYPE_SELECT    = 'select';

	// =========================================================================
	// Locking / Plan Helpers
	// =========================================================================

	/**
	 * Minimum plan label (display-ready, capitalised) for each extension slug.
	 * Used to build "Feature available starting with the X plan." messages.
	 */
	private static $extension_min_plan_labels = array(
		'strong-testimonials-country-selector' => 'Basic',
		'strong-testimonials-assignment'       => 'Plus',
		'strong-testimonials-properties'       => 'Plus',
		'strong-testimonials-review-markup'    => 'Plus',
		'strong-testimonials-advanced-views'   => 'Plus',
		'strong-testimonials-captcha'          => 'Plus',
		'strong-testimonials-pro-templates'    => 'Plus',
		'strong-testimonials-importer'         => 'Plus',
		'strong-testimonials-infinite-scroll'  => 'Business',
		'strong-testimonials-emails'           => 'Business',
		'strong-testimonials-filters'          => 'Business',
		'strong-testimonials-role-management'  => 'Business',
		'strong-testimonials-mailchimp'        => 'Business',
		'strong-testimonials-custom-fields'    => 'Business',
		'strong-testimonials-multiple-forms'   => 'Business',
	);

	/**
	 * Build the locked/badge/message triplet for an extension-gated subtab.
	 *
	 * Two distinct messages are returned:
	 *  • Plan doesn't cover the extension (no Pro, no license, or wrong tier):
	 *      "Feature available starting with the X plan."
	 *  • Plan covers it but extension is not activated:
	 *      The caller-supplied $activate_msg.
	 *
	 * @param string      $slug         Extension slug, e.g. 'strong-testimonials-role-management'.
	 * @param string      $activate_msg Message to show when the plan covers the extension but it is inactive.
	 * @param object|null $ext          Pro Extensions instance, or null when Pro is not installed.
	 * @return array{ locked: bool, badge: string, message: string }
	 */
	private function build_lock_info( $slug, $activate_msg, $ext ) {
		$plan_label = isset( self::$extension_min_plan_labels[ $slug ] )
			? self::$extension_min_plan_labels[ $slug ]
			: 'Pro';

		$is_active = $ext && $ext->extension_enabled( $slug );
		$in_plan   = $ext && $ext->extension_in_plan( $slug );

		if ( $is_active ) {
			return array(
				'locked'  => false,
				'badge'   => '',
				'message' => '',
			);
		}

		if ( $in_plan ) {
			return array(
				'locked'  => true,
				'badge'   => 'Pro',
				'message' => $activate_msg,
			);
		}

		return array(
			'locked'  => true,
			'badge'   => $plan_label,
			'message' => sprintf(
				/* translators: %s: plan name, e.g. "Business" */
				esc_html__( 'Feature available starting with the %s plan.', 'strong-testimonials' ),
				$plan_label
			),
		);
	}

	// =========================================================================
	// Helper Methods — Option Value Retrieval
	// =========================================================================

	/**
	 * Retrieve a single key from a saved option array, with a fallback default.
	 *
	 * @param array  $options Saved option array (result of get_option).
	 * @param string $key     Key to look up.
	 * @param mixed  $default Fallback when the key is absent.
	 *
	 * @return mixed
	 */
	private function get_field_value( $options, $key, $default = null ) {
		if ( isset( $options[ $key ] ) ) {
			return $options[ $key ];
		}
		return $default;
	}

	// =========================================================================
	// Helper Methods — Field Builders
	// =========================================================================

	/**
	 * Build a generic field definition array.
	 *
	 * @param string $type Field type constant.
	 * @param string $name Field name (matches the WP option key).
	 * @param array  $args Additional field properties.
	 *
	 * @return array
	 */
	private function build_field( $type, $name, $args = array() ) {
		return array_merge(
			array(
				'type' => $type,
				'name' => $name,
			),
			$args
		);
	}

	/**
	 * Build a toggle (boolean checkbox) field.
	 *
	 * @param string $name    WP option key.
	 * @param string $label   Field label shown in the UI.
	 * @param bool   $default Current saved value (used as the React defaultValue).
	 * @param array  $args    Extra properties (description, conditions, …).
	 *
	 * @return array
	 */
	private function build_toggle_field( $name, $label, $default, $args = array() ) {
		return $this->build_field(
			self::FIELD_TYPE_TOGGLE,
			$name,
			array_merge(
				array(
					'label'        => $label,
					'default'      => $default,
					'sanitization' => array( 'bool' ),
				),
				$args
			)
		);
	}

	/**
	 * Build a text input field.
	 *
	 * @param string $name    WP option key.
	 * @param string $label   Field label.
	 * @param string $default Current saved value.
	 * @param array  $args    Extra properties.
	 *
	 * @return array
	 */
	private function build_text_field( $name, $label, $default, $args = array() ) {
		return $this->build_field(
			self::FIELD_TYPE_TEXT,
			$name,
			array_merge(
				array(
					'label'        => $label,
					'default'      => $default,
					'sanitization' => array( 'text' ),
				),
				$args
			)
		);
	}

	/**
	 * Build a number input field.
	 *
	 * @param string $name    WP option key.
	 * @param string $label   Field label.
	 * @param int    $default Current saved value.
	 * @param array  $args    Extra properties (min, max, …).
	 *
	 * @return array
	 */
	private function build_number_field( $name, $label, $default, $args = array() ) {
		return $this->build_field(
			self::FIELD_TYPE_NUMBER,
			$name,
			array_merge(
				array(
					'label'        => $label,
					'default'      => $default,
					'sanitization' => array( 'number' ),
				),
				$args
			)
		);
	}

	/**
	 * Build a select field
	 *
	 * @param string $name    Field name.
	 * @param string $label   Field label.
	 * @param array  $options Select options array.
	 * @param mixed  $default Default value.
	 * @param array  $args    Additional field arguments.
	 *
	 * @return array Field definition array
	 *
	 * @since 2.11.0
	 */
	private function build_select_field( $name, $label, $options, $default, $args = array() ) {
		$sanitization = array();
		if ( ! empty( $options ) && is_array( $options ) ) {
			if ( isset( $options[0]['value'] ) ) {
				// Options are in format array( array( 'value' => ..., 'label' => ... ) ).
				$sanitization = array( 'enum' => array_column( $options, 'value' ) );
			} else {
				// Options are simple key-value pairs.
				$sanitization = array( 'enum' => array_keys( $options ) );
			}
		}

		return $this->build_field(
			self::FIELD_TYPE_SELECT,
			$name,
			array_merge(
				array(
					'label'        => $label,
					'options'      => $options,
					'default'      => $default,
					'sanitization' => $sanitization,
				),
				$args
			)
		);
	}


	/**
	 * Build a combo field — groups multiple related fields into a single row.
	 *
	 * @param array $fields Array of field definitions.
	 * @param array $args   Extra properties.
	 *
	 * @return array
	 */
	private function build_combo_field( $fields, $args = array() ) {
		return $this->build_field(
			self::FIELD_TYPE_COMBO,
			'',
			array_merge(
				array( 'fields' => $fields ),
				$args
			)
		);
	}

	/**
	 * Build a paragraph field — used for section headings or informational text.
	 *
	 * @param string $name        Unique identifier (not an option key).
	 * @param string $label       Heading text.
	 * @param string $description Body text / description.
	 * @param array  $args        Extra properties.
	 *
	 * @return array
	 */
	private function build_paragraph_field( $name, $label, $description, $args = array() ) {
		return $this->build_field(
			self::FIELD_TYPE_PARAGRAPH,
			$name,
			array_merge(
				array(
					'label'       => $label,
					'description' => $description,
				),
				$args
			)
		);
	}

	// =========================================================================
	// Private Methods — Settings Tab Builders
	// =========================================================================

	/**
	 * Build the "Comments" subtab config for the General tab.
	 *
	 * @return array
	 */
	private function get_general_comments() {
		$options          = get_option( self::OPTION_GENERAL, array() );
		$support_comments = $this->get_field_value( $options, 'support_comments', false );

		return array(
			'option' => self::OPTION_GENERAL,
			'fields' => array(
				$this->build_toggle_field(
					'support_comments',
					esc_html__( 'Enable comments for testimonials', 'strong-testimonials' ),
					(bool) $support_comments,
					array(
						'description' => esc_html__( 'This will only be visible in the single testimonial page.', 'strong-testimonials' ),
					)
				),
			),
		);
	}

	/**
	 * Build the "Embed" subtab config for the General tab.
	 *
	 * @return array
	 */
	private function get_general_embed() {
		$options     = get_option( self::OPTION_GENERAL, array() );
		$embed_width = $this->get_field_value( $options, 'embed_width', '' );

		$fields = array(
			$this->build_number_field(
				'embed_width',
				esc_html__( 'Embedded link width in pixels', 'strong-testimonials' ),
				(int) $embed_width,
				array(
					'description' => esc_html__( 'For embedded links (YouTube, Twitter, etc.) set the frame width in pixels. Leave at 0 for default width (usually 100% for videos).', 'strong-testimonials' ),
					'min'         => 0,
				)
			),
		);

		return array(
			'option' => self::OPTION_GENERAL,
			'fields' => $fields,
		);
	}

	/**
	 * Build the "Link Control" subtab config for the General tab.
	 *
	 * @return array
	 */
	private function get_general_link_control() {
		$options    = get_option( self::OPTION_GENERAL, array() );
		$nofollow   = $this->get_field_value( $options, 'nofollow', true );
		$noopener   = $this->get_field_value( $options, 'noopener', true );
		$noreferrer = $this->get_field_value( $options, 'noreferrer', true );

		return array(
			'option' => self::OPTION_GENERAL,
			'fields' => array(
				$this->build_toggle_field(
					'nofollow',
					esc_html__( 'Add nofollow', 'strong-testimonials' ),
					(bool) $nofollow,
					array(
						'description' => esc_html__( 'Add rel="nofollow" attribute to URL custom fields.', 'strong-testimonials' ),
					)
				),
				$this->build_toggle_field(
					'noopener',
					esc_html__( 'Add noopener', 'strong-testimonials' ),
					(bool) $noopener,
					array(
						'description' => esc_html__( 'Add rel="noopener" attribute to URL custom fields.', 'strong-testimonials' ),
					)
				),
				$this->build_toggle_field(
					'noreferrer',
					esc_html__( 'Add noreferrer', 'strong-testimonials' ),
					(bool) $noreferrer,
					array(
						'description' => esc_html__( 'Add rel="noreferrer" attribute to URL custom fields.', 'strong-testimonials' ),
					)
				),
			),
		);
	}

	/**
	 * Build the "Debug" subtab config for the General tab.
	 * Contains settings that control wp-admin behaviour.
	 *
	 * @return array
	 */
	private function get_general_debug() {
		$options = get_option( self::OPTION_ADVANCED, array() );

		$debug_log = $this->get_field_value( $options, 'debug_log', false );

		return array(
			'option' => self::OPTION_ADVANCED,
			'fields' => array(
				$this->build_toggle_field(
					'debug_log',
					esc_html__( 'Enable debug log', 'strong-testimonials' ),
					(bool) $debug_log,
					array(
						'description' => sprintf(
							'%s <a href="%s">%s</a>',
							esc_html__( "Creates debug logs for Strong Testimonial module and it's addons.", 'strong-testimonials' ),
							esc_url( admin_url( 'edit.php?post_type=wpm-testimonial&page=strong-testimonials-logs' ) ),
							esc_html__( 'View logs', 'strong-testimonials' )
						),
					)
				),
			),
		);
	}

	/**
	 * Build the Role Management subtab config.
	 *
	 * Iterates all non-admin WordPress roles and maps each one to a `role` field
	 * containing the testimonial-specific capabilities as child toggle fields.
	 * The default value for every toggle is read directly from the current WP role
	 * object via has_cap(), so the React form always reflects the live state.
	 *
	 * @return array
	 */
	private function get_roles() {
		global $wp_roles;

		$capabilities = array(
			'read_testimonial'            => esc_html__( 'Read Own Testimonial', 'strong-testimonials' ),
			'edit_testimonial'            => esc_html__( 'Edit Own Testimonial', 'strong-testimonials' ),
			'delete_testimonial'          => esc_html__( 'Delete Own Testimonial', 'strong-testimonials' ),
			'read_private_testimonials'   => esc_html__( 'Read Private Testimonials', 'strong-testimonials' ),
			'edit_testimonials'           => esc_html__( 'Edit Any Testimonials', 'strong-testimonials' ),
			'edit_other_testimonials'     => esc_html__( 'Edit Other Testimonials', 'strong-testimonials' ),
			'publish_testimonials'        => esc_html__( 'Publish Any Testimonials', 'strong-testimonials' ),
			'delete_testimonials'         => esc_html__( 'Delete Any Testimonials', 'strong-testimonials' ),
			'delete_others_testimonials'  => esc_html__( 'Delete Other Testimonials', 'strong-testimonials' ),
			'strong_testimonials_options' => esc_html__( 'Manage Settings', 'strong-testimonials' ),
		);

		$roles_array = array();

		foreach ( $wp_roles->roles as $role_key => $wp_role ) {
			if ( 'administrator' === $role_key ) {
				continue;
			}

			$role = get_role( $role_key );

			$role_field = array(
				'type'    => self::FIELD_TYPE_ROLE,
				'name'    => $role_key . '.enabled',
				'label'   => translate_user_role( $wp_role['name'] ),
				'default' => $this->is_role_enabled( $role, $capabilities ),
				'fields'  => array(),
			);

			foreach ( $capabilities as $capability => $capability_name ) {
				$role_field['fields'][] = $this->build_toggle_field(
					$role_key . '.' . $capability,
					$capability_name,
					(bool) $role->has_cap( $capability )
				);
			}

			// Upload Files is only relevant for lower-privilege roles.
			if ( in_array( $role_key, array( 'subscriber', 'contributor' ), true ) ) {
				$role_field['fields'][] = $this->build_toggle_field(
					$role_key . '.upload_files',
					esc_html__( 'Upload Files', 'strong-testimonials' ),
					(bool) $role->has_cap( 'upload_files' )
				);
			}

			$roles_array[] = $role_field;
		}

		return array(
			'option' => self::OPTION_ROLES,
			'grid'   => true,
			'fields' => $roles_array,
		);
	}

	/**
	 * Check whether a role currently holds at least one testimonial capability.
	 * Used to set the master "Enable" toggle default in the Role field header.
	 *
	 * @param WP_Role $role         WordPress role object.
	 * @param array   $capabilities Capabilities map (capability => label).
	 *
	 * @return bool
	 */
	private function is_role_enabled( $role, $capabilities ) {
		foreach ( array_keys( $capabilities ) as $cap ) {
			if ( $role->has_cap( $cap ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Build the "Admin" subtab config for the Mailchimp tab.
	 * Contains settings that control wp-admin behaviour.
	 *
	 * @return array
	 */
	private function get_mailchimp_admin() {
		$options = get_option( self::OPTION_MAILCHIMP, array() );

		$api_key   = $this->get_field_value( $options, 'mailchimp_api_key', '' );
		$connected = false;
		if ( class_exists( 'Strong_Testimonials_Pro\Extensions\Mailchimp\Mailchimp' ) ) {
			$connected = Strong_Testimonials_Pro\Extensions\Mailchimp\Mailchimp::get_status();
		}
		$status_class = $connected ? 'positive' : 'negative';
		$status_text  = $connected
		? esc_html__( 'CONNECTED', 'strong-testimonials' )
		: esc_html__( 'NOT CONNECTED', 'strong-testimonials' );
		return array(
			'option' => self::OPTION_MAILCHIMP,
			'fields' => array(
				$this->build_paragraph_field(
					'',
					esc_html__( 'Status', 'strong-testimonials' ),
					wp_kses_post( '<span class="status ' . esc_attr( $status_class ) . '">' . $status_text . '</span>' ),
				),
				$this->build_text_field(
					'mailchimp_api_key',
					esc_html__( 'API Key', 'strong-testimonials' ),
					(string) $api_key,
					array(
						/* translators: %1$s and %2$s are opening and closing HTML anchor tags for the Mailchimp API key link */
						'description' => sprintf( esc_html__( 'The API key for connecting with your Mailchimp account. %1$s Get your API key here %2$s.', 'strong-testimonials' ), '<a target="_blank" href="https://admin.mailchimp.com/account/api">', '</a>' ),
					)
				),
			),
		);
	}


	/**
	 * Build the "Admin" subtab config for the Mailchimp tab.
	 * Contains settings that control wp-admin behaviour.
	 *
	 * @return array
	 */
	private function get_assignment_fields() {
		$options = get_option( self::OPTION_ASSIGNMENT, array() );

		$assignees  = $this->get_field_value( $options, 'assignees', '' );
		$selection  = $this->get_field_value( $options, 'selection', '' );
		$post_types = get_post_types( array( 'public' => true ), 'objects' );

		$fields = array();
		foreach ( $post_types as $post_type => $post_type_object ) {
			$fields[] = $this->build_toggle_field(
				'assignees.' . $post_type,
				sprintf( '%s (%s)', $post_type_object->label, $post_type_object->name ),
				isset( $options['assignees'][ $post_type ] ) ? $options['assignees'][ $post_type ] : false
			);
		}

		return array(
			'option' => self::OPTION_ASSIGNMENT,
			'fields' => array(
				$this->build_paragraph_field(
					'',
					esc_html__( 'Content Types', 'strong-testimonials' ),
					esc_html__( 'Allow testimonials to be assigned to these content types:', 'strong-testimonials' ),
				),
				$this->build_combo_field( $fields ),
				$this->build_select_field(
					'selection',
					esc_html__( 'Selection', 'strong-testimonials' ),
					array(
						array(
							'label' => esc_html__( 'Single selections only', 'strong-testimonials' ),
							'value' => 'single',
						),
						array(
							'label' => esc_html__( 'Multiple selections allowed', 'strong-testimonials' ),
							'value' => 'multiple',
						),
					),
					$selection,
					array( 'size' => 'small' )
				),
			),
		);
	}

	/**
	 * Build a placeholder config for a locked subtab (provides something for the blur overlay to cover).
	 *
	 * @return array
	 */
	private function get_locked_placeholder() {
		return array(
			'option' => '',
			'fields' => array(
				$this->build_paragraph_field(
					'locked_placeholder',
					'',
					esc_html__( 'Upgrade your plan to unlock this feature.', 'strong-testimonials' )
				),
			),
		);
	}

	/**
	 * Build the Properties subtabs config.
	 * Always returns the full 5-section config; locked when the extension is inactive.
	 *
	 * @param bool $is_active Whether the Properties extension is enabled.
	 * @return array  Subtabs array keyed by subtab slug.
	 */
	private function get_properties_subtabs( $lock_info ) {
		// Hardcoded defaults — mirrors Options_Defaults in the Pro plugin.
		$defaults = array(
			'cpt' => array(
				'labels'              => array(
					'name'          => _x( 'Testimonials', 'post type general name', 'strong-testimonials' ),
					'singular_name' => _x( 'Testimonial', 'post type singular name', 'strong-testimonials' ),
				),
				'menu_icon'           => 'dashicons-editor-quote',
				'menu_position'       => 20,
				'show_in_admin_bar'   => true,
				'show_in_nav_menus'   => true,
				'can_export'          => true,
				'publicly_queryable'  => false,
				'exclude_from_search' => false,
				'supports'            => array( 'title', 'excerpt', 'editor', 'thumbnail', 'page-attributes', 'comments' ),
				'rewrite'             => array(
					'slug'       => _x( 'testimonial', 'slug', 'strong-testimonials' ),
					'with_front' => true,
					'feeds'      => false,
					'pages'      => true,
				),
			),
			'tax' => array(
				'labels'             => array(
					'name'          => __( 'Testimonial Categories', 'strong-testimonials' ),
					'singular_name' => __( 'Testimonial Category', 'strong-testimonials' ),
				),
				'publicly_queryable' => true,
				'rewrite'            => array(
					'slug'         => _x( 'testimonial-category', 'slug', 'strong-testimonials' ),
					'with_front'   => true,
					'hierarchical' => false,
				),
			),
		);

		$options = get_option( self::OPTION_PROPERTIES, array() );
		$locked  = $lock_info['locked'];
		$badge   = $lock_info['badge'];
		$message = $lock_info['message'];

		$cpt = wp_parse_args(
			isset( $options['cpt'] ) ? $options['cpt'] : array(),
			$defaults['cpt']
		);
		$tax = wp_parse_args(
			isset( $options['tax'] ) ? $options['tax'] : array(),
			$defaults['tax']
		);

		$cpt_labels      = wp_parse_args(
			isset( $cpt['labels'] ) ? $cpt['labels'] : array(),
			$defaults['cpt']['labels']
		);
		$tax_labels      = wp_parse_args(
			isset( $tax['labels'] ) ? $tax['labels'] : array(),
			$defaults['tax']['labels']
		);
		$cpt_rewrite     = wp_parse_args(
			isset( $cpt['rewrite'] ) ? $cpt['rewrite'] : array(),
			$defaults['cpt']['rewrite']
		);
		$cpt_query_var   = wp_parse_args(
			isset( $cpt['query_var'] ) ? $cpt['query_var'] : array(),
			array(
				'on'    => false,
				'using' => 'default',
				'name'  => 'testimonial',
			)
		);
		$cpt_has_archive = wp_parse_args(
			isset( $cpt['has_archive'] ) ? $cpt['has_archive'] : array(),
			array(
				'on'    => false,
				'using' => 'current',
				'slug'  => 'testimonial',
			)
		);
		$tax_rewrite     = wp_parse_args(
			isset( $tax['rewrite'] ) ? $tax['rewrite'] : array(),
			$defaults['tax']['rewrite']
		);
		$tax_query_var   = wp_parse_args(
			isset( $tax['query_var'] ) ? $tax['query_var'] : array(),
			array(
				'on'    => false,
				'using' => 'default',
			)
		);

		// Scalar booleans.
		$cpt_show_in_admin_bar   = (bool) ( isset( $cpt['show_in_admin_bar'] ) ? $cpt['show_in_admin_bar'] : true );
		$cpt_show_in_nav_menus   = (bool) ( isset( $cpt['show_in_nav_menus'] ) ? $cpt['show_in_nav_menus'] : true );
		$cpt_can_export          = (bool) ( isset( $cpt['can_export'] ) ? $cpt['can_export'] : true );
		$cpt_publicly_queryable  = (bool) ( isset( $cpt['publicly_queryable'] ) ? $cpt['publicly_queryable'] : false );
		$cpt_exclude_from_search = (bool) ( isset( $cpt['exclude_from_search'] ) ? $cpt['exclude_from_search'] : false );
		$tax_publicly_queryable  = (bool) ( isset( $tax['publicly_queryable'] ) ? $tax['publicly_queryable'] : true );

		// Nested booleans.
		$cpt_query_var_on         = isset( $cpt_query_var['on'] ) && (bool) $cpt_query_var['on'];
		$cpt_rewrite_on           = isset( $cpt_rewrite['on'] ) && (bool) $cpt_rewrite['on'];
		$cpt_rewrite_with_front   = (bool) ( isset( $cpt_rewrite['with_front'] ) ? $cpt_rewrite['with_front'] : true );
		$cpt_rewrite_feeds        = (bool) ( isset( $cpt_rewrite['feeds'] ) ? $cpt_rewrite['feeds'] : false );
		$cpt_rewrite_pages        = (bool) ( isset( $cpt_rewrite['pages'] ) ? $cpt_rewrite['pages'] : true );
		$cpt_has_archive_on       = isset( $cpt_has_archive['on'] ) && (bool) $cpt_has_archive['on'];
		$tax_query_var_on         = isset( $tax_query_var['on'] ) && (bool) $tax_query_var['on'];
		$tax_rewrite_on           = isset( $tax_rewrite['on'] ) && (bool) $tax_rewrite['on'];
		$tax_rewrite_with_front   = (bool) ( isset( $tax_rewrite['with_front'] ) ? $tax_rewrite['with_front'] : true );
		$tax_rewrite_hierarchical = (bool) ( isset( $tax_rewrite['hierarchical'] ) ? $tax_rewrite['hierarchical'] : false );
		$cpt_supports             = isset( $cpt['supports'] ) ? $cpt['supports'] : $defaults['cpt']['supports'];

		// Shared dropdown option lists.
		$menu_position_options   = array(
			array(
				'value' => 5,
				'label' => __( 'below Posts', 'strong-testimonials' ),
			),
			array(
				'value' => 10,
				'label' => __( 'below Media', 'strong-testimonials' ),
			),
			array(
				'value' => 15,
				'label' => __( 'below Links', 'strong-testimonials' ),
			),
			array(
				'value' => 20,
				'label' => __( 'below Pages', 'strong-testimonials' ),
			),
			array(
				'value' => 25,
				'label' => __( 'below Comments', 'strong-testimonials' ),
			),
			array(
				'value' => 60,
				'label' => __( 'below first separator', 'strong-testimonials' ),
			),
			array(
				'value' => 65,
				'label' => __( 'below Plugins', 'strong-testimonials' ),
			),
			array(
				'value' => 70,
				'label' => __( 'below Users', 'strong-testimonials' ),
			),
			array(
				'value' => 75,
				'label' => __( 'below Tools', 'strong-testimonials' ),
			),
			array(
				'value' => 80,
				'label' => __( 'below Settings', 'strong-testimonials' ),
			),
			array(
				'value' => 100,
				'label' => __( 'below second separator', 'strong-testimonials' ),
			),
		);
		$query_var_using_options = array(
			array(
				'value' => 'default',
				'label' => __( 'using preset', 'strong-testimonials' ),
			),
			array(
				'value' => 'custom',
				'label' => __( 'using custom', 'strong-testimonials' ),
			),
		);
		$archive_using_options   = array(
			array(
				'value' => 'current',
				'label' => __( 'using current slug', 'strong-testimonials' ),
			),
			array(
				'value' => 'custom',
				'label' => __( 'using custom slug', 'strong-testimonials' ),
			),
		);

		$base = array(
			'option'        => self::OPTION_PROPERTIES,
			'remove_button' => true,
		);

		// ----------------------------------------------------------------
		// Admin Options
		// ----------------------------------------------------------------
		$admin_options = array_merge(
			$base,
			array(
				'restore_defaults' => array(
					'cpt' => array(
						'menu_icon'         => $defaults['cpt']['menu_icon'],
						'menu_position'     => $defaults['cpt']['menu_position'],
						'show_in_admin_bar' => $defaults['cpt']['show_in_admin_bar'],
						'show_in_nav_menus' => $defaults['cpt']['show_in_nav_menus'],
						'can_export'        => $defaults['cpt']['can_export'],
					),
				),
				'fields'           => array(
					array(
						'type'    => 'dashicons',
						'name'    => 'cpt.menu_icon',
						'label'   => __( 'Menu icon', 'strong-testimonials' ),
						'default' => isset( $cpt['menu_icon'] ) ? $cpt['menu_icon'] : 'dashicons-editor-quote',
					),
					array(
						'type'    => 'select',
						'name'    => 'cpt.menu_position',
						'label'   => __( 'Menu position', 'strong-testimonials' ),
						'options' => $menu_position_options,
						'default' => isset( $cpt['menu_position'] ) ? (int) $cpt['menu_position'] : 20,
					),
					array(
						'type'        => 'toggle',
						'name'        => 'cpt.show_in_admin_bar',
						'label'       => __( 'Admin bar', 'strong-testimonials' ),
						'description' => __( 'Make testimonials available in the New menu in the admin bar', 'strong-testimonials' ),
						'default'     => $cpt_show_in_admin_bar,
					),
					array(
						'type'        => 'toggle',
						'name'        => 'cpt.show_in_nav_menus',
						'label'       => __( 'Navigation menus', 'strong-testimonials' ),
						'description' => __( 'Make testimonials available for selection in navigation menus', 'strong-testimonials' ),
						'default'     => $cpt_show_in_nav_menus,
					),
					array(
						'type'        => 'toggle',
						'name'        => 'cpt.can_export',
						'label'       => __( 'Export', 'strong-testimonials' ),
						'description' => __( 'Make testimonials available for export (on Tools menu)', 'strong-testimonials' ),
						'default'     => $cpt_can_export,
					),
				),
			)
		);

		// ----------------------------------------------------------------
		// Labels
		// ----------------------------------------------------------------
		$labels_config = array_merge(
			$base,
			array(
				'restore_defaults' => array(
					'cpt' => array(
						'labels' => array(
							'name'          => $defaults['cpt']['labels']['name'],
							'singular_name' => $defaults['cpt']['labels']['singular_name'],
						),
					),
					'tax' => array(
						'labels' => array(
							'name'          => $defaults['tax']['labels']['name'],
							'singular_name' => $defaults['tax']['labels']['singular_name'],
						),
					),
				),
				'fields'           => array(
					array(
						'type'    => 'text',
						'name'    => 'cpt.labels.name',
						'label'   => __( 'Plural name', 'strong-testimonials' ),
						'default' => $cpt_labels['name'],
					),
					array(
						'type'    => 'text',
						'name'    => 'cpt.labels.singular_name',
						'label'   => __( 'Singular name', 'strong-testimonials' ),
						'default' => $cpt_labels['singular_name'],
					),
					array(
						'type'    => 'text',
						'name'    => 'tax.labels.name',
						'label'   => __( 'Category plural name', 'strong-testimonials' ),
						'default' => $tax_labels['name'],
					),
					array(
						'type'    => 'text',
						'name'    => 'tax.labels.singular_name',
						'label'   => __( 'Category singular name', 'strong-testimonials' ),
						'default' => $tax_labels['singular_name'],
					),
				),
			)
		);

		// ----------------------------------------------------------------
		// Posts
		// ----------------------------------------------------------------
		$posts_config = array_merge(
			$base,
			array(
				'restore_defaults' => array(
					'cpt' => array(
						'publicly_queryable'  => false,
						'query_var'           => array(
							'on'    => false,
							'using' => 'default',
							'name'  => 'testimonial',
						),
						'rewrite'             => $defaults['cpt']['rewrite'],
						'has_archive'         => array(
							'on'    => false,
							'using' => 'current',
							'slug'  => 'testimonial',
						),
						'exclude_from_search' => $defaults['cpt']['exclude_from_search'],
					),
				),
				'fields'           => array(
					array(
						'type'        => 'toggle',
						'name'        => 'cpt.publicly_queryable',
						'label'       => __( 'Viewable', 'strong-testimonials' ),
						'description' => __( 'Publicly viewable by parameter or permalink', 'strong-testimonials' ),
						'default'     => $cpt_publicly_queryable,
					),
					array(
						'type'        => 'toggle',
						'name'        => 'cpt.query_var.on',
						'label'       => __( 'Parameter', 'strong-testimonials' ),
						'description' => __( 'Viewable by custom parameter. Will override default structure.', 'strong-testimonials' ),
						'default'     => $cpt_query_var_on,
					),
					array(
						'type'       => 'select',
						'name'       => 'cpt.query_var.using',
						'label'      => __( 'Using', 'strong-testimonials' ),
						'options'    => $query_var_using_options,
						'default'    => isset( $cpt_query_var['using'] ) ? $cpt_query_var['using'] : 'default',
						'conditions' => array(
							array(
								'field'      => 'cpt.query_var.on',
								'comparison' => '===',
								'value'      => true,
							),
						),
					),
					array(
						'type'       => 'text',
						'name'       => 'cpt.query_var.name',
						'label'      => __( 'Parameter name', 'strong-testimonials' ),
						'default'    => isset( $cpt_query_var['name'] ) ? $cpt_query_var['name'] : 'testimonial',
						'conditions' => array(
							array(
								'field'      => 'cpt.query_var.on',
								'comparison' => '===',
								'value'      => true,
							),
							array(
								'field'      => 'cpt.query_var.using',
								'comparison' => '===',
								'value'      => 'custom',
							),
						),
					),
					array(
						'type'        => 'toggle',
						'name'        => 'cpt.rewrite.on',
						'label'       => __( 'Permalink', 'strong-testimonials' ),
						'description' => __( 'Viewable by permalink. Will override default structure.', 'strong-testimonials' ),
						'default'     => $cpt_rewrite_on,
					),
					array(
						'type'       => 'text',
						'name'       => 'cpt.rewrite.slug',
						'label'      => __( 'Slug', 'strong-testimonials' ),
						'default'    => isset( $cpt_rewrite['slug'] ) ? $cpt_rewrite['slug'] : 'testimonial',
						'conditions' => array(
							array(
								'field'      => 'cpt.rewrite.on',
								'comparison' => '===',
								'value'      => true,
							),
						),
					),
					array(
						'type'        => 'toggle',
						'name'        => 'cpt.rewrite.with_front',
						'label'       => __( 'Front base', 'strong-testimonials' ),
						'description' => __( 'Use the same front base as the general permalink setting', 'strong-testimonials' ),
						'default'     => $cpt_rewrite_with_front,
						'conditions'  => array(
							array(
								'field'      => 'cpt.rewrite.on',
								'comparison' => '===',
								'value'      => true,
							),
						),
					),
					array(
						'type'        => 'toggle',
						'name'        => 'cpt.has_archive.on',
						'label'       => __( 'Archive page', 'strong-testimonials' ),
						'description' => __( 'Enable archive page', 'strong-testimonials' ),
						'default'     => $cpt_has_archive_on,
						'conditions'  => array(
							array(
								'field'      => 'cpt.rewrite.on',
								'comparison' => '===',
								'value'      => true,
							),
						),
					),
					array(
						'type'       => 'select',
						'name'       => 'cpt.has_archive.using',
						'label'      => __( 'Archive using', 'strong-testimonials' ),
						'options'    => $archive_using_options,
						'default'    => isset( $cpt_has_archive['using'] ) ? $cpt_has_archive['using'] : 'current',
						'conditions' => array(
							array(
								'field'      => 'cpt.has_archive.on',
								'comparison' => '===',
								'value'      => true,
							),
						),
					),
					array(
						'type'       => 'text',
						'name'       => 'cpt.has_archive.slug',
						'label'      => __( 'Archive slug', 'strong-testimonials' ),
						'default'    => isset( $cpt_has_archive['slug'] ) ? $cpt_has_archive['slug'] : 'testimonial',
						'conditions' => array(
							array(
								'field'      => 'cpt.has_archive.using',
								'comparison' => '===',
								'value'      => 'custom',
							),
						),
					),
					array(
						'type'        => 'toggle',
						'name'        => 'cpt.rewrite.feeds',
						'label'       => __( 'Feeds', 'strong-testimonials' ),
						'description' => __( 'Build a feed permalink structure', 'strong-testimonials' ),
						'default'     => $cpt_rewrite_feeds,
						'conditions'  => array(
							array(
								'field'      => 'cpt.has_archive.on',
								'comparison' => '===',
								'value'      => true,
							),
						),
					),
					array(
						'type'        => 'toggle',
						'name'        => 'cpt.rewrite.pages',
						'label'       => __( 'Pages', 'strong-testimonials' ),
						'description' => __( 'Allow pagination in the archive permalink structure', 'strong-testimonials' ),
						'default'     => $cpt_rewrite_pages,
						'conditions'  => array(
							array(
								'field'      => 'cpt.has_archive.on',
								'comparison' => '===',
								'value'      => true,
							),
						),
					),
					array(
						'type'        => 'toggle',
						'name'        => 'cpt.exclude_from_search',
						'label'       => __( 'Search', 'strong-testimonials' ),
						'description' => __( 'Exclude testimonials from front-end search results', 'strong-testimonials' ),
						'default'     => $cpt_exclude_from_search,
					),
				),
			)
		);

		// ----------------------------------------------------------------
		// Categories
		// ----------------------------------------------------------------
		$categories_config = array_merge(
			$base,
			array(
				'restore_defaults' => array(
					'tax' => array(
						'publicly_queryable' => $defaults['tax']['publicly_queryable'],
						'query_var'          => array(
							'on'    => false,
							'using' => 'default',
						),
						'rewrite'            => $defaults['tax']['rewrite'],
					),
				),
				'fields'           => array(
					array(
						'type'        => 'toggle',
						'name'        => 'tax.publicly_queryable',
						'label'       => __( 'Viewable', 'strong-testimonials' ),
						'description' => __( 'Publicly viewable by parameter or permalink', 'strong-testimonials' ),
						'default'     => $tax_publicly_queryable,
					),
					array(
						'type'        => 'toggle',
						'name'        => 'tax.query_var.on',
						'label'       => __( 'Parameter', 'strong-testimonials' ),
						'description' => __( 'Viewable by custom parameter.', 'strong-testimonials' ),
						'default'     => $tax_query_var_on,
					),
					array(
						'type'       => 'select',
						'name'       => 'tax.query_var.using',
						'label'      => __( 'Using', 'strong-testimonials' ),
						'options'    => $query_var_using_options,
						'default'    => isset( $tax_query_var['using'] ) ? $tax_query_var['using'] : 'default',
						'conditions' => array(
							array(
								'field'      => 'tax.query_var.on',
								'comparison' => '===',
								'value'      => true,
							),
						),
					),
					array(
						'type'        => 'toggle',
						'name'        => 'tax.rewrite.on',
						'label'       => __( 'Permalink', 'strong-testimonials' ),
						'description' => __( 'Viewable by permalink.', 'strong-testimonials' ),
						'default'     => $tax_rewrite_on,
					),
					array(
						'type'       => 'text',
						'name'       => 'tax.rewrite.slug',
						'label'      => __( 'Slug', 'strong-testimonials' ),
						'default'    => isset( $tax_rewrite['slug'] ) ? $tax_rewrite['slug'] : 'testimonial-category',
						'conditions' => array(
							array(
								'field'      => 'tax.rewrite.on',
								'comparison' => '===',
								'value'      => true,
							),
						),
					),
					array(
						'type'       => 'toggle',
						'name'       => 'tax.rewrite.with_front',
						'label'      => __( 'Front base', 'strong-testimonials' ),
						'default'    => $tax_rewrite_with_front,
						'conditions' => array(
							array(
								'field'      => 'tax.rewrite.on',
								'comparison' => '===',
								'value'      => true,
							),
						),
					),
					array(
						'type'        => 'toggle',
						'name'        => 'tax.rewrite.hierarchical',
						'label'       => __( 'Hierarchical', 'strong-testimonials' ),
						'description' => __( 'Use hierarchical URL structure for subcategories', 'strong-testimonials' ),
						'default'     => $tax_rewrite_hierarchical,
						'conditions'  => array(
							array(
								'field'      => 'tax.rewrite.on',
								'comparison' => '===',
								'value'      => true,
							),
						),
					),
				),
			)
		);

		// ----------------------------------------------------------------
		// Editor permissions
		// ----------------------------------------------------------------
		$editor_config = array_merge(
			$base,
			array(
				'restore_defaults' => array(
					'cpt' => array(
						'supports' => $defaults['cpt']['supports'],
					),
				),
				'fields'           => array(
					array(
						'type'    => 'checks',
						'name'    => 'cpt.supports',
						'label'   => __( 'Supported features', 'strong-testimonials' ),
						'default' => $cpt_supports,
						'options' => array(
							array(
								'value'       => 'title',
								'label'       => __( 'Title', 'strong-testimonials' ),
								'description' => __( 'The title field', 'strong-testimonials' ),
							),
							array(
								'value'       => 'editor',
								'label'       => __( 'Content', 'strong-testimonials' ),
								'description' => __( 'The content field', 'strong-testimonials' ),
							),
							array(
								'value'       => 'excerpt',
								'label'       => __( 'Excerpt', 'strong-testimonials' ),
								'description' => __( 'The excerpt field', 'strong-testimonials' ),
							),
							array(
								'value'       => 'thumbnail',
								'label'       => __( 'Featured Image', 'strong-testimonials' ),
								'description' => __( 'The featured image', 'strong-testimonials' ),
							),
							array(
								'value'       => 'page-attributes',
								'label'       => __( 'Page Attributes', 'strong-testimonials' ),
								'description' => __( 'The page attributes meta box', 'strong-testimonials' ),
							),
							array(
								'value'       => 'custom-fields',
								'label'       => __( 'Custom Fields', 'strong-testimonials' ),
								'description' => __( 'The custom fields meta box', 'strong-testimonials' ),
							),
							array(
								'value'       => 'comments',
								'label'       => __( 'Comments', 'strong-testimonials' ),
								'description' => __( 'The comments meta box', 'strong-testimonials' ),
							),
							array(
								'value'       => 'trackbacks',
								'label'       => __( 'Trackbacks', 'strong-testimonials' ),
								'description' => __( 'The trackbacks meta box', 'strong-testimonials' ),
							),
							array(
								'value'       => 'revisions',
								'label'       => __( 'Revisions', 'strong-testimonials' ),
								'description' => __( 'The revisions meta box', 'strong-testimonials' ),
							),
							array(
								'value'       => 'author',
								'label'       => __( 'Author', 'strong-testimonials' ),
								'description' => __( 'The author meta box', 'strong-testimonials' ),
							),
							array(
								'value'       => 'post-formats',
								'label'       => __( 'Post Formats', 'strong-testimonials' ),
								'description' => __( 'The post formats meta box', 'strong-testimonials' ),
							),
						),
					),
				),
			)
		);

		return array(
			'properties_admin'  => array(
				'label'   => esc_html__( 'Admin Options', 'strong-testimonials' ),
				'locked'  => $locked,
				'badge'   => $badge,
				'message' => $message,
				'config'  => $admin_options,
			),
			'properties_labels' => array(
				'label'   => esc_html__( 'Labels', 'strong-testimonials' ),
				'locked'  => $locked,
				'badge'   => $badge,
				'message' => $message,
				'config'  => $labels_config,
			),
			'properties_posts'  => array(
				'label'   => esc_html__( 'Posts', 'strong-testimonials' ),
				'locked'  => $locked,
				'badge'   => $badge,
				'message' => $message,
				'config'  => $posts_config,
			),
			'properties_cats'   => array(
				'label'   => esc_html__( 'Categories', 'strong-testimonials' ),
				'locked'  => $locked,
				'badge'   => $badge,
				'message' => $message,
				'config'  => $categories_config,
			),
			'properties_editor' => array(
				'label'   => esc_html__( 'Editor Permissions', 'strong-testimonials' ),
				'locked'  => $locked,
				'badge'   => $badge,
				'message' => $message,
				'config'  => $editor_config,
			),
		);
	}

	/**
	 * Build select options from custom fields filtered by one or more input_type values.
	 *
	 * @param string|array $filter_types  input_type value(s) to include. Empty string = all.
	 * @param array        $prepend       Options to prepend before the field list.
	 *
	 * @return array  Array of { value, label } option objects.
	 */
	private function get_custom_field_options( $filter_types, $prepend = array() ) {
		$options      = $prepend;
		$filter_types = $filter_types ? (array) $filter_types : array();

		if ( function_exists( 'wpmtst_get_custom_fields' ) ) {
			$custom_fields = array_diff_key( wpmtst_get_custom_fields(), array( 'email' => '' ) );
			foreach ( $custom_fields as $key => $field ) {
				if ( empty( $filter_types ) || in_array( $field['input_type'], $filter_types, true ) ) {
					$options[] = array(
						'value' => $key,
						'label' => isset( $field['label'] ) ? $field['label'] : $key,
					);
				}
			}
		}

		return $options;
	}

	/**
	 * Build the Review Markup subtabs config.
	 * Always returns the full config; locked when the extension is inactive.
	 *
	 * @param bool $is_active Whether the Review Markup extension is enabled.
	 * @return array  Subtabs array keyed by subtab slug.
	 */
	private function get_review_markup_subtabs( $lock_info ) {
		$options = get_option( self::OPTION_REVIEW_MARKUP, array() );
		$locked  = $lock_info['locked'];
		$badge   = $lock_info['badge'];
		$message = $lock_info['message'];

		$none_option = array(
			'value' => '',
			'label' => esc_html__( '— None —', 'strong-testimonials' ),
		);

		$content_options  = array(
			array(
				'value' => 'content',
				'label' => esc_html__( 'Post Content', 'strong-testimonials' ),
			),
			array(
				'value' => 'excerpt',
				'label' => esc_html__( 'Post Excerpt', 'strong-testimonials' ),
			),
		);
		$text_options     = $this->get_custom_field_options( 'text', array( $none_option ) );
		$url_text_options = $this->get_custom_field_options( array( 'text', 'url' ), array( $none_option ) );
		$url_options      = $this->get_custom_field_options( 'url', array( $none_option ) );
		$rating_options   = $this->get_custom_field_options( 'rating', array( $none_option ) );

		$thing_type_options = array(
			array(
				'value' => 'Book',
				'label' => 'Book',
			),
			array(
				'value' => 'Course',
				'label' => 'Course',
			),
			array(
				'value' => 'CreativeWorkSeason',
				'label' => 'CreativeWorkSeason',
			),
			array(
				'value' => 'CreativeWorkSeries',
				'label' => 'CreativeWorkSeries',
			),
			array(
				'value' => 'Episode',
				'label' => 'Episode',
			),
			array(
				'value' => 'Event',
				'label' => 'Event',
			),
			array(
				'value' => 'Game',
				'label' => 'Game',
			),
			array(
				'value' => 'LocalBusiness',
				'label' => 'LocalBusiness',
			),
			array(
				'value' => 'MediaObject',
				'label' => 'MediaObject',
			),
			array(
				'value' => 'Movie',
				'label' => 'Movie',
			),
			array(
				'value' => 'MusicPlaylist',
				'label' => 'MusicPlaylist',
			),
			array(
				'value' => 'MusicRecording',
				'label' => 'MusicRecording',
			),
			array(
				'value' => 'Organization',
				'label' => 'Organization',
			),
			array(
				'value' => 'Product',
				'label' => 'Product',
			),
			array(
				'value' => 'SoftwareApplication',
				'label' => 'SoftwareApplication',
			),
		);

		$availability_options = array(
			array(
				'value' => 'InStock',
				'label' => esc_html__( 'In Stock', 'strong-testimonials' ),
			),
			array(
				'value' => 'OutOfStock',
				'label' => esc_html__( 'Out of Stock', 'strong-testimonials' ),
			),
			array(
				'value' => 'PreOrder',
				'label' => esc_html__( 'Pre-Order', 'strong-testimonials' ),
			),
			array(
				'value' => 'Discontinued',
				'label' => esc_html__( 'Discontinued', 'strong-testimonials' ),
			),
			array(
				'value' => 'InStoreOnly',
				'label' => esc_html__( 'In Store Only', 'strong-testimonials' ),
			),
			array(
				'value' => 'LimitedAvailability',
				'label' => esc_html__( 'Limited Availability', 'strong-testimonials' ),
			),
			array(
				'value' => 'OnlineOnly',
				'label' => esc_html__( 'Online Only', 'strong-testimonials' ),
			),
			array(
				'value' => 'SoldOut',
				'label' => esc_html__( 'Sold Out', 'strong-testimonials' ),
			),
		);

		$offer_availability_options = array(
			array(
				'value' => 'None',
				'label' => esc_html__( '— None —', 'strong-testimonials' ),
			),
			array(
				'value' => 'InStock',
				'label' => esc_html__( 'In Stock', 'strong-testimonials' ),
			),
			array(
				'value' => 'SoldOut',
				'label' => esc_html__( 'Sold Out', 'strong-testimonials' ),
			),
			array(
				'value' => 'PreOrder',
				'label' => esc_html__( 'Pre-Order', 'strong-testimonials' ),
			),
		);

		$performer_options = array(
			array(
				'value' => 'Person',
				'label' => esc_html__( 'Person', 'strong-testimonials' ),
			),
			array(
				'value' => 'Organization',
				'label' => esc_html__( 'Organization', 'strong-testimonials' ),
			),
		);

		$g = isset( $options['thing_settings'] ) ? $options['thing_settings'] : array();

		$ts = function ( $type, $key, $default = '' ) use ( $g ) {
			return isset( $g[ $type ][ $key ] ) ? $g[ $type ][ $key ] : $default;
		};

		$single_product_options = array(
			array(
				'value' => '',
				'label' => esc_html__( 'do not add markup to the single testimonial', 'strong-testimonials' ),
			),
			array(
				'value' => 'global',
				'label' => esc_html__( 'add markup using the Thing Name', 'strong-testimonials' ),
			),
			array(
				'value' => 'category',
				'label' => esc_html__( 'add markup using the category name', 'strong-testimonials' ),
			),
		);

		$use_max_rating_options = array(
			array(
				'value' => 1,
				'label' => esc_html__( 'if no rating, use the maximum value (5/5) instead', 'strong-testimonials' ),
			),
			array(
				'value' => 0,
				'label' => esc_html__( 'if no rating, add no rating markup', 'strong-testimonials' ),
			),
		);

		// Markup Fields subtab
		$fields_config = array(
			'option' => self::OPTION_REVIEW_MARKUP,
			'fields' => array(
				// ---- Markup Fields section ----
				array(
					'type'  => 'paragraph',
					'name'  => 'section_markup_fields',
					'label' => esc_html__( 'Markup Fields', 'strong-testimonials' ),
				),
				array(
					'type'        => 'select',
					'name'        => 'content_field',
					'label'       => esc_html__( 'Content Field', 'strong-testimonials' ),
					'description' => esc_html__( '(Required)', 'strong-testimonials' ),
					'options'     => $content_options,
					'default'     => isset( $options['content_field'] ) ? $options['content_field'] : 'content',
				),
				array(
					'type'        => 'select',
					'name'        => 'author_field',
					'label'       => esc_html__( 'Author Field', 'strong-testimonials' ),
					'description' => esc_html__( '(Recommended)', 'strong-testimonials' ),
					'options'     => $text_options,
					'default'     => isset( $options['author_field'] ) ? $options['author_field'] : '',
				),
				array(
					'type'    => 'select',
					'name'    => 'company_field',
					'label'   => esc_html__( 'Company Field', 'strong-testimonials' ),
					'options' => $url_text_options,
					'default' => isset( $options['company_field'] ) ? $options['company_field'] : '',
				),
				array(
					'type'    => 'select',
					'name'    => 'company_url',
					'label'   => esc_html__( 'Company URL Field', 'strong-testimonials' ),
					'options' => $url_options,
					'default' => isset( $options['company_url'] ) ? $options['company_url'] : '',
				),
				array(
					'type'        => 'select',
					'name'        => 'rating_field',
					'label'       => esc_html__( 'Rating Field', 'strong-testimonials' ),
					'description' => esc_html__( '(Recommended)', 'strong-testimonials' ),
					'options'     => $rating_options,
					'default'     => isset( $options['rating_field'] ) ? $options['rating_field'] : '',
				),
				array(
					'type'    => 'select',
					'name'    => 'use_max_rating',
					'label'   => esc_html__( 'Max Rating', 'strong-testimonials' ),
					'options' => $use_max_rating_options,
					'default' => isset( $options['use_max_rating'] ) ? (int) $options['use_max_rating'] : 1,
				),
				array(
					'type'        => 'toggle',
					'name'        => 'include_ratings',
					'label'       => esc_html__( 'Include Ratings', 'strong-testimonials' ),
					'description' => esc_html__( 'Include rating values in the markup output.', 'strong-testimonials' ),
					'default'     => isset( $options['include_ratings'] ) ? (bool) $options['include_ratings'] : true,
				),
				array(
					'type' => 'separator',
					'name' => 'sep_schema',
				),
				// ---- Schema Settings section ----
				array(
					'type'  => 'paragraph',
					'name'  => 'section_schema_settings',
					'label' => esc_html__( 'Schema Settings', 'strong-testimonials' ),
				),
				array(
					'type'        => 'text',
					'name'        => 'product_name',
					'label'       => esc_html__( 'Thing Name', 'strong-testimonials' ),
					'description' => esc_html__( '(Recommended)', 'strong-testimonials' ),
					'default'     => isset( $options['product_name'] ) ? $options['product_name'] : '',
				),
				array(
					'type'    => 'text',
					'name'    => 'product_id',
					'label'   => esc_html__( 'Thing ID', 'strong-testimonials' ),
					'default' => isset( $options['product_id'] ) ? $options['product_id'] : '',
				),
				array(
					'type'        => 'select',
					'name'        => 'thing_type',
					'label'       => esc_html__( 'Thing Type', 'strong-testimonials' ),
					'description' => esc_html__( '(Recommended)', 'strong-testimonials' ),
					'options'     => $thing_type_options,
					'default'     => isset( $options['thing_type'] ) ? $options['thing_type'] : 'Product',
				),
				array(
					'type'        => 'text',
					'name'        => 'thing_description',
					'label'       => esc_html__( 'Thing Description', 'strong-testimonials' ),
					'description' => esc_html__( 'The description of your product, service or business.', 'strong-testimonials' ),
					'default'     => isset( $options['thing_description'] ) ? $options['thing_description'] : '',
				),
				// Course settings
				array(
					'type'       => 'select',
					'name'       => 'thing_settings.course.provider',
					'label'      => esc_html__( 'Course Provider Type', 'strong-testimonials' ),
					'options'    => $performer_options,
					'default'    => $ts( 'course', 'provider', 'Person' ),
					'conditions' => array(
						array(
							'field'      => 'thing_type',
							'comparison' => '===',
							'value'      => 'Course',
						),
					),
				),
				array(
					'type'       => 'text',
					'name'       => 'thing_settings.course.provider_name',
					'label'      => esc_html__( 'Course Provider Name', 'strong-testimonials' ),
					'default'    => $ts( 'course', 'provider_name' ),
					'conditions' => array(
						array(
							'field'      => 'thing_type',
							'comparison' => '===',
							'value'      => 'Course',
						),
					),
				),
				// Event settings
				array(
					'type'       => 'text',
					'name'       => 'thing_settings.event.venue_name',
					'label'      => esc_html__( 'Venue Name', 'strong-testimonials' ),
					'default'    => $ts( 'event', 'venue_name' ),
					'conditions' => array(
						array(
							'field'      => 'thing_type',
							'comparison' => '===',
							'value'      => 'Event',
						),
					),
				),
				array(
					'type'       => 'text',
					'name'       => 'thing_settings.event.street_address',
					'label'      => esc_html__( 'Street Address', 'strong-testimonials' ),
					'default'    => $ts( 'event', 'street_address' ),
					'conditions' => array(
						array(
							'field'      => 'thing_type',
							'comparison' => '===',
							'value'      => 'Event',
						),
					),
				),
				array(
					'type'       => 'text',
					'name'       => 'thing_settings.event.locality',
					'label'      => esc_html__( 'City', 'strong-testimonials' ),
					'default'    => $ts( 'event', 'locality' ),
					'conditions' => array(
						array(
							'field'      => 'thing_type',
							'comparison' => '===',
							'value'      => 'Event',
						),
					),
				),
				array(
					'type'       => 'text',
					'name'       => 'thing_settings.event.region',
					'label'      => esc_html__( 'State / Region', 'strong-testimonials' ),
					'default'    => $ts( 'event', 'region' ),
					'conditions' => array(
						array(
							'field'      => 'thing_type',
							'comparison' => '===',
							'value'      => 'Event',
						),
					),
				),
				array(
					'type'       => 'text',
					'name'       => 'thing_settings.event.postal_code',
					'label'      => esc_html__( 'Postal Code', 'strong-testimonials' ),
					'default'    => $ts( 'event', 'postal_code' ),
					'conditions' => array(
						array(
							'field'      => 'thing_type',
							'comparison' => '===',
							'value'      => 'Event',
						),
					),
				),
				array(
					'type'       => 'text',
					'name'       => 'thing_settings.event.country',
					'label'      => esc_html__( 'Country', 'strong-testimonials' ),
					'default'    => $ts( 'event', 'country' ),
					'conditions' => array(
						array(
							'field'      => 'thing_type',
							'comparison' => '===',
							'value'      => 'Event',
						),
					),
				),
				array(
					'type'        => 'text',
					'name'        => 'thing_settings.event.start_date',
					'label'       => esc_html__( 'Start Date', 'strong-testimonials' ),
					'description' => esc_html__( 'Event date in format yyyy-mm-dd.', 'strong-testimonials' ),
					'default'     => $ts( 'event', 'start_date' ),
					'conditions'  => array(
						array(
							'field'      => 'thing_type',
							'comparison' => '===',
							'value'      => 'Event',
						),
					),
				),
				array(
					'type'        => 'text',
					'name'        => 'thing_settings.event.end_date',
					'label'       => esc_html__( 'End Date', 'strong-testimonials' ),
					'description' => esc_html__( 'Event date in format yyyy-mm-dd.', 'strong-testimonials' ),
					'default'     => $ts( 'event', 'end_date' ),
					'conditions'  => array(
						array(
							'field'      => 'thing_type',
							'comparison' => '===',
							'value'      => 'Event',
						),
					),
				),
				array(
					'type'        => 'text',
					'name'        => 'thing_settings.event.image',
					'label'       => esc_html__( 'Event Image URL', 'strong-testimonials' ),
					'description' => esc_html__( 'The URL of the event image.', 'strong-testimonials' ),
					'default'     => $ts( 'event', 'image' ),
					'conditions'  => array(
						array(
							'field'      => 'thing_type',
							'comparison' => '===',
							'value'      => 'Event',
						),
					),
				),
				array(
					'type'       => 'select',
					'name'       => 'thing_settings.event.performer',
					'label'      => esc_html__( 'Performer Type', 'strong-testimonials' ),
					'options'    => $performer_options,
					'default'    => $ts( 'event', 'performer', 'Person' ),
					'conditions' => array(
						array(
							'field'      => 'thing_type',
							'comparison' => '===',
							'value'      => 'Event',
						),
					),
				),
				array(
					'type'       => 'text',
					'name'       => 'thing_settings.event.performer_name',
					'label'      => esc_html__( 'Performer Name', 'strong-testimonials' ),
					'default'    => $ts( 'event', 'performer_name' ),
					'conditions' => array(
						array(
							'field'      => 'thing_type',
							'comparison' => '===',
							'value'      => 'Event',
						),
					),
				),
				array(
					'type'        => 'text',
					'name'        => 'thing_settings.event.ticket_url',
					'label'       => esc_html__( 'Ticket URL', 'strong-testimonials' ),
					'description' => esc_html__( 'A URL where visitors can purchase tickets for the event.', 'strong-testimonials' ),
					'default'     => $ts( 'event', 'ticket_url' ),
					'conditions'  => array(
						array(
							'field'      => 'thing_type',
							'comparison' => '===',
							'value'      => 'Event',
						),
					),
				),
				array(
					'type'        => 'text',
					'name'        => 'thing_settings.event.offer_price',
					'label'       => esc_html__( 'Entry Price', 'strong-testimonials' ),
					'description' => esc_html__( 'Entry price of the event.', 'strong-testimonials' ),
					'default'     => $ts( 'event', 'offer_price' ),
					'conditions'  => array(
						array(
							'field'      => 'thing_type',
							'comparison' => '===',
							'value'      => 'Event',
						),
					),
				),
				array(
					'type'        => 'text',
					'name'        => 'thing_settings.event.offer_currency',
					'label'       => esc_html__( 'Currency', 'strong-testimonials' ),
					'description' => esc_html__( 'ISO 4217 Currency code. Example: EUR.', 'strong-testimonials' ),
					'default'     => $ts( 'event', 'offer_currency' ),
					'conditions'  => array(
						array(
							'field'      => 'thing_type',
							'comparison' => '===',
							'value'      => 'Event',
						),
					),
				),
				array(
					'type'       => 'select',
					'name'       => 'thing_settings.event.offer_availability',
					'label'      => esc_html__( 'Availability', 'strong-testimonials' ),
					'options'    => $offer_availability_options,
					'default'    => $ts( 'event', 'offer_availability', 'None' ),
					'conditions' => array(
						array(
							'field'      => 'thing_type',
							'comparison' => '===',
							'value'      => 'Event',
						),
					),
				),
				array(
					'type'       => 'text',
					'name'       => 'thing_settings.event.offer_availability_starts',
					'label'      => esc_html__( 'Availability Starts', 'strong-testimonials' ),
					'default'    => $ts( 'event', 'offer_availability_starts' ),
					'conditions' => array(
						array(
							'field'      => 'thing_type',
							'comparison' => '===',
							'value'      => 'Event',
						),
					),
				),
				// LocalBusiness settings
				array(
					'type'        => 'text',
					'name'        => 'thing_settings.localbusiness.address',
					'label'       => esc_html__( 'Address', 'strong-testimonials' ),
					'description' => esc_html__( 'The postal address of your business.', 'strong-testimonials' ),
					'default'     => $ts( 'localbusiness', 'address' ),
					'conditions'  => array(
						array(
							'field'      => 'thing_type',
							'comparison' => '===',
							'value'      => 'LocalBusiness',
						),
					),
				),
				array(
					'type'        => 'text',
					'name'        => 'thing_settings.localbusiness.image',
					'label'       => esc_html__( 'Image URL', 'strong-testimonials' ),
					'description' => esc_html__( 'The URL of a local business image.', 'strong-testimonials' ),
					'default'     => $ts( 'localbusiness', 'image' ),
					'conditions'  => array(
						array(
							'field'      => 'thing_type',
							'comparison' => '===',
							'value'      => 'LocalBusiness',
						),
					),
				),
				array(
					'type'       => 'text',
					'name'       => 'thing_settings.localbusiness.price_range',
					'label'      => esc_html__( 'Price Range', 'strong-testimonials' ),
					'default'    => $ts( 'localbusiness', 'price_range' ),
					'conditions' => array(
						array(
							'field'      => 'thing_type',
							'comparison' => '===',
							'value'      => 'LocalBusiness',
						),
					),
				),
				array(
					'type'       => 'text',
					'name'       => 'thing_settings.localbusiness.telephone',
					'label'      => esc_html__( 'Telephone', 'strong-testimonials' ),
					'default'    => $ts( 'localbusiness', 'telephone' ),
					'conditions' => array(
						array(
							'field'      => 'thing_type',
							'comparison' => '===',
							'value'      => 'LocalBusiness',
						),
					),
				),
				// Movie settings
				array(
					'type'        => 'text',
					'name'        => 'thing_settings.movie.image',
					'label'       => esc_html__( 'Image', 'strong-testimonials' ),
					'description' => esc_html__( '(Required) The URL of a movie image.', 'strong-testimonials' ),
					'default'     => $ts( 'movie', 'image' ),
					'conditions'  => array(
						array(
							'field'      => 'thing_type',
							'comparison' => '===',
							'value'      => 'Movie',
						),
					),
				),
				array(
					'type'        => 'text',
					'name'        => 'thing_settings.movie.date_created',
					'label'       => esc_html__( 'Date Created', 'strong-testimonials' ),
					'description' => esc_html__( 'The created date of the movie.', 'strong-testimonials' ),
					'default'     => $ts( 'movie', 'date_created' ),
					'conditions'  => array(
						array(
							'field'      => 'thing_type',
							'comparison' => '===',
							'value'      => 'Movie',
						),
					),
				),
				array(
					'type'        => 'text',
					'name'        => 'thing_settings.movie.director',
					'label'       => esc_html__( 'Director', 'strong-testimonials' ),
					'description' => esc_html__( 'The director of the movie.', 'strong-testimonials' ),
					'default'     => $ts( 'movie', 'director' ),
					'conditions'  => array(
						array(
							'field'      => 'thing_type',
							'comparison' => '===',
							'value'      => 'Movie',
						),
					),
				),
				// Product settings
				array(
					'type'       => 'text',
					'name'       => 'thing_settings.product.sku',
					'label'      => esc_html__( 'Product SKU', 'strong-testimonials' ),
					'default'    => $ts( 'product', 'sku' ),
					'conditions' => array(
						array(
							'field'      => 'thing_type',
							'comparison' => '===',
							'value'      => 'Product',
						),
					),
				),
				array(
					'type'       => 'text',
					'name'       => 'thing_settings.product.brand',
					'label'      => esc_html__( 'Product Brand', 'strong-testimonials' ),
					'default'    => $ts( 'product', 'brand' ),
					'conditions' => array(
						array(
							'field'      => 'thing_type',
							'comparison' => '===',
							'value'      => 'Product',
						),
					),
				),
				array(
					'type'        => 'text',
					'name'        => 'thing_settings.product.image',
					'label'       => esc_html__( 'Product Image', 'strong-testimonials' ),
					'description' => esc_html__( '(Required)', 'strong-testimonials' ),
					'default'     => $ts( 'product', 'image' ),
					'conditions'  => array(
						array(
							'field'      => 'thing_type',
							'comparison' => '===',
							'value'      => 'Product',
						),
					),
				),
				array(
					'type'        => 'text',
					'name'        => 'thing_settings.product.price',
					'label'       => esc_html__( 'Product Price', 'strong-testimonials' ),
					'description' => esc_html__( '(Required)', 'strong-testimonials' ),
					'default'     => $ts( 'product', 'price' ),
					'conditions'  => array(
						array(
							'field'      => 'thing_type',
							'comparison' => '===',
							'value'      => 'Product',
						),
					),
				),
				array(
					'type'        => 'text',
					'name'        => 'thing_settings.product.currency',
					'label'       => esc_html__( 'Product Currency', 'strong-testimonials' ),
					'description' => esc_html__( 'ISO 4217 Currency code. Example: EUR.', 'strong-testimonials' ),
					'default'     => $ts( 'product', 'currency' ),
					'conditions'  => array(
						array(
							'field'      => 'thing_type',
							'comparison' => '===',
							'value'      => 'Product',
						),
					),
				),
				array(
					'type'       => 'text',
					'name'       => 'thing_settings.product.url',
					'label'      => esc_html__( 'Product URL', 'strong-testimonials' ),
					'default'    => $ts( 'product', 'url' ),
					'conditions' => array(
						array(
							'field'      => 'thing_type',
							'comparison' => '===',
							'value'      => 'Product',
						),
					),
				),
				array(
					'type'        => 'text',
					'name'        => 'thing_settings.product.price_valid_until',
					'label'       => esc_html__( 'Price Valid Until', 'strong-testimonials' ),
					'description' => esc_html__( 'The date after which the price will no longer be available.', 'strong-testimonials' ),
					'default'     => $ts( 'product', 'price_valid_until' ),
					'conditions'  => array(
						array(
							'field'      => 'thing_type',
							'comparison' => '===',
							'value'      => 'Product',
						),
					),
				),
				array(
					'type'       => 'select',
					'name'       => 'thing_settings.product.availability',
					'label'      => esc_html__( 'Product Availability', 'strong-testimonials' ),
					'options'    => $availability_options,
					'default'    => $ts( 'product', 'availability', 'InStock' ),
					'conditions' => array(
						array(
							'field'      => 'thing_type',
							'comparison' => '===',
							'value'      => 'Product',
						),
					),
				),
				// SoftwareApplication settings
				array(
					'type'        => 'text',
					'name'        => 'thing_settings.software_application.price',
					'label'       => esc_html__( 'Price', 'strong-testimonials' ),
					'description' => esc_html__( '(Required) Setting Price to 0 will disable Offer markup.', 'strong-testimonials' ),
					'default'     => $ts( 'software_application', 'price' ),
					'conditions'  => array(
						array(
							'field'      => 'thing_type',
							'comparison' => '===',
							'value'      => 'SoftwareApplication',
						),
					),
				),
				array(
					'type'        => 'text',
					'name'        => 'thing_settings.software_application.price_currency',
					'label'       => esc_html__( 'Price Currency', 'strong-testimonials' ),
					'description' => esc_html__( 'ISO 4217 Currency code. Example: EUR.', 'strong-testimonials' ),
					'default'     => $ts( 'software_application', 'price_currency' ),
					'conditions'  => array(
						array(
							'field'      => 'thing_type',
							'comparison' => '===',
							'value'      => 'SoftwareApplication',
						),
					),
				),
				array(
					'type'       => 'text',
					'name'       => 'thing_settings.software_application.os',
					'label'      => esc_html__( 'Operating System', 'strong-testimonials' ),
					'default'    => $ts( 'software_application', 'os' ),
					'conditions' => array(
						array(
							'field'      => 'thing_type',
							'comparison' => '===',
							'value'      => 'SoftwareApplication',
						),
					),
				),
				array(
					'type'       => 'text',
					'name'       => 'thing_settings.software_application.category',
					'label'      => esc_html__( 'Application Category', 'strong-testimonials' ),
					'default'    => $ts( 'software_application', 'category' ),
					'conditions' => array(
						array(
							'field'      => 'thing_type',
							'comparison' => '===',
							'value'      => 'SoftwareApplication',
						),
					),
				),
				array(
					'type' => 'separator',
					'name' => 'sep_page',
				),
				// ---- Page Settings section ----
				array(
					'type'  => 'paragraph',
					'name'  => 'section_page_settings',
					'label' => esc_html__( 'Page Settings', 'strong-testimonials' ),
				),
				array(
					'type'    => 'select',
					'name'    => 'single_product',
					'label'   => esc_html__( 'Single Testimonial Page', 'strong-testimonials' ),
					'options' => $single_product_options,
					'default' => isset( $options['single_product'] ) ? $options['single_product'] : '',
				),
			),
		);

		// Aggregate Rating subtab — informative, no save, custom React component
		$aggregate_config = array(
			'option'        => '',
			'remove_button' => false,
			'fields'        => array(
				array(
					'type' => 'aggregate_rating',
					'name' => 'aggregate_rating_display',
				),
			),
		);

		return array(
			'review_markup_fields'    => array(
				'label'   => esc_html__( 'Markup Fields', 'strong-testimonials' ),
				'locked'  => $locked,
				'badge'   => $badge,
				'message' => $message,
				'config'  => $fields_config,
			),
			'review_markup_aggregate' => array(
				'label'   => esc_html__( 'Aggregate Rating', 'strong-testimonials' ),
				'locked'  => $locked,
				'badge'   => $badge,
				'message' => $message,
				'config'  => $aggregate_config,
			),
		);
	}

	/**
	 * Return available fields and display types for the client section builder.
	 *
	 * @return array
	 */
	public function get_client_section_options() {
		$allowed_record_types = array( 'custom', 'optional', 'builtin' );

		$raw_custom = array_filter(
			wpmtst_get_custom_fields(),
			function ( $field ) use ( $allowed_record_types ) {
				if ( ! in_array( $field['record_type'], $allowed_record_types, true ) ) {
					return false;
				}
				if ( 'category' === strtok( $field['input_type'], '-' ) ) {
					return false;
				}
				if ( 'email' === $field['input_type'] ) {
					return false;
				}
				return true;
			}
		);

		$custom_fields = array_values(
			array_map(
				function ( $f ) {
					return array(
						'name'       => $f['name'],
						'label'      => isset( $f['label'] ) ? $f['label'] : $f['name'],
						'input_type' => $f['input_type'],
					);
				},
				$raw_custom
			)
		);

		$raw_builtin    = wpmtst_get_builtin_fields();
		$builtin_fields = array_values(
			array_map(
				function ( $f ) {
					return array(
						'name'       => $f['name'],
						'label'      => isset( $f['label'] ) ? $f['label'] : $f['name'],
						'input_type' => $f['input_type'],
					);
				},
				$raw_builtin
			)
		);

		$url_fields = array_values(
			array_filter(
				$custom_fields,
				function ( $f ) {
					return 'url' === $f['input_type'];
				}
			)
		);

		$raw_types = apply_filters(
			'wpmtst_view_field_inputs_types',
			array(
				'text'      => esc_html__( 'text', 'strong-testimonials' ),
				'link'      => esc_html__( 'link with another field', 'strong-testimonials' ),
				'link2'     => esc_html__( 'link (must be URL type)', 'strong-testimonials' ),
				'date'      => esc_html__( 'date', 'strong-testimonials' ),
				'category'  => esc_html__( 'category', 'strong-testimonials' ),
				'rating'    => esc_html__( 'rating', 'strong-testimonials' ),
				'platform'  => esc_html__( 'platform', 'strong-testimonials' ),
				'shortcode' => esc_html__( 'shortcode', 'strong-testimonials' ),
				'checkbox'  => esc_html__( 'checkbox', 'strong-testimonials' ),
			)
		);

		$types = array();
		foreach ( $raw_types as $value => $label ) {
			$types[] = array(
				'value' => $value,
				'label' => $label,
			);
		}

		return array(
			'custom_fields'  => $custom_fields,
			'builtin_fields' => $builtin_fields,
			'url_fields'     => $url_fields,
			'types'          => $types,
		);
	}

	/**
	 * Return aggregate rating data for the REST API.
	 *
	 * @return array
	 */
	public function get_aggregate_rating() {
		$args              = array(
			'posts_per_page'   => -1,
			'post_type'        => 'wpm-testimonial',
			'post_status'      => 'publish',
			'suppress_filters' => true,
		);
		$testimonial_count = count( get_posts( $args ) );

		$aggregate       = get_option( 'wpmtst_aggregate_rating', array() );
		$timestamp       = get_option( 'wpmtst_aggregate_recalculated', null );
		$can_recalculate = isset( $GLOBALS['strong_review_markup'] )
			&& isset( $GLOBALS['strong_review_markup']->counter );

		return array(
			'testimonial_count' => $testimonial_count,
			'review_count'      => isset( $aggregate['review_count'] ) ? $aggregate['review_count'] : null,
			'rating_count'      => isset( $aggregate['rating_count'] ) ? $aggregate['rating_count'] : null,
			'rating_total'      => isset( $aggregate['rating_total'] ) ? $aggregate['rating_total'] : null,
			'rating_value'      => isset( $aggregate['rating_value'] ) ? $aggregate['rating_value'] : null,
			'last_updated'      => $timestamp
				? date_i18n( get_option( 'date_format' ) . ' @ ' . get_option( 'time_format' ), $timestamp )
				: null,
			'last_updated_diff' => $timestamp ? human_time_diff( $timestamp, current_time( 'U' ) ) : null,
			'can_recalculate'   => $can_recalculate,
		);
	}

	/**
	 * Trigger aggregate recalculation directly via the Pro counter and return fresh data.
	 *
	 * @return array
	 */
	public function recalculate_aggregate() {
		if ( isset( $GLOBALS['strong_review_markup'] ) && isset( $GLOBALS['strong_review_markup']->counter ) ) {
			$GLOBALS['strong_review_markup']->counter->update_aggregates();
		}
		return $this->get_aggregate_rating();
	}

	// =========================================================================
	// Public API Methods
	// =========================================================================

	/**
	 * Return the full tabs configuration for the React settings page.
	 *
	 * Each tab has:
	 *   - label   : Display name shown in the navigation bar
	 *   - slug    : URL-friendly identifier (used as ?tab= query param)
	 *   - subtabs : Associative array of accordion panels, each with label/locked/badge/config
	 *
	 * Each subtab config has:
	 *   - option : WordPress option name that will be updated on save
	 *   - fields : Array of field definitions consumed by React SettingsForm
	 *
	 * @return array
	 */
	public function get_tabs() {
		$ext = null;
		if ( class_exists( 'Strong_Testimonials_Pro\Extensions\Extensions' ) ) {
			$ext = Strong_Testimonials_Pro\Extensions\Extensions::get_instance();
		}

		$has_valid_license = $ext && $ext->has_valid_plan();

		$roles_lock         = $this->build_lock_info(
			'strong-testimonials-role-management',
			esc_html__( 'Activate the Role Management extension in Strong Testimonials Pro to manage role capabilities.', 'strong-testimonials' ),
			$ext
		);
		$mailchimp_lock     = $this->build_lock_info(
			'strong-testimonials-mailchimp',
			esc_html__( 'Activate the Mailchimp extension in Strong Testimonials Pro to manage Mailchimp lists.', 'strong-testimonials' ),
			$ext
		);
		$assignment_lock    = $this->build_lock_info(
			'strong-testimonials-assignment',
			esc_html__( 'Activate the Assignment extension in Strong Testimonials Pro to manage post assignments.', 'strong-testimonials' ),
			$ext
		);
		$properties_lock    = $this->build_lock_info(
			'strong-testimonials-properties',
			esc_html__( 'Activate the Properties extension in Strong Testimonials Pro to manage post type properties.', 'strong-testimonials' ),
			$ext
		);
		$review_markup_lock = $this->build_lock_info(
			'strong-testimonials-review-markup',
			esc_html__( 'Activate the Review Markup extension in Strong Testimonials Pro to manage review markup settings.', 'strong-testimonials' ),
			$ext
		);
		$captcha_lock       = $this->build_lock_info(
			'strong-testimonials-captcha',
			esc_html__( 'Activate the Captcha extension in Strong Testimonials Pro to manage spam control settings.', 'strong-testimonials' ),
			$ext
		);

		$tabs = array(
			array(
				'label'   => esc_html__( 'General', 'strong-testimonials' ),
				'slug'    => 'general',
				'subtabs' => array(
					'comments'     => array(
						'label'  => esc_html__( 'Comments', 'strong-testimonials' ),
						'locked' => false,
						'badge'  => '',
						'config' => apply_filters( 'wpmtst_react_general_comments_config', $this->get_general_comments() ),
					),
					'embed'        => array(
						'label'  => esc_html__( 'Embed', 'strong-testimonials' ),
						'locked' => false,
						'badge'  => '',
						'config' => apply_filters( 'wpmtst_react_general_embed_config', $this->get_general_embed() ),
					),
					'link-control' => array(
						'label'  => esc_html__( 'Link Control', 'strong-testimonials' ),
						'locked' => false,
						'badge'  => '',
						'config' => apply_filters( 'wpmtst_react_general_link_control_config', $this->get_general_link_control() ),
					),
					'debug'        => array(
						'label'  => esc_html__( 'Debug', 'strong-testimonials' ),
						'locked' => false,
						'badge'  => '',
						'config' => apply_filters( 'wpmtst_react_general_debugt_config', $this->get_general_debug() ),
					),
				),
			),
			array(
				'label'   => esc_html__( 'Role Management', 'strong-testimonials' ),
				'slug'    => 'role-management',
				'subtabs' => apply_filters(
					'wpmtst_react_role_management_subtabs',
					array(
						'role-management' => array(
							'label'   => esc_html__( 'Role Management', 'strong-testimonials' ),
							'locked'  => $roles_lock['locked'],
							'badge'   => $roles_lock['badge'],
							'message' => $roles_lock['message'],
							'config'  => $this->get_roles(),
						),
					)
				),
			),
			array(
				'label'   => esc_html__( 'Mailchimp', 'strong-testimonials' ),
				'slug'    => 'mailchimp',
				'subtabs' => array(
					'mailchimp' => array(
						'label'   => esc_html__( 'Mailchimp', 'strong-testimonials' ),
						'locked'  => $mailchimp_lock['locked'],
						'badge'   => $mailchimp_lock['badge'],
						'message' => $mailchimp_lock['message'],
						'config'  => apply_filters( 'wpmtst_react_mailchimp_admin_config', $this->get_mailchimp_admin() ),
					),
				),
			),
			array(
				'label'   => esc_html__( 'Assignment', 'strong-testimonials' ),
				'slug'    => 'assignment',
				'subtabs' => apply_filters(
					'wpmtst_react_assignment_subtabs',
					array(
						'assignment' => array(
							'label'   => esc_html__( 'Assignment', 'strong-testimonials' ),
							'locked'  => $assignment_lock['locked'],
							'badge'   => $assignment_lock['badge'],
							'message' => $assignment_lock['message'],
							'config'  => apply_filters( 'wpmtst_react_assignment_admin_config', $this->get_assignment_fields() ),
						),
					)
				),
			),
			array(
				'label'   => esc_html__( 'Advanced Controls', 'strong-testimonials' ),
				'slug'    => 'properties',
				'subtabs' => $this->get_properties_subtabs( $properties_lock ),
			),
			array(
				'label'   => esc_html__( 'Review Markup', 'strong-testimonials' ),
				'slug'    => 'review-markup',
				'subtabs' => $this->get_review_markup_subtabs( $review_markup_lock ),
			),
			array(
				'label'   => esc_html__( 'Spam Control', 'strong-testimonials' ),
				'slug'    => 'spam-control',
				'subtabs' => array(
					'honeypot'       => array(
						'label'   => esc_html__( 'Honeypot', 'strong-testimonials' ),
						'locked'  => $captcha_lock['locked'],
						'badge'   => $captcha_lock['badge'],
						'message' => $captcha_lock['message'],
						'config'  => $this->get_spam_control_honeypot(),
					),
					'turnstile'      => array(
						'label'   => esc_html__( 'Turnstile', 'strong-testimonials' ),
						'locked'  => $captcha_lock['locked'],
						'badge'   => $captcha_lock['badge'],
						'message' => $captcha_lock['message'],
						'config'  => $this->get_spam_control_turnstile(),
					),
					'captcha'        => array(
						'label'   => esc_html__( 'Captcha', 'strong-testimonials' ),
						'locked'  => $captcha_lock['locked'],
						'badge'   => $captcha_lock['badge'],
						'message' => $captcha_lock['message'],
						'config'  => $this->get_spam_control_captcha(),
					),
					'ip-restriction' => array(
						'label'   => esc_html__( 'IP Restriction', 'strong-testimonials' ),
						'locked'  => $captcha_lock['locked'],
						'badge'   => $captcha_lock['badge'],
						'message' => $captcha_lock['message'],
						'config'  => $this->get_spam_control_ip(),
					),
				),
			),
		);

		return apply_filters( 'wpmtst_react_settings_tabs', $tabs );
	}

	/**
	 * Return the current saved settings values (used by the GET /general-settings endpoint).
	 *
	 * @return array
	 */
	public function get_settings() {
		$settings = array(
			self::OPTION_GENERAL       => get_option( self::OPTION_GENERAL, array() ),
			self::OPTION_ADVANCED      => get_option( self::OPTION_ADVANCED, array() ),
			self::OPTION_PROPERTIES    => get_option( self::OPTION_PROPERTIES, array() ),
			self::OPTION_REVIEW_MARKUP => get_option( self::OPTION_REVIEW_MARKUP, array() ),
		);

		return apply_filters( 'wpmtst_react_settings_values', $settings );
	}

	// =========================================================================
	// Spam Control Subtab Configs
	// =========================================================================

	private function get_spam_control_honeypot() {
		$options = get_option( 'wpmtst_form_options', array() );
		return array(
			'option' => 'wpmtst_form_options',
			'fields' => array(
				$this->build_toggle_field(
					'honeypot_before',
					esc_html__( 'Honeypot Before', 'strong-testimonials' ),
					! empty( $options['honeypot_before'] ),
					array(
						'description' => esc_html__( 'Adds an invisible empty field. Spambots fill every field — empty = human, not empty = spambot.', 'strong-testimonials' ),
					)
				),
				$this->build_toggle_field(
					'honeypot_after',
					esc_html__( 'Honeypot After', 'strong-testimonials' ),
					! empty( $options['honeypot_after'] ),
					array(
						'description' => esc_html__( 'Adds a hidden field on submit via JavaScript. Spambots cannot run JS so the field is never added — missing field = spambot.', 'strong-testimonials' ),
					)
				),
			),
		);
	}

	private function get_spam_control_turnstile() {
		$options = get_option( 'wpmtst_form_options', array() );
		$cond_on = array(
			array(
				'field'      => 'enable_turnstile',
				'comparison' => '===',
				'value'      => true,
			),
		);
		return array(
			'option' => 'wpmtst_form_options',
			'fields' => array(
				$this->build_toggle_field(
					'enable_turnstile',
					esc_html__( 'Enable Cloudflare Turnstile', 'strong-testimonials' ),
					! empty( $options['enable_turnstile'] )
				),
				$this->build_text_field(
					'turnstile_site_key',
					esc_html__( 'Site Key', 'strong-testimonials' ),
					$options['turnstile_site_key'] ?? '',
					array( 'conditions' => $cond_on )
				),
				$this->build_text_field(
					'turnstile_secret_key',
					esc_html__( 'Secret Key', 'strong-testimonials' ),
					$options['turnstile_secret_key'] ?? '',
					array( 'conditions' => $cond_on )
				),
				$this->build_text_field(
					'turnstile_label',
					esc_html__( 'Label', 'strong-testimonials' ),
					$options['turnstile_label'] ?? _x( 'Captcha', 'Default label for Captcha field on submission form.', 'strong-testimonials' ),
					array( 'conditions' => $cond_on )
				),
			),
		);
	}

	private function get_spam_control_captcha() {
		$options = get_option( 'wpmtst_form_options', array() );
		$cond_on = array(
			array(
				'field'      => 'enable_recaptcha',
				'comparison' => '===',
				'value'      => true,
			),
		);
		return array(
			'option' => 'wpmtst_form_options',
			'fields' => array(
				$this->build_toggle_field(
					'enable_recaptcha',
					esc_html__( 'Enable Google reCAPTCHA', 'strong-testimonials' ),
					! empty( $options['enable_recaptcha'] )
				),
				$this->build_paragraph_field(
					'recaptcha_api_info',
					'',
					esc_html__( 'Register your website with Google to get required API keys and enter them below', 'strong-testimonials' )
					. '. <a target="_blank" href="https://www.google.com/recaptcha/admin#list">'
					. esc_html__( 'Get the API Keys', 'strong-testimonials' )
					. '</a>',
					array( 'conditions' => $cond_on )
				),
				array(
					'type'       => self::FIELD_TYPE_SELECT,
					'name'       => 'recaptcha_version',
					'label'      => esc_html__( 'Version', 'strong-testimonials' ),
					'default'    => $options['recaptcha_version'] ?? 'v2',
					'options'    => array(
						array(
							'value' => 'v2',
							'label' => 'reCAPTCHA v2',
						),
						array(
							'value' => 'invisible',
							'label' => esc_html__( 'Invisible reCAPTCHA badge', 'strong-testimonials' ),
						),
						array(
							'value' => 'v3',
							'label' => 'reCAPTCHA v3',
						),
					),
					'conditions' => $cond_on,
				),
				$this->build_text_field(
					'recaptcha_site_key',
					esc_html__( 'Site Key', 'strong-testimonials' ),
					$options['recaptcha_site_key'] ?? '',
					array( 'conditions' => $cond_on )
				),
				$this->build_text_field(
					'recaptcha_secret_key',
					esc_html__( 'Secret Key', 'strong-testimonials' ),
					$options['recaptcha_secret_key'] ?? '',
					array( 'conditions' => $cond_on )
				),
				$this->build_text_field(
					'recaptcha_label',
					esc_html__( 'Label', 'strong-testimonials' ),
					$options['recaptcha_label'] ?? _x( 'Captcha', 'Default label for Captcha field on submission form.', 'strong-testimonials' ),
					array( 'conditions' => $cond_on )
				),
				$this->build_number_field(
					'recaptcha_score',
					esc_html__( 'Minimum Score (v3 only)', 'strong-testimonials' ),
					isset( $options['recaptcha_score'] ) ? (float) $options['recaptcha_score'] : 0.5,
					array(
						'description' => esc_html__( 'Score from 0.0 to 1.0. Users scoring below this threshold are blocked.', 'strong-testimonials' ),
						'min'         => 0,
						'max'         => 1,
						'step'        => 0.1,
						'conditions'  => array(
							array(
								'field'      => 'enable_recaptcha',
								'comparison' => '===',
								'value'      => true,
							),
							array(
								'field'      => 'recaptcha_version',
								'comparison' => '===',
								'value'      => 'v3',
							),
						),
					)
				),
			),
		);
	}

	private function get_spam_control_ip() {
		$options = get_option( 'wpmtst_form_options', array() );
		$raw     = $options['restrict_ip'] ?? array();
		$default = is_array( $raw )
			? implode( "\n", array_filter( $raw ) )
			: (string) $raw;
		return array(
			'option' => 'wpmtst_form_options',
			'fields' => array(
				array(
					'type'        => 'textarea',
					'name'        => 'restrict_ip',
					'label'       => esc_html__( 'Blocked IP Addresses', 'strong-testimonials' ),
					'default'     => $default,
					'description' => esc_html__( 'Enter each IP address on a new line. Submissions from blocked IPs will be rejected.', 'strong-testimonials' ),
				),
			),
		);
	}

	/**
	 * Return the sanitization schema consumed by the REST API save handler.
	 *
	 * Structure: array( 'option_name' => array( 'field_name' => array( 'sanitizer' ) ) )
	 *
	 * @return array
	 */
	public function settings_sanitization() {
		$schema = array(
			self::OPTION_GENERAL    => array(
				'support_custom_fields'   => array( 'bool' ),
				'disable_rewrite'         => array( 'bool' ),
				'single_testimonial_slug' => array( 'text' ),
				'touch_enabled'           => array( 'bool' ),
				'scrolltop'               => array( 'bool' ),
				'scrolltop_offset'        => array( 'number' ),
				'remove_whitespace'       => array( 'bool' ),
				'support_comments'        => array( 'bool' ),
				'embed_width'             => array( 'number' ),
				'nofollow'                => array( 'bool' ),
				'noopener'                => array( 'bool' ),
				'noreferrer'              => array( 'bool' ),
				'lazyload'                => array( 'bool' ),
				'no_lazyload_plugin'      => array( 'bool' ),
				'disable_upsells'         => array( 'bool' ),
			),
			self::OPTION_ADVANCED   => array(
				'debug_log' => array( 'bool' ),
			),
			self::OPTION_MAILCHIMP  => array(
				'mailchimp_api_key' => array( 'text' ),
			),
			self::OPTION_ASSIGNMENT => array(
				'assignees' => array( 'text' ),
				'selection' => array( 'text' ),
			),
		);

		$schema[ self::OPTION_PROPERTIES ] = array(
			'cpt' => array(
				'labels'              => array(
					'name'          => array( 'text' ),
					'singular_name' => array( 'text' ),
				),
				'menu_icon'           => array( 'text' ),
				'menu_position'       => array( 'number' ),
				'show_in_admin_bar'   => array( 'bool' ),
				'show_in_nav_menus'   => array( 'bool' ),
				'can_export'          => array( 'bool' ),
				'publicly_queryable'  => array( 'bool' ),
				'query_var'           => array(
					'on'    => array( 'bool' ),
					'using' => array( 'enum' => array( 'default', 'custom' ) ),
					'name'  => array( 'text' ),
				),
				'rewrite'             => array(
					'on'         => array( 'bool' ),
					'slug'       => array( 'text' ),
					'with_front' => array( 'bool' ),
					'feeds'      => array( 'bool' ),
					'pages'      => array( 'bool' ),
				),
				'has_archive'         => array(
					'on'    => array( 'bool' ),
					'using' => array( 'enum' => array( 'current', 'custom' ) ),
					'slug'  => array( 'text' ),
				),
				'exclude_from_search' => array( 'bool' ),
				// supports passes through unsanitized (closed set of known strings).
			),
			'tax' => array(
				'labels'             => array(
					'name'          => array( 'text' ),
					'singular_name' => array( 'text' ),
				),
				'publicly_queryable' => array( 'bool' ),
				'query_var'          => array(
					'on'    => array( 'bool' ),
					'using' => array( 'enum' => array( 'default', 'custom' ) ),
				),
				'rewrite'            => array(
					'on'           => array( 'bool' ),
					'slug'         => array( 'text' ),
					'with_front'   => array( 'bool' ),
					'hierarchical' => array( 'bool' ),
				),
			),
		);

		$thing_settings_schema = array(
			'course'               => array(
				'provider'      => array( 'text' ),
				'provider_name' => array( 'text' ),
			),
			'event'                => array(
				'street_address'            => array( 'text' ),
				'locality'                  => array( 'text' ),
				'region'                    => array( 'text' ),
				'postal_code'               => array( 'text' ),
				'country'                   => array( 'text' ),
				'venue_name'                => array( 'text' ),
				'start_date'                => array( 'text' ),
				'end_date'                  => array( 'text' ),
				'image'                     => array( 'text' ),
				'performer'                 => array( 'text' ),
				'performer_name'            => array( 'text' ),
				'ticket_url'                => array( 'text' ),
				'offer_price'               => array( 'text' ),
				'offer_currency'            => array( 'text' ),
				'offer_availability'        => array( 'text' ),
				'offer_availability_starts' => array( 'text' ),
			),
			'localbusiness'        => array(
				'address'     => array( 'text' ),
				'image'       => array( 'text' ),
				'price_range' => array( 'text' ),
				'telephone'   => array( 'text' ),
			),
			'movie'                => array(
				'image'        => array( 'text' ),
				'date_created' => array( 'text' ),
				'director'     => array( 'text' ),
			),
			'product'              => array(
				'sku'               => array( 'text' ),
				'brand'             => array( 'text' ),
				'image'             => array( 'text' ),
				'price'             => array( 'text' ),
				'url'               => array( 'text' ),
				'currency'          => array( 'text' ),
				'price_valid_until' => array( 'text' ),
				'availability'      => array( 'text' ),
			),
			'software_application' => array(
				'price'          => array( 'text' ),
				'price_currency' => array( 'text' ),
				'os'             => array( 'text' ),
				'category'       => array( 'text' ),
			),
		);

		$schema[ self::OPTION_REVIEW_MARKUP ] = array(
			'content_field'     => array( 'text' ),
			'author_field'      => array( 'text' ),
			'company_field'     => array( 'text' ),
			'company_url'       => array( 'text' ),
			'rating_field'      => array( 'text' ),
			'use_max_rating'    => array( 'number' ),
			'include_ratings'   => array( 'bool' ),
			'single_product'    => array( 'text' ),
			'product_name'      => array( 'text' ),
			'product_id'        => array( 'text' ),
			'thing_type'        => array( 'text' ),
			'thing_description' => array( 'text' ),
			'thing_settings'    => $thing_settings_schema,
		);

		// Role Management: build schema dynamically from WP roles when the extension is active.
		if ( class_exists( 'Strong_Testimonials_Pro\Extensions\Role_Management\Role_Management' ) ) {
			global $wp_roles;

			$capabilities_list = array(
				'read_testimonial',
				'edit_testimonial',
				'delete_testimonial',
				'read_private_testimonials',
				'edit_testimonials',
				'edit_other_testimonials',
				'publish_testimonials',
				'delete_testimonials',
				'delete_others_testimonials',
				'strong_testimonials_options',
			);

			$roles_schema = array();

			foreach ( $wp_roles->roles as $role_key => $wp_role ) {
				if ( 'administrator' === $role_key ) {
					continue;
				}

				$roles_schema[ $role_key ] = array( 'enabled' => array( 'bool' ) );

				foreach ( $capabilities_list as $cap ) {
					$roles_schema[ $role_key ][ $cap ] = array( 'bool' );
				}

				if ( in_array( $role_key, array( 'subscriber', 'contributor' ), true ) ) {
					$roles_schema[ $role_key ]['upload_files'] = array( 'bool' );
				}
			}

			$schema[ self::OPTION_ROLES ] = $roles_schema;
		}

		$schema['wpmtst_form_options'] = array(
			'honeypot_before'      => array( 'bool' ),
			'honeypot_after'       => array( 'bool' ),
			'enable_turnstile'     => array( 'bool' ),
			'turnstile_site_key'   => array( 'text' ),
			'turnstile_secret_key' => array( 'text' ),
			'turnstile_label'      => array( 'text' ),
			'enable_recaptcha'     => array( 'bool' ),
			'recaptcha_version'    => array( 'enum' => array( 'v2', 'invisible', 'v3' ) ),
			'recaptcha_site_key'   => array( 'text' ),
			'recaptcha_secret_key' => array( 'text' ),
			'recaptcha_label'      => array( 'text' ),
			'recaptcha_score'      => array( 'float' ),
			'restrict_ip'          => array( 'ip_list' ),
		);

		return apply_filters( 'wpmtst_react_settings_sanitization', $schema );
	}
}
