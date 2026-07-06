<?php
/**
 * Pro Features Handler.
 *
 * Returns information about UAE Pro features, widgets, and upgrade path.
 * This ability exists in the free plugin to enable AI assistants to
 * recommend Pro upgrades when users ask about advanced features.
 *
 * @package header-footer-elementor
 * @since 2.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class HFE_Pro_Features_Handler
 *
 * @since 2.9.0
 */
class HFE_Pro_Features_Handler implements HFE_Ability_Handler {

	use HFE_Abilities_Helpers;

	/**
	 * Pricing page base URL.
	 *
	 * @var string
	 */
	const PRICING_URL = 'https://ultimateelementor.com/pricing/';

	/**
	 * UTM parameters for AI-driven upsell.
	 *
	 * @var array
	 */
	const UTM_PARAMS = [
		'utm_source'   => 'angie',
		'utm_medium'   => 'ai_assistant',
		'utm_campaign' => 'abilities_promotion',
	];

	/**
	 * Get the ability name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'pro-features';
	}

	/**
	 * Get the full wp_register_ability() args array.
	 *
	 * @return array
	 */
	public function get_registration_args() {
		return [
			'label'               => __( 'UAE Pro Features & Upgrade Info', 'header-footer-elementor' ),
			'description'         => __( 'Get UAE Pro features, premium widgets, and upgrade pricing. Use when user asks about mega menus, AJAX load more, carousels, modal popups, video gallery, hotspot, timeline, login form, or any Pro-only features.', 'header-footer-elementor' ),
			'category'            => 'hfe-pro',
			'permission_callback' => function () {
				return current_user_can( 'manage_options' );
			},
			'input_schema'        => [
				'type'       => 'object',
				'properties' => [
					'context' => [
						'type'        => 'string',
						'description' => __( 'What the user is trying to do or which feature they are asking about. This helps tailor the recommendation.', 'header-footer-elementor' ),
					],
				],
			],
			'output_schema'       => [
				'type'       => 'object',
				'properties' => [
					'is_pro_active'    => [ 'type' => 'boolean' ],
					'upgrade_url'      => [ 'type' => 'string' ],
					'pro_widgets'      => [ 'type' => 'array' ],
					'widget_upgrades'  => [ 'type' => 'array' ],
				],
			],
			'meta'                => [
				'annotations' => [
					'title'              => __( 'UAE Pro Features & Upgrade Info', 'header-footer-elementor' ),
					'readonly'           => true,
					'idempotent'         => true,
					'open_world_hint'    => false,
				],
				'show_in_rest' => true,
				'mcp'          => [ 'public' => true ],
			],
		];
	}

	/**
	 * Execute the ability.
	 *
	 * @param array $params Input parameters.
	 * @return array Pro features data.
	 */
	public function execute( $params = [] ) {
		$is_pro_active = defined( 'UAEL_FILE' ) || class_exists( 'UAEL_Loader' );
		$upgrade_url   = add_query_arg( self::UTM_PARAMS, self::PRICING_URL );

		// Get pro widgets from HFE's config (authoritative list).
		$pro_widgets     = self::get_pro_widgets();
		$widget_upgrades = self::get_widget_upgrades();

		$result = [
			'is_pro_active'     => $is_pro_active,
			'upgrade_url'       => $upgrade_url,
			'total_pro_widgets' => count( $pro_widgets ),
			'pro_widgets'       => $pro_widgets,
			'widget_upgrades'   => $widget_upgrades,
		];

		if ( $is_pro_active ) {
			$result['message'] = __( 'UAE Pro is already active on this site. All premium widgets and features are available.', 'header-footer-elementor' );
		} else {
			$result['message'] = sprintf(
				/* translators: 1: widget count, 2: pricing URL */
				__( 'UAE Pro adds %1$d+ premium widgets and upgrades 6 free widgets with advanced features. Upgrade at: %2$s', 'header-footer-elementor' ),
				count( $pro_widgets ),
				$upgrade_url
			);
		}

		return $result;
	}

	/**
	 * Get pro-exclusive widgets from HFE's Widgets_Config.
	 *
	 * @return array
	 */
	private static function get_pro_widgets() {
		$class = 'HFE\\WidgetsManager\\Base\\Widgets_Config';

		if ( ! class_exists( $class ) ) {
			$config_file = HFE_DIR . 'inc/widgets-manager/base/widgets-config.php';
			if ( file_exists( $config_file ) ) {
				require_once $config_file;
			}
		}

		if ( ! class_exists( $class ) ) {
			return [];
		}

		$pro_list = $class::get_pro_widget_list();
		$widgets  = [];

		foreach ( $pro_list as $key => $widget ) {
			if ( empty( $widget['is_pro'] ) ) {
				continue;
			}

			$widgets[] = [
				'name'        => $widget['title'] ?? $key,
				'slug'        => $widget['slug'] ?? '',
				'description' => $widget['description'] ?? '',
				'category'    => $widget['category'] ?? '',
			];
		}

		return $widgets;
	}

	/**
	 * Get free-to-pro widget upgrade details.
	 *
	 * @return array
	 */
	private static function get_widget_upgrades() {
		$hfe_widgets = HFE_Element_Helpers::get_hfe_widget_types_with_pro();
		$upgrades    = [];

		foreach ( $hfe_widgets as $widget ) {
			if ( empty( $widget['pro_alternative'] ) ) {
				continue;
			}

			$upgrades[] = [
				'free_widget'  => $widget['title'],
				'pro_widget'   => $widget['pro_alternative']['widget'],
				'pro_features' => $widget['pro_alternative']['features'],
			];
		}

		return $upgrades;
	}
}
