<?php

namespace DgoraWcas\Analytics;

use DgoraWcas\Helpers;
use DgoraWcas\Multilingual;
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
class Recorder {
    public function listen() {
        Database::registerTables();
        add_action(
            'dgwt/wcas/analytics/after_searching',
            [$this, 'listener'],
            10,
            3
        );
    }

    /**
     * Validate input data and save them to the index
     *
     * @param string $phrase
     * @param int $hits
     * @param string $lang
     *
     * @return void
     */
    public function listener( $phrase, $hits, $lang ) {
        $autocomplete = true;
        // Break early the search phrase is empty or has a specific shape
        if ( empty( $phrase ) || !is_string( $phrase ) || $phrase === 'fibotests' ) {
            return;
        }
        if ( !is_numeric( $hits ) || $hits < 0 ) {
            return;
        }
        // Save only critical searches.
        if ( defined( 'DGWT_WCAS_ANALYTICS_ONLY_CRITICAL' ) && DGWT_WCAS_ANALYTICS_ONLY_CRITICAL && $hits > 0 ) {
            return;
        }
        // Allow to exclude critical phrases.
        if ( $hits === 0 ) {
            $excludedPhrases = apply_filters( 'dgwt/wcas/analytics/excluded_critical_phrases', [] );
            if ( is_array( $excludedPhrases ) && in_array( $phrase, $excludedPhrases, true ) ) {
                return;
            }
        }
        // Break early when a user has specific roles.
        $roles = apply_filters( 'dgwt/wcas/analytics/exclude_roles', [] );
        if ( !empty( $roles ) ) {
            foreach ( $roles as $role ) {
                if ( current_user_can( $role ) ) {
                    return;
                }
            }
        }
        if ( Helpers::isProductSearchPage() ) {
            $autocomplete = false;
        }
        $phrase = strtolower( substr( $phrase, 0, 255 ) );
        $lang = ( !empty( $lang ) && Multilingual::isLangCode( $lang ) ? $lang : '' );
        $shouldRecord = apply_filters( 'dgwt/wcas/analytics/should_record', true, [
            'phrase'       => $phrase,
            'hits'         => $hits,
            'lang'         => $lang,
            'autocomplete' => $autocomplete,
        ] );
        if ( !$shouldRecord ) {
            return;
        }
        $this->push(
            $phrase,
            $hits,
            $autocomplete,
            $lang
        );
    }

    /**
     * Push a record to the index.
     *
     * @param string $phrase
     * @param int $hits
     * @param bool $autocomplete
     * @param string $lang
     *
     * @return void
     */
    public function push(
        $phrase,
        $hits,
        $autocomplete,
        $lang
    ) {
        global $wpdb;
        $data = [
            'phrase'       => $phrase,
            'hits'         => $hits,
            'created_at'   => date( 'Y-m-d H:i:s', current_time( 'timestamp', true ) ),
            'autocomplete' => $autocomplete,
        ];
        $format = [
            '%s',
            '%d',
            '%s',
            '%d'
        ];
        if ( !empty( $lang ) ) {
            $data['lang'] = $lang;
            $format[] = '%s';
        }
        $wpdb->insert( $wpdb->dgwt_wcas_stats, $data, $format );
    }

}
