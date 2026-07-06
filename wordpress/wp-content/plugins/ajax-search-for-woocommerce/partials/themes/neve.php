<?php
// Exit if accessed directly
if ( ! defined( 'DGWT_WCAS_FILE' ) ) {
	exit;
}

add_filter(
	'hfg_template_locations',
	function ( $locations ) {
		$locations = array_merge( [ DGWT_WCAS_DIR . 'partials/themes/neve/' ], $locations );

		return $locations;
	}
);

add_action(
	'wp_head',
	function () { ?>
	<style>
		html[data-neve-theme="dark"] .header .dgwt-wcas-search-icon svg,
		html[data-neve-theme="dark"] .header .dgwt-wcas-search-icon path {
			fill: white;
		}
	</style>
		<?php
	}
);
