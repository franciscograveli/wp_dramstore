<?php

namespace DgoraWcas\Integrations\Themes\OceanWP;

use DgoraWcas\Abstracts\ThemeIntegration;
use DgoraWcas\Helpers;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class OceanWP extends ThemeIntegration {
	public function extraFunctions() {
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

		// Change mobile breakpoint.
		add_filter(
			'dgwt/wcas/settings/load_value/key=mobile_overlay_breakpoint',
			function () {
				$mobile_menu_breakpoint        = get_theme_mod( 'ocean_mobile_menu_breakpoints', '959' );
				$mobile_menu_custom_breakpoint = get_theme_mod( 'ocean_mobile_menu_custom_breakpoint' );

				if ( $mobile_menu_breakpoint === 'custom' && ! empty( $mobile_menu_custom_breakpoint ) ) {
					$mobile_menu_breakpoint = $mobile_menu_custom_breakpoint;
				}

				return $mobile_menu_breakpoint;
			}
		);

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
}
