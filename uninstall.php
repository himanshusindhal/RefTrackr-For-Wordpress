<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package RefTrackr
 * @since   1.0.0
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

// Drop custom tables.
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}reftrackr_orders" );   // phpcs:ignore WordPress.DB.DirectDatabaseQuery
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}reftrackr_clicks" );   // phpcs:ignore WordPress.DB.DirectDatabaseQuery
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}reftrackr_influencers" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

// Delete options.
delete_option( 'reftrackr_settings' );
delete_option( 'reftrackr_db_version' );

// Clear any cached data.
wp_cache_flush();
