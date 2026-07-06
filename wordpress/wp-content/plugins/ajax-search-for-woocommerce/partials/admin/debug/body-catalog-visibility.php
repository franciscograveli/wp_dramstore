<?php

use DgoraWcas\Helpers;
// Exit if accessed directly
if ( !defined( 'DGWT_WCAS_FILE' ) ) {
    exit;
}
$visibility_terms = wc_get_product_visibility_term_ids();
?>
	<h3>Products visibility</h3>

	<p>Show terms IDs that are in charge of catalog visibility. It's obtained from <code>wc_get_product_visibility_term_ids()</code></p>
	<table class="wc_status_table widefat dgwt-wcas-table-visibility" style="max-width: 300px;">
		<tr>
			<td></td>
			<td><b>Term ID</b></td>
		</tr>
		<tr>
			<td>Exclude from catalog</td>
			<td><?php 
echo esc_html( $visibility_terms['exclude-from-catalog'] );
?></td>
		</tr>
		<tr>
			<td>Exclude from search</td>
			<td><?php 
echo esc_html( $visibility_terms['exclude-from-search'] );
?></td>
		</tr>
		<tr>
			<td>Out of stock</td>
			<td><?php 
echo esc_html( $visibility_terms['outofstock'] );
?></td>
		</tr>
	</table>


	<h3>Show visibility data</h3>
	<p>Visibility checker bases on visibility options stored in a <code>term_relationships</code> table.
		All products below <b>are published</b>. There are no draft, private or pending products.</p>
	<form action="<?php 
echo admin_url( 'admin.php' );
?>" method="get">
		<input type="hidden" name="page" value="dgwt_wcas_debug">
		<input type="hidden" name="catalog_visibility" value="1">
		<?php 
wp_nonce_field( 'dgwt_wcas_debug_visibility', '_wpnonce', false );
?>
		<button class="button" type="submit">Show results</button>
	</form>

	<?php 
?>

	<br/>
<?php 
if ( !empty( $_GET['catalog_visibility'] ) && !empty( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'dgwt_wcas_debug_visibility' ) ) {
    $shopOnly = Helpers::getProductsByVisibility( $visibility_terms['exclude-from-search'] );
    $searchOnly = Helpers::getProductsByVisibility( $visibility_terms['exclude-from-catalog'] );
    $outOfStock = Helpers::getProductsByVisibility( $visibility_terms['outofstock'] );
    $hidden = array_intersect( $shopOnly, $searchOnly );
    ?>

	<table class="wc_status_table widefat" cellspacing="0">
		<thead>
		<tr>
			<th colspan="2" data-export-label="Searchable Index"><h3>Counters</h3></th>
		</tr>
		</thead>
		<tbody>
		<tr>
			<td><b>Shop only</b></td>
			<td><?php 
    echo '<b>' . count( $shopOnly );
    ?></b> products</td>
		</tr>
		<tr>
			<td><b>Search results only</b></td>
			<td><?php 
    echo '<b>' . count( $searchOnly );
    ?></b> products</td>
		</tr>
		<tr>
			<td>
				<b>Hidden</b>
				<span class="dgwt-wcas-tooltip-basic dashicons dashicons-editor-help" title='Hidden products are actually those marked in the database as "Shop only" and "Search results only"'></span>
			</td>
			<td><?php 
    echo '<b>' . count( $hidden );
    ?></b> products</td>
		</tr>
		<tr>
			<td><b>Out of stock</b></td>
			<td><?php 
    echo '<b>' . count( $outOfStock );
    ?></b> products</td>
		</tr>
		</tbody>
	</table>

	<table class="wc_status_table widefat" cellspacing="0">
		<thead>
		<tr>
			<th colspan="2" data-export-label="Searchable Index"><h3>Product IDs</h3></th>
		</tr>
		</thead>
		<tbody>
		<tr>
			<td><b>Shop only</b></td>
			<td><?php 
    Helpers::printExplodedProductsIdsAsLinks( $shopOnly );
    ?></td>
		</tr>
		<tr>
			<td><b>Search results only</b></td>
			<td><?php 
    Helpers::printExplodedProductsIdsAsLinks( $searchOnly );
    ?></td>
		</tr>
		<tr>
			<td>
				<b>Hidden</b>
				<span class="dgwt-wcas-tooltip-basic dashicons dashicons-editor-help" title='Hidden products are actually those marked in the database as "Shop only" and "Search results only"'></span>
			</td>
			<td><?php 
    Helpers::printExplodedProductsIdsAsLinks( $hidden );
    ?></td>
		</tr>
		<tr>
			<td><b>Out of stock</b></td>
			<td><?php 
    Helpers::printExplodedProductsIdsAsLinks( $outOfStock );
    ?></td>
		</tr>
		</tbody>
	</table>

	<?php 
}
if ( !empty( $_GET['out_of_stock_visibility_fix_tool'] ) && !empty( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'dgwt_wcas_debug_visibility_fix_outofstock' ) ) {
    Helpers::fixOutOfStock__premium_only();
    wp_safe_redirect( admin_url( 'admin.php?page=dgwt_wcas_debug&outofstock_fixed=1' ) );
}
if ( isset( $_GET['outofstock_fixed'] ) && $_GET['outofstock_fixed'] === '1' ) {
    ?>
	<div class="dgwt-wcas-notice notice notice-success">
		<p>Success!</p>
	</div>
	<?php 
}