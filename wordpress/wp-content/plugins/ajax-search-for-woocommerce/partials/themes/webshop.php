<?php
// Exit if accessed directly
if ( ! defined( 'DGWT_WCAS_FILE' ) ) {
	exit;
}

if ( ! function_exists( 'webshop_header_search_markup' ) ) {
	function webshop_header_search_markup() {
		?>
		<div class="ws-header-search">
			<?php echo do_shortcode( '[fibosearch]' ); ?>
		</div>
		<?php
	}

	add_action( 'webshop_header_search', 'webshop_header_search_markup' );
}

add_action(
	'wp_head',
	function () {
		?>
	<style>
		.ws-header-search .dgwt-wcas-search-wrapp {
			max-width: 100%;
		}

		.ws-header-search .dgwt-wcas-sf-wrapp input[type=search].dgwt-wcas-search-input {
			font-size: 16px;
			height: 50px;
		}
	</style>
		<?php
	}
);
