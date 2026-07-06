<?php

// Exit if accessed directly
if ( ! defined( 'DGWT_WCAS_FILE' ) ) {
	exit;
}

add_action(
	'wp_head',
	function () { ?>
	<style>
		.w-search.layout_modern .w-search-close {

		}

		.w-search.layout_modern .w-search-close {
			color: rgba(0, 0, 0, 0.5) !important;
		}

		.w-search.layout_modern .dgwt-wcas-close {
			display: none;
		}

		.w-search.layout_modern .dgwt-wcas-preloader {
			right: 20px;
		}

		.w-search.layout_fullscreen .w-form-row-field {
			top: 48px;
		}
	</style>
		<?php
	}
);

add_action(
	'wp_footer',
	function () {
		?>
	<script>
		(function ($) {
			function dgwtWcasImprezaGetActiveInstance() {
				var $el = $('.dgwt-wcas-search-wrapp.dgwt-wcas-active'),
					instance;
				if ($el.length > 0) {
					$el.each(function () {
						var $input = $(this).find('.dgwt-wcas-search-input');
						if (typeof $input.data('autocomplete') == 'object') {
							instance = $input.data('autocomplete');
							return false;
						}
					});
				}

				return instance;
			}

			$(document).ready(function () {
				var $btn = $('.w-search-open');
				var originalEvents = [];

				$('.w-search.layout_modern .w-search-close').on('click', function () {
					var instance = dgwtWcasImprezaGetActiveInstance();

					if (typeof instance == 'object') {
						instance.suggestions = [];
						instance.hide();
						instance.el.val('');
					}
				});

				function dgwtWcasImprezaUpdateHandlers() {
					const isMobile = $(window).width() < 900;

					if (isMobile && !$btn.data('mobileHandlerOverride')) {
						originalEvents = $._data($btn[0], 'events')?.click?.slice() || [];
						$btn.off('click');

						$btn.on('click', function (e) {
							e.preventDefault();

							var $mobileHandler = $(e.target).closest('.w-search').find('.js-dgwt-wcas-enable-mobile-form');
							if ($mobileHandler.length) {
								$mobileHandler[0].click();
							}

							setTimeout(function () {
								$('.w-search').removeClass('active');
							}, 500);
						});

						$btn.data('mobileHandlerOverride', true);
					} else if (!isMobile && $btn.data('mobileHandlerOverride')) {
						$btn.off('click');
						originalEvents.forEach(evt => $btn.on('click', evt.handler));
						$btn.removeData('mobileHandlerOverride');
					}
				}

				dgwtWcasImprezaUpdateHandlers();
				$(window).on('resize', dgwtWcasImprezaUpdateHandlers);
			});
		})(jQuery);
	</script>
		<?php
	},
	1000
);

/**
 * Activate the search engine during AJAX loading of subsequent pages when the Grid
 * element's pagination is set to "Load items on page scroll"
 */
add_filter(
	'dgwt/wcas/helpers/is_search_query',
	function ( $enabled, $query ) {
		if (
		did_action( 'us_grid_before_custom_query' ) &&
		$query->get( 'post_type' ) &&
		is_string( $query->get( 'post_type' ) ) &&
		$query->get( 'post_type' ) === 'product' &&
		is_string( $query->get( 's' ) ) &&
		strlen( $query->get( 's' ) ) > 0
		) {
			$enabled = true;
		}

		return $enabled;
	},
	10,
	2
);

/**
 * Force orderby (if empty) during AJAX loading of subsequent pages when the Grid
 * element's pagination is set to "Load items on page scroll"
 */
add_filter(
	'woocommerce_get_catalog_ordering_args',
	function ( $args ) {
		if ( function_exists( 'us_maybe_get_post_json' ) ) {
			$template_vars = us_maybe_get_post_json();
			if (
			! empty( $template_vars ) &&
			isset( $template_vars['query_args']['post_type'] ) &&
			$template_vars['query_args']['post_type'] === 'product' &&
			! empty( $template_vars['query_args']['s'] ) &&
			empty( $template_vars['query_args']['orderby'] )
			) {
				$args['orderby'] = 'relevance';
				$args['order']   = 'DESC';
			}
		}

		return $args;
	},
	999,
	1
);
