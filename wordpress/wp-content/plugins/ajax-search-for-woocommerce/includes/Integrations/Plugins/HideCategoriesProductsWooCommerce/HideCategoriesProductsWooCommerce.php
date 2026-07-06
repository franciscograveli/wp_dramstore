<?php

namespace DgoraWcas\Integrations\Plugins\HideCategoriesProductsWooCommerce;

// Exit if accessed directly
use DgoraWcas\Integrations\Plugins\AbstractPluginIntegration;
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Integration with Hide Categories and Products for Woocommerce
 *
 * Plugin URL: https://wordpress.org/plugins/hide-categories-products-woocommerce/
 * Author: N.O.U.S. Open Useful and Simple
 */
class HideCategoriesProductsWooCommerce extends AbstractPluginIntegration {
    protected const LABEL = 'Hide Categories and Products for WooCommerce';

    public static function isActive() : bool {
        return function_exists( 'Hide_Categories_Products_WC' );
    }

    public function init() : void {
        add_filter( 'dgwt/wcas/search_query/args', [$this, 'excludeHiddenProducts'] );
    }

    /**
     * Exclude hidden products (native search)
     */
    public function excludeHiddenProducts( $args ) {
        $hiddenCategories = $this->getExcludedCategories();
        if ( !empty( $hiddenCategories ) ) {
            if ( !isset( $args['tax_query'] ) ) {
                $args['tax_query'] = [];
            }
            $args['tax_query'][] = [
                'taxonomy' => 'product_cat',
                'field'    => 'term_id',
                'terms'    => $hiddenCategories,
                'operator' => 'NOT IN',
            ];
        }
        return $args;
    }

    /**
     * Support both the legacy typo and the corrected API name.
     *
     * @return array
     */
    private function getExcludedCategories() : array {
        $plugin = Hide_Categories_Products_WC();
        if ( !is_object( $plugin ) ) {
            return [];
        }
        if ( method_exists( $plugin, 'get_excluded_cats' ) ) {
            $categories = $plugin->get_excluded_cats();
            return ( is_array( $categories ) ? $categories : [] );
        }
        if ( method_exists( $plugin, 'get_exluded_cats' ) ) {
            $categories = $plugin->get_exluded_cats();
            return ( is_array( $categories ) ? $categories : [] );
        }
        return [];
    }

}
