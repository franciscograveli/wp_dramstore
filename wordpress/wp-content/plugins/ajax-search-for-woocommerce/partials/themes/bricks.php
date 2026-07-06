<?php

use DgoraWcas\Helpers;

// Exit if accessed directly.
if ( ! defined( 'DGWT_WCAS_FILE' ) ) {
	exit;
}

global $dgwtWcasBricksStyles;

$dgwtWcasBricksStyles = '';

add_action(
	'wp_head',
	function () {
		?>
	<style>
		.dgwt-wcas-pd-addtc-form .quantity .action {
			display: none;
		}

		.dgwt-wcas-pd-addtc-form .add_to_cart_inline .add_to_cart_button{
			padding: 7px 13px;
		}

		.dgwt-wcas-pd-rating .star-rating::before{
			left: 0;
		}

		.woocommerce .dgwt-wcas-pd-rating .star-rating{
			color: #d5d6d7;
		}
	</style>
		<?php
	}
);

/**
 * Support for Bricks custom pagination parameter.
 */
add_action(
	'pre_get_posts',
	function ( $query ) {
		if ( ! Helpers::isSearchQuery( $query ) ) {
			return;
		}
		//phpcs:disable WordPress.Security.NonceVerification.Recommended
		if ( ! empty( $_GET['product-page'] ) && intval( $_GET['product-page'] ) > 0 ) {
			$query->set( 'paged', intval( $_GET['product-page'] ) );
		}
		//phpcs:enable
	},
	900000
);

/**
 * This filter should return true or false depending on whether the Element is to be displayed,
 * but we use it to override the search Element. This is not entirely the correct way to use a filter,
 * but we have no other way to override the Element's rendering function.
 */
add_filter(
	'bricks/element/render',
	function ( $render_element, $element ) {
		global $dgwtWcasBricksStyles;

		if ( ! $render_element ) {
			return $render_element;
		}
		if ( ! isset( $element->block ) || $element->block !== 'core/search' ) {
			return $render_element;
		}

		if ( isset( $element->settings['searchType'] ) && $element->settings['searchType'] === 'overlay' ) {
			echo do_shortcode( '[fibosearch layout="icon"]' );

			if ( ! empty( $element->settings['iconTypography']['color']['hex'] ) ) {
				ob_start();
				?>
			<style>
				.dgwt-wcas-ico-magnifier, .dgwt-wcas-ico-magnifier-handler {
					fill: <?php echo esc_attr( $element->settings['iconTypography']['color']['hex'] ); ?>;
				}
			</style>
				<?php
				$dgwtWcasBricksStyles .= ob_get_clean();
			}
		} else {
			echo do_shortcode( '[fibosearch]' );
		}

		return false;
	},
	10,
	2
);

add_action(
	'wp_footer',
	function () {
		global $dgwtWcasBricksStyles;

		if ( ! empty( $dgwtWcasBricksStyles ) ) {
			echo $dgwtWcasBricksStyles;
		}
	}
);

/**
 * Support for Bricks "Products" element on the search results page.
 */
add_filter(
	'dgwt/wcas/helpers/is_search_query',
	function ( $enabled, $query ) {
		if (
		//phpcs:ignore WordPress.Security.NonceVerification.Recommended
		isset( $_GET['dgwt_wcas'] ) &&
		is_object( $query ) &&
		is_a( $query, 'WP_Query' ) &&
		! empty( $query->query_vars['s'] ) &&
		Helpers::is_running_inside_class( 'Bricks\Woocommerce', 30 )
		) {
			$enabled = true;
		}

		return $enabled;
	},
	10,
	2
);

/**
 * Set the default sorting order to relevance on search results page
 *
 * Two cases:
 * 1. "Products" element is used.
 * 2. Custom loop is used marked by "fibofilters_products_loop_item" option from FiboFilters.
 */
add_filter(
	'bricks/posts/query_vars',
	function ( $query_vars, $settings, $element_id, $element_name ) {
		if (
		(
			//phpcs:ignore WordPress.Security.NonceVerification.Recommended
			isset( $_GET['dgwt_wcas'] ) &&
			$element_name === 'woocommerce-products' &&
			! empty( $settings['is_archive_main_query'] ) &&
			! empty( $query_vars['s'] )
		) ||
		(
			// FiboFilters
			! empty( $settings['fibofilters_products_loop_item'] )
		)
		) {
			//phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( empty( $_GET['orderby'] ) || $_GET['orderby'] === 'relevance' ) {
				$query_vars['orderby'] = 'relevance';
				$query_vars['order']   = 'DESC';
			}
		}

		return $query_vars;
	},
	10,
	4
);

/**
 * Bricks disabling WooCommerce-specific query modifications on search page if FiboSearch is active.
 *
 * @see wp-content/themes/bricks/includes/woocommerce.php:1773
 *
 * In this case, we need to manually enable WooCommerce-specific sorting, e.g. by price.
 */
add_action(
	'pre_get_posts',
	function ( $query ) {
		if (
		//phpcs:ignore WordPress.Security.NonceVerification.Recommended
		isset( $_GET['dgwt_wcas'] ) &&
		is_object( $query ) &&
		is_a( $query, 'WP_Query' ) &&
		! empty( $query->query_vars['s'] ) &&
		isset( $query->query_vars['post_type'][0] ) &&
		$query->query_vars['post_type'][0] === 'product' &&
		Helpers::is_running_inside_class( 'Bricks\Query', 30 )
		) {
			$orderby = '';
			$order   = '';
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( ! isset( $_GET['orderby'] ) ) {
				$orderby_value = wc_clean( get_query_var( 'orderby' ) );
				$order_value   = wc_clean( get_query_var( 'order' ) );

				if ( $orderby_value === 'price' && $order_value === 'DESC' ) {
					$orderby = $orderby_value;
					$order   = $order_value;
				}
			}

			/**
			 * Calling this method on a WC_Query object will cause subsequent WP_Query to support
			 * WooCommerce-specific sorting, e.g. by price.
			 */
			$bricksWcQuery = new \WC_Query();
			$bricksWcQuery->get_catalog_ordering_args( $orderby, $order );
		}
	}
);

/**
 * Reinitialize Bricks AJAX "Add to Cart" handlers
 * when the FiboSearch details panel is opened.
 *
 * This ensures that "Add to Cart" buttons inside the
 * FiboSearch details panel work correctly when the
 * "Enable AJAX add to cart" option is enabled in Bricks.
 */
add_action(
	'wp_footer',
	function () {
		if (
			! class_exists( '\Bricks\Woocommerce' ) ||
			! method_exists( '\Bricks\Woocommerce', 'enabled_ajax_add_to_cart' ) ||
			! \Bricks\Woocommerce::enabled_ajax_add_to_cart() ) {
			return;
		}
		?>
	<script>
		document.addEventListener('fibosearch/show-details-panel', function () {
			if (typeof bricksWooAjaxAddToCartFn !== 'undefined') {
				bricksWooAjaxAddToCartFn.run();
			}
		});
	</script>
		<?php
	},
	100
);
