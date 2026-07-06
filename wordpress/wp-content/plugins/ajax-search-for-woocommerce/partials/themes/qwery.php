<?php
// Exit if accessed directly
if ( ! defined( 'DGWT_WCAS_FILE' ) ) {
	exit;
}

function qwery_trx_addons_action_search( $args ) {
	echo do_shortcode( '[fibosearch layout="icon"]' );
}

add_filter(
	'dgwt/wcas/form/magnifier_ico',
	function ( $html, $class ) {
		return '<span class="trx_addons_icon-search ' . $class . '"></span>';
	},
	10,
	2
);

add_action(
	'wp_head',
	function () {
		?>
	<style>
		.dgwt-wcas-search-icon {
			width: 25px;
			height: 25px;
			margin-bottom: -9px;
		}

		.dgwt-wcas-search-icon > .dgwt-wcas-ico-magnifier-handler {
			font-size: 24px;
		}

		.dgwt-wcas-search-icon > .dgwt-wcas-ico-magnifier-handler:before,
		.dgwt-wcas-search-submit > .dgwt-wcas-ico-magnifier:before {
			color: var(--theme-color-text_dark);
			content: '\e9a6';
			font-family: "fontello";
			padding: 1px;
		}

		.dgwt-wcas-suggestion {
			transition: none;
		}
	</style>
		<?php
	}
);
