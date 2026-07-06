<?php
// Exit if accessed directly.
if ( ! defined( 'DGWT_WCAS_FILE' ) ) {
	exit;
}

if ( ! function_exists( 'pix_get_header_search' ) ) {
	function pix_get_header_search( $opts ) {
		$attrs = [];

		// Use color CSS classes defined by theme.
		if (
			isset( $opts['color'] ) &&
			$opts['color'] !== 'custom'
		) {
			$attrs[] = 'class="text-' . esc_attr( $opts['color'] ) . '"';
		}

		// Custom color.
		if (
			isset( $opts['color'] ) &&
			$opts['color'] === 'custom' &&
			isset( $opts['custom_color'] )
		) {
			$attrs[] = 'icon_color="' . esc_attr( $opts['custom_color'] ) . '"';
		}

		echo do_shortcode( sprintf( '[fibosearch %s layout="icon"]', join( ' ', $attrs ) ) );
	}
}

if ( ! function_exists( 'sc_pix_search' ) ) {
	function sc_pix_search( $attr, $content = null ) {
		return do_shortcode( '[fibosearch]' );
	}
}

add_action(
	'wp_footer',
	function () {
		?>
	<style>
		.pix-header-desktop .dgwt-wcas-search-wrapp,
		.pix-header-mobile .dgwt-wcas-search-wrapp {
			margin-top: -5px;
			margin-left: 0.5rem;
			margin-right: 0.5rem;
		}

		.pix-header-desktop .dgwt-wcas-search-wrapp .dgwt-wcas-ico-magnifier-handler,
		.pix-header-mobile .dgwt-wcas-search-wrapp .dgwt-wcas-ico-magnifier-handler {
			max-width: 18px;
		}

		<?php // Sticky header. ?>
		.pix-header-desktop.is-scroll .dgwt-wcas-search-wrapp .dgwt-wcas-ico-magnifier-handler path {
			fill: var(--text-heading-default) !important;
		}
	</style>
		<?php
	}
);
