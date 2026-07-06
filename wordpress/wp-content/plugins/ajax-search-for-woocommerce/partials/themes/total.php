<?php

use TotalTheme\Header\Core as Header;
use TotalTheme\Header\Menu\Search;
use TotalTheme\Mobile\Menu as Mobile_Menu;

// Exit if accessed directly.
if ( ! defined( 'DGWT_WCAS_FILE' ) ) {
	exit;
}

// Remove search icon from theme.
add_action(
	'init',
	function () {
		remove_filter( 'wp_nav_menu_items', 'TotalTheme\Header\Menu\Search::insert_icon', 11, 2 );

		// Override theme shortcodes (used in Flex headers).
		add_shortcode(
			'header_search_icon',
			function () {
				return do_shortcode( '[fibosearch layout="icon" class="wpex-ml-20"]' );
			}
		);
		add_shortcode(
			'searchform',
			function () {
				return do_shortcode( '[fibosearch layout="classic" class="wpex-ml-20"]' );
			}
		);
	}
);

add_filter(
	'dgwt/wcas/scripts/mobile_overlay_breakpoint',
	function () {
		return Mobile_Menu::breakpoint();
	}
);

add_filter(
	'wp_nav_menu_items',
	function ( $items, $args ) {
		// The following code is partly from TotalTheme\Header\Menu\Search::insert_icon().
		if ( Header::has_flex_container() || ! Search::is_enabled() ) {
			return $items;
		}
		$search_icon_theme_locations = (array) apply_filters( 'wpex_menu_search_icon_theme_locations', [ 'main_menu' ] );
		if ( ! in_array( $args->theme_location, $search_icon_theme_locations ) ) {
			return $items;
		}

		$liClasses = 'dgwt-wcas-search-menu-item search-toggle-li menu-item wpex-menu-extra';

		$menuSearch = '<li class="' . esc_attr( $liClasses ) . '">';
		if ( Header::style() === 'six' ) {
			$menuSearch .= do_shortcode( '[fibosearch layout="classic"]' );
		} else {
			$menuSearch .= do_shortcode( '[fibosearch layout="icon"]' );
		}
		$menuSearch .= '</li>';

		/**
		 * Filters the header menu search icon position.
		 *
		 * @param $position | options: start or end.
		 */
		$menuSearchPosition = apply_filters( 'wpex_header_menu_search_position', 'end' );

		switch ( $menuSearchPosition ) {
			case 'start':
				$items = $menuSearch . $items;
				break;
			case 'end':
			default:
				$items = $items . $menuSearch;
				break;
		}

		return $items;
	},
	11,
	2
);

add_action(
	'wp_head',
	function () {
		$iconSize = get_theme_mod( 'menu_search_icon_size' );
		if ( empty( $iconSize ) ) {
			$iconSize = '14px';
		}
		?>
	<style>
		.navbar-fixed-line-height .main-navigation-ul > .menu-item > .dgwt-wcas-search-wrapp {
			height: var(--wpex-main-nav-height, 50px);
			line-height: var(--wpex-main-nav-line-height, var(--wpex-main-nav-height, 50px));
		}

		.dgwt-wcas-ico-magnifier, .dgwt-wcas-ico-magnifier-handler {
			max-width: none;
			max-height: <?php echo esc_attr( $iconSize ); ?>;
			fill: var(--wpex-main-nav-link-color, var(--wpex-text-2));
		}

		.dgwt-wcas-search-menu-item:hover .dgwt-wcas-ico-magnifier,
		.dgwt-wcas-search-menu-item:hover .dgwt-wcas-ico-magnifier-handler,
		#site-header-flex-aside .dgwt-wcas-ico-magnifier:hover,
		#site-header-flex-aside .dgwt-wcas-ico-magnifier-handler:hover {
			fill: var(--wpex-hover-main-nav-link-color, var(--wpex-accent, var(--wpex-main-nav-link-color, var(--wpex-text-2))));
		}

		.main-navigation-ul > .dgwt-wcas-search-menu-item {
			padding-left: var(--wpex-main-nav-link-padding-x, 0px);
			padding-right: var(--wpex-main-nav-link-padding-x, 0px);
		}

		#site-header-flex-aside-inner .dgwt-wcas-search-wrapp.dgwt-wcas-layout-classic {
			max-width: 200px;
		}
	</style>
		<?php
	}
);

add_action(
	'wp_footer',
	function () {
		$style = Mobile_Menu::style();
		if ( $style === 'sidr' ) {
			// Mobile Menu Style: Sidebar.
			// We use the default search input, after clicking which we open our overlay.
			?>
		<script>
			(function ($) {
				$(document).on('focus', '#sidr-main input[type="search"]', function () {
					var $input = $('.js-dgwt-wcas-enable-mobile-form');
					if ($input.length > 0) {
						$input[0].click();
					}
				});
			}(jQuery));
		</script>
			<?php
		} else {
			echo '<div id="wcas-mobile-search" style="display: none;">' . do_shortcode( '[fibosearch layout="classic"]' ) . '</div>';
			?>
		<script>
			var mobileSearch = document.querySelector('#mobile-menu-search');
			if (mobileSearch !== null) {
				mobileSearch.replaceWith(document.querySelector('#wcas-mobile-search > div'));
			}
			document.querySelector('#wcas-mobile-search').remove();
		</script>
			<?php
		}

		if ( Header::style() === 'two' ) {
			// Header style: 2. Bottom Menu
			echo '<div id="wcas-desktop-search" style="display: none;"><div class="wpex-clr">' . do_shortcode( '[fibosearch layout="classic"]' ) . '</div></div>';
			?>
		<script>
			var desktopSearch = document.querySelector('#header-two-search');
			if (desktopSearch !== null) {
				desktopSearch.replaceWith(document.querySelector('#wcas-desktop-search > div'));
			}
			document.querySelector('#wcas-desktop-search').remove()
		</script>
			<?php
		}
	}
);
