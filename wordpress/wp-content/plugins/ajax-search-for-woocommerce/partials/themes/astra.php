<?php
// Exit if accessed directly
if ( ! defined( 'DGWT_WCAS_FILE' ) ) {
	exit;
}

// Astra cut our search using wp_kses(), so we need overwrite whole function, but for 4.0.0.
if ( defined( 'ASTRA_EXT_VER' ) && version_compare( ASTRA_EXT_VER, '4.1.0' ) < 0 ) {
	if ( ! function_exists( 'astra_addon_get_search_form' ) ) {
		function astra_addon_get_search_form( $echo = true ) {
			$result = apply_filters( 'astra_get_search_form', '' );
			if ( $echo ) {
				echo $result;
			} else {
				return $result;
			}
		}
	}
}

// From version 4.1.0 Astra has a filters that can be used to indicate allowed tags and attributes in the search form.
if ( defined( 'ASTRA_EXT_VER' ) && version_compare( ASTRA_EXT_VER, '4.1.0' ) >= 0 ) {
	add_filter(
		'astra_addon_form_post_kses_protocols',
		function ( $args ) {
			$args['input']['id']    = [];
			$args['input']['style'] = [];
			$args['label']          = [
				'class'  => [],
				'id'     => [],
				'style'  => [],
				'data-*' => true,
				'align'  => [],
				'for'    => [],
			];
			$args['button']         = [
				'class'      => [],
				'aria-label' => [],
				'type'       => [],
			];

			return $args;
		}
	);

	add_filter(
		'safe_style_css',
		function ( $styles ) {
			$styles[] = 'display';

			return $styles;
		}
	);
}

$astra_settings           = get_option( 'astra-settings' );
$is_header_footer_builder = isset( $astra_settings['is-header-footer-builder'] ) ? (bool) $astra_settings['is-header-footer-builder'] : true;

if ( $is_header_footer_builder ) {
	require_once 'astra/builder.php';
} else {
	require_once 'astra/legacy.php';
}
