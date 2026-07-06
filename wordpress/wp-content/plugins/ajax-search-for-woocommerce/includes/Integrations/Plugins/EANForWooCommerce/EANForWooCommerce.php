<?php

namespace DgoraWcas\Integrations\Plugins\EANForWooCommerce;

// Exit if accessed directly
use DgoraWcas\Integrations\Plugins\AbstractPluginIntegration;
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Integration with EAN for WooCommerce
 *
 * Plugin URL: https://wordpress.org/plugins/ean-for-woocommerce/
 * Author: WPFactory
 */
class EANForWooCommerce extends AbstractPluginIntegration {
    protected const LABEL = 'EAN for WooCommerce';

    protected const VERSION_CONST = 'ALG_WC_EAN_VERSION';

    protected const MIN_VERSION = '4.3';

    public static function isActive() : bool {
        if ( parent::isActive() === false ) {
            return false;
        }
        return function_exists( 'alg_wc_ean' );
    }

    public function init() : void {
        // Disable plugin hook on WP_Query.
        if ( !is_admin() ) {
            if ( isset( alg_wc_ean()->core->search ) && get_option( 'alg_wc_ean_frontend_search', 'no' ) === 'yes' ) {
                remove_action( 'pre_get_posts', [alg_wc_ean()->core->search, 'search'], 10 );
                if ( !dgoraAsfwFs()->is_premium() ) {
                    add_filter( 'dgwt/wcas/native/search_query/join', [$this, 'searchQueryJoin'] );
                    add_filter(
                        'dgwt/wcas/native/search_query/search_or',
                        [$this, 'searchQueryOr'],
                        10,
                        2
                    );
                }
            }
        }
    }

    /**
     * Prepare join for EAN lookup
     *
     * @param string $join
     *
     * @return string
     */
    public function searchQueryJoin( $join ) {
        global $wpdb;
        if ( !strpos( $join, 'dgwt_wcasmsku' ) !== false ) {
            $join .= " INNER JOIN {$wpdb->postmeta} AS dgwt_wcasmean ON ( {$wpdb->posts}.ID = dgwt_wcasmean.post_id )";
        }
        return $join;
    }

    /**
     * Inject EAN lookup into search query
     *
     * @param string $search
     * @param string $like
     *
     * @return string
     */
    public function searchQueryOr( $search, $like ) {
        global $wpdb;
        $field = alg_wc_ean()->core->ean_key ?? '';
        if ( empty( $field ) ) {
            return $search;
        }
        if ( strpos( $search, 'dgwt_wcasmsku' ) !== false ) {
            $search .= $wpdb->prepare( ' OR (dgwt_wcasmsku.meta_key=%s AND dgwt_wcasmsku.meta_value LIKE %s)', $field, $like );
        } else {
            $search .= $wpdb->prepare( ' OR (dgwt_wcasmean.meta_key=%s AND dgwt_wcasmean.meta_value LIKE %s)', $field, $like );
        }
        return $search;
    }

}
