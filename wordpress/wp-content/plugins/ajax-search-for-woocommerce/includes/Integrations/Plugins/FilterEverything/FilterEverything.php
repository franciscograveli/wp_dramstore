<?php

namespace DgoraWcas\Integrations\Plugins\FilterEverything;

use DgoraWcas\Helpers;
use DgoraWcas\Integrations\Plugins\AbstractPluginIntegration;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Integration with FilterEverything
 *
 * Plugin URL: https://wordpress.org/plugins/filter-everything/
 * Author: Stepasyuk
 */
class FilterEverything extends AbstractPluginIntegration {
	protected const LABEL = 'Filter Everything';

	public static function isActive(): bool {
		return class_exists( 'FlrtFilter' );
	}

	public function init(): void {
		add_filter( 'dgwt/wcas/helpers/is_search_query', [ $this, 'allow_to_process_search_query' ], 10, 2 );
	}

	/**
	 *  Allow to process search query
	 *
	 * @param bool $enable
	 * @param \WP_Query $query
	 */
	public function allow_to_process_search_query( $enable, $query ) {
		if (
			is_object( $query ) &&
			is_a( $query, 'WP_Query' ) &&
			! empty( $query->query_vars['s'] ) &&
			Helpers::is_running_inside_class( 'FilterEverything\Filter\EntityManager', 20 ) &&
			Helpers::isRunningInsideFunction( 'getAllSetWpQueriedPostIds', 20 )
		) {
			$enable = true;
		}

		return $enable;
	}
}
