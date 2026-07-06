<?php
// Exit if accessed directly
if ( ! defined( 'DGWT_WCAS_FILE' ) ) {
	exit;
}

add_action(
	'wp_footer',
	function () {
		echo '<div id="wcas-theme-search-icon" style="display: block;">' . do_shortcode( '[fibosearch layout="icon"]' ) . '</div>';
		echo '<div id="wcas-theme-search-input" style="display: block;">' . do_shortcode( '[fibosearch layout="classic"]' ) . '</div>';
	}
);

add_action(
	'wp_footer',
	function () {
		?>
	<script>
		var wcasThemeSearchIcon = document.querySelector('#search_button');
		if (wcasThemeSearchIcon !== null) {
			wcasThemeSearchIcon.replaceWith(document.querySelector('#wcas-theme-search-icon > div'));
		}
		document.querySelector('#wcas-theme-search-icon').remove();

		var wcasThemeSearchInput = document.querySelector('#Top_bar .top-bar-right-input.has-input');
		if (wcasThemeSearchInput !== null) {
			wcasThemeSearchInput.replaceWith(document.querySelector('#wcas-theme-search-input > div'));
		}
		document.querySelector('#wcas-theme-search-input').remove();
	</script>
	<style>
		#Top_bar .dgwt-wcas-search-wrapp.dgwt-wcas-layout-icon {
			margin-left: 5px;
		}

		#Top_bar .dgwt-wcas-search-wrapp.dgwt-wcas-layout-icon .dgwt-wcas-ico-magnifier-handler {
			max-width: 17px;
		}

		.dgwt-wcas-search-wrapp {
			max-width: <?php echo function_exists( 'mfn_opts_get' ) ? mfn_opts_get( 'header-search-input-width', 200, [ 'unit' => 'px' ] ) : '200px'; ?>
		}

		.dgwt-wcas-style-pirx .dgwt-wcas-sf-wrapp button.dgwt-wcas-search-submit::after {
			display: none;
		}

		.dgwt-wcas-overlay-mobile-on {
			margin-top: 0 !important;
		}
	</style>
		<?php
	},
	100
);
