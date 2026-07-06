<?php

namespace DgoraWcas\Integrations\Plugins\JetSmartFilters;

use DgoraWcas\Helpers;
use DgoraWcas\Integrations\Plugins\AbstractPluginIntegration;
use function function_exists;
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Integration with JetSmartFilters
 *
 * Plugin URL: https://crocoblock.com/plugins/jetsmartfilters/
 * Author: Crocoblock
 */
class JetSmartFilters extends AbstractPluginIntegration {
    protected const LABEL = 'JetSmartFilters';

    protected const MIN_VERSION = '1.8.3';

    public static function pluginVersion() : string {
        return ( function_exists( 'jet_smart_filters' ) ? (string) \jet_smart_filters()->get_version() : '' );
    }

    public function init() : void {
        // Widget: Elementor Pro Archive Products
        add_filter( 'jet-smart-filters/query/final-query', [$this, 'filter_query'] );
        add_filter(
            'dgwt/wcas/helpers/is_search_query',
            [$this, 'allow_to_process_search_query'],
            10,
            2
        );
        // Widget: JetWooBuilder Products Grid
        add_filter( 'jet-woo-builder/shortcodes/jet-woo-products/final-query-args', [$this, 'filter_query_builder_grid'], 10 );
        // Widget: JetWooBuilder Products List
        add_filter(
            'jet-woo-builder/shortcodes/jet-woo-products-list/query-args',
            [$this, 'filter_query_builder_list'],
            10,
            2
        );
        add_filter( 'jet-smart-filters/filters/localized-data', [$this, 'jet_smart_filter_settings'], 10 );
    }

    /**
     * Mark query arguments if they relate to product search initiated by the integrated plugin
     * Widget: Elementor Pro Archive Products (epro-archive-products)
     * Widget: JetEngine Listing Grid (jet-engine/default)
     *
     * @param array $query
     *
     * @return array
     */
    public function filter_query( $query ) {
        $customQueryIds = apply_filters( 'dgwt/wcas/integrations/jetsmartfilters/custom_query_ids', [] );
        $providers = ['epro-archive-products/default', 'jet-engine/default'];
        if ( !empty( $customQueryIds ) ) {
            foreach ( $customQueryIds as $customQueryId ) {
                if ( is_string( $customQueryId ) && !empty( $customQueryId ) ) {
                    $providers[] = 'jet-engine/' . $customQueryId;
                }
            }
        }
        if ( isset( $_POST['action'] ) && $_POST['action'] === 'jet_smart_filters' && isset( $_POST['provider'] ) && in_array( $_POST['provider'], $providers ) && !empty( $_POST['defaults']['s'] ) ) {
            $query['run_wcas_search'] = true;
        }
        return $query;
    }

    /**
     * Filter search results if the query was marked in the filter above
     * Widget: Elementor Pro Archive Products (epro-archive-products)
     *
     * OR
     *
     * Filter WP_Query used by plugin to determine counters for filter values.
     *
     * @param bool $enable
     * @param \WP_Query $query
     *
     * @return bool
     */
    public function allow_to_process_search_query( $enable, $query ) {
        if ( is_object( $query ) && is_a( $query, 'WP_Query' ) && isset( $query->query_vars['run_wcas_search'] ) ) {
            $enable = true;
        }
        // @since 1.26.0.
        if ( is_object( $query ) && is_a( $query, 'WP_Query' ) && !empty( $query->query_vars['s'] ) && Helpers::is_running_inside_class( 'Jet_Smart_Filters_Indexer_Data', 20 ) && Helpers::isRunningInsideFunction( 'get_queried_ids', 20 ) ) {
            $enable = true;
        }
        return $enable;
    }

    /**
     * Filter arguments of builder's own query
     * Widget: JetWooBuilder Products Grid (jet-woo-products-grid)
     *
     * @param $query_args
     *
     * @return mixed
     */
    public function filter_query_builder_grid( $query_args ) {
        $phrase = false;
        if ( $this->is_jet_woo_products_query( $query_args, 'jet-woo-products-grid/default' ) ) {
            //phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $phrase = $_GET['s'];
        } elseif ( $this->is_jet_woo_products_ajax_query( $query_args, 'jet-woo-products-grid/default' ) ) {
            //phpcs:ignore WordPress.Security.NonceVerification.Missing
            $phrase = $_POST['settings']['dgwt_wcas_s'];
        }
        if ( $phrase ) {
            if ( !dgoraAsfwFs()->is_premium() ) {
                $query_args['post__in'] = Helpers::searchProducts( $phrase );
            }
        }
        return $query_args;
    }

    /**
     * Filter arguments of builder's own query
     * Widget: JetWooBuilder Products List (jet-woo-products-list)
     *
     * @param $query_args
     *
     * @return mixed
     */
    public function filter_query_builder_list( $query_args, $products_list_shortcode ) {
        $phrase = false;
        if ( $this->is_jet_woo_products_query( $query_args, 'jet-woo-products-list/default' ) ) {
            //phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $phrase = $_GET['s'];
        } elseif ( $this->is_jet_woo_products_ajax_query( $query_args, 'jet-woo-products-list/default' ) ) {
            //phpcs:ignore WordPress.Security.NonceVerification.Missing
            $phrase = $_POST['settings']['dgwt_wcas_s'];
        }
        if ( $phrase ) {
            if ( !dgoraAsfwFs()->is_premium() ) {
                $query_args['post__in'] = Helpers::searchProducts( $phrase );
            }
        }
        return $query_args;
    }

    /**
     * Passing the search phrase to the plugin settings
     *
     * Widget: JetWooBuilder Products Grid (jet-woo-products-grid)
     * Widget: JetWooBuilder Products List (jet-woo-products-list)
     *
     * @param array $settings
     *
     * @return array
     */
    public function jet_smart_filter_settings( $settings ) {
        //phpcs:disable WordPress.Security.NonceVerification.Recommended
        if ( Helpers::isProductSearchPage() ) {
            if ( isset( $settings['settings']['jet-woo-products-grid']['default'] ) && !empty( $_GET['s'] ) ) {
                $settings['settings']['jet-woo-products-grid']['default']['dgwt_wcas_s'] = $_GET['s'];
            }
            if ( isset( $settings['settings']['jet-woo-products-list']['default'] ) && !empty( $_GET['s'] ) ) {
                $settings['settings']['jet-woo-products-list']['default']['dgwt_wcas_s'] = $_GET['s'];
            }
        }
        //phpcs:enable
        return $settings;
    }

    private function is_jet_woo_products_query( $query_args, $type ) {
        return Helpers::isProductSearchPage() && isset( $query_args['jet_smart_filters'] ) && $query_args['jet_smart_filters'] === $type;
    }

    private function is_jet_woo_products_ajax_query( $query_args, $type ) {
        //phpcs:disable WordPress.Security.NonceVerification.Missing
        return isset( $_POST['settings']['dgwt_wcas_s'] ) && !empty( $_POST['settings']['dgwt_wcas_s'] ) && isset( $query_args['jet_smart_filters'] ) && $query_args['jet_smart_filters'] === $type;
        //phpcs:enable
    }

}
