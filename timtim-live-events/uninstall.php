<?php
/**
 * Deleting the plugin removes everything it stored: the settings (including
 * the key) and every cached answer. Nothing is left in the database.
 *
 * @package TimTimLiveEvents
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'ttle_settings' );
delete_site_transient( 'ttle_update_info' );

global $wpdb;
$wpdb->query(
	$wpdb->prepare(
		"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
		$wpdb->esc_like( '_transient_ttle_' ) . '%',
		$wpdb->esc_like( '_transient_timeout_ttle_' ) . '%'
	)
);
