<?php
/**
 * Theme Info Handler.
 *
 * Merged handler for theme/get-compatibility and theme/list-supported.
 * Returns current theme info, support status, compatibility method,
 * and the full list of natively supported themes.
 *
 * @package header-footer-elementor
 * @since 2.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class HFE_Theme_Info_Handler
 *
 * Implements HFE_Ability_Handler for the theme/get-info ability.
 *
 * @since 2.9.0
 */
class HFE_Theme_Info_Handler implements HFE_Ability_Handler {

	use HFE_Abilities_Helpers;

	/**
	 * Supported themes map: slug => display name.
	 *
	 * @var array
	 */
	private $supported_themes = [
		'astra'                => 'Astra',
		'genesis'              => 'Genesis',
		'bb-theme'             => 'Beaver Builder Theme',
		'beaver-builder-theme' => 'Beaver Builder Theme',
		'generatepress'        => 'GeneratePress',
		'oceanwp'              => 'OceanWP',
		'storefront'           => 'Storefront',
		'hello-elementor'      => 'Hello Elementor',
		'kadence'              => 'Kadence',
		'neve'                 => 'Neve',
		'blocksy'              => 'Blocksy',
	];

	/**
	 * Get the ability name.
	 *
	 * @since 2.9.0
	 *
	 * @return string Ability name without plugin prefix.
	 */
	public function get_name() {
		return 'theme-get-info';
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
			'label'               => __( 'Get Theme Info', 'header-footer-elementor' ),
			'description'         => __( 'Returns current theme compatibility info and the full list of natively supported themes.', 'header-footer-elementor' ),
			'category'            => 'hfe-theme-compat',
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
					'theme_name'           => [ 'type' => 'string' ],
					'theme_display_name'   => [ 'type' => 'string' ],
					'is_supported'         => [ 'type' => 'boolean' ],
					'compatibility_method' => [ 'type' => 'string' ],
					'supported_themes'     => [
						'type'  => 'array',
						'items' => [
							'type'       => 'object',
							'properties' => [
								'slug'      => [ 'type' => 'string' ],
								'name'      => [ 'type' => 'string' ],
								'is_active' => [ 'type' => 'boolean' ],
							],
						],
					],
				],
			],
			'meta'                => [
				'annotations'  => [
					'readonly'     => true,
					'destructive'  => false,
					'idempotent'   => true,
					'instructions' => 'Use this to check current theme support status, compatibility method, and which themes are natively supported.',
				],
				'show_in_rest' => true,
				'mcp'          => [ 'public' => true ],
			],
		];
	}

	/**
	 * Execute the ability.
	 *
	 * Returns current theme info and the deduplicated list of supported themes.
	 *
	 * @since 2.9.0
	 *
	 * @param array $input Unused input parameters.
	 * @return array Theme info with supported themes list.
	 */
	public function execute( $input ) {
		$theme_slug = get_template();
		$theme_obj  = wp_get_theme( $theme_slug );

		return [
			'theme_name'           => $theme_slug,
			'theme_display_name'   => $theme_obj->get( 'Name' ),
			'is_supported'         => (bool) get_option( 'hfe_is_theme_supported', false ),
			'compatibility_method' => $this->resolve_compat_method(),
			'supported_themes'     => $this->get_supported_themes_list( $theme_slug ),
		];
	}

	/**
	 * Get deduplicated list of supported themes.
	 *
	 * Deduplicates by display name to avoid listing Beaver Builder Theme twice
	 * (bb-theme and beaver-builder-theme map to the same display name).
	 *
	 * @since 2.9.0
	 *
	 * @param string $current_theme Current theme slug.
	 * @return array Array of supported theme objects.
	 */
	private function get_supported_themes_list( $current_theme ) {
		$result     = [];
		$seen_names = [];

		foreach ( $this->supported_themes as $slug => $name ) {
			// Avoid duplicate entries for bb-theme / beaver-builder-theme.
			if ( isset( $seen_names[ $name ] ) ) {
				continue;
			}
			$seen_names[ $name ] = true;

			$result[] = [
				'slug'      => $slug,
				'name'      => $name,
				'is_active' => $slug === $current_theme,
			];
		}

		return $result;
	}
}
