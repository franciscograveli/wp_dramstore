<?php

namespace DgoraWcas\Integrations\Themes\Mobex;

use DgoraWcas\Abstracts\ThemeIntegration;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Mobex extends ThemeIntegration {
	public function init() {
		add_filter(
			'dgwt/wcas/settings',
			function ( $settings ) {
				if ( isset( $settings['dgwt_wcas_basic'][52]['desc'] ) ) {
					$settings['dgwt_wcas_basic'][52]['desc'] = '';
				}
				if ( isset( $settings['dgwt_wcas_basic'][10]['label'] ) ) {
					$settings['dgwt_wcas_basic'][10]['label'] = '';
				}
				if ( isset( $settings['dgwt_wcas_basic'][90]['label'] ) ) {
					$settings['dgwt_wcas_basic'][90]['label'] = 'Ways to embed a search bar';
				}
				return $settings;
			},
			20
		);

		add_action(
			'wp_enqueue_scripts',
			function () {
				wp_enqueue_script( 'jquery-dgwt-wcas' );
			},
			15
		);
	}
}
