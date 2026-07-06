<?php

namespace DgoraWcas\Integrations\Themes\Bacola;

use DgoraWcas\Abstracts\ThemeIntegration;
use DgoraWcas\Helpers;
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
class Bacola extends ThemeIntegration {
    private $post_ids = [];

    public function init() : void {
        if ( !$this->canReplaceSearch() ) {
            return;
        }
        add_action( 'pre_get_posts', [$this, 'pre_get_posts'] );
        add_action( 'wp_ajax_load_more', [$this, 'set_search_post_ids_from_ajax'], 5 );
        add_action( 'wp_ajax_nopriv_load_more', [$this, 'set_search_post_ids_from_ajax'], 5 );
        add_filter( 'dgwt/wcas/override_search_results_page', '__return_true', 100 );
    }

    public function set_search_post_ids_from_ajax() {
        if ( !$this->is_relevant_product_ajax_query() ) {
            return;
        }
        // phpcs:ignore WordPress.Security.NonceVerification
        $search_term = $_POST['s'] ?? '';
        if ( empty( $search_term ) ) {
            return;
        }
        if ( !dgoraAsfwFs()->is_premium() ) {
            $this->post_ids = Helpers::searchProducts( $search_term );
        }
    }

    /**
     *
     * Narrow the list of products in the AJAX search to those returned by our search engine
     *
     * @param \WP_Query $query
     */
    public function pre_get_posts( $query ) {
        if ( !$this->is_relevant_product_ajax_query() ) {
            return;
        }
        if ( $query->get( 'post_type' ) !== 'product' ) {
            return;
        }
        if ( !empty( $this->post_ids ) ) {
            $query->set( 'post__in', $this->post_ids );
            $query->set( 'orderby', 'post__in' );
            $query->set( 's', '' );
        }
    }

    /**
     * Check whether the current AJAX query is for the product grid in us_ajax_grid
     *
     * @return bool
     */
    private function is_relevant_product_ajax_query() : bool {
        if ( !defined( 'DOING_AJAX' ) ) {
            return false;
        }
        // phpcs:ignore WordPress.Security.NonceVerification
        if ( !isset( $_POST['action'] ) || $_POST['action'] !== 'load_more' ) {
            return false;
        }
        return true;
    }

}
