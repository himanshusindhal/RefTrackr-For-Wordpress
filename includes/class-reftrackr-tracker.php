<?php
/**
 * Referral tracking class.
 *
 * @package RefTrackr
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class RefTrackr_Tracker
 *
 * Handles referral URL detection, cookie management, and click recording.
 */
class RefTrackr_Tracker {

	/**
	 * Cookie name.
	 *
	 * @var string
	 */
	const COOKIE_NAME = 'reftrackr_ref';

	/**
	 * DB instance.
	 *
	 * @var RefTrackr_DB
	 */
	private $db;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->db = new RefTrackr_DB();
		add_action( 'template_redirect', array( $this, 'handle_referral' ) );
	}

	/**
	 * Detect referral parameter and set cookie.
	 *
	 * @return void
	 */
	public function handle_referral() {
		$settings = get_option( 'reftrackr_settings', array() );

		// Check if tracking is enabled.
		if ( isset( $settings['tracking_enabled'] ) && 'yes' !== $settings['tracking_enabled'] ) {
			return;
		}

		// Check for ref parameter.
		if ( ! isset( $_GET['ref'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return;
		}

		$slug = sanitize_title( wp_unslash( $_GET['ref'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
		if ( empty( $slug ) ) {
			return;
		}

		// Look up the influencer.
		$influencer = $this->db->get_influencer_by_slug( $slug );
		if ( ! $influencer || 'active' !== $influencer->status ) {
			return;
		}

		// Set the referral cookie.
		$cookie_duration = isset( $settings['cookie_duration'] ) ? absint( $settings['cookie_duration'] ) : 7;
		$expiry          = time() + ( $cookie_duration * DAY_IN_SECONDS );

		setcookie( self::COOKIE_NAME, absint( $influencer->id ), $expiry, '/', COOKIE_DOMAIN, is_ssl(), true );

		// Record the click.
		$this->db->record_click(
			array(
				'influencer_id' => $influencer->id,
				'ip_hash'       => wp_hash( $this->get_client_ip() ),
				'device_type'   => $this->detect_device(),
				'referrer_url'  => isset( $_SERVER['HTTP_REFERER'] ) ? esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : '',
			)
		);
	}

	/**
	 * Get the referral influencer ID from cookie.
	 *
	 * @return int Influencer ID or 0.
	 */
	public static function get_referral_influencer_id() {
		if ( isset( $_COOKIE[ self::COOKIE_NAME ] ) ) {
			return absint( $_COOKIE[ self::COOKIE_NAME ] );
		}
		return 0;
	}

	/**
	 * Clear the referral cookie.
	 *
	 * @return void
	 */
	public static function clear_cookie() {
		if ( isset( $_COOKIE[ self::COOKIE_NAME ] ) ) {
			setcookie( self::COOKIE_NAME, '', time() - 3600, '/', COOKIE_DOMAIN, is_ssl(), true );
			unset( $_COOKIE[ self::COOKIE_NAME ] );
		}
	}

	/**
	 * Detect device type from User-Agent.
	 *
	 * @return string mobile|tablet|desktop
	 */
	private function detect_device() {
		if ( ! isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
			return 'desktop';
		}

		$ua = sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) );
		$ua = strtolower( $ua );

		// Tablet patterns (check before mobile since tablets also match some mobile patterns).
		if ( preg_match( '/tablet|ipad|playbook|silk/i', $ua ) ) {
			return 'tablet';
		}

		// Mobile patterns.
		if ( preg_match( '/mobile|android|iphone|ipod|opera mini|iemobile|wpdesktop/i', $ua ) ) {
			return 'mobile';
		}

		return 'desktop';
	}

	/**
	 * Get client IP address (for hashing only).
	 *
	 * @return string
	 */
	private function get_client_ip() {
		$ip = '';

		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_CLIENT_IP'] ) );
		} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
			// Take the first IP if multiple.
			if ( strpos( $ip, ',' ) !== false ) {
				$ip = trim( explode( ',', $ip )[0] );
			}
		} elseif ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
		}

		return $ip;
	}
}
