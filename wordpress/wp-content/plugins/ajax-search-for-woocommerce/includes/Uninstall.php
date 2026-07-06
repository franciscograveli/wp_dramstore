<?php

namespace DgoraWcas;

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
class Uninstall {
    public static function afterUninstall() {
        self::wipeOptions();
        self::wipeTransients();
        /**
         * Multisite.
         */
        if ( is_multisite() ) {
            $currentSiteId = get_current_blog_id();
            foreach ( get_sites() as $site ) {
                if ( is_numeric( $site->blog_id ) && $site->blog_id > 1 ) {
                    switch_to_blog( $site->blog_id );
                    self::wipeOptions();
                    self::wipeTransients();
                }
            }
            // Switch back to the original site.
            switch_to_blog( $currentSiteId );
        }
    }

    /**
     * Wipe options.
     */
    private static function wipeOptions() {
        $options = [
            'dgwt_wcas_schedule_single',
            'dgwt_wcas_settings_show_advanced',
            'dgwt_wcas_images_regenerated',
            'dgwt_wcas_activation_date',
            'dgwt_wcas_dismiss_review_notice',
            'dgwt_wcas_stats_db_version',
            'widget_dgwt_wcas_ajax_search'
        ];
        foreach ( $options as $option ) {
            delete_option( $option );
        }
    }

    /**
     * Wipe transients.
     */
    private static function wipeTransients() {
        $transients = ['dgwt_wcas_troubleshooting_async_results'];
        foreach ( $transients as $transient ) {
            delete_transient( $transient );
        }
    }

}
