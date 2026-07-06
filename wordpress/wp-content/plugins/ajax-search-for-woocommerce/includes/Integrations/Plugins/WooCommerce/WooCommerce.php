<?php

namespace DgoraWcas\Integrations\Plugins\WooCommerce;

use \DgoraWcas\Helpers;
use DgoraWcas\Integrations\Plugins\AbstractPluginIntegration;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Integration with native WooCommerce filters
 */
class WooCommerce extends AbstractPluginIntegration {
	protected const LABEL         = 'WooCommerce';
	protected const VERSION_CONST = 'WC_VERSION';

	public static function isActive(): bool {
		return true;
	}

	public function init(): void {

		add_filter( 'woocommerce_layered_nav_link', [ $this, 'add_search_param_to_link' ] );

		add_filter( 'woocommerce_widget_get_current_page_url', [ $this, 'add_search_param_to_link' ] );

		if ( Helpers::isProductSearchPage() ) {
			add_filter( 'woocommerce_price_filter_sql', [ $this, 'filter_price_sql' ] );

			add_filter( 'get_terms_args', [ $this, 'narrow_term_filters' ], 10, 2 );

			add_filter( 'woocommerce_get_filtered_term_product_counts_query', [ $this, 'filter_counts_query' ] );
		}

		$this->syncWithOutOfStockVisibility();

		add_action( 'before_woocommerce_init', [ $this, 'declare_compatibility' ] );

		$this->brandsSupport();

		add_filter( 'woocommerce_short_description', [ $this, 'removeShortDescriptionAutop' ], 5 );
	}

	/**
	 * Sync with WooCommerce >> Settings >> Products >> Inventory >> "Out of stock visibility"
	 */
	private function syncWithOutOfStockVisibility() {
		if ( get_option( 'woocommerce_hide_out_of_stock_items' ) === 'yes' ) {
			// Hide "Exclude “out of stock” products" option
			add_filter(
				'dgwt/wcas/settings',
				function ( $settingsFields ) {
					if ( is_array( $settingsFields['dgwt_wcas_search'] ) ) {
						foreach ( $settingsFields['dgwt_wcas_search'] as $index => $field ) {
							if ( $field['name'] === 'exclude_out_of_stock' ) {
								unset( $settingsFields['dgwt_wcas_search'][ $index ] );
								break;
							}
						}
					}

					return $settingsFields;
				},
				PHP_INT_MAX - 10
			);

			// Force value of "Exclude “out of stock” products" option as "on"
			add_filter(
				'dgwt/wcas/settings/load_value/key=exclude_out_of_stock',
				function ( $value ) {
					return 'on';
				},
				PHP_INT_MAX - 10
			);
		}
	}

	/**
	 * Add &dgwt_wcas=1 to the link
	 *
	 * @return string
	 */
	public function add_search_param_to_link( $link ) {
		//phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['s'] ) ) {
			$link = add_query_arg( [ 'dgwt_wcas' => '1' ], $link );
		}

		return $link;
	}

	/**
	 * Narrowing the list of products to those from our search engine
	 *
	 * @param string $sql
	 *
	 * @return string
	 */
	public function filter_price_sql( $sql ) {
		if ( ! Helpers::is_running_inside_class( 'WC_Widget_Price_Filter' ) ) {
			return $sql;
		}

		$post_ids = apply_filters( 'dgwt/wcas/search_page/result_post_ids', [] );

		if ( $post_ids ) {
			$sql .= ' AND product_id IN(' . implode( ',', $post_ids ) . ')';
		}

		return $sql;
	}

	/**
	 * Passing our search results to plugin's terms filters
	 *
	 * The plugin will use our products IDs to determine terms in the displayed filters.
	 *
	 * @param array $args An array of get_terms() arguments.
	 * @param string[] $taxonomies An array of taxonomy names.
	 *
	 * @return array
	 */
	public function narrow_term_filters( $args, $taxonomies ) {
		if ( ! Helpers::is_running_inside_class( 'WC_Widget_Layered_Nav' ) ) {
			return $args;
		}

		$post_ids = apply_filters( 'dgwt/wcas/search_page/result_post_ids', [] );

		if ( $post_ids ) {
			$args['object_ids'] = $post_ids;
		}

		return $args;
	}

	/**
	 * Including products from our search engine in the term count display
	 *
	 * @param $query
	 *
	 * @return mixed
	 */
	public function filter_counts_query( $query ) {
		global $wpdb;

		$post_ids = apply_filters( 'dgwt/wcas/search_page/result_post_ids', [] );

		if ( $post_ids ) {
			$query['where'] .= " AND $wpdb->posts.ID IN(" . implode( ',', $post_ids ) . ')';
		}

		return $query;
	}

	/**
	 * Declare compatibility with WooCommerce features
	 *
	 * @return void
	 */
	public function declare_compatibility() {
		// https://github.com/woocommerce/woocommerce/wiki/High-Performance-Order-Storage-Upgrade-Recipe-Book#declaring-extension-incompatibility
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', DGWT_WCAS_FILE, true );
		}
	}

	/**
	 * Remove the 'wpautop' filter from the short description on the shop page if the FiboSearch form is present
	 *
	 * This is a bit tricky, because in calback of a filter we remove another filter that has a different priority.
	 *
	 * @param string $description
	 *
	 * @return string
	 */
	public function removeShortDescriptionAutop( $description ) {
		if ( is_search() ) {
			return $description;
		}

		if ( is_post_type_archive( 'product' ) && in_array( absint( get_query_var( 'paged' ) ), [ 0, 1 ], true ) ) {
			$shop_page = get_post( wc_get_page_id( 'shop' ) );

			if ( $shop_page ) {
				if ( strpos( $shop_page->post_content, '[fibosearch' ) !== false || strpos( $shop_page->post_content, 'wp:fibosearch/search' ) !== false ) {
					remove_filter( 'woocommerce_short_description', 'wpautop' );
				}
			}
		}

		return $description;
	}

	/**
	 * Adds support fow a WooCommerce brands feature.
	 * https://developer.woocommerce.com/2024/10/01/introducing-brands/
	 *
	 * Starting with WooCommerce 9.4, all functionality previously offered by the WooCommerce Brands plugin
	 * are part of WooCommerce core, and available to use for free.
	 * The feature will be enabled for all users, starting with WooCommerce 9.6.
	 *
	 * @return void
	 */
	private function brandsSupport() {
		$minWooVersion    = defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '9.6', '>=' );
		$inStagedRollouts = ( class_exists( 'Automattic\WooCommerce\Internal\Brands' )
							&& method_exists( 'Automattic\WooCommerce\Internal\Brands', 'is_enabled' )
							&& \Automattic\WooCommerce\Internal\Brands::is_enabled() );

		// Break early if the WooCommerce doesn't support brands.
		if ( ! $minWooVersion && ! $inStagedRollouts ) {
			return;
		}

		add_filter(
			'dgwt/wcas/indexer/taxonomies',
			function ( $taxonomies ) {
				$taxonomies[] = [
					'taxonomy'      => 'product_brand',
					'labels'        => [
						'name'          => __( 'Brands', 'woocommerce' ),
						'singular_name' => __( 'Brand', 'woocommerce' ),
					],
					'image_support' => true,
				];

				return $taxonomies;
			}
		);
	}
}
