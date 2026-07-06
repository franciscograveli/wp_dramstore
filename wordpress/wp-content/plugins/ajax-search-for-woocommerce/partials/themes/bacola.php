<?php

// Exit if accessed directly
if ( ! defined( 'DGWT_WCAS_FILE' ) ) {
	exit;
}

add_action(
	'wp_footer',
	function () {
		?>
	<script>
		const isMobile = window.matchMedia('(max-width: 768px)').matches;
		const searchForm = document.querySelector('#searchform');
		const searchWrapper = document.querySelector('.dgwt-wcas-search-wrapp');
		if (isMobile && searchForm && searchWrapper) {
			searchForm.replaceWith(searchWrapper)
		}

		document.addEventListener('click', (event) => {
			if (!isMobile) return;
			const isReturn = event.target.closest('.js-dgwt-wcas-om-return');

			if (!isReturn && event.target.closest('.dgwt-wcas-search-wrapp') || event.target.closest('.mobile-nav-wrapper a.search')) {
				const enableMobileForm = document.querySelector('.dgwt-wcas-search-wrapp .js-dgwt-wcas-enable-mobile-form');
				if (enableMobileForm) enableMobileForm.click();
			}

			if (isReturn) {
				const headerSearchActive = document.querySelector('.header-search.active');
				if (headerSearchActive) {
					headerSearchActive.classList.remove('active')
				}
			}
		});
	</script>

	<style>
		.dgwt-wcas-style-pirx .dgwt-wcas-sf-wrapp button.dgwt-wcas-search-submit {
			position: absolute;
			min-height: 33px;
			min-width: 33px;
			height: 33px;
			width: 33px;
			left: 23px;
			top: 23px;
			pointer-events: none;
		}

		.header-main .header-search .dgwt-wcas-style-pirx-compact .dgwt-wcas-sf-wrapp input.dgwt-wcas-search-input {
			height: auto;
			padding: 10px 0 10px 48px;
		}

		.header-main .header-search .dgwt-wcas-style-solaris .dgwt-wcas-sf-wrapp button.dgwt-wcas-search-submit {
			position: absolute;
			width: auto;
			min-width: 30px;
			right: 0;
			left: auto;
			top: 0;
			bottom: auto;
			padding: 0 10px;
			background-color: #333;
		}

		.header-main .header-search .dgwt-wcas-sf-wrapp button.dgwt-wcas-search-submit::before {
			top: 50%;
			transform: translateY(-50%);
		}

		.dgwt-wcas-style-pirx .dgwt-wcas-search-form .dgwt-wcas-sf-wrapp input[type="search"].dgwt-wcas-search-input {
			padding-left: 48px;
		}

		.dgwt-wcas-search-wrapp .dgwt-wcas-sf-wrapp:after {
			display: none;
		}

		@media screen and (max-width: 768px) {
			.site-header.mobile-nav-enable .header-main > .container {
				height: auto;
			}

			.dgwt-wcas-overlay-mobile-on .dgwt-wcas-style-pirx .dgwt-wcas-search-form .dgwt-wcas-sf-wrapp input[type="search"].dgwt-wcas-search-input {
				padding-left: 5px;
			}

			.header-main .mobile-search-column .dgwt-wcas-style-solaris .dgwt-wcas-sf-wrapp button.dgwt-wcas-search-submit {
				position: absolute;
			}
		}
	</style>
		<?php
	}
);

/**
 * Hide the quantity field in the details panel.
 *
 * Reason:
 *  - The Bacola theme attaches its own actions to the "Add to cart" button,
 *    which sends an extra request that executes: WC_AJAX::get_refreshed_fragments().
 *  - As a side effect, the quantity ends up forced to 1 regardless of what
 *    the user sets in the details panel.
 *
 *  To avoid this conflict, we hide the quantity field here.
 */
add_action(
	'dgwt/wcas/details_panel/product/add_to_cart_before',
	function ( $vars ) {
		$vars->showQuantity = false;
	}
);
