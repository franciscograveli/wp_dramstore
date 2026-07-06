<?php

namespace DgoraWcas\Integrations\Plugins\UsCore;

use DgoraWcas\Helpers;
use DgoraWcas\Integrations\Plugins\AbstractPluginIntegration;
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Integration with UpSolution Core addon by UpSolution
 *
 * Page URL: https://us-themes.com/
 * Author: UpSolution
 */
class UsCore extends AbstractPluginIntegration {
    protected const LABEL = 'UpSolution Core addon by UpSolution';

    protected const VERSION_CONST = 'US_CORE_VERSION';

    private $post_ids = [];

    public function init() : void {
        add_action( 'pre_get_posts', [$this, 'pre_get_posts'] );
        add_action( 'wp_ajax_us_ajax_grid', [$this, 'set_search_post_ids_from_ajax'], 5 );
        add_action( 'wp_ajax_nopriv_us_ajax_grid', [$this, 'set_search_post_ids_from_ajax'], 5 );
    }

    public function set_search_post_ids_from_ajax() {
        if ( !$this->isRelevantProductAjaxQuery() ) {
            return;
        }
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        $template_vars_json = $_POST['template_vars'] ?? '';
        $template_vars = json_decode( stripslashes( $template_vars_json ), true );
        $search_term = $template_vars['query_args']['s'] ?? '';
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
        if ( !$this->isRelevantProductAjaxQuery() ) {
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
    private function isRelevantProductAjaxQuery() : bool {
        if ( !defined( 'DOING_AJAX' ) ) {
            return false;
        }
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        if ( !isset( $_POST['action'] ) || $_POST['action'] !== 'us_ajax_grid' ) {
            return false;
        }
        $template_vars = ( function_exists( 'us_get_HTTP_POST_json' ) ? us_get_HTTP_POST_json( 'template_vars' ) : [] );
        $post_type = $template_vars['query_args']['post_type'] ?? null;
        if ( empty( $post_type ) || is_array( $post_type ) && !in_array( 'product', $post_type, true ) || is_string( $post_type ) && $post_type !== 'product' ) {
            return false;
        }
        return true;
    }

}
