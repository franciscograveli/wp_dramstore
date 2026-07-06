<?php
// Exit if accessed directly.
if ( ! defined( 'DGWT_WCAS_FILE' ) ) {
	exit;
}

// Change mobile breakpoint
add_filter(
	'dgwt/wcas/scripts/mobile_overlay_breakpoint',
	function () {
		$mobile_menu_breakpoint        = get_theme_mod( 'ocean_mobile_menu_breakpoints', '959' );
		$mobile_menu_custom_breakpoint = get_theme_mod( 'ocean_mobile_menu_custom_breakpoint' );

		if ( $mobile_menu_breakpoint === 'custom' && ! empty( $mobile_menu_custom_breakpoint ) ) {
			$mobile_menu_breakpoint = $mobile_menu_custom_breakpoint;
		}

		return $mobile_menu_breakpoint;
	}
);

add_filter(
	'get_search_form',
	function ( $form, $args ) {
		// Used when search style is "Drop down" and on 404 page.
		return do_shortcode( '[fibosearch]' );
	},
	10,
	2
);

add_action(
	'wp',
	function () {
		// Mobile search - icon in menu.
		if (
		! function_exists( 'oceanwp_mobile_menu_search_style' ) ||
		! function_exists( 'oceanwp_header_style' )
		) {
			return;
		}

		$search_style = oceanwp_mobile_menu_search_style(); // Search Icon Style.
		// TODO
		// phpcs:ignore WordPress.PHP.DisallowShortTernary.Found
		$search_style = $search_style ?: 'disabled';
		$header_style = oceanwp_header_style();

		if ( $search_style === 'disabled' || $header_style === 'vertical' ) {
			return;
		}

		remove_action( 'ocean_after_mobile_icon', 'oceanwp_mobile_search_icon' );
		remove_action( 'ocean_mobile_menu_icon_after', 'oceanwp_mobile_search_form_html' );

		add_action(
			'ocean_after_mobile_icon',
			function () {
				// Placeholders to prevent JS errors.
				echo '<span class="search-icon-dropdown"></span>';
				echo '<span class="search-style-dropdown"></span>';
				echo do_shortcode( '[fibosearch layout="icon"]' );
			}
		);

		add_action(
			'wp_footer',
			function () {
				$headerHeight = get_theme_mod( 'ocean_header_height', '74' );
				?>
		<style>
			.oceanwp-mobile-menu-icon .dgwt-wcas-search-wrapp {
				display: inline-block;
				margin-left: 12px;
			}

			.oceanwp-mobile-menu-icon .dgwt-wcas-search-wrapp .icon-magnifier {
				color: #555;
				font-size: 13px;
					line-height: <?php echo esc_attr( $headerHeight ); ?>px;
			}

			.oceanwp-mobile-menu-icon .dgwt-wcas-search-wrapp:hover .icon-magnifier {
				color: #13aff0;
			}
		</style>
				<?php
			}
		);

		add_filter(
			'dgwt/wcas/form/magnifier_ico',
			function ( $html, $class ) {
				if ( $class === 'dgwt-wcas-ico-magnifier-handler' ) {
					$html = '<i class=" icon-magnifier" aria-hidden="true" role="img"></i>';
				}

				return $html;
			},
			10,
			2
		);
	},
	20
);

add_action(
	'wp_head',
	function () {
		if ( ! function_exists( 'oceanwp_header_style' ) ) {
			return;
		}
		$headerStyle = oceanwp_header_style();

		// Hide default search before overwrite.
		if ( $headerStyle === 'medium' ) {
			?>
		<style>
			#site-header.medium-header #medium-searchform > form {
				display: none;
			}
		</style>
			<?php
		} elseif ( $headerStyle === 'vertical' ) {
			?>
		<style>
			#vertical-searchform > form {
				display: none;
			}
		</style>
			<?php
		}
	}
);

add_action(
	'wp_footer',
	function () {
		if (
		! function_exists( 'oceanwp_menu_search_style' ) ||
		! function_exists( 'oceanwp_header_style' ) ||
		! function_exists( 'oceanwp_mobile_menu_style' )
		) {
			return;
		}

		$menuSearchStyle = oceanwp_menu_search_style();
		$headerStyle     = oceanwp_header_style();

		$mobileMenuStyle  = oceanwp_mobile_menu_style(); // Mobile Menu Style.
		$mobileMenuSearch = get_theme_mod( 'ocean_mobile_menu_search', true ); // MOBILE MENU SEARCH.

		// Search styles - desktop.
		if ( $menuSearchStyle === 'drop_down' ) {
			// Drop down.
			?>
		<script>
			var desktopSearchInput = document.querySelector('#searchform-dropdown .dgwt-wcas-search-input');
			if (desktopSearchInput !== null) {
				// This class is used to focus input.
				desktopSearchInput.classList.add('field');
			}
		</script>
			<?php
		} elseif ( $menuSearchStyle === 'overlay' ) {
			// Overlay.
			echo '<div id="dgwt-wcas-desktop-search" style="display: none;"><div>' . do_shortcode( '[fibosearch]' ) . '<a href="#" class="search-overlay-close"><span></span></a></div></div>';
			?>
		<script>
			var desktopSearch = document.querySelector('#searchform-overlay > div > form');
			if (desktopSearch !== null) {
				desktopSearch.replaceWith(document.querySelector('#dgwt-wcas-desktop-search > div'));
			}
			document.querySelector('#dgwt-wcas-desktop-search').remove();
		</script>
		<style>
			#searchform-overlay a.search-overlay-close {
				top: 15% !important;
			}

			#searchform-overlay form {
				position: static;
				margin-top: 0;
			}

			#searchform-overlay .dgwt-wcas-search-wrapp {
				max-width: none;
				position: absolute;
				top: 30%;
				text-align: left;
			}

			.dgwt-wcas-details-wrapp {
				z-index: 9999 !important;
			}

			#searchform-overlay form input {
				z-index: inherit;
			}

			#searchform-overlay .dgwt-wcas-no-submit .dgwt-wcas-ico-magnifier {
				fill: #fff;
			}

			#searchform-overlay .dgwt-wcas-voice-search path {
				fill: #fff;
			}
		</style>
			<?php
		} elseif ( $menuSearchStyle === 'header_replace' ) {
			// Header replace.
			echo '<div id="dgwt-wcas-desktop-search" style="display: none;">' . do_shortcode( '[fibosearch]' ) . '</div>';
			?>
		<script>
			var desktopSearch = document.querySelector('#searchform-header-replace > form');
			if (desktopSearch !== null) {
				desktopSearch.replaceWith(document.querySelector('#dgwt-wcas-desktop-search > div'));
			}
			document.querySelector('#dgwt-wcas-desktop-search').remove();
		</script>
		<style>
			#searchform-header-replace {
				display: flex;
				align-items: center;
			}

			#searchform-header-replace input {
				padding: 10px 15px 10px 40px;
				width: 100%;
				font-size: inherit;
			}

			#searchform-header-replace .dgwt-wcas-search-wrapp {
				margin-right: 40px;
			}
		</style>
			<?php
		}

		// Mobile menu.
		if ( $mobileMenuSearch ) {
			// Menu style - dropdown.
			if ( $mobileMenuStyle === 'dropdown' ) {
				echo '<div id="dgwt-wcas-mobile-search" style="display: none;">' . do_shortcode( '[fibosearch]' ) . '</div>';
				?>
			<script>
				var mobileSearch = document.querySelector('#mobile-menu-search > form');
				if (mobileSearch !== null) {
					mobileSearch.replaceWith(document.querySelector('#dgwt-wcas-mobile-search > div'));
				}
				document.querySelector('#dgwt-wcas-mobile-search').remove();
			</script>
			<style>
				#mobile-dropdown #mobile-menu-search form input {
					padding: 10px 15px 10px 40px !important;
				}
			</style>
				<?php
			} elseif ( $mobileMenuStyle === 'sidebar' ) {
				// Menu style - sidebar.
				echo '<div id="dgwt-wcas-mobile-search" style="display: none;">' . do_shortcode( '[fibosearch]' ) . '</div>';
				?>
			<script>
				(function ($) {
					$(window).on('load', function () {
						var mobileSearch = document.querySelector('#sidr .sidr-class-mobile-searchform');
						if (mobileSearch !== null) {
							mobileSearch.replaceWith(document.querySelector('#dgwt-wcas-mobile-search > div'));
						}
						document.querySelector('#dgwt-wcas-mobile-search').remove();
					});
				}(jQuery));
			</script>
			<style>
				#sidr .sidr-class-mobile-searchform input {
					padding: 10px 15px 10px 40px !important;
				}

				#sidr .dgwt-wcas-search-wrapp {
					padding: 0 20px;
					margin-top: 20px;
				}
			</style>
				<?php
			} elseif ( $mobileMenuStyle === 'fullscreen' ) {
				// Menu style - full screen.
				echo '<div id="dgwt-wcas-mobile-search" style="display: none;">' . do_shortcode( '[fibosearch]' ) . '</div>';
				?>
			<script>
				var mobileSearch = document.querySelector('#mobile-fullscreen #mobile-search > form');
				if (mobileSearch !== null) {
					mobileSearch.replaceWith(document.querySelector('#dgwt-wcas-mobile-search > div'));
				}
				document.querySelector('#dgwt-wcas-mobile-search').remove();
			</script>
			<style>
				#mobile-fullscreen #mobile-search input {
					padding: 10px 15px 10px 40px !important;
					font-size: 16px;
				}

				#mobile-fullscreen #mobile-search {
					margin: 0 auto;
					max-width: 280px;
				}

				#mobile-fullscreen #mobile-search .dgwt-wcas-ico-magnifier {
					fill: #fff;
				}

				#mobile-fullscreen #mobile-search .dgwt-wcas-voice-search path {
					fill: #fff;
				}
			</style>
				<?php
			}
		}

		// Header styles.
		if ( $headerStyle === 'medium' ) {
			echo '<div id="dgwt-wcas-desktop-search-medium" style="display: none;">' . do_shortcode( '[fibosearch]' ) . '</div>';
			?>
		<script>
			var desktopSearch = document.querySelector('#medium-searchform form');
			if (desktopSearch !== null) {
				desktopSearch.replaceWith(document.querySelector('#dgwt-wcas-desktop-search-medium > div'));
			}
			document.querySelector('#dgwt-wcas-desktop-search-medium').remove();
		</script>
		<style>
			#site-header.medium-header #medium-searchform input {
				padding: 10px 15px 10px 40px;
				border: 1px solid #ddd;
				max-width: 100%;
			}

			#site-header.medium-header #medium-searchform input:focus {
				max-width: 100%;
			}

			#site-header.medium-header .dgwt-wcas-search-wrapp {
				max-width: 200px;
			}
		</style>
			<?php
		} elseif ( $headerStyle === 'vertical' ) {
			echo '<div id="dgwt-wcas-desktop-search-vertical" style="display: none;">' . do_shortcode( '[fibosearch]' ) . '</div>';
			?>
		<script>
			var desktopSearch = document.querySelector('#vertical-searchform form');
			if (desktopSearch !== null) {
				desktopSearch.replaceWith(document.querySelector('#dgwt-wcas-desktop-search-vertical > div'));
			}
			document.querySelector('#dgwt-wcas-desktop-search-vertical').remove();
		</script>
		<style>
			#site-header.vertical-header #vertical-searchform form input {
				padding: 10px 15px 10px 40px;
				width: 100%;
				font-size: inherit;
				z-index: inherit;
			}
		</style>
			<?php
		} elseif ( $headerStyle === 'full_screen' ) {
			echo '<div id="dgwt-wcas-desktop-search-full-screen" style="display: none;">' . do_shortcode( '[fibosearch]' ) . '</div>';
			?>
		<script>
			var desktopSearch = document.querySelector('#full-screen-menu .search-toggle-li > form');
			if (desktopSearch !== null) {
				desktopSearch.replaceWith(document.querySelector('#dgwt-wcas-desktop-search-full-screen > div'));
			}
			document.querySelector('#dgwt-wcas-desktop-search-full-screen').remove();
		</script>
		<style>
			#site-header.full_screen-header .fs-dropdown-menu > li.search-toggle-li {
				max-width: 350px;
			}

			#site-header.full_screen-header .fs-dropdown-menu > li.search-toggle-li input {
				padding: 10px 15px 10px 40px;
				z-index: inherit;
			}

			#full-screen-menu .search-toggle-li .dgwt-wcas-ico-magnifier {
				fill: #fff;
			}

			#full-screen-menu .search-toggle-li .dgwt-wcas-voice-search path {
				fill: #fff;
			}
		</style>
			<?php
		}
	}
);
