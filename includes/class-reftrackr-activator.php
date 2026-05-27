<?php
/**
 * Plugin activator.
 *
 * @package RefTrackr
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class RefTrackr_Activator
 *
 * Creates custom database tables on activation.
 */
class RefTrackr_Activator {

	/**
	 * Run activation routines.
	 *
	 * @return void
	 */
	public static function activate() {
		self::create_tables();
		self::set_default_options();
	}

	/**
	 * Create custom database tables.
	 *
	 * @return void
	 */
	private static function create_tables() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		$sql_influencers = "CREATE TABLE {$wpdb->prefix}reftrackr_influencers (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			name varchar(255) NOT NULL,
			email varchar(255) DEFAULT '',
			instagram_handle varchar(255) DEFAULT '',
			coupon_code varchar(100) DEFAULT '',
			referral_slug varchar(100) NOT NULL,
			status varchar(20) DEFAULT 'active',
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			UNIQUE KEY referral_slug (referral_slug)
		) $charset_collate;";

		$sql_clicks = "CREATE TABLE {$wpdb->prefix}reftrackr_clicks (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			influencer_id bigint(20) UNSIGNED NOT NULL,
			ip_hash varchar(64) DEFAULT '',
			device_type varchar(50) DEFAULT '',
			referrer_url varchar(500) DEFAULT '',
			clicked_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY influencer_id (influencer_id),
			KEY clicked_at (clicked_at)
		) $charset_collate;";

		$sql_orders = "CREATE TABLE {$wpdb->prefix}reftrackr_orders (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			influencer_id bigint(20) UNSIGNED NOT NULL,
			order_id bigint(20) UNSIGNED NOT NULL,
			product_id bigint(20) UNSIGNED DEFAULT 0,
			product_name varchar(255) DEFAULT '',
			order_total decimal(12,2) DEFAULT 0.00,
			city varchar(100) DEFAULT '',
			state varchar(100) DEFAULT '',
			coupon_used varchar(100) DEFAULT '',
			referral_source varchar(50) DEFAULT '',
			order_status varchar(50) DEFAULT '',
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY influencer_id (influencer_id),
			KEY order_id (order_id),
			KEY created_at (created_at)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql_influencers );
		dbDelta( $sql_clicks );
		dbDelta( $sql_orders );

		update_option( 'reftrackr_db_version', '1.0.0' );
	}

	/**
	 * Set default plugin options.
	 *
	 * @return void
	 */
	private static function set_default_options() {
		$defaults = array(
			'cookie_duration'    => 7,
			'tracking_enabled'   => 'yes',
			'coupon_attribution' => 'yes',
			'currency'           => '',
			'date_format'        => 'Y-m-d',
		);

		if ( false === get_option( 'reftrackr_settings' ) ) {
			add_option( 'reftrackr_settings', $defaults );
		}
	}
}
