<?php

namespace DgoraWcas\Integrations\Plugins\ThemifyBuilderPro;

use DgoraWcas\Helpers;
use DgoraWcas\Integrations\Plugins\AbstractPluginIntegration;
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Integration with ThemifyBuilderPro
 *
 * Plugin URL: https://themify.me/
 * Author: Themify
 */
class ThemifyBuilderPro extends AbstractPluginIntegration {
    protected const LABEL = 'Themify Builder Pro';

    protected const VERSION_CONST = 'TBP_VER';

    public function init() : void {
        add_filter( 'tbp_archive_products_query', [$this, 'set_search_post_ids'] );
    }

    public function set_search_post_ids( $query_args ) {
        if ( !is_search() ) {
            return $query_args;
        }
        $search_term = ( isset( $query_args['s'] ) ? (string) $query_args['s'] : '' );
        if ( $search_term === '' ) {
            return $query_args;
        }
        $post_ids = [];
        if ( !dgoraAsfwFs()->is_premium() ) {
            $post_ids = Helpers::searchProducts( $search_term );
        }
        if ( !empty( $post_ids ) ) {
            unset($query_args['s']);
            $query_args['post__in'] = $post_ids;
            $query_args['orderby'] = 'post__in';
        }
        return $query_args;
    }

}
