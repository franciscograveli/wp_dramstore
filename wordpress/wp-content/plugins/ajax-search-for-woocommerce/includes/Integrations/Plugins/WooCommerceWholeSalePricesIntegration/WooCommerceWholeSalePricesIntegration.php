<?php

namespace DgoraWcas\Integrations\Plugins\WooCommerceWholeSalePricesIntegration;

use DgoraWcas\Helpers;
use DgoraWcas\Integrations\Plugins\AbstractPluginIntegration;
use DgoraWcas\Multilingual;
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Integration with WooCommerce Wholesale Prices
 *
 * Plugin URL: https://wholesalesuiteplugin.com
 * Author: Rymera Web Co
 */
class WooCommerceWholeSalePricesIntegration extends AbstractPluginIntegration {
    protected const LABEL = 'WooCommerce Wholesale Prices';

    protected const MIN_VERSION = '1.24.4';

    protected const VERSION_CONST = 'WooCommerceWholeSalePricesPremium::VERSION';

    public function init() : void {
        add_filter( 'dgwt/wcas/search_query/args', [$this, 'filterSearchQueryArgs'] );
        add_filter( 'dgwt/wcas/search/product_cat/args', [$this, 'filterProductCatArgs'] );
        add_filter( 'dgwt/wcas/troubleshooting/renamed_plugins', [$this, 'getFolderRenameInfo'] );
    }

    /**
     * Exclude hidden products from search results (native engine)
     *
     * @param array $args
     *
     * @return array
     */
    public function filterSearchQueryArgs( $args ) {
        global $wc_wholesale_prices_premium;
        if ( current_user_can( 'manage_options' ) || current_user_can( 'manage_woocommerce' ) ) {
            return $args;
        }
        return $wc_wholesale_prices_premium->wwpp_query->pre_get_posts_arg( $args );
    }

    /**
     * Exclude hidden categories from search results (native engine)
     *
     * @param array $args
     *
     * @return array
     */
    public function filterProductCatArgs( $args ) {
        global $wc_wholesale_prices_premium;
        if ( current_user_can( 'manage_options' ) || current_user_can( 'manage_woocommerce' ) ) {
            return $args;
        }
        $postsArgs = [
            'tax_query' => [],
        ];
        $postsArgs = $wc_wholesale_prices_premium->wwpp_query->pre_get_posts_arg( $postsArgs );
        if ( !isset( $args['exclude'] ) ) {
            $args['exclude'] = [];
        }
        $args['exclude'] = array_merge( $args['exclude'], $this->getExcludedCategoryIds( $postsArgs ) );
        return $args;
    }

    /**
     * Get info about renamed plugin folder
     *
     * @param array $plugins
     *
     * @return array
     */
    public function getFolderRenameInfo( $plugins ) {
        $filters = new Filters();
        $result = Helpers::getFolderRenameInfo__premium_only( 'WooCommerce Wholesale Prices Premium', $filters->plugin_names );
        if ( $result ) {
            $plugins[] = $result;
        }
        return $plugins;
    }

    private function getExcludedCategoryIds( $postsArgs ) {
        $categoryIds = [];
        if ( !empty( $postsArgs['tax_query'] ) ) {
            foreach ( $postsArgs['tax_query'] as $taxQuery ) {
                if ( isset( $taxQuery['taxonomy'] ) && $taxQuery['taxonomy'] === 'product_cat' && isset( $taxQuery['operator'] ) && $taxQuery['operator'] === 'NOT IN' ) {
                    $categoryIds = $taxQuery['terms'];
                }
            }
        }
        return $categoryIds;
    }

}
