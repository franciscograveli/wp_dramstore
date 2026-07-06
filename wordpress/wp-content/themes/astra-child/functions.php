<?php
/**
 * Astra Child Theme functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Astra Child
 * @since 1.0.0
 */

/**
 * Define Constants
 */
define( 'CHILD_THEME_ASTRA_CHILD_VERSION', '1.0.0' );

/**
 * Enqueue styles
 */
function child_enqueue_styles() {

	wp_enqueue_style( 'astra-child-theme-css', get_stylesheet_directory_uri() . '/style.css', array('astra-theme-css'), CHILD_THEME_ASTRA_CHILD_VERSION, 'all' );

}

add_action( 'wp_enqueue_scripts', 'child_enqueue_styles', 15 );

/**
 * Fix: YITH Product Slider Carousel (Owl Carousel) swallows product clicks
 * whenever the mouse moves even a few pixels between mousedown and mouseup,
 * mistaking a normal click for a drag/swipe. This intercepts the click at the
 * document capture phase (before Owl's own handler) and forces navigation
 * when the pointer barely moved, while leaving real drags untouched.
 */
function dram_store_fix_ywcps_carousel_clicks() {
	?>
	<script>
	(function () {
		var startX, startY, dragged = false;

		document.addEventListener( 'mousedown', function ( e ) {
			if ( e.target.closest && e.target.closest( '.ywcps-slider' ) ) {
				startX = e.clientX;
				startY = e.clientY;
				dragged = false;
			}
		}, true );

		document.addEventListener( 'mousemove', function ( e ) {
			if ( startX === undefined ) return;
			if ( Math.abs( e.clientX - startX ) > 6 || Math.abs( e.clientY - startY ) > 6 ) {
				dragged = true;
			}
		}, true );

		document.addEventListener( 'mouseup', function () {
			startX = undefined;
		}, true );

		document.addEventListener( 'click', function ( e ) {
			var link = e.target.closest && e.target.closest( '.ywcps-slider a[href]' );
			if ( ! link || dragged ) return;
			e.stopImmediatePropagation();
			e.preventDefault();
			window.location.href = link.href;
		}, true );
	})();
	</script>
	<?php
}
add_action( 'wp_footer', 'dram_store_fix_ywcps_carousel_clicks', 100 );