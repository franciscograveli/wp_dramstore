<?php
// Exit if accessed directly
if ( ! defined( 'DGWT_WCAS_FILE' ) ) {
	exit;
}

/*
	╭────────────────────────╮
	|  REY INTEGRATION INFO  |
	╰────────────────────────╯
	The Rey theme could display 4 styles of search forms. See the table below. A user can select the search style
	in Customizer -> Header -> Search (Button & Panel) -> Search Style.
	┌──────────────┬────────┐
	│ Search style │ Key    │
	├──────────────┼────────┤
	│ Simple Form  │ button │
	│ Wide Panel   │ wide   │
	│ Side Panel   │ side   │
	│ Inline Form  │ inline │
	└──────────────┴────────┘

	In the Rey theme searches could be displayed in two independent ways:
	1. Using basic Rey header (Customizer -> Header -> General -> Header Layout is set to BASIC)
	2. Using Rey global sections (Customizer -> Header -> General -> Header Layout is set to GLOBAL SECTION)
		and an own header template is selected. The header template is created in Elementor.

	The way to replace search bars differently depends on which method the header is displayed.
	━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 */

/**
 * Reusable part of the search form HTML.
 *
 * @param string $shortcode
 *
 * @return void
 */
function fibofilters_integration_rey_theme_search_render( string $shortcode ): void {
	echo '<div class="rey-headerIcon">';
	echo do_shortcode( $shortcode );
	echo '</div>';
}

/**
 * Keep all reusable CSs style in a one place.
 *
 * @param string $search_style Rey supports the following values: button | inline | wide | side.
 *                             These styles can be set in Customizer -> Header -> Search (Button & Panel) -> Search Style
 *
 * @return void
 */
function fibofilters_integration_rey_theme_search_css( string $search_style ): void {
	add_action(
		'wp_footer',
		function () use ( $search_style ) {
			?>
		<style>
			<?php
			if ( in_array(
				$search_style,
				[
					'button',
					'inline',
				]
			) ) :
				?>

			.dgwt-wcas-style-pirx:not(.dgwt-wcas-layout-icon) .dgwt-wcas-sf-wrapp {
				background-color: var(--header-bgcolor, #fff);
			}

			<?php endif; ?>

			<?php
			if ( in_array(
				$search_style,
				[
					'wide',
					'side',
				]
			) ) :
				?>
			.dgwt-wcas-ico-magnifier, .dgwt-wcas-ico-magnifier-handler {
				fill: var(--header-text-color, HSL(var(--neutral-9)));
			}

			<?php endif; ?>
		</style>
			<?php
		}
	);
}

/*
	╭───────────────────────────────────────────────────╮
	|  Method 1                                         |
	|  WHEN A HEADER IS NOT SERVED AS A GLOBAL SECTION  |
	╰───────────────────────────────────────────────────╯
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 */

/*
 * ╭───────────────────────────╮
 * |  Replacing "Simple Form"  |
 * ╰───────────────────────────╯
 */
if ( ! function_exists( 'rey__header__search' ) ) {
	function rey__header__search() {
		if ( ! get_theme_mod( 'header_enable_search', false ) ) {
			return;
		}

		fibofilters_integration_rey_theme_search_render( '[fibosearch layout="flex-icon-on-mobile"]' );
		fibofilters_integration_rey_theme_search_css( 'button' );
	}
}

/*
 * ╭────────────────────────────────────────────────╮
 * |  Replacing both "Wide Panel" and "Side Panel"  |
 * ╰────────────────────────────────────────────────╯
 */
if ( ! function_exists( 'reycore__header__search' ) ) {
	function reycore__header__search() {
		if ( ! get_theme_mod( 'header_enable_search', false ) ) {
			return;
		}

		if ( (
			//phpcs:ignore WordPress.CodeAnalysis.AssignmentInCondition.Found,Squiz.PHP.DisallowMultipleAssignments.FoundInControlStructure
			$search_style = reycore_wc__get_header_search_args( 'search_style' ) ) && in_array(
				$search_style,
				[
					'wide',
					'side',
				]
			)
		) {
			fibofilters_integration_rey_theme_search_render( '[fibosearch layout="icon"]' );
		}

		fibofilters_integration_rey_theme_search_css( 'wide' );
	}
}

/*
 * ╭───────────────────────────╮
 * |  Replacing "Inline Form"  |
 * ╰───────────────────────────╯
 */
add_action(
	'init',
	function () {
		if (
		get_theme_mod( 'header_enable_search', false ) &&
		function_exists( 'reycore_wc__get_header_search_args' ) &&
		reycore_wc__get_header_search_args( 'search_style' ) === 'inline'
		) {
			add_filter(
				'reycore/get_template_part',
				function ( $template, $slug ) {
					if ( $slug === 'inc/modules/inline-search/tpl-search-form-inline' ) {
						$template = DGWT_WCAS_DIR . 'partials/themes/rey/search-form-inline.php';
					}

					return $template;
				},
				10,
				2
			);

			fibofilters_integration_rey_theme_search_css( 'inline' );
		}
	}
);


/*
	╭───────────────────────────────────────────────╮
	|  Method 2                                     |
	|  WHEN A HEADER IS SERVED BY A GLOBAL SECTION  |
	╰───────────────────────────────────────────────╯
	Customizer -> Header -> General -> Header Layout is set to GLOBAL SECTION and an own header template is selected.
	In this case rewriting rey__header__search, reycore__header__search functions and reycore/get_template_part filter
	doesn't work.

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 */


/*
 * ╭──────────────────────────────────────────╮
 * |  Block rendering a header search widget  |
 * ╰──────────────────────────────────────────╯
 */
add_filter(
	'elementor/frontend/widget/should_render',
	function ( $should_render, $object ) {
		if ( is_object( $object ) && get_class( $object ) === 'ReyCore\Elementor\Widgets\HeaderSearch' ) {
			$should_render = false;
		}

		return $should_render;
	},
	10,
	2
);


/*
 * ╭─────────────────────╮
 * |  Render FiboSearch  |
 * ╰─────────────────────╯
 */
add_action(
	'elementor/frontend/widget/before_render',
	function ( $object ) {
		if ( is_object( $object ) && get_class( $object ) === 'ReyCore\Elementor\Widgets\HeaderSearch' ) {

			$settings = $object->get_settings_for_display();

			$search_style = ! is_null( $settings['search_style'] ) && '' !== $settings['search_style'] ? $settings['search_style'] : get_theme_mod( 'header_search_style', 'wide' );

			switch ( $search_style ) {
				case 'wide':
				case 'side':
					fibofilters_integration_rey_theme_search_render( '[fibosearch layout="icon"]' );
					fibofilters_integration_rey_theme_search_css( $search_style );
					break;
				case 'button':
				case 'inline':
					fibofilters_integration_rey_theme_search_render( '[fibosearch layout="flex-icon-on-mobile"]' );
					break;
			}

			fibofilters_integration_rey_theme_search_css( $search_style );
		}
	}
);
