<?php
// Exit if accessed directly
if ( ! defined( 'DGWT_WCAS_FILE' ) ) {
	exit;
}

add_action(
	'wp_head',
	function () { ?>
	<style>
		.header-search-form {
			display: none;
		}

		.dgwt-wcas-search-wrapp {
			max-width: 100%;
		}
	</style>
		<?php
	}
);

add_action(
	'wp_footer',
	function () {
		echo '<div id="wcas-theme-search" style="display: block;">' . do_shortcode( '[fibosearch layout="classic"]' ) . '</div>';
		?>
	<script>
		wcasThemeSearch = document.querySelector('.header-search-form');
		if (wcasThemeSearch !== null) {
			wcasThemeSearch.replaceWith(document.querySelector('#wcas-theme-search > div'));
		}
		document.querySelector('#wcas-theme-search').remove();
	</script>
		<?php
	}
);
