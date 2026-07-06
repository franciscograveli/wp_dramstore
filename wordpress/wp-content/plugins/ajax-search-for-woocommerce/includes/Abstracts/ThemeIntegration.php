<?php

namespace DgoraWcas\Abstracts;

use DgoraWcas\Helpers;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Base class for integration with themes
 */
abstract class ThemeIntegration {

	protected $themeSlug = '';
	protected $themeName = '';
	protected $args      = [];

	public function __construct( $themeSlug = '', $themeName = '', $args = [] ) {
		$this->themeSlug = $themeSlug;
		$this->themeName = $themeName;

		if ( empty( $this->themeName ) || empty( $this->themeSlug ) ) {
			return;
		}

		$this->args = wp_parse_args(
			$args,
			[
				'replaceSearchSuffix'          => '',
				'partialFilename'              => '',
				'alwaysEnabled'                => false,
				'whiteLabel'                   => false,
				'forceMobileOverlay'           => false,
				'forceMobileOverlayBreakpoint' => false,
				'forceLayoutBreakpoint'        => false,
			]
		);

		$this->maybeOverwriteSearch();
		$this->maybeOverwriteSettings();

		// Run additional functions on init.
		if ( is_callable( [ $this, 'init' ] ) ) {
			$this->init();
		}

		// Run additional functions besides loading the file with integration.
		if ( is_callable( [ $this, 'extraFunctions' ] ) && $this->canReplaceSearch() ) {
			$this->extraFunctions();
		}

		add_filter( 'dgwt/wcas/settings', [ $this, 'registerSettings' ] );
	}

	/**
	 * Add settings
	 *
	 * @param array $settings
	 *
	 * @return array
	 */
	public function registerSettings( $settings ) {
		$key = 'dgwt_wcas_basic';

		$settings[ $key ][10] = [
			'name'  => $this->themeSlug . '_main_head',
			'label' => sprintf( __( 'Replace the search bars', 'ajax-search-for-woocommerce' ), $this->themeName ),
			'type'  => 'head',
			'class' => 'dgwt-wcas-sgs-header',
		];

		if ( ! $this->args['whiteLabel'] ) {
			$settings[ $key ][52] = [
				'name'  => $this->themeSlug . '_settings_head',
				'label' => sprintf( __( '%s Theme', 'ajax-search-for-woocommerce' ), $this->themeName ),
				'type'  => 'desc',
				'desc'  => Helpers::embeddingInThemeHtml(),
				'class' => 'dgwt-wcas-sgs-themes-label',
			];

			$img = DGWT_WCAS()->themeCompatibility->getThemeImageSrc();
			if ( ! empty( $img ) ) {
				$settings[ $key ][52]['label'] = '<img src="' . $img . '">';
			}
		}

		if ( $this->args['whiteLabel'] ) {
			$replaceDesc = __( "Replace your theme's default search bars.", 'ajax-search-for-woocommerce' ) . $this->args['replaceSearchSuffix'];
		} else {
			$replaceDesc = __( 'Replace them', 'ajax-search-for-woocommerce' ) . $this->args['replaceSearchSuffix'];
		}

		if ( ! $this->args['alwaysEnabled'] ) {
			$settings[ $key ][55] = [
				'name'    => $this->themeSlug . '_replace_search',
				'label'   => __( 'Search bars', 'ajax-search-for-woocommerce' ),
				'desc'    => $replaceDesc,
				'type'    => 'checkbox',
				'default' => 'off',
			];
		}

		$settings[ $key ][90] = [
			'name'  => $this->themeSlug . '_othersways__head',
			'label' => __( 'Alternative ways to embed a search bar', 'ajax-search-for-woocommerce' ),
			'type'  => 'head',
			'class' => 'dgwt-wcas-sgs-header',
		];

		return $settings;
	}

	/**
	 * Check if can replace the native search form with the FiboSearch form.
	 *
	 * @return bool
	 */
	protected function canReplaceSearch() {
		$canIntegrate = false;

		if ( $this->args['alwaysEnabled'] ) {
			$canIntegrate = true;
		} elseif ( DGWT_WCAS()->settings->getOption( $this->themeSlug . '_replace_search', 'off' ) === 'on' ) {
			$canIntegrate = true;
		}

		return $canIntegrate;
	}

	/**
	 * Overwrite search
	 *
	 * @return void
	 */
	protected function maybeOverwriteSearch() {

		// Don't include partials when you are on dashboard.
		if ( is_admin() ) {
			return;
		}

		$partialFilename = ! empty( $this->args['partialFilename'] ) ? $this->args['partialFilename'] : $this->themeSlug . '.php';
		$partialPath     = DGWT_WCAS_DIR . 'partials/themes/' . $partialFilename;
		$partialMuPath   = str_replace( '.php', '-mu.php', $partialPath );

		// Load "must-use" partials
		if ( file_exists( $partialMuPath ) ) {
			require_once $partialMuPath;
		}

		if ( $this->canReplaceSearch() && file_exists( $partialPath ) ) {
			require_once $partialPath;
		}
	}

	/**
	 * Overwrite settings
	 *
	 * @return void
	 */
	protected function maybeOverwriteSettings() {
		if ( ! $this->canReplaceSearch() ) {
			return;
		}

		if ( $this->args['forceMobileOverlay'] ) {
			// Force enable overlay for mobile search.
			add_filter(
				'dgwt/wcas/settings/load_value/key=enable_mobile_overlay',
				function () {
					return 'on';
				}
			);

			// Mark that the value of the option "mobile overlay" is forced.
			add_filter(
				'dgwt/wcas/settings/section=form',
				function ( $settings ) {
					$settings[680]['disabled'] = true;
					$settings[680]['label']    = Helpers::createOverrideTooltip( 'ovtt-theme-mobile-overlay', Helpers::getOverrideOptionText( $this->themeName ) ) . $settings[680]['label'];

					return $settings;
				}
			);
		}

		if ( $this->args['forceMobileOverlayBreakpoint'] !== false ) {
			// Change mobile breakpoint.
			if ( is_numeric( $this->args['forceMobileOverlayBreakpoint'] ) && intval( $this->args['forceMobileOverlayBreakpoint'] ) > 0 ) {
				add_filter(
					'dgwt/wcas/settings/load_value/key=mobile_overlay_breakpoint',
					function () {
						return $this->args['forceMobileOverlayBreakpoint'];
					}
				);
			}

			// Mark that the value of the option "mobile breakpoint" is forced.
			add_filter(
				'dgwt/wcas/settings/section=form',
				function ( $settings ) {
					$settings[685]['disabled'] = true;
					$settings[685]['label']    = Helpers::createOverrideTooltip( 'ovtt-theme-breakpoint', Helpers::getOverrideOptionText( $this->themeName ) ) . $settings[685]['label'];

					return $settings;
				}
			);
		}

		if ( $this->args['forceLayoutBreakpoint'] !== false ) {
			// Change layout breakpoint.
			if ( is_numeric( $this->args['forceLayoutBreakpoint'] ) && intval( $this->args['forceLayoutBreakpoint'] ) > 0 ) {
				add_filter(
					'dgwt/wcas/settings/load_value/key=mobile_breakpoint',
					function () {
						return $this->args['forceLayoutBreakpoint'];
					}
				);
			}

			// Mark that the value of the option "layout breakpoint" is forced.
			add_filter(
				'dgwt/wcas/settings/section=form',
				function ( $settings ) {
					$settings[670]['disabled'] = true;
					$settings[670]['label']    = Helpers::createOverrideTooltip( 'ovtt-theme-breakpoint', Helpers::getOverrideOptionText( $this->themeName ) ) . $settings[670]['label'];

					return $settings;
				}
			);
		}
	}
}
