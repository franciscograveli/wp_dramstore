<?php

// Exit if accessed directly
if ( ! defined( 'DGWT_WCAS_FILE' ) ) {
	exit;
}

echo '<div data-id="search">'; // This attribute is needed for selectors for search icon colors to work.
echo do_shortcode( '[fibosearch layout="icon"]' );
echo '</div>';
