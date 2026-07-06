<?php

namespace DgoraWcas\Integrations\Themes;

use DgoraWcas\Helpers;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ThemesCompatibility {
	private $themeSlug       = '';
	private $themeName       = '';
	private $themeVersion    = '';
	private $parentThemeName = '';
	private $theme           = null;
	private $supportActive   = false;

	public function __construct() {
		// Break early if the "nofibosearch" mode is active.
		if ( Helpers::isNoFiboSearchModeActive() ) {
			return;
		}

		$this->setCurrentTheme();
		$this->loadCompatibilities();
	}

	private function setCurrentTheme() {
		$theme = wp_get_theme();

		if ( is_object( $theme ) && is_a( $theme, 'WP_Theme' ) ) {
			$template        = $theme->get_template();
			$stylesheet      = $theme->get_stylesheet();
			$isChildTheme    = $template !== $stylesheet;
			$this->themeSlug = sanitize_title( $theme->Name );

			$this->theme           = $theme;
			$this->themeName       = $theme->name;
			$this->themeVersion    = $theme->offsetGet( 'Version' );
			$this->parentThemeName = ! empty( $theme->parent_theme ) ? $theme->parent_theme : '';

			if ( $isChildTheme ) {
				$this->themeSlug    = strtolower( $template );
				$this->themeVersion = $theme->parent()->get( 'Version' );
			}
		}

		$this->themeSlug = apply_filters( 'dgwt/wcas/integrations/themes/current_theme_slug', $this->themeSlug );
		$this->themeName = apply_filters( 'dgwt/wcas/integrations/themes/current_theme_name', $this->themeName );
		if ( $this->isChildTheme() ) {
			$this->parentThemeName = apply_filters( 'dgwt/wcas/integrations/themes/current_parent_theme_name', $this->parentThemeName );
		}
	}

	/**
	 *  All supported themes
	 *
	 * @return array
	 */
	public function supportedThemes() {
		return [
			'storefront'       => [
				'slug' => 'storefront',
				'name' => 'Storefront',
			],
			'flatsome'         => [
				'slug' => 'flatsome',
				'name' => 'Flatsome',
				'args' => [
					'forceMobileOverlayBreakpoint' => 850,
					'forceLayoutBreakpoint'        => 850,
				],
			],
			'astra'            => [
				'slug' => 'astra',
				'name' => 'Astra',
				'args' => [
					'forceMobileOverlayBreakpoint' => true,
					'forceLayoutBreakpoint'        => true,
				],
			],
			'thegem'           => [
				'slug' => 'thegem',
				'name' => 'TheGem',
			],
			'impreza'          => [
				'slug' => 'impreza',
				'name' => 'Impreza',
				'args' => [
					'alwaysEnabled' => true,
				],
			],
			'woodmart'         => [
				'slug' => 'woodmart',
				'name' => 'Woodmart',
			],
			'enfold'           => [
				'slug' => 'enfold',
				'name' => 'Enfold',
			],
			'shopkeeper'       => [
				'slug' => 'shopkeeper',
				'name' => 'Shopkeeper',
				'args' => [
					'forceMobileOverlayBreakpoint' => 767,
					'forceLayoutBreakpoint'        => 767,
				],
			],
			'the7'             => [
				'slug' => 'the7',
				'name' => 'The7',
			],
			'dt-the7'          => [
				'slug' => 'dt-the7',
				'name' => 'The7',
			],
			'avada'            => [
				'slug' => 'avada',
				'name' => 'Avada',
			],
			'shop-isle'        => [
				'slug' => 'shop-isle',
				'name' => 'Shop Isle',
			],
			'shopical'         => [
				'slug' => 'shopical',
				'name' => 'Shopical',
			],
			'shopical-pro'     => [
				'slug' => 'shopical-pro',
				'name' => 'ShopicalPro',
				'args' => [
					'partialFilename' => 'shopical.php',
				],
			],
			'ekommart'         => [
				'slug' => 'ekommart',
				'name' => 'Ekommart',
			],
			'savoy'            => [
				'slug' => 'savoy',
				'name' => 'Savoy',
			],
			'sober'            => [
				'slug' => 'sober',
				'name' => 'Sober',
			],
			'bridge'           => [
				'slug' => 'bridge',
				'name' => 'Bridge',
			],
			'divi'             => [
				'slug' => 'divi',
				'name' => 'Divi',
				'args' => [
					'forceMobileOverlayBreakpoint' => 980,
					'forceLayoutBreakpoint'        => 980,
				],
			],
			'block-shop'       => [
				'slug' => 'block-shop',
				'name' => 'BlockShop',
				'args' => [
					'forceMobileOverlayBreakpoint' => 1200,
					'forceLayoutBreakpoint'        => 1200,
				],
			],
			'dfd-ronneby'      => [
				'slug' => 'dfd-ronneby',
				'name' => 'DFDRonneby',
				'args' => [
					'forceMobileOverlayBreakpoint' => 500,
					'forceLayoutBreakpoint'        => 500,
				],
			],
			'restoration'      => [
				'slug' => 'restoration',
				'name' => 'Restoration',
			],
			'salient'          => [
				'slug' => 'salient',
				'name' => 'Salient',
				'args' => [
					'forceMobileOverlayBreakpoint' => 1000,
					'forceLayoutBreakpoint'        => 1000,
				],
			],
			'konte'            => [
				'slug' => 'konte',
				'name' => 'Konte',
				'args' => [
					'forceMobileOverlayBreakpoint' => 1024,
					'forceLayoutBreakpoint'        => 1024,
				],
			],
			'rehub-theme'      => [
				'slug' => 'rehub-theme',
				'name' => 'Rehub',
				'args' => [
					'forceMobileOverlayBreakpoint' => 1200,
					'forceLayoutBreakpoint'        => 1200,
				],
			],
			'supro'            => [
				'slug' => 'supro',
				'name' => 'Supro',
			],
			'open-shop'        => [
				'slug' => 'open-shop',
				'name' => 'OpenShop',
			],
			'ciyashop'         => [
				'slug' => 'ciyashop',
				'name' => 'CiyaShop',
			],
			'bigcart'          => [
				'slug' => 'bigcart',
				'name' => 'BigCart',
				'args' => [
					'forceMobileOverlayBreakpoint' => 782,
					'forceLayoutBreakpoint'        => 782,
				],
			],
			'top-store-pro'    => [
				'slug' => 'top-store-pro',
				'name' => 'TopStorePro',
			],
			'top-store'        => [
				'slug' => 'top-store',
				'name' => 'TopStore',
				'args' => [
					'partialFilename' => 'top-store-pro.php',
				],
			],
			'goya'             => [
				'slug' => 'goya',
				'name' => 'Goya',
			],
			'electro'          => [
				'slug' => 'electro',
				'name' => 'Electro',
			],
			'shopisle-pro'     => [
				'slug' => 'shopisle-pro',
				'name' => 'ShopIsle PRO',
				'args' => [
					'partialFilename' => 'shop-isle.php',
				],
			],
			'estore'           => [
				'slug' => 'estore',
				'name' => 'eStore',
			],
			'estore-pro'       => [
				'slug' => 'estore-pro',
				'name' => 'eStore Pro',
				'args' => [
					'partialFilename' => 'estore.php',
				],
			],
			'generatepress'    => [
				'slug' => 'generatepress',
				'name' => 'GeneratePress',
			],
			'open-shop-pro'    => [
				'slug' => 'open-shop-pro',
				'name' => 'Open Shop Pro',
				'args' => [
					'partialFilename' => 'open-shop.php',
				],
			],
			'uncode'           => [
				'slug' => 'uncode',
				'name' => 'Uncode',
				'args' => [
					'forceMobileOverlayBreakpoint' => 960,
					'forceLayoutBreakpoint'        => 960,
				],
			],
			'xstore'           => [
				'slug' => 'xstore',
				'name' => 'XStore',
			],
			'kadence'          => [
				'slug' => 'kadence',
				'name' => 'Kadence',
			],
			'thegem-elementor' => [
				'slug' => 'thegem-elementor',
				'name' => 'TheGem (Elementor)',
			],
			'thegem-wpbakery'  => [
				'slug' => 'thegem-wpbakery',
				'name' => 'TheGem (WPBakery)',
				'args' => [
					'partialFilename' => 'thegem-elementor.php',
				],
			],
			'neve'             => [
				'slug' => 'neve',
				'name' => 'Neve',
			],
			'woostify'         => [
				'slug' => 'woostify',
				'name' => 'Woostify',
			],
			'oceanwp'          => [
				'slug' => 'oceanwp',
				'name' => 'OceanWP',
			],
			'webshop'          => [
				'slug' => 'webshop',
				'name' => 'WebShop',
				'args' => [
					'forceMobileOverlay'           => true,
					'forceMobileOverlayBreakpoint' => 767,
				],
			],
			'essentials'       => [
				'slug' => 'essentials',
				'name' => 'Essentials',
				'args' => [
					'forceMobileOverlay'           => true,
					'forceMobileOverlayBreakpoint' => 991,
				],
			],
			'blocksy'          => [
				'slug' => 'blocksy',
				'name' => 'Blocksy',
				'args' => [
					'forceMobileOverlay'           => true,
					'forceMobileOverlayBreakpoint' => 689,
				],
			],
			'qwery'            => [
				'slug' => 'qwery',
				'name' => 'Qwery',
				'args' => [
					'forceMobileOverlay'           => true,
					'forceMobileOverlayBreakpoint' => 767,
				],
			],
			'storebiz'         => [
				'slug' => 'storebiz',
				'name' => 'StoreBiz',
				'args' => [
					'forceMobileOverlay'           => true,
					'forceMobileOverlayBreakpoint' => 767,
				],
			],
			'minimog'          => [
				'slug' => 'minimog',
				'name' => 'Minimog',
			],
			'total'            => [
				'slug' => 'total',
				'name' => 'Total',
				'args' => [
					'forceMobileOverlay'           => true,
					'forceMobileOverlayBreakpoint' => 959,
				],
			],
			'bricks'           => [
				'slug' => 'bricks',
				'name' => 'Bricks',
				'args' => [
					'alwaysEnabled'  => true,
					'minimumVersion' => '1.11.1',
				],
			],
			'bacola'           => [
				'slug' => 'bacola',
				'name' => 'Bacola',
				'args' => [
					'minimumVersion'               => '1.5.1.6',
					'forceMobileOverlayBreakpoint' => 768,
				],
			],
			'betheme'          => [
				'slug' => 'betheme',
				'name' => 'Betheme',
				'args' => [
					'forceMobileOverlay'           => true,
					'forceMobileOverlayBreakpoint' => 767,
				],
			],
			'rey'              => [
				'slug' => 'rey',
				'name' => 'Rey',
				'args' => [
					'forceMobileOverlay'           => true,
					'forceMobileOverlayBreakpoint' => 1024,
					'forceLayoutBreakpoint'        => 1024,
				],
			],
			'cartzilla'        => [
				'slug' => 'cartzilla',
				'name' => 'Cartzilla',
				'args' => [
					'forceMobileOverlay'           => true,
					'forceMobileOverlayBreakpoint' => 991,
				],
			],
			'mobex'            => [
				'slug' => 'mobex',
				'name' => 'Mobex',
				'args' => [
					'alwaysEnabled' => true,
				],
			],
		];
	}

	/**
	 * Load class with compatibilities logic for current theme
	 *
	 * @return void
	 */
	private function loadCompatibilities() {
		foreach ( $this->supportedThemes() as $theme ) {
			if ( $theme['slug'] === $this->themeSlug ) {
				if ( isset( $theme['args']['minimumVersion'] ) && version_compare( $this->themeVersion, $theme['args']['minimumVersion'], '<' ) ) {
					$currentVersion = $this->themeVersion;
					// Break if theme version is lower than required.
					add_filter(
						'dgwt/wcas/troubleshooting/unsupported_theme_version',
						function () use ( $theme, $currentVersion ) {
							return [
								'name'           => $theme['name'],
								'currentVersion' => $currentVersion,
								'minimumVersion' => $theme['args']['minimumVersion'],
							];
						}
					);
					break;
				}

				$this->supportActive = true;

				$class = '\\DgoraWcas\\Integrations\\Themes\\';

				if ( isset( $theme['className'] ) ) {
					$class .= $theme['className'] . '\\' . $theme['className'];
				} else {
					$class .= $theme['name'] . '\\' . $theme['name'];
				}

				$args = isset( $theme['args'] ) && is_array( $theme['args'] ) ? $theme['args'] : [];

				if ( $this->isWhiteLabel() ) {
					$args['whiteLabel'] = true;
				}

				if ( class_exists( $class ) ) {
					new $class( $this->themeSlug, $this->themeName, $args );
				} else {
					new GenericTheme( $this->themeSlug, $this->themeName, $args );
				}

				break;
			}
		}
	}

	/**
	 * Check if current theme is supported
	 *
	 * @return bool
	 */
	public function isCurrentThemeSupported() {
		return $this->supportActive;
	}

	/**
	 * Get current theme info
	 *
	 * @return null|object
	 */
	public function getTheme() {
		return $this->theme;
	}

	/**
	 * Get the name of the current theme
	 *
	 * @return string
	 */
	public function getThemeName() {
		return ! empty( $this->themeName ) && is_string( $this->themeName ) ? $this->themeName : '';
	}

	/**
	 * Check if the current them is child theme
	 *
	 * @return bool
	 */
	public function isChildTheme() {
		return ! empty( $this->parentThemeName );
	}

	/**
	 * Check if the integration is under white label
	 *
	 * @return bool
	 */
	public function isWhiteLabel() {
		return apply_filters( 'dgwt/wcas/integrations/themes/white_label', false );
	}

	/**
	 * Get the name of the current parent theme
	 *
	 * @return string
	 */
	public function getParentThemeName() {
		return ! empty( $this->parentThemeName ) ? $this->parentThemeName : '';
	}

	/**
	 * Get current theme image src
	 *
	 * @return string
	 */
	public function getThemeImageSrc() {
		$src = '';

		if ( ! empty( $this->theme ) ) {

			foreach ( [ 'png', 'jpg' ] as $ext ) {
				if ( empty( $src ) && file_exists( $this->theme->get_template_directory() . '/screenshot.' . $ext ) ) {
					$src = $this->theme->get_template_directory_uri() . '/screenshot.' . $ext;
					break;
				}
			}
		}

		return ! empty( $src ) ? esc_url( $src ) : '';
	}

}


