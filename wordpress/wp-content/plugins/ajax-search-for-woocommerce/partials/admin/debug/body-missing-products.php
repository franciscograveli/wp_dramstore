<?php

use DgoraWcas\Engines\TNTSearchMySQL\Indexer\Builder;
use DgoraWcas\Multilingual;

// Exit if accessed directly
if ( ! defined( 'DGWT_WCAS_FILE' ) ) {
	exit;
}

global $wpdb;

$missingIds = [];

if (
	(
		Builder::getInfo( 'status' ) === 'completed' ||
		(
			\DgoraWcas\Engines\TNTSearchMySQL\Config::isParallelBuildingEnabled() &&
			Builder::getInfo( 'status', 'tmp' ) === 'completed'
		)
	) &&
	Builder::isIndexValid()
) {
	// If multilingual, check only default language.
	$lang = Multilingual::isMultilingual() ? Multilingual::getDefaultLanguage() : '';

	$readableTableName = \DgoraWcas\Engines\TNTSearchMySQL\Indexer\Utils::getTableName( 'readable', $lang );
	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$readableIds = $wpdb->get_col( "SELECT post_id FROM {$readableTableName} WHERE type = 'product'" );

	$doclistTableName = \DgoraWcas\Engines\TNTSearchMySQL\Indexer\Utils::getTableName( 'searchable_doclist', $lang );
	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$doclistIds = $wpdb->get_col( "SELECT DISTINCT doc_id FROM {$doclistTableName}" );

	$missingIds = array_diff( $readableIds, $doclistIds );
}
?>
	<h3>Missing products</h3>

<?php
if ( ! empty( $missingIds ) ) {
	?>
	<p>The following products are present in the readable index but missing in the searchable index:</p>
	<ol>
		<?php
		foreach ( $missingIds as $missingId ) {
			?>
			<li>
				<a href="<?php echo esc_attr( get_edit_post_link( $missingId ) ); ?>"
				   <?php // phpcs:ignore WordPress.WhiteSpace.PrecisionAlignment.Found ?>
				   target="_blank"><?php echo esc_html( get_the_title( $missingId ) ); ?>
					(#<?php echo esc_html( $missingId ); ?>)</a>
			</li>
			<?php
		}
		?>
	</ol>
	<?php
} else {
	?>
	<p>No missing products found. All products in the readable index are also present in the searchable index.</p>
	<?php
}
