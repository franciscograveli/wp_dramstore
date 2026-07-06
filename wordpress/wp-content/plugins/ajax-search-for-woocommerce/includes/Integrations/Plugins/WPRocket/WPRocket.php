<?php

namespace DgoraWcas\Integrations\Plugins\WPRocket;

// Exit if accessed directly
use DgoraWcas\Integrations\Plugins\AbstractPluginIntegration;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Integration with WP Rocket
 *
 * Plugin URL: https://wp-rocket.me/
 * Author: WP Media
 */
class WPRocket extends AbstractPluginIntegration {
	protected const LABEL         = 'WP Rocket';
	protected const MIN_VERSION   = '3.7';
	protected const VERSION_CONST = 'WP_ROCKET_VERSION';

	public function init(): void {
		add_filter( 'rocket_delay_js_exclusions', [ $this, 'excludedJs' ] );
		add_filter( 'rocket_rucss_inline_content_exclusions', [ $this, 'addRucssContentExcluded' ] );
		add_filter( 'rocket_defer_inline_exclusions', [ $this, 'addDeferInlineExcluded' ] );
	}

	/**
	 * Adding our scripts to the list of excluded from delay loading
	 *
	 * @param array $excluded
	 *
	 * @return array
	 */
	public function excludedJs( $excluded ) {
		$excluded[] = 'jquery-migrate-js';
		$excluded[] = 'jquery-core-js';
		$excluded[] = 'dgwt-wcas';
		$excluded[] = 'wcasThemeSearch';

		return $excluded;
	}

	/**
	 * Adding our inline styles to the list of excluded from remove from content.
	 *
	 * @param array $inlineExclusions
	 *
	 * @return array
	 */
	public function addRucssContentExcluded( $inlineExclusions ) {
		$inlineExclusions [] = '.dgwt-wcas';

		return $inlineExclusions;
	}

	/**
	 * Adding our inline scripts to the list of excluded from defer loading.
	 *
	 * @param array $inlineExclusions
	 *
	 * @return array
	 */
	public function addDeferInlineExcluded( $inlineExclusions ) {
		if ( ! is_array( $inlineExclusions ) ) {
			$inlineExclusions = [];
		}

		$inlineExclusions[] = 'dgwt_wcas';

		return $inlineExclusions;
	}
}
