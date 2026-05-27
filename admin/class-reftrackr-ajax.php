<?php
/**
 * AJAX handlers class.
 *
 * @package RefTrackr
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

// phpcs:ignorefile WordPress.Security.NonceVerification.Missing

/**
 * Class RefTrackr_Ajax
 *
 * Handles all admin AJAX requests with nonce verification and capability checks.
 */
class RefTrackr_Ajax {

	/**
	 * DB instance.
	 *
	 * @var RefTrackr_DB
	 */
	private $db;

	/**
	 * Constructor — register AJAX actions.
	 */
	public function __construct() {
		$this->db = new RefTrackr_DB();

		$actions = array(
			'reftrackr_add_influencer',
			'reftrackr_update_influencer',
			'reftrackr_delete_influencer',
			'reftrackr_toggle_influencer',
			'reftrackr_get_dashboard_stats',
			'reftrackr_get_orders',
			'reftrackr_get_chart_data',
			'reftrackr_save_settings',
			'reftrackr_get_referrals',
			'reftrackr_get_coupon_stats',
			'reftrackr_get_reports',
		);

		foreach ( $actions as $action ) {
			$method = str_replace( 'reftrackr_', 'ajax_', $action );
			add_action( 'wp_ajax_' . $action, array( $this, $method ) );
		}
	}

	/**
	 * Verify nonce and capability. Dies on failure.
	 *
	 * @return void
	 */
	private function verify_request() {
		check_ajax_referer( 'reftrackr_ajax_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'reftrackr' ) ) );
		}
	}

	/**
	 * Add influencer.
	 */
	public function ajax_add_influencer() {
		$this->verify_request();

		$name          = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
		$email         = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
		$instagram     = isset( $_POST['instagram_handle'] ) ? sanitize_text_field( wp_unslash( $_POST['instagram_handle'] ) ) : '';
		$coupon_code   = isset( $_POST['coupon_code'] ) ? sanitize_text_field( wp_unslash( $_POST['coupon_code'] ) ) : '';
		$referral_slug = isset( $_POST['referral_slug'] ) ? sanitize_title( wp_unslash( $_POST['referral_slug'] ) ) : '';
		if ( empty( $name ) ) {
			wp_send_json_error( array( 'message' => __( 'Name is required.', 'reftrackr' ) ) );
		}
		if ( empty( $referral_slug ) ) {
			wp_send_json_error( array( 'message' => __( 'Referral slug is required.', 'reftrackr' ) ) );
		}

		// Check uniqueness.
		$existing = $this->db->get_influencer_by_slug( $referral_slug );
		if ( $existing ) {
			wp_send_json_error( array( 'message' => __( 'This referral slug is already taken.', 'reftrackr' ) ) );
		}

		$id = $this->db->add_influencer(
			array(
				'name'                  => $name,
				'email'                 => $email,
				'instagram_handle'      => $instagram,
				'coupon_code'           => $coupon_code,
				'referral_slug'         => $referral_slug,
			)
		);

		if ( $id ) {
			wp_send_json_success( array(
				'message' => __( 'Influencer added successfully!', 'reftrackr' ),
				'id'      => $id,
			) );
		}

		wp_send_json_error( array( 'message' => __( 'Failed to add influencer.', 'reftrackr' ) ) );
	}

	/**
	 * Update influencer.
	 */
	public function ajax_update_influencer() {
		$this->verify_request();

		$id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
		if ( ! $id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid influencer ID.', 'reftrackr' ) ) );
		}

		$name          = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
		$email         = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
		$instagram     = isset( $_POST['instagram_handle'] ) ? sanitize_text_field( wp_unslash( $_POST['instagram_handle'] ) ) : '';
		$coupon_code   = isset( $_POST['coupon_code'] ) ? sanitize_text_field( wp_unslash( $_POST['coupon_code'] ) ) : '';
		$referral_slug = isset( $_POST['referral_slug'] ) ? sanitize_title( wp_unslash( $_POST['referral_slug'] ) ) : '';
		if ( empty( $name ) ) {
			wp_send_json_error( array( 'message' => __( 'Name is required.', 'reftrackr' ) ) );
		}
		if ( empty( $referral_slug ) ) {
			wp_send_json_error( array( 'message' => __( 'Referral slug is required.', 'reftrackr' ) ) );
		}

		// Check slug uniqueness (exclude current).
		$existing = $this->db->get_influencer_by_slug( $referral_slug );
		if ( $existing && (int) $existing->id !== $id ) {
			wp_send_json_error( array( 'message' => __( 'This referral slug is already taken.', 'reftrackr' ) ) );
		}

		$result = $this->db->update_influencer( $id, array(
			'name'                  => $name,
			'email'                 => $email,
			'instagram_handle'      => $instagram,
			'coupon_code'           => $coupon_code,
			'referral_slug'         => $referral_slug,
		) );

		if ( $result ) {
			wp_send_json_success( array( 'message' => __( 'Influencer updated successfully!', 'reftrackr' ) ) );
		}

		wp_send_json_error( array( 'message' => __( 'Failed to update influencer.', 'reftrackr' ) ) );
	}

	/**
	 * Delete influencer.
	 */
	public function ajax_delete_influencer() {
		$this->verify_request();

		$id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
		if ( ! $id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid influencer ID.', 'reftrackr' ) ) );
		}

		$result = $this->db->delete_influencer( $id );
		if ( $result ) {
			wp_send_json_success( array( 'message' => __( 'Influencer deleted successfully.', 'reftrackr' ) ) );
		}

		wp_send_json_error( array( 'message' => __( 'Failed to delete influencer.', 'reftrackr' ) ) );
	}

	/**
	 * Toggle influencer status.
	 */
	public function ajax_toggle_influencer() {
		$this->verify_request();

		$id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
		if ( ! $id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid influencer ID.', 'reftrackr' ) ) );
		}

		$new_status = $this->db->toggle_influencer_status( $id );
		if ( $new_status ) {
			wp_send_json_success( array(
				'message' => __( 'Status updated successfully.', 'reftrackr' ),
				'status'  => $new_status,
			) );
		}

		wp_send_json_error( array( 'message' => __( 'Failed to update status.', 'reftrackr' ) ) );
	}

	/**
	 * Get dashboard stats.
	 */
	public function ajax_get_dashboard_stats() {
		$this->verify_request();

		$date_from = isset( $_POST['date_from'] ) ? sanitize_text_field( wp_unslash( $_POST['date_from'] ) ) : '';
		$date_to   = isset( $_POST['date_to'] ) ? sanitize_text_field( wp_unslash( $_POST['date_to'] ) ) : '';

		$stats       = $this->db->get_dashboard_stats( $date_from, $date_to );
		$top_product = $this->db->get_top_product( $date_from, $date_to );
		$chart_data  = $this->db->get_revenue_chart_data( $date_from, $date_to );
		$leaderboard = $this->db->get_leaderboard( 5 );

		wp_send_json_success( array(
			'stats'       => $stats,
			'top_product' => $top_product,
			'chart_data'  => $chart_data,
			'leaderboard' => $leaderboard,
		) );
	}

	/**
	 * Get orders.
	 */
	public function ajax_get_orders() {
		$this->verify_request();

		$args = array(
			'influencer_id' => isset( $_POST['influencer_id'] ) ? absint( $_POST['influencer_id'] ) : 0,
			'date_from'     => isset( $_POST['date_from'] ) ? sanitize_text_field( wp_unslash( $_POST['date_from'] ) ) : '',
			'date_to'       => isset( $_POST['date_to'] ) ? sanitize_text_field( wp_unslash( $_POST['date_to'] ) ) : '',
			'coupon'        => isset( $_POST['coupon'] ) ? sanitize_text_field( wp_unslash( $_POST['coupon'] ) ) : '',
			'per_page'      => isset( $_POST['per_page'] ) ? absint( $_POST['per_page'] ) : 20,
			'offset'        => isset( $_POST['page'] ) ? ( absint( $_POST['page'] ) - 1 ) * 20 : 0,
		);

		$orders = $this->db->get_orders( $args );
		$total  = $this->db->get_order_count( $args['influencer_id'], $args['date_from'], $args['date_to'] );

		wp_send_json_success( array(
			'orders' => $orders,
			'total'  => $total,
		) );
	}

	/**
	 * Get chart data.
	 */
	public function ajax_get_chart_data() {
		$this->verify_request();

		$date_from = isset( $_POST['date_from'] ) ? sanitize_text_field( wp_unslash( $_POST['date_from'] ) ) : '';
		$date_to   = isset( $_POST['date_to'] ) ? sanitize_text_field( wp_unslash( $_POST['date_to'] ) ) : '';

		$chart_data = $this->db->get_revenue_chart_data( $date_from, $date_to );

		wp_send_json_success( array( 'chart_data' => $chart_data ) );
	}

	/**
	 * Save settings.
	 */
	public function ajax_save_settings() {
		$this->verify_request();

		$cookie_duration = isset( $_POST['cookie_duration'] ) ? absint( $_POST['cookie_duration'] ) : 7;
		if ( $cookie_duration < 1 ) {
			$cookie_duration = 1;
		}
		if ( $cookie_duration > 365 ) {
			$cookie_duration = 365;
		}

		$settings = array(
			'cookie_duration'    => $cookie_duration,
			'tracking_enabled'   => isset( $_POST['tracking_enabled'] ) && 'yes' === $_POST['tracking_enabled'] ? 'yes' : 'no',
			'coupon_attribution' => isset( $_POST['coupon_attribution'] ) && 'yes' === $_POST['coupon_attribution'] ? 'yes' : 'no',
			'currency'           => isset( $_POST['currency'] ) ? sanitize_text_field( wp_unslash( $_POST['currency'] ) ) : '',
			'date_format'        => isset( $_POST['date_format'] ) ? sanitize_text_field( wp_unslash( $_POST['date_format'] ) ) : 'Y-m-d',
		);

		update_option( 'reftrackr_settings', $settings );

		wp_send_json_success( array( 'message' => __( 'Settings saved successfully!', 'reftrackr' ) ) );
	}

	/**
	 * Get referral clicks.
	 */
	public function ajax_get_referrals() {
		$this->verify_request();

		$args = array(
			'influencer_id' => isset( $_POST['influencer_id'] ) ? absint( $_POST['influencer_id'] ) : 0,
			'date_from'     => isset( $_POST['date_from'] ) ? sanitize_text_field( wp_unslash( $_POST['date_from'] ) ) : '',
			'date_to'       => isset( $_POST['date_to'] ) ? sanitize_text_field( wp_unslash( $_POST['date_to'] ) ) : '',
			'per_page'      => isset( $_POST['per_page'] ) ? absint( $_POST['per_page'] ) : 20,
			'offset'        => isset( $_POST['page'] ) ? ( absint( $_POST['page'] ) - 1 ) * 20 : 0,
		);

		$clicks = $this->db->get_clicks( $args );
		$total  = $this->db->get_click_count( $args['influencer_id'], $args['date_from'], $args['date_to'] );

		wp_send_json_success( array(
			'clicks' => $clicks,
			'total'  => $total,
		) );
	}

	/**
	 * Get coupon stats.
	 */
	public function ajax_get_coupon_stats() {
		$this->verify_request();

		$stats = $this->db->get_coupon_stats();
		wp_send_json_success( array( 'stats' => $stats ) );
	}

	/**
	 * Get reports data.
	 */
	public function ajax_get_reports() {
		$this->verify_request();

		$date_from = isset( $_POST['date_from'] ) ? sanitize_text_field( wp_unslash( $_POST['date_from'] ) ) : '';
		$date_to   = isset( $_POST['date_to'] ) ? sanitize_text_field( wp_unslash( $_POST['date_to'] ) ) : '';
		$type      = isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : 'all';

		$data = array();

		if ( 'all' === $type || 'revenue_by_influencer' === $type ) {
			$data['leaderboard'] = $this->db->get_leaderboard( 20 );
		}
		if ( 'all' === $type || 'geographic' === $type ) {
			$data['geographic'] = $this->db->get_geographic_data( $date_from, $date_to );
		}
		if ( 'all' === $type || 'product_performance' === $type ) {
			$data['products'] = $this->db->get_product_performance( $date_from, $date_to );
		}

		wp_send_json_success( $data );
	}
}
