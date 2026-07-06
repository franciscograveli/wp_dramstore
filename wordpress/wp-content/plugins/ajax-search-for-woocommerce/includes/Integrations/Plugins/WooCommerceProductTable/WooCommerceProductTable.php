<?php

namespace DgoraWcas\Integrations\Plugins\WooCommerceProductTable;

use \DgoraWcas\Helpers;
use DgoraWcas\Integrations\Plugins\AbstractPluginIntegration;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Integration with WooCommerce Product Table
 *
 * Plugin URL: https://barn2.co.uk/wordpress-plugins/woocommerce-product-table/
 * Author: Barn2 Plugins
 */
class WooCommerceProductTable extends AbstractPluginIntegration {
	protected const LABEL         = 'WooCommerce Product Table';
	protected const VERSION_CONST = '\Barn2\Plugin\WC_Product_Table\PLUGIN_VERSION';
	protected const MIN_VERSION   = '2.6.2';

	public function init(): void {
		add_action( 'pre_get_posts', [ $this, 'pre_get_posts' ] );
	}

	/**
	 * Overwrite the search results in the table
	 *
	 * @param \WP_Query $query
	 */
	public function pre_get_posts( $query ) {
		if ( ! Helpers::isProductSearchPage() ) {
			return;
		}
		if ( ! Helpers::is_running_inside_class( 'Barn2\Plugin\WC_Product_Table\Table_Query', 10 ) && ! Helpers::is_running_inside_class( 'WC_Product_Table_Query' ) ) {
			return;
		}
		$post_ids = apply_filters( 'dgwt/wcas/search_page/result_post_ids', [] );

		if ( $post_ids ) {
			// We set a variable to make our filters work for WP_Query
			$query->set( 'dgwt_wcas', $query->query_vars['s'] );

			$query->set( 'post__in', $post_ids );
			$query->set( 'orderby', 'post__in' );

			add_action( 'wp_footer', [ $this, 'add_js' ], 5 );
		}
	}

	/**
	 * Reset search value in search input and hide the search box above/below the table
	 */
	public function add_js() {
		?>
		<script>
			(function ($) {
				$(document).ready(function () {
					$('.wc-product-table').on('init.wcpt', function (e, table) {
						table.$table.prev('.wc-product-table-controls').find('.dataTables_filter input').val('').trigger('keyup');
						table.$table.prev('.wc-product-table-controls').find('.dataTables_filter label').hide();
						table.$table.next('.wc-product-table-controls').find('.dataTables_filter label').hide();
					});
				});
			})(jQuery);
		</script>
		<?php
	}
}
