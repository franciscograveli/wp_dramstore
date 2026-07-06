<?php

/**
 * Extensions class for Strong Testimonials plugin.
 */
class Strong_Testimonials_Extensions_Base {
	/**
	 * Class instance.
	 *
	 * @var Extensions
	 */
	private static $instance;
	/**
	 * Extensions array
	 *
	 * @var array
	 */
	public $extensions = array();
	/**
	 * Active extensions option
	 *
	 * @var string
	 */
	private $active_extensions = 'strong_testimonials_pro_active_extensions';
	/**
	 * Current plan option
	 *
	 * @var string
	 */
	private $current_plan = 'strong_testimonials_current_plan';
	/**
	 * Active extensions cache
	 *
	 * @var array
	 */
	private static $active_extensions_cache = array();
	/**
	 * Plan map
	 *
	 * @var array
	 */
	private $plan_map = array();
	/**
	 * Initialize the extensions.
	 */
	public function __construct() {
		$this->create_plan_map();
		add_action( 'init', array( $this, 'add_default_extensions' ) );
	}

	/**
	 * Create plan map
	 */
	private function create_plan_map() {
		$free = array();

		$basic = array(
			'divider-basic',
			'strong-testimonials-country-selector',
		);

		$plus = array_merge(
			$basic,
			array(
				'divider-plus',
				'strong-testimonials-assignment',
				'strong-testimonials-properties',
				'strong-testimonials-review-markup',
				'strong-testimonials-advanced-views',
				'strong-testimonials-captcha',
				'strong-testimonials-pro-templates',
				'strong-testimonials-importer',
			)
		);

		$business = array_merge(
			$plus,
			array(
				'divider-business',
				'strong-testimonials-infinite-scroll',
				'strong-testimonials-emails',
				'strong-testimonials-filters',
				'strong-testimonials-role-management',
				'strong-testimonials-mailchimp',
				'strong-testimonials-custom-fields',
				'strong-testimonials-multiple-forms',
			)
		);

		$this->plan_map = array(
			'free'     => $free,
			'basic'    => $basic,
			'plus'     => $plus,
			'business' => $business,
		);
	}

	/**
	 * Get the instance of the class.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) || ! ( self::$instance instanceof Strong_Testimonials_Extensions_Base ) ) {
			self::$instance = new Strong_Testimonials_Extensions_Base();
		}

		return self::$instance;
	}

	/**
	 * Add default extensions
	 */
	public function add_default_extensions() {
		$this->extensions = array(
			'strong-testimonials-country-selector' => array(
				'available'   => false,
				'enabled'     => false,
				'name'        => __( 'Country Selector', 'strong-testimonials' ),
				'slug'        => 'strong-testimonials-country-selector',
				'description' => __( 'Allow customers to select their country when submitting testimonials.', 'strong-testimonials' ),
			),
			'strong-testimonials-assignment'       => array(
				'available'   => false,
				'enabled'     => false,
				'name'        => __( 'Assignment', 'strong-testimonials' ),
				'slug'        => 'strong-testimonials-assignment',
				'description' => __( 'Assign testimonials to custom post types.', 'strong-testimonials' ),
			),
			'strong-testimonials-properties'       => array(
				'available'   => false,
				'enabled'     => false,
				'name'        => __( 'Advanced Controls', 'strong-testimonials' ),
				'slug'        => 'strong-testimonials-properties',
				'description' => __( 'Change properties of the testimonial post type: labels, permalink structure, admin options and post editor features.', 'strong-testimonials' ),
			),
			'strong-testimonials-review-markup'    => array(
				'available'   => false,
				'enabled'     => false,
				'name'        => __( 'Review Markup', 'strong-testimonials' ),
				'slug'        => 'strong-testimonials-review-markup',
				'description' => __( 'SEO-friendly Testimonials. Take full advantage of your testimonials with our Schema.org Markup extension.', 'strong-testimonials' ),
			),
			'strong-testimonials-advanced-views'   => array(
				'available'   => false,
				'enabled'     => false,
				'name'        => __( 'Advanced Views', 'strong-testimonials' ),
				'slug'        => 'strong-testimonials-advanced-views',
				'description' => __( 'Customize your testimonials beyond star ratings, reorder fields and more.', 'strong-testimonials' ),
			),
			'strong-testimonials-captcha'          => array(
				'available'   => false,
				'enabled'     => false,
				'name'        => __( 'Captcha', 'strong-testimonials' ),
				'slug'        => 'strong-testimonials-captcha',
				'description' => __( 'Protect your testimonial submission forms against spam and other types of automated abuse.', 'strong-testimonials' ),
			),
			'strong-testimonials-pro-templates'    => array(
				'available'   => false,
				'enabled'     => false,
				'name'        => __( 'Pro Templates', 'strong-testimonials' ),
				'slug'        => 'strong-testimonials-pro-templates',
				'description' => __( 'Create beautiful testimonial designs with a number of predesigned and easy-to-use premium templates that you can use on your website.', 'strong-testimonials' ),
			),
			'strong-testimonials-importer'         => array(
				'available'   => false,
				'enabled'     => false,
				'name'        => __( 'External Platform Importer', 'strong-testimonials' ),
				'slug'        => 'strong-testimonials-importer',
				'description' => __( 'Import testimonials/reviews from other platforms like: Facebook, Google, Yelp, Zomato, Woocommerce.', 'strong-testimonials' ),
			),
			'strong-testimonials-infinite-scroll'  => array(
				'available'   => false,
				'enabled'     => false,
				'name'        => __( 'Infinite Scroll', 'strong-testimonials' ),
				'slug'        => 'strong-testimonials-infinite-scroll',
				'description' => __( 'Load more testimonials on scroll. Using this extension you can control the number of testimonials that are visible on a pages first load. As the user starts scrolling down the page, more testimonials are brought into view using a continuous loading animation.', 'strong-testimonials' ),
			),
			'strong-testimonials-emails'           => array(
				'available'   => false,
				'enabled'     => false,
				'name'        => __( 'Enhanced Emails', 'strong-testimonials' ),
				'slug'        => 'strong-testimonials-emails',
				'description' => __( 'Use the Enhanced Emails extension to: send a thank you email to your client once their testimonial\'s approved. Increase brand loyalty by showing you really care about your clients. Keep your clients engaged and increase your chances of selling more.', 'strong-testimonials' ),
			),
			'strong-testimonials-filters'          => array(
				'available'   => false,
				'enabled'     => false,
				'name'        => __( 'Filters', 'strong-testimonials' ),
				'slug'        => 'strong-testimonials-filters',
				'description' => __( 'An extension that allows you to create filters for your testimonials. Filters can be created from categories. Import your testimonials from 3rd party sources and group them by platform, review, or whatever else you could think of.', 'strong-testimonials' ),
			),
			'strong-testimonials-role-management'  => array(
				'available'   => false,
				'enabled'     => false,
				'name'        => __( 'Role Management', 'strong-testimonials' ),
				'slug'        => 'strong-testimonials-role-management',
				'description' => __( 'Granular control over who can add, edit, delete or manage testimonials. Expands on WordPress\'s current user role system.', 'strong-testimonials' ),
			),
			'strong-testimonials-mailchimp'        => array(
				'available'   => false,
				'enabled'     => false,
				'name'        => __( 'Mailchimp', 'strong-testimonials' ),
				'slug'        => 'strong-testimonials-mailchimp',
				'description' => __( 'With this extension you can automatically subscribe your users to a MailChimp email list. Follow up with a targeted message or a coupon to thank them for leaving a good review. Unlock even more marketing & automation potential.', 'strong-testimonials' ),
			),
			'strong-testimonials-custom-fields'    => array(
				'available'   => false,
				'enabled'     => false,
				'name'        => __( 'Custom Fields', 'strong-testimonials' ),
				'slug'        => 'strong-testimonials-custom-fields',
				'description' => __( 'Enhance your testimonials and submission forms with the Custom Fields extension to both collect and display additional information.', 'strong-testimonials' ),
			),
			'strong-testimonials-multiple-forms'   => array(
				'available'   => false,
				'enabled'     => false,
				'name'        => __( 'Multiple Forms', 'strong-testimonials' ),
				'slug'        => 'strong-testimonials-multiple-forms',
				'description' => __( 'Easily collect testimonials from customers by creating and customizing multiple forms at once.', 'strong-testimonials' ),
			),
		);
	}

	/**
	 * Get extensions
	 *
	 * @return array
	 */
	public function get_extensions() {
		$active_extensions = get_option( $this->active_extensions, array() );
		$current_plan      = 'free';

		$this->update_extension_status( $active_extensions, $current_plan );
		$this->sort_extensions_by_business_order();

		$divider_slugs      = $this->find_divider_positions( $current_plan );
		$ordered_extensions = $this->build_ordered_extensions();
		$this->extensions   = $this->insert_dividers( $ordered_extensions, $divider_slugs );

		return $this->extensions;
	}

	/**
	 * Update enabled and available status for each extension
	 *
	 * @param array  $active_extensions Active extension slugs.
	 * @param string $current_plan      Current plan name.
	 */
	private function update_extension_status( $active_extensions, $current_plan ) {
		foreach ( $this->extensions as $extension => $data ) {
			$this->extensions[ $extension ]['enabled']   = in_array( $extension, $active_extensions, true );
			$this->extensions[ $extension ]['available'] = false;

			if ( $current_plan && isset( $this->plan_map[ $current_plan ] ) ) {
				if ( in_array( $extension, $this->plan_map[ $current_plan ], true ) ) {
					$this->extensions[ $extension ]['available'] = true;
				}
			}
		}
	}

	/**
	 * Sort extensions by business plan order
	 */
	private function sort_extensions_by_business_order() {
		$business_order = isset( $this->plan_map['business'] ) ? array_values( $this->plan_map['business'] ) : array();
		$index_map      = array_flip( $business_order );

		uasort(
			$this->extensions,
			function ( $a, $b ) use ( $index_map ) {
				$a_in = isset( $index_map[ $a['slug'] ] );
				$b_in = isset( $index_map[ $b['slug'] ] );

				if ( $a_in && $b_in ) {
					return $index_map[ $a['slug'] ] - $index_map[ $b['slug'] ];
				}
				if ( $a_in ) {
					return -1;
				}
				if ( $b_in ) {
					return 1;
				}

				// Fallback: based on availability
				if ( $a['available'] !== $b['available'] ) {
					return intval( $b['available'] ) - intval( $a['available'] );
				}

				return strcmp( $a['slug'], $b['slug'] );
			}
		);
	}

	/**
	 * Get current plan
	 *
	 * @return string Current plan name.
	 */
	public function get_current_plan() {
		$plan = get_option( $this->current_plan, 'free' );
		return isset( $this->plan_map[ $plan ] ) ? $plan : 'free';
	}

	/**
	 * Get plan upgrade hierarchy
	 *
	 * @return array Plan upgrade orders.
	 */
	private function get_plan_upgrade_orders() {
		return array(
			'free'     => array( 'basic', 'plus', 'business' ),
			'basic'    => array( 'plus', 'business' ),
			'plus'     => array( 'business' ),
			'business' => array(),
		);
	}

	/**
	 * Find positions where dividers should be placed
	 *
	 * @param string $current_plan Current plan name.
	 * @return array Divider slugs mapped to divider data (with divider_key and plan).
	 */
	private function find_divider_positions( $current_plan ) {
		$plan_orders   = $this->get_plan_upgrade_orders();
		$current_order = isset( $this->plan_map[ $current_plan ] ) ? array_values( $this->plan_map[ $current_plan ] ) : array();
		$divider_slugs = array();

		if ( empty( $plan_orders[ $current_plan ] ) ) {
			return $divider_slugs;
		}

		foreach ( $plan_orders[ $current_plan ] as $next_plan ) {
			if ( empty( $this->plan_map[ $next_plan ] ) ) {
				continue;
			}

			$next_plan_order = array_values( $this->plan_map[ $next_plan ] );
			$exclude_order   = $this->get_exclude_order_for_plan( $current_plan, $next_plan, $current_order );

			$first_unique_slug = $this->find_first_unique_extension( $next_plan_order, $exclude_order );
			if ( $first_unique_slug ) {
				$divider_slugs[ $first_unique_slug ] = array(
					'divider_key' => 'divider-before-' . $first_unique_slug,
					'plan'        => $next_plan,
				);
			}
		}

		return $divider_slugs;
	}

	/**
	 * Get the order array to exclude when checking for unique extensions
	 *
	 * @param string $current_plan Current plan name.
	 * @param string $next_plan     Next plan name.
	 * @param array  $current_order Current plan order.
	 * @return array Order array to exclude.
	 */
	private function get_exclude_order_for_plan( $current_plan, $next_plan, $current_order ) {
		if ( 'business' === $next_plan ) {
			if ( 'basic' === $current_plan ) {
				return isset( $this->plan_map['plus'] ) ? array_values( $this->plan_map['plus'] ) : $current_order;
			} elseif ( 'free' === $current_plan ) {
				return isset( $this->plan_map['plus'] ) ? array_values( $this->plan_map['plus'] ) : $current_order;
			}
		}

		if ( 'plus' === $next_plan && 'free' === $current_plan ) {
			return isset( $this->plan_map['basic'] ) ? array_values( $this->plan_map['basic'] ) : $current_order;
		}

		return $current_order;
	}

	/**
	 * Find the first extension slug that's unique to the next plan
	 *
	 * @param array $next_plan_order Extension order from next plan.
	 * @param array $exclude_order   Extensions to exclude.
	 * @return string|null First unique extension slug or null.
	 */
	private function find_first_unique_extension( $next_plan_order, $exclude_order ) {
		foreach ( $next_plan_order as $next_slug ) {
			if ( strpos( $next_slug, 'divider-' ) === 0 ) {
				continue;
			}

			if ( isset( $this->extensions[ $next_slug ] ) && ! in_array( $next_slug, $exclude_order, true ) ) {
				return $next_slug;
			}
		}

		return null;
	}

	/**
	 * Build ordered extensions array based on business plan order
	 *
	 * @return array Ordered extensions.
	 */
	private function build_ordered_extensions() {
		$business_order     = isset( $this->plan_map['business'] ) ? array_values( $this->plan_map['business'] ) : array();
		$ordered_extensions = array();

		foreach ( $business_order as $slug ) {
			if ( $this->is_divider_string( $slug ) ) {
				continue;
			}

			if ( isset( $this->extensions[ $slug ] ) ) {
				$ordered_extensions[ $slug ] = $this->extensions[ $slug ];
			}
		}

		foreach ( $this->extensions as $slug => $data ) {
			if ( ! isset( $ordered_extensions[ $slug ] ) ) {
				$ordered_extensions[ $slug ] = $data;
			}
		}

		return $ordered_extensions;
	}

	/**
	 * Check if a slug is a divider string
	 *
	 * @param string $slug Slug to check.
	 * @return bool True if divider string.
	 */
	private function is_divider_string( $slug ) {
		return strpos( $slug, 'divider-' ) === 0;
	}

	/**
	 * Insert dividers into the extensions array
	 *
	 * @param array $ordered_extensions Ordered extensions array.
	 * @param array $divider_slugs      Divider slugs mapped to divider data (with divider_key and plan).
	 * @return array Extensions with dividers inserted.
	 */
	private function insert_dividers( $ordered_extensions, $divider_slugs ) {
		$extensions_with_divider = array();

		foreach ( $ordered_extensions as $slug => $ext ) {
			if ( isset( $divider_slugs[ $slug ] ) ) {
				$divider_data = $divider_slugs[ $slug ];
				$extensions_with_divider[ $divider_data['divider_key'] ] = array(
					'is_divider' => true,
					'slug'       => $divider_data['divider_key'],
					'plan'       => $divider_data['plan'],
					'url'        => \WPMTST_STORE_UPGRADE_URL . '?utm_source=strong-testimonials-pro&utm_medium=extensions&utm_campaign=upgrade-to-' . $divider_data['plan'],
				);
			}
			$extensions_with_divider[ $slug ] = $ext;
		}

		return $extensions_with_divider;
	}

	/**
	 * Calculate the numeric tier for a given plan.
	 *
	 * @param array  $plan_hierarchy Plan to tier mapping.
	 * @param string $plan           Current plan.
	 *
	 * @return int
	 */
	private function get_plan_tier( $plan_hierarchy, $plan ) {
		return $plan_hierarchy[ $plan ] ?? 0;
	}

	/**
	 * Apply badges for fields belonging to a tab with multiple plan mappings.
	 *
	 * @param array $fields          Fields configuration (by reference).
	 * @param array $plans_to_fields Required plans mapped to field ids.
	 * @param array $plan_hierarchy  Plan to tier mapping.
	 * @param int   $current_tier    Current plan tier.
	 */
	private function apply_field_badges( array &$fields, array $plans_to_fields, array $plan_hierarchy, $current_tier ) {
		foreach ( $plans_to_fields as $required_plan => $field_ids ) {
			$required_tier = $this->get_plan_tier( $plan_hierarchy, $required_plan );

			foreach ( $fields as &$field ) {
				if ( empty( $field['id'] ) || ! in_array( $field['id'], (array) $field_ids, true ) ) {
					continue;
				}

				if ( $current_tier >= $required_tier ) {
					$field['badge']  = null;
					$field['locked'] = false;
					continue;
				}

				$field['badge']  = $required_plan;
				$field['locked'] = true;
			}
			unset( $field );
		}
	}

	/**
	 * Apply badge for a tab that maps to a single required plan.
	 *
	 * @param array  $subtab         Tab configuration.
	 * @param string $required_plan  Plan required for unlocking.
	 * @param array  $plan_hierarchy Plan to tier mapping.
	 * @param int    $current_tier   Current plan tier.
	 *
	 * @return array
	 */
	private function apply_tab_badge( array $subtab, $required_plan, array $plan_hierarchy, $current_tier ) {
		$required_tier = $this->get_plan_tier( $plan_hierarchy, $required_plan );

		if ( $current_tier >= $required_tier ) {
			$subtab['badge']  = null;
			$subtab['locked'] = false;

			return $subtab;
		}

		$subtab['badge']  = $required_plan;
		$subtab['locked'] = true;

		return $subtab;
	}

	/**
	 * Check if an addon is upgradable
	 *
	 * @param string $addon Addon slug.
	 * @return bool True if upgradable, false otherwise.
	 */
	public function is_upgradable_addon( $addon = null ) {
		if ( ! $addon ) {
			return false;
		}

		$current_plan = get_option( $this->current_plan, 'free' );
		if ( ! isset( $this->plan_map[ $current_plan ] ) ) {
			$current_plan = 'free';
		}

		if ( 'strong-testimonials-pro' === $addon ) {
			return false;
		}

		// Legacy standalone extension plugins (old PRO system) should not show upsells.
		$plugin_file    = $addon . '/' . $addon . '.php';
		$active_plugins = (array) get_option( 'active_plugins', array() );
		if ( in_array( $plugin_file, $active_plugins, true ) ) {
			return false;
		}
		if ( is_multisite() ) {
			$network_plugins = array_keys( (array) get_site_option( 'active_sitewide_plugins', array() ) );
			if ( in_array( $plugin_file, $network_plugins, true ) ) {
				return false;
			}
		}

		if ( ! defined( 'WPMTST_PRO_VERSION' ) ) {
			return true;
		}

		$owned_extensions = $this->plan_map[ $current_plan ] ?? array();

		return ! in_array( $addon, $owned_extensions, true );
	}
	/**
	 * Get active extensions
	 *
	 * @return array Active extensions.
	 */
	public function get_active_extensions() {
		if ( empty( self::$active_extensions_cache ) ) {
			self::$active_extensions_cache = get_option( $this->active_extensions, array() );
		}
		return self::$active_extensions_cache;
	}

	/**
	 * Check if an extension is enabled
	 *
	 * @param string $extension Extension slug.
	 * @return bool True if enabled, false otherwise.
	 */
	public function extension_enabled( $extension ) {
		$active_extensions = $this->get_active_extensions();
		return in_array( $extension, $active_extensions, true );
	}
}

/**
 * Compatibility shim for old extensions that check for Strong_Testimonials_WPChill_Upsells.
 */
if ( ! class_exists( 'Strong_Testimonials_WPChill_Upsells' ) ) {
	class Strong_Testimonials_WPChill_Upsells {

		private static $instance;

		public static function get_instance( $args = array() ) {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		public function is_upgradable_addon( $addon ) {
			return Strong_Testimonials_Extensions_Base::get_instance()->is_upgradable_addon( $addon );
		}
	}
}
