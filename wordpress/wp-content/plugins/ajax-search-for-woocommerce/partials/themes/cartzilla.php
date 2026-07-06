<?php
// Exit if accessed directly
if ( ! defined( 'DGWT_WCAS_FILE' ) ) {
	exit;
}

add_filter(
	'widget_display_callback',
	function ( $instance, $widget_obj, $args ) {
		if ( is_a( $widget_obj, 'WC_Widget_Product_Search' ) ) {
			echo do_shortcode( '[fibosearch]' );
		}

		return false;
	},
	10,
	3
);


add_action(
	'wp_footer',
	function () {
		?>
	<script>
		(function ($) {
			$(window).on('load', function () {
				$('a[href="#searchBox"]').on('click', function (e) {
					setTimeout(function () {
						var $input = $('#searchBox .dgwt-wcas-search-input');
						if ($input.length > 0) {
							$input.trigger('focus');
						}
					}, 500);
				});
			});
		}(jQuery));
	</script>
		<?php
	}
);
