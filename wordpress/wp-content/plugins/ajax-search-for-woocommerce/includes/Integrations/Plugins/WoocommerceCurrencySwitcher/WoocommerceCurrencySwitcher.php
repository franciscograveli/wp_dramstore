<?php

namespace DgoraWcas\Integrations\Plugins\WoocommerceCurrencySwitcher;

// Exit if accessed directly
use DgoraWcas\Integrations\Plugins\AbstractPluginIntegration;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Integration with Currency Switcher Professional for WooCommerce
 *
 * Plugin URL: https://wordpress.org/plugins/woocommerce-currency-switcher/
 * Author: realmag777
 */
class WoocommerceCurrencySwitcher extends AbstractPluginIntegration {
	protected const LABEL         = 'FOX - Currency Switcher Professional for WooCommerce';
	protected const VERSION_CONST = 'WOOCS_VERSION';

	public function init(): void {
		add_filter(
			'dgwt/wcas/product/html_price',
			function ( $price ) {
				return preg_replace(
					'/\s*woocs_preloader_ajax\s*/',
					' ',
					$price
				);
			},
		);
	}
}
