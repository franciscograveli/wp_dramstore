<?php

/**
 * @dgwt_wcas_premium_only
 */
namespace DgoraWcas\Integrations\Plugins\WooCommercePrivateStore;

use DgoraWcas\Helpers;
use DgoraWcas\Integrations\Plugins\AbstractPluginIntegration;
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Integration with WooCommerce Private Store
 *
 * Plugin URL: https://barn2.co.uk/wordpress-plugins/woocommerce-private-store/
 * Author: Barn2 Plugins
 */
class WooCommercePrivateStore extends AbstractPluginIntegration {
    protected const LABEL = 'WooCommerce Private Store';

    protected const VERSION_CONST = '\\Barn2\\Plugin\\WC_Private_Store\\PLUGIN_VERSION';

    protected const MIN_VERSION = '1.6.3';

    public function init() : void {
        if ( !dgoraAsfwFs()->is_premium() ) {
            add_filter(
                'http_request_args',
                [$this, 'httpRequestArgs'],
                10,
                2
            );
            add_filter( 'dgwt/wcas/search_results/output', [$this, 'hideSearchResults'] );
        }
    }

    /**
     * Pass Private Store cookie to search request on search page
     *
     * @param $args
     * @param $url
     *
     * @return mixed
     */
    public function httpRequestArgs( $args, $url ) {
        if ( defined( 'DGWT_WCAS_SEARCH_ACTION' ) && defined( 'WCPS_COOKIE_PREFIX' ) && strpos( $url, \WC_AJAX::get_endpoint( \DGWT_WCAS_SEARCH_ACTION ) ) !== false ) {
            $cookie = \filter_input( \INPUT_COOKIE, \WCPS_COOKIE_PREFIX . \COOKIEHASH );
            if ( !empty( $cookie ) ) {
                $args['cookies'] = [
                    \WCPS_COOKIE_PREFIX . \COOKIEHASH => $cookie,
                ];
            }
        }
        return $args;
    }

    /**
     * Return empty results if store is locked
     *
     * @param $output
     *
     * @return array
     */
    public function hideSearchResults( $output ) {
        if ( !apply_filters( 'dgwt/wcas/integrations/woocommerce-private-store/hide-search-results', true ) ) {
            return $output;
        }
        if ( is_callable( '\\Barn2\\Plugin\\WC_Private_Store\\Util::store_locked' ) ) {
            if ( \Barn2\Plugin\WC_Private_Store\Util::store_locked() ) {
                $output['total'] = 0;
                $output['suggestions'] = [[
                    'value' => '',
                    'type'  => 'no-results',
                ]];
                $output['time'] = '0 sec';
            }
        }
        return $output;
    }

}
