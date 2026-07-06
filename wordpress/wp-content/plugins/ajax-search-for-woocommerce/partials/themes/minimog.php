<?php
// Exit if accessed directly
if ( ! defined( 'DGWT_WCAS_FILE' ) ) {
	exit;
}

add_action(
	'wp_head',
	function () { ?>
	<style>
		#popup-search .row-popular-search-keywords {
			display: none;
		}

		.dgwt-wcas-suggestion {
			transition: none;
		}
	</style>
		<?php
	}
);

add_action(
	'wp_enqueue_scripts',
	function () {
		// Force faster enqueuing as otherwise jQuery dependency error occurs.
		wp_enqueue_script( 'jquery-dgwt-wcas' );
	},
	15
);

add_action(
	'wp_footer',
	function () {
		echo '<div id="wcas-theme-search" style="display: block;">' . do_shortcode( '[fibosearch layout="classic"]' ) . '</div>';
		?>
	<script>
		wcasThemeSearch = document.querySelector('#popup-search .page-search-popup-content');
		if (wcasThemeSearch !== null) {
			wcasThemeSearch.replaceWith(document.querySelector('#wcas-theme-search > div'));
		}
		document.querySelector('#wcas-theme-search').remove();
	</script>
	<script>
		(function ($) {
			// Autofocus.
			$('.page-open-popup-search').on('click', function () {
				$inputMobile = $('#popup-search .js-dgwt-wcas-enable-mobile-form');
				$input = $('#popup-search .dgwt-wcas-search-input');
				if ($inputMobile.length > 0) {
					setTimeout(function () {
						$inputMobile.trigger('click');
					}, 50);
					// Close the theme search window.
					setTimeout(function () {
						$('#popup-search #search-popup-close').trigger('click');
					}, 200)
				} else if ($input.length > 0) {
					setTimeout(function () {
						$input.trigger('focus');
					}, 350);
				}
			});
		}(jQuery));
	</script>
		<?php
	},
	50
);
