<?php
/**
 * WooCommerce integration class.
 *
 * @package RefTrackr
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class RefTrackr_WooCommerce
 *
 * Hooks into WooCommerce to track orders, update statuses, and show coupon info.
 */
class RefTrackr_WooCommerce {

	/**
	 * DB instance.
	 *
	 * @var RefTrackr_DB
	 */
	private $db;

	/**
	 * Constructor — register WooCommerce hooks.
	 */
	public function __construct() {
		$this->db = new RefTrackr_DB();

		// Track orders during classic and blocks checkouts.
		add_action( 'woocommerce_checkout_order_processed', array( $this, 'handle_classic_checkout' ), 10, 3 );
		add_action( 'woocommerce_store_api_checkout_order_processed', array( $this, 'handle_blocks_checkout' ), 10, 2 );

		// Sync order status and act as a fallback for manual/admin or other orders.
		add_action( 'woocommerce_order_status_changed', array( $this, 'update_order_status' ), 10, 4 );
		add_action( 'woocommerce_coupon_options', array( $this, 'show_coupon_influencer_info' ), 10, 2 );
	}

	/**
	 * Handle classic checkout order tracking.
	 *
	 * @param int      $order_id    Order ID.
	 * @param array    $posted_data Posted data from checkout form.
	 * @param WC_Order $order       Order object.
	 * @return void
	 */
	public function handle_classic_checkout( $order_id, $posted_data, $order ) {
		$this->track_order( $order_id, $order );
	}

	/**
	 * Handle blocks checkout order tracking.
	 *
	 * @param WC_Order $order   Order object.
	 * @param object   $request Request object.
	 * @return void
	 */
	public function handle_blocks_checkout( $order, $request = null ) {
		if ( $order && is_a( $order, 'WC_Order' ) ) {
			$this->track_order( $order->get_id(), $order );
		}
	}

	/**
	 * Track an order when created.
	 *
	 * @param int      $order_id Order ID.
	 * @param WC_Order $order    Order object.
	 * @return void
	 */
	public function track_order( $order_id, $order = null ) {
		$settings = get_option( 'reftrackr_settings', array() );

		// Check if tracking is enabled.
		if ( isset( $settings['tracking_enabled'] ) && 'yes' !== $settings['tracking_enabled'] ) {
			return;
		}

		if ( ! $order || ! is_a( $order, 'WC_Order' ) ) {
			$order = wc_get_order( $order_id );
		}

		if ( ! $order ) {
			return;
		}

		// Check if already tracked.
		$existing = $this->db->get_order_by_order_id( $order_id );
		if ( $existing ) {
			return;
		}

		// Get attribution.
		$coupon_handler = new RefTrackr_Coupon();
		$attribution    = $coupon_handler->get_attributed_influencer( $order );

		if ( ! $attribution ) {
			return;
		}

		$influencer = $attribution['influencer'];
		$source     = $attribution['source'];
		$coupon     = $attribution['coupon'];

		// Get order data.
		$billing_city  = $order->get_billing_city();
		$billing_state = $order->get_billing_state();
		$order_status  = $order->get_status();

		// Get used coupons as string.
		$coupons_used = implode( ', ', $order->get_coupon_codes() );
		if ( ! empty( $coupon ) ) {
			$coupons_used = $coupon;
		}

		// Track each product in the order.
		$items = $order->get_items();
		if ( ! empty( $items ) ) {
			foreach ( $items as $item ) {
				$product_id   = $item->get_product_id();
				$product_name = $item->get_name();
				$line_total   = floatval( $item->get_total() );

				$this->db->record_order(
					array(
						'influencer_id'   => $influencer->id,
						'order_id'        => $order_id,
						'product_id'      => $product_id,
						'product_name'    => $product_name,
						'order_total'     => $line_total,
						'city'            => $billing_city,
						'state'           => $billing_state,
						'coupon_used'     => $coupons_used,
						'referral_source' => $source,
						'order_status'    => $order_status,
					)
				);
			}
		} else {
			// Fallback: record the order without product details.
			$order_total = floatval( $order->get_total() );

			$this->db->record_order(
				array(
					'influencer_id'   => $influencer->id,
					'order_id'        => $order_id,
					'product_id'      => 0,
					'product_name'    => '',
					'order_total'     => $order_total,
					'city'            => $billing_city,
					'state'             => $billing_state,
					'coupon_used'     => $coupons_used,
					'referral_source'   => $source,
					'order_status'      => $order_status,
				)
			);
		}

		// Clear the referral cookie after tracking.
		RefTrackr_Tracker::clear_cookie();
	}

	/**
	 * Update order status when WooCommerce order status changes.
	 *
	 * @param int    $order_id   Order ID.
	 * @param string $old_status Old status.
	 * @param string $new_status New status.
	 * @param object $order      Order object.
	 * @return void
	 */
	public function update_order_status( $order_id, $old_status, $new_status, $order = null ) {
		global $wpdb;
		$table = $wpdb->prefix . 'reftrackr_orders';

		// Check if the order is already tracked.
		$existing = $this->db->get_order_by_order_id( $order_id );

		if ( $existing ) {
			// If already tracked, update the status for all items in the order.
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->update(
				$table,
				array( 'order_status' => sanitize_text_field( $new_status ) ),
				array( 'order_id' => absint( $order_id ) ),
				array( '%s' ),
				array( '%d' )
			);
		} else {
			// Fallback: If not tracked yet (e.g. manual admin order, subscription renewal, etc.),
			// try to track it now. It will only be tracked if it's attributed to an active influencer.
			if ( ! $order || ! is_a( $order, 'WC_Order' ) ) {
				$order = wc_get_order( $order_id );
			}
			if ( $order ) {
				$this->track_order( $order_id, $order );
			}
		}
	}

	/**
	 * Show influencer info on WooCommerce coupon edit page.
	 *
	 * @param int       $coupon_id Coupon post ID.
	 * @param WC_Coupon $coupon    Coupon object.
	 * @return void
	 */
	public function show_coupon_influencer_info( $coupon_id, $coupon ) {
		$coupon_code = $coupon->get_code();
		$influencer  = $this->db->get_influencer_by_coupon( $coupon_code );

		if ( $influencer ) {
			woocommerce_wp_text_input(
				array(
					'id'                => 'reftrackr_linked_influencer',
					'label'             => __( 'RefTrackr Influencer', 'reftrackr' ),
					'description'       => sprintf(
						/* translators: %s: influencer name */
						__( 'This coupon is linked to influencer: %s', 'reftrackr' ),
						esc_html( $influencer->name )
					),
					'desc_tip'          => true,
					'value'             => esc_html( $influencer->name ),
					'custom_attributes' => array( 'readonly' => 'readonly' ),
				)
			);
		}
	}
}
