<?php

use DgoraWcas\Helpers;

// Exit if accessed directly
if ( ! defined( 'DGWT_WCAS_FILE' ) ) {
	exit;
}

add_action(
	'init',
	function () {
		$headerSource = thegem_get_option( 'header_source', 'default' );
		// Built-in Header.
		if ( $headerSource === 'default' ) {
			// Header Vertical
			remove_filter( 'wp_nav_menu_items', 'thegem_menu_item_search', 10, 2 );
			add_filter(
				'wp_nav_menu_items',
				function ( $items, $args ) {
					if ( $args->theme_location == 'primary' && ! thegem_get_option( 'hide_search_icon' ) ) {
						$items .= '<li class="menu-item menu-item-search dgwt-wcas-thegem-menu-search">';
						$items .= do_shortcode( '[fibosearch layout="icon"]' );
						$items .= '</li>';
					}

					return $items;
				},
				10,
				2
			);

			// Header Fullwidth hamburger
			remove_filter( 'wp_nav_menu_items', 'thegem_menu_item_hamburger_widget', 100, 2 );
			add_action(
				'thegem_before_nav_menu',
				function () {
					if ( in_array(
						thegem_get_option( 'header_layout' ),
						[
							'perspective',
							'fullwidth_hamburger',
							'overlay',
						]
					) ) {
						echo do_shortcode( '[fibosearch]' );
					}
				}
			);

			// Perspective header
			remove_filter( 'get_search_form', 'thegem_serch_form_vertical_header' );
			add_action(
				'thegem_perspective_menu_buttons',
				function () {
					echo do_shortcode( '[fibosearch]' );
				}
			);

			// Remove the search bar from vertical header
			add_filter(
				'get_search_form',
				function ( $form ) {
					if ( in_array( thegem_get_option( 'header_layout' ), [ 'fullwidth_hamburger', 'vertical' ] ) ) {
						$form = '';
					}

					return $form;
				},
				100
			);

			add_action(
				'thegem_before_header',
				function () {
					if ( ! in_array( thegem_get_option( 'header_layout' ), [ 'vertical' ] ) ) {
						return;
					}

					$html  = '<div class="dgwt-wcas-thegem-vertical-search">';
					$html .= do_shortcode( '[fibosearch]' );
					$html .= '</div>';

					echo $html;
				},
				20
			);

			// Force enable overlay for mobile search
			add_filter(
				'dgwt/wcas/settings/load_value/key=enable_mobile_overlay',
				function () {
					return 'on';
				}
			);
		} else {
			// Header Builder.
			function dgwtWcasTheGemGetCustomCss( $atts, $uniqid ) {
				$defaultAtts = [
					'desktop_disable' => '',
					'tablet_disable'  => '',
					'mobile_disable'  => '',
				];
				if ( function_exists( 'thegem_templates_extra_options_extract' ) && function_exists( 'thegem_templates_design_options_extract' ) ) {
					$defaultAtts = array_merge( thegem_templates_extra_options_extract(), thegem_templates_design_options_extract() );
				}
				$atts = wp_parse_args( $atts, $defaultAtts );

				return thegem_templates_element_design_options( $uniqid, '.dgwt-wcas-search-wrapp', $atts );
			}

			add_shortcode(
				'thegem_te_search_form',
				function ( $atts, $content, $shortcodeTag ) {
					// TODO
					//phpcs:ignore WordPress.WP.AlternativeFunctions.rand_rand
					$uniqid = uniqid( 'thegem-custom-' ) . rand( 1, 9999 );

					$customCss = dgwtWcasTheGemGetCustomCss( $atts, $uniqid );
					$cssOutput = empty( $customCss ) ? '' : '<style>' . esc_html( $customCss ) . '</style>';

					return $cssOutput . do_shortcode( '[fibosearch layout="classic" class="' . $uniqid . '"]' );
				}
			);

			add_shortcode(
				'thegem_te_search',
				function ( $atts, $content, $shortcodeTag ) {
					// TODO
					//phpcs:ignore WordPress.WP.AlternativeFunctions.rand_rand
					$uniqid = uniqid( 'thegem-custom-' ) . rand( 1, 9999 );

					$customCss = dgwtWcasTheGemGetCustomCss( $atts, $uniqid );

					if ( isset( $atts['icon_size'] ) ) {
						$customCss .= ".$uniqid .dgwt-wcas-ico-magnifier, .$uniqid .dgwt-wcas-ico-magnifier-handler{max-height:" . $atts['icon_size'] . 'px;}';
					}

					if ( isset( $atts['icon_color_normal'] ) ) {
						$customCss .= ".$uniqid .dgwt-wcas-ico-magnifier, .$uniqid .dgwt-wcas-ico-magnifier-handler{fill:" . $atts['icon_color_normal'] . ';}';
					}

					if ( isset( $atts['icon_color_hover'] ) ) {
						$customCss .= ".$uniqid:hover .dgwt-wcas-ico-magnifier, .$uniqid:hover .dgwt-wcas-ico-magnifier-handler{fill:" . $atts['icon_color_hover'] . ';}';
					}

					$cssOutput = empty( $customCss ) ? '' : '<style>' . esc_html( $customCss ) . '</style>';

					return $cssOutput . do_shortcode( '[fibosearch layout="icon" class="' . $uniqid . '"]' );
				}
			);
		}
	}
);

/**
 * Forcing our engine to run when WooCommerce >> Shop grid >> Layout Type is "Products Grid".
 */
add_filter(
	'dgwt/wcas/helpers/is_search_query',
	function ( $enabled, $query ) {
		if (
		$query->get( 'post_type' ) &&
		is_string( $query->get( 'post_type' ) ) &&
		$query->get( 'post_type' ) === 'product' &&
		is_string( $query->get( 's' ) ) &&
		strlen( $query->get( 's' ) ) > 0 &&
		Helpers::isRunningInsideFunction( 'thegem_extended_products_get_posts', 25 )
		) {
			$enabled = true;
		}

		return $enabled;
	},
	10,
	2
);


add_action(
	'wp_head',
	function () {
		$headerSource = thegem_get_option( 'header_source', 'default' );
		$color        = thegem_get_option( 'main_menu_level1_color' );
		$colorHover   = thegem_get_option( 'main_menu_level1_hover_color' );
		$mobileColor  = thegem_get_option( 'mobile_menu_level1_color' );
		// Built-in Header.
		if ( $headerSource === 'default' ) {
			?>
		<style>
			.dgwt-wcas-thegem-menu-search .dgwt-wcas-search-wrapp {
				margin-left: 10px;
				margin-right: 10px;
			}

			.dgwt-wcas-thegem-menu-search .dgwt-wcas-ico-magnifier-handler {
				max-width: 19px;
			}

			.dgwt-wcas-thegem-menu-search .dgwt-wcas-search-icon path {
				fill: <?php echo esc_attr( $color ); ?>;
			}

			.dgwt-wcas-thegem-menu-search .dgwt-wcas-search-icon:hover path {
				fill: <?php echo esc_attr( $colorHover ); ?>;
			}

			#primary-navigation.responsive .dgwt-wcas-thegem-menu-search .dgwt-wcas-search-icon path {
				fill: <?php echo esc_attr( $mobileColor ); ?>;
			}

			#primary-navigation.responsive .dgwt-wcas-thegem-menu-search {
				padding: 16px 20px;
			}

			.header-layout-perspective > .dgwt-wcas-search-wrapp {
				top: 30px;
				position: absolute;
				max-width: 600px;
				left: 270px;
				right: auto;
				margin: 0 auto;
				z-index: 10;
			}

			@media (max-width: 979px) {
				.header-layout-fullwidth_hamburger #primary-navigation > .dgwt-wcas-search-wrapp,
				.header-layout-perspective > .dgwt-wcas-search-wrapp {
					max-width: 350px;
				}

				.header-style-vertical #site-header-wrapper .dgwt-wcas-thegem-vertical-search {
					display: none;
				}
			}

			@media (max-width: 769px) {
				.header-layout-fullwidth_hamburger #primary-navigation > .dgwt-wcas-search-wrapp,
				.header-layout-overlay .primary-navigation.responsive .overlay-menu-cell > .dgwt-wcas-search-wrapp,
				.header-layout-perspective > .dgwt-wcas-search-wrapp {
					display: none !important;
				}
			}

			#page.vertical-header .dgwt-wcas-thegem-vertical-search {
				margin-right: auto;
				margin-left: auto;
				padding-left: 21px;
				padding-right: 21px;
			}

			.header-layout-fullwidth_hamburger #primary-navigation > .dgwt-wcas-search-wrapp {
				top: 30px;
				position: absolute;
				left: 150px;
				max-width: 600px;

				-webkit-transition: all .3s, transform 1s;
				transition: all .3s, transform 1s;
				-webkit-transform: translateX(0);
				transform: translateX(0);
			}

			@media (max-width: 979px) {
				.header-layout-fullwidth_hamburger #primary-navigation > .dgwt-wcas-search-wrapp {
					top: 12px;
				}
			}

			.header-layout-fullwidth_hamburger #primary-navigation.hamburger-active > .dgwt-wcas-search-wrapp {
				-moz-transform: translateX(-450px);
				-webkit-transform: translateX(-450px);
				transform: translateX(-450px);
			}

			.site-header.fixed .header-layout-fullwidth_hamburger #primary-navigation > .dgwt-wcas-search-wrapp,
			.site-header.fixed .header-layout-perspective > .dgwt-wcas-search-wrapp {
				top: 4px;
			}

			.site-header.fixed .header-layout-fullwidth_hamburger #primary-navigation > .dgwt-wcas-search-wrapp {
				left: 80px;
			}

			@media (max-width: 979px) {
				.header-layout-perspective > .dgwt-wcas-search-wrapp {
					top: 10px;
				}
			}

			@media (max-width: 1212px) and (min-width: 980px) {
				.header-layout-perspective #perspective-menu-buttons {
					padding-bottom: 30px;
				}

				.header-layout-perspective > .dgwt-wcas-search-wrapp {
					top: 110px;
				}
			}
		</style>
			<?php
		} else {
			// Header Builder.
			?>
		<style>
			.thegem-template-header .dgwt-wcas-search-wrapp.dgwt-wcas-layout-classic {
				max-width: 300px;
				margin: 0;
			}

			.thegem-template-header .dgwt-wcas-search-wrapp.dgwt-wcas-layout-icon {
				max-width: 300px;
			}
		</style>
			<?php
		}
	}
);
