<?php

/**
 * @dgwt_wcas_premium_only
 */
namespace DgoraWcas\Integrations\Plugins\XforWooCommerceFilter;

use DgoraWcas\Helpers;
use DgoraWcas\Integrations\Plugins\AbstractPluginIntegration;
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Integration with Product Filter for WooCommerce
 *
 * Plugin URL: https://xforwoocommerce.com
 * Author: 7VX LLC, USA CA
 */
class XforWooCommerceFilter extends AbstractPluginIntegration {
    protected const LABEL = 'Product Filter for WooCommerce';

    protected const MIN_VERSION = '7.2.3';

    protected $post_ids = [];

    public static function pluginVersion() : string {
        if ( class_exists( 'XforWC_Product_Filters' ) && isset( \XforWC_Product_Filters::$version ) ) {
            $version = \XforWC_Product_Filters::$version;
            return ( is_string( $version ) || is_numeric( $version ) ? (string) $version : '' );
        }
        return '';
    }

    public function init() : void {
        add_action( 'prdctfltr_add_inputs', [$this, 'prdctfltr_add_inputs'] );
        add_action( 'pre_get_posts', [$this, 'search_products'], 1000000 );
        add_action( 'wp_ajax_nopriv_prdctfltr_respond_550', [$this, 'set_search_post_ids_from_ajax'], 5 );
        add_action( 'wp_ajax_prdctfltr_respond_550', [$this, 'set_search_post_ids_from_ajax'], 5 );
    }

    /**
     * Adding an input to be submitted during an AJAX query when changing filters
     *
     * Only on search page or during AJAX query on the search page.
     */
    public function prdctfltr_add_inputs() {
        // phpcs:disable WordPress.Security.NonceVerification.Missing
        if ( Helpers::isProductSearchPage() || defined( 'DOING_AJAX' ) && isset( $_POST['action'] ) && $_POST['action'] === 'prdctfltr_respond_550' && isset( $_POST['pf_id'] ) && isset( $_POST['pf_filters'][$_POST['pf_id']]['dgwt_wcas'] ) ) {
            echo '<input type="hidden" name="dgwt_wcas" value="1"  class="pf_added_input" />';
            echo '<input type="hidden" name="post_type" value="product"  class="pf_added_input" />';
        }
    }

    public function set_search_post_ids_from_ajax() {
        // phpcs:disable WordPress.Security.NonceVerification.Missing
        if ( !isset( $_POST['pf_filters'][$_POST['pf_id']]['dgwt_wcas'] ) ) {
            return;
        }
        $orderby = ( isset( $_POST['pf_filters'][$_POST['pf_id']]['orderby'] ) ? wc_clean( wp_unslash( $_POST['pf_filters'][$_POST['pf_id']]['orderby'] ) ) : 'relevance' );
        $order = 'desc';
        if ( $orderby === 'price' ) {
            $order = 'asc';
        }
        $phrase = $_POST['pf_filters'][$_POST['pf_id']]['s'];
        // phpcs:enable
        if ( !dgoraAsfwFs()->is_premium() ) {
            $this->post_ids = Helpers::searchProducts( $phrase );
        }
    }

    /**
     * Narrow the list of products in the AJAX search to those returned by our search engine
     *
     * Filtered custom WP_Query used by this plugin: wp-content/plugins/xforwoocommerce/x-pack/prdctfltr/includes/pf-shortcode.php:1333
     *
     * @param \WP_Query $query
     */
    public function search_products( $query ) {
        if ( !$this->is_prdctfltr_ajax_search() ) {
            return;
        }
        if ( $query->get( 'prdctfltr_active' ) !== true ) {
            return;
        }
        if ( $this->post_ids ) {
            $query->set( 's', '' );
            $query->is_search = false;
            $query->set( 'post__in', $this->post_ids );
            $query->set( 'orderby', 'post__in' );
        }
    }

    /**
     * Checking if we are in the middle of an AJAX query that handles filter and search results refreshing
     *
     * @return bool
     */
    private function is_prdctfltr_ajax_search() {
        // phpcs:disable WordPress.Security.NonceVerification.Missing
        if ( !defined( 'DOING_AJAX' ) ) {
            return false;
        }
        if ( !isset( $_POST['action'] ) ) {
            return false;
        }
        if ( $_POST['action'] !== 'prdctfltr_respond_550' ) {
            return false;
        }
        if ( !isset( $_POST['pf_id'] ) ) {
            return false;
        }
        if ( !isset( $_POST['pf_filters'][$_POST['pf_id']] ) ) {
            return false;
        }
        if ( !isset( $_POST['pf_filters'][$_POST['pf_id']]['s'] ) ) {
            return false;
        }
        if ( !isset( $_POST['pf_filters'][$_POST['pf_id']]['dgwt_wcas'] ) ) {
            return false;
        }
        // phpcs:enable
        return true;
    }

}
