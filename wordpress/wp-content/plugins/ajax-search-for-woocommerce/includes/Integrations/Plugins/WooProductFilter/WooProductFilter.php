<?php

namespace DgoraWcas\Integrations\Plugins\WooProductFilter;

use DgoraWcas\Helpers;
use DgoraWcas\Integrations\Plugins\AbstractPluginIntegration;
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Integration with Product Filter by WBW
 *
 * Plugin URL: https://wordpress.org/plugins/woo-product-filter/
 * Author: WBW
 */
class WooProductFilter extends AbstractPluginIntegration {
    protected const LABEL = 'Product Filter by WBW';

    protected const VERSION_CONST = 'WPF_VERSION';

    protected const MIN_VERSION = '1.2.8';

    private $post_ids = [];

    public function init() : void {
        // TODO This filter must be added by the plugin author
        add_filter( 'wpf_getFilteredPriceSql', [$this, 'filter_price_sql'] );
        add_action( 'pre_get_posts', [$this, 'pre_get_posts'] );
        add_action( 'wp_ajax_filtersFrontend', [$this, 'set_search_post_ids_from_ajax'], 5 );
        add_action( 'wp_ajax_nopriv_filtersFrontend', [$this, 'set_search_post_ids_from_ajax'], 5 );
    }

    /**
     * Narrowing the list of products for determining edge prices to those returned by our search engine
     *
     * @param string $sql
     *
     * @return string
     */
    public function filter_price_sql( $sql ) {
        global $wpdb;
        $post_ids = apply_filters( 'dgwt/wcas/search_page/result_post_ids', [] );
        if ( $post_ids ) {
            $sql .= " AND {$wpdb->posts}.ID IN(" . implode( ',', $post_ids ) . ')';
        }
        return $sql;
    }

    public function set_search_post_ids_from_ajax() {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        if ( !isset( $_POST['mod'] ) || isset( $_POST['mod'] ) && $_POST['mod'] !== 'woofilters' ) {
            return;
        }
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        if ( !isset( $_POST['currenturl'] ) ) {
            return;
        }
        $orderby = 'relevance';
        $order = 'desc';
        // parse args from url passed as POST var
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        $url_query = wp_parse_url( $_POST['currenturl'] );
        $url_query_args = [];
        if ( empty( $url_query['query'] ) ) {
            return;
        }
        wp_parse_str( $url_query['query'], $url_query_args );
        if ( !isset( $url_query_args['dgwt_wcas'] ) || !isset( $url_query_args['s'] ) ) {
            return;
        }
        if ( !empty( $url_query_args['orderby'] ) ) {
            $orderby = wc_clean( wp_unslash( $url_query_args['orderby'] ) );
        }
        if ( !empty( $url_query_args['order'] ) ) {
            $order = strtolower( wc_clean( wp_unslash( $url_query_args['order'] ) ) );
        }
        if ( $orderby === 'price' ) {
            $order = 'asc';
        }
        if ( !dgoraAsfwFs()->is_premium() ) {
            $this->post_ids = Helpers::searchProducts( $url_query_args['s'] );
        }
    }

    /**
     * Narrow the list of products in the AJAX search to those returned by our search engine
     *
     * Filtered custom WP_Query used by this plugin: wp-content/plugins/woo-product-filter/modules/woofilters/controller.php~152
     *
     * @param \WP_Query $query
     */
    public function pre_get_posts( $query ) {
        if ( !defined( 'DOING_AJAX' ) ) {
            return;
        }
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        if ( !isset( $_POST['action'] ) || isset( $_POST['action'] ) && $_POST['action'] !== 'filtersFrontend' ) {
            return;
        }
        if ( $query->get( 'wpf_query' ) !== 1 ) {
            return;
        }
        if ( $this->post_ids ) {
            if ( version_compare( WPF_VERSION, '1.4.8', '>=' ) ) {
                $query->set( 's', '' );
            }
            $query->set( 'post__in', $this->post_ids );
            $query->set( 'orderby', 'post__in' );
        }
    }

}
