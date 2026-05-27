<?php
/**
 * Coupon attribution class.
 *
 * @package RefTrackr
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class RefTrackr_Coupon
 *
 * Determines influencer attribution for orders using priority: coupon > cookie > none.
 */
class RefTrackr_Coupon {

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
	}

	/**
	 * Get the attributed influencer for an order.
	 *
	 * Attribution priority:
	 * 1. Coupon code match.
	 * 2. Referral cookie.
	 * 3. null (no attribution).
	 *
	 * @param WC_Order $order The WooCommerce order.
	 * @return array|null Array with 'influencer' object and 'source' string, or null.
	 */
	public function get_attributed_influencer( $order ) {
		$settings = get_option( 'reftrackr_settings', array() );

		// 1. Check coupon codes first (highest priority).
		$coupon_attribution = isset( $settings['coupon_attribution'] ) ? $settings['coupon_attribution'] : 'yes';
		if ( 'yes' === $coupon_attribution ) {
			$coupons = $order->get_coupon_codes();
			if ( ! empty( $coupons ) ) {
				foreach ( $coupons as $coupon_code ) {
					$influencer = $this->db->get_influencer_by_coupon( $coupon_code );
					if ( $influencer && 'active' === $influencer->status ) {
						return array(
							'influencer' => $influencer,
							'source'     => 'coupon',
							'coupon'     => $coupon_code,
						);
					}
				}
			}
		}

		// 2. Check referral cookie.
		$referral_id = RefTrackr_Tracker::get_referral_influencer_id();
		if ( $referral_id > 0 ) {
			$influencer = $this->db->get_influencer( $referral_id );
			if ( $influencer && 'active' === $influencer->status ) {
				return array(
					'influencer' => $influencer,
					'source'     => 'referral',
					'coupon'     => '',
				);
			}
		}

		// 3. No attribution.
		return null;
	}
}
