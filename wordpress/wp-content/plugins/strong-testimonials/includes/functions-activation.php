<?php

/**
 * Add tables for Views.
 *
 * @since 1.21.0
 */
function wpmtst_update_tables() {
	global $wpdb;
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';

	$charset_collate = $wpdb->get_charset_collate();

	$table_name = $wpdb->prefix . 'strong_views';

	$sql = "CREATE TABLE $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			name varchar(100) NOT NULL,
			value text NOT NULL,
			PRIMARY KEY  (id)
		) $charset_collate;";

	$wpdb->show_errors();
	$result = dbDelta( $sql );
	$wpdb->hide_errors();

	if ( $wpdb->last_error ) {
		deactivate_plugins( 'strong-testimonials/strong-testimonials.php' );
		$message  = '<p><span style="color: #CD0000;">';
		$message .= esc_html__( 'An error occurred:', 'strong-testimonials' ) . '</span>&nbsp;';
		$message .= esc_html__( 'The plugin has been deactivated.', 'strong-testimonials' );
		$message .= '</p>';
		$message .= '<p><code>' . $wpdb->last_error . '</code></p>';
		// translators: %s is the URL to the WordPress dashboard.
		$message .= '<p>' . sprintf( __( '<a href="%s">Go back to Dashboard</a>', 'strong-testimonials' ), esc_url( admin_url() ) ) . '</p>';

		wp_die( sprintf( '<div class="error strong-view-error">%s</div>', wp_kses_post( $message ) ) );
	}

	update_option( 'wpmtst_db_version', WPMST()->get_db_version(), 'no' );
}

/**
 * Returns true if any view in the table has the given mode.
 *
 * @since 3.2.23
 */
function wpmtst_mode_view_exists( $table, $mode ) {
	global $wpdb;
	$rows = $wpdb->get_col( "SELECT value FROM {$table}" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	foreach ( $rows as $row ) {
		$data = maybe_unserialize( $row );
		if ( isset( $data['mode'] ) && $data['mode'] === $mode ) {
			return true;
		}
	}
	return false;
}

/**
 * Create default views on plugin activation (and on admin_init as a safety net).
 *
 * - Single Template: protected, re-created if its stored ID no longer exists.
 * - Display / Form: created once; skipped if any view with that mode already exists.
 *
 * @since 3.2.23
 */
function wpmtst_create_default_views() {
	global $wpdb;

	$table      = $wpdb->prefix . 'strong_views';
	$stored_ids = get_option( 'wpmtst_default_views', array() );
	$updated    = false;

	// Single Template — always ensure one view is protected.
	// If the stored protected ID is still in the DB, nothing to do.
	// Otherwise find the first existing single_template view and protect it,
	// or create a new one if none exist.
	$protected_id = absint( $stored_ids['single_template'] ?? 0 );
	$still_exists = $protected_id > 0 && $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$table} WHERE id = %d", $protected_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

	if ( ! $still_exists ) {
		// Find the first existing single_template view in the DB.
		$rows       = $wpdb->get_results( "SELECT id, value FROM {$table}", ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$first_id   = 0;
		foreach ( $rows as $row ) {
			$data = maybe_unserialize( $row['value'] );
			if ( isset( $data['mode'] ) && 'single_template' === $data['mode'] ) {
				$first_id = absint( $row['id'] );
				break;
			}
		}

		if ( $first_id > 0 ) {
			// Promote the first found view to protected.
			$stored_ids['single_template'] = $first_id;
			$updated                       = true;
		} else {
			// No single_template view exists — create one.
			$view                   = Strong_Testimonials_Defaults::get_default_view();
			$view['mode']           = 'single_template';
			$view['client_section'] = array(
				array(
					'field'  => 'client_name',
					'type'   => 'text',
					'before' => '',
					'class'  => 'testimonial-name',
				),
				array(
					'field'   => 'company_name',
					'type'    => 'link',
					'before'  => '',
					'url'     => 'company_website',
					'class'   => 'testimonial-company',
					'new_tab' => true,
				),
				array(
					'field'  => 'star_rating',
					'type'   => 'rating',
					'before' => '',
					'class'  => 'testimonial-rating',
				),
			);
			$wpdb->query( // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$wpdb->prepare(
					"INSERT INTO {$table} (name, value) VALUES (%s, %s)",
					__( 'Single Template', 'strong-testimonials' ),
					serialize( $view ) // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize
				)
			);
			$new_id = absint( $wpdb->insert_id );
			if ( $new_id > 0 ) {
				$stored_ids['single_template'] = $new_id;
				$updated                       = true;
			}
		}
	}

	// Testimonials Display — create once; if stored ID exists the user has already seen/deleted it.
	if ( ! isset( $stored_ids['display'] ) && ! wpmtst_mode_view_exists( $table, 'display' ) ) {
		$view         = Strong_Testimonials_Defaults::get_default_view();
		$view['mode'] = 'display';
		$wpdb->query( // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->prepare(
				"INSERT INTO {$table} (name, value) VALUES (%s, %s)",
				__( 'Testimonials Display', 'strong-testimonials' ),
				serialize( $view ) // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize
			)
		);
		$new_id = absint( $wpdb->insert_id );
		if ( $new_id > 0 ) {
			$stored_ids['display'] = $new_id;
			$updated               = true;
		}
	} elseif ( ! isset( $stored_ids['display'] ) && wpmtst_mode_view_exists( $table, 'display' ) ) {
		// Existing install already has a display view — mark as seen so we never create one.
		$stored_ids['display'] = 0;
		$updated               = true;
	}

	// Testimonials Collection Form — create once; if stored ID exists the user has already seen/deleted it.
	if ( ! isset( $stored_ids['form'] ) && ! wpmtst_mode_view_exists( $table, 'form' ) ) {
		$view               = Strong_Testimonials_Defaults::get_default_view();
		$view['mode']     = 'form';
		$view['template'] = 'default-form';
		$wpdb->query( // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->prepare(
				"INSERT INTO {$table} (name, value) VALUES (%s, %s)",
				__( 'Testimonials Collection Form', 'strong-testimonials' ),
				serialize( $view ) // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize
			)
		);
		$new_id = absint( $wpdb->insert_id );
		if ( $new_id > 0 ) {
			$stored_ids['form'] = $new_id;
			$updated            = true;
		}
	} elseif ( ! isset( $stored_ids['form'] ) && wpmtst_mode_view_exists( $table, 'form' ) ) {
		// Existing install already has a form view — mark as seen so we never create one.
		$stored_ids['form'] = 0;
		$updated            = true;
	}

	if ( $updated ) {
		update_option( 'wpmtst_default_views', $stored_ids, 'no' );
	}
}
