<?php
// Exit if accessed directly
if ( ! defined( 'DGWT_WCAS_FILE' ) ) {
	exit;
}

// Force the theme to use the default $wp_query on the search page.
add_filter( 'uncode_use_legacy_search_query', '__return_true' );

add_action(
	'wp_footer',
	function () {
		echo '<div id="wcas-desktop-search" style="display: none;">' . do_shortcode( '[wcas-search-form layout="icon"]' ) . '</div>';
		echo '<div id="wcas-mobile-search" style="display: none;">' . do_shortcode( '[wcas-search-form layout="icon"]' ) . '</div>';
		?>
	<script>
		var desktopSearch = document.querySelector('.menu-wrapper a.search-icon');
		if (desktopSearch !== null) {
			desktopSearch.replaceWith(document.querySelector('#wcas-desktop-search > div'));
		}
		document.querySelector('#wcas-desktop-search').remove()

		var mobileSearch = document.querySelector('.menu-wrapper a.mobile-search-icon');
		if (mobileSearch !== null) {
			mobileSearch.replaceWith(document.querySelector('#wcas-mobile-search > div'));
		}
		document.querySelector('#wcas-mobile-search').remove();
	</script>

	<style>
		.menu-icons .dgwt-wcas-search-wrapp {
			margin-right: 9px;
		}

		.dgwt-wcas-ico-magnifier-handler {
			max-width: 18px;
		}

		.dgwt-wcas-sf-wrapp input[type=search].dgwt-wcas-search-input {
			border-color: #ddd !important;
		}

		.menu-dark .dgwt-wcas-search-icon path {
			fill: #ffffff;
		}

		.menu-dark .dgwt-wcas-search-icon:hover path {
			fill: rgba(255, 255, 255, 0.5);
		}

		header ul li.search-icon .dgwt-wcas-search-wrapp {
			z-index: 100;
		}

		@media (max-width: 959px) {
			.menu-icons .dgwt-wcas-search-wrapp {
				padding: 9px 36px 9px 36px;
				max-width: none;
			}
		}

		@media (max-width: 959px) {
			header ul .dgwt-wcas-search-wrapp a.dgwt-wcas-enable-mobile-form {
				position: absolute;
				top: 0;
				left: 0;
				right: 0;
				bottom: 0;
			}
			.dgwt-wcas-search-wrapp {
				padding: 2px 25px;
			}
		}

		@media (min-width: 960px) {
			.dgwt-wcas-sf-wrapp::before {
				position: absolute;
				width: 0;
				height: 0;
				overflow: hidden;
			}
			.dgwt-wcas-search-wrapp {
				display: inline-grid;
			}
		}
	</style>
		<?php
	}
);
