<?php
/**
 * Database operations class.
 *
 * @package RefTrackr
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

// phpcs:ignorefile WordPress.DB.PreparedSQLPlaceholders, WordPress.DB.PreparedSQL.InterpolatedNotPrepared

/**
 * Class RefTrackr_DB
 *
 * Handles all CRUD and analytics queries. Every query uses $wpdb->prepare().
 */
class RefTrackr_DB {

	/**
	 * WordPress database object.
	 *
	 * @var wpdb
	 */
	private $wpdb;

	/**
	 * Table names.
	 *
	 * @var string
	 */
	private $influencers_table;
	private $clicks_table;
	private $orders_table;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->wpdb              = $wpdb;
		$this->influencers_table = $wpdb->prefix . 'reftrackr_influencers';
		$this->clicks_table      = $wpdb->prefix . 'reftrackr_clicks';
		$this->orders_table      = $wpdb->prefix . 'reftrackr_orders';
	}

	/*--------------------------------------------------------------
	 * Influencer CRUD
	 *--------------------------------------------------------------*/

	/**
	 * Get influencers with optional filters.
	 *
	 * @param array $args Query arguments.
	 * @return array
	 */
	public function get_influencers( $args = array() ) {
		global $wpdb;
		$defaults = array(
			'per_page' => 20,
			'offset'   => 0,
			'status'   => '',
			'search'   => '',
			'orderby'  => 'created_at',
			'order'    => 'DESC',
		);
		$args     = wp_parse_args( $args, $defaults );

		$where = array( '1=1' );
		$values = array();

		if ( ! empty( $args['status'] ) ) {
			$where[]  = 'status = %s';
			$values[] = $args['status'];
		}

		if ( ! empty( $args['search'] ) ) {
			$like     = '%' . $wpdb->esc_like( $args['search'] ) . '%';
			$where[]  = '(name LIKE %s OR email LIKE %s OR referral_slug LIKE %s)';
			$values[] = $like;
			$values[] = $like;
			$values[] = $like;
		}

		$where_sql = implode( ' AND ', $where );
		$orderby   = sanitize_sql_orderby( $args['orderby'] . ' ' . $args['order'] );
		if ( ! $orderby ) {
			$orderby = 'created_at DESC';
		}

		if ( (int) $args['per_page'] === -1 ) {
			if ( ! empty( $values ) ) {
				$sql = $wpdb->prepare(
					"SELECT * FROM {$this->influencers_table} WHERE {$where_sql} ORDER BY {$orderby}", // phpcs:ignore WordPress.DB.PreparedSQL
					...$values
				);
			} else {
				$sql = "SELECT * FROM {$this->influencers_table} WHERE {$where_sql} ORDER BY {$orderby}"; // phpcs:ignore WordPress.DB.PreparedSQL
			}
		} else {
			$values[] = (int) $args['per_page'];
			$values[] = (int) $args['offset'];
			$sql = $wpdb->prepare(
				"SELECT * FROM {$this->influencers_table} WHERE {$where_sql} ORDER BY {$orderby} LIMIT %d OFFSET %d", // phpcs:ignore WordPress.DB.PreparedSQL
				...$values
			);
		}

		return $wpdb->get_results( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	/**
	 * Get a single influencer by ID.
	 *
	 * @param int $id Influencer ID.
	 * @return object|null
	 */
	public function get_influencer( $id ) {
		global $wpdb;
		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$this->influencers_table} WHERE id = %d",
				absint( $id )
			)
		);
	}

	/**
	 * Get influencer by referral slug.
	 *
	 * @param string $slug Referral slug.
	 * @return object|null
	 */
	public function get_influencer_by_slug( $slug ) {
		global $wpdb;
		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$this->influencers_table} WHERE referral_slug = %s",
				sanitize_title( $slug )
			)
		);
	}

	/**
	 * Get influencer by coupon code.
	 *
	 * @param string $coupon_code Coupon code.
	 * @return object|null
	 */
	public function get_influencer_by_coupon( $coupon_code ) {
		global $wpdb;
		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$this->influencers_table} WHERE LOWER(coupon_code) = LOWER(%s) AND coupon_code != ''",
				sanitize_text_field( $coupon_code )
			)
		);
	}

	/**
	 * Add a new influencer.
	 *
	 * @param array $data Influencer data.
	 * @return int|false Insert ID or false on failure.
	 */
	public function add_influencer( $data ) {
		global $wpdb;
		$result = $wpdb->insert(
			$this->influencers_table,
			array(
				'name'                  => sanitize_text_field( $data['name'] ),
				'email'                 => sanitize_email( $data['email'] ?? '' ),
				'instagram_handle'      => sanitize_text_field( $data['instagram_handle'] ?? '' ),
				'coupon_code'           => sanitize_text_field( $data['coupon_code'] ?? '' ),
				'referral_slug'         => sanitize_title( $data['referral_slug'] ),
				'status'                => 'active',
			),
			array( '%s', '%s', '%s', '%s', '%s', '%s' )
		);

		return false !== $result ? $wpdb->insert_id : false;
	}

	/**
	 * Update an influencer.
	 *
	 * @param int   $id   Influencer ID.
	 * @param array $data Updated data.
	 * @return bool
	 */
	public function update_influencer( $id, $data ) {
		global $wpdb;
		$update = array();
		$format = array();

		if ( isset( $data['name'] ) ) {
			$update['name'] = sanitize_text_field( $data['name'] );
			$format[]       = '%s';
		}
		if ( isset( $data['email'] ) ) {
			$update['email'] = sanitize_email( $data['email'] );
			$format[]        = '%s';
		}
		if ( isset( $data['instagram_handle'] ) ) {
			$update['instagram_handle'] = sanitize_text_field( $data['instagram_handle'] );
			$format[]                   = '%s';
		}
		if ( isset( $data['coupon_code'] ) ) {
			$update['coupon_code'] = sanitize_text_field( $data['coupon_code'] );
			$format[]              = '%s';
		}
		if ( isset( $data['referral_slug'] ) ) {
			$update['referral_slug'] = sanitize_title( $data['referral_slug'] );
			$format[]                = '%s';
		}


		if ( empty( $update ) ) {
			return false;
		}

		$result = $wpdb->update(
			$this->influencers_table,
			$update,
			array( 'id' => absint( $id ) ),
			$format,
			array( '%d' )
		);

		return false !== $result;
	}

	/**
	 * Delete an influencer.
	 *
	 * @param int $id Influencer ID.
	 * @return bool
	 */
	public function delete_influencer( $id ) {
		global $wpdb;
		$id = absint( $id );

		// Delete related clicks.
		$wpdb->delete( $this->clicks_table, array( 'influencer_id' => $id ), array( '%d' ) );

		// Delete related orders.
		$wpdb->delete( $this->orders_table, array( 'influencer_id' => $id ), array( '%d' ) );

		// Delete influencer.
		$result = $wpdb->delete( $this->influencers_table, array( 'id' => $id ), array( '%d' ) );

		return false !== $result;
	}

	/**
	 * Toggle influencer status between active and paused.
	 *
	 * @param int $id Influencer ID.
	 * @return string|false New status or false on failure.
	 */
	public function toggle_influencer_status( $id ) {
		global $wpdb;
		$influencer = $this->get_influencer( absint( $id ) );
		if ( ! $influencer ) {
			return false;
		}

		$new_status = 'active' === $influencer->status ? 'paused' : 'active';

		$result = $wpdb->update(
			$this->influencers_table,
			array( 'status' => $new_status ),
			array( 'id' => absint( $id ) ),
			array( '%s' ),
			array( '%d' )
		);

		return false !== $result ? $new_status : false;
	}

	/**
	 * Count influencers.
	 *
	 * @param string $status Optional status filter.
	 * @return int
	 */
	public function get_influencer_count( $status = '' ) {
		global $wpdb;
		if ( ! empty( $status ) ) {
			return (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$this->influencers_table} WHERE status = %s",
					$status
				)
			);
		}

		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$this->influencers_table}" );
	}

	/*--------------------------------------------------------------
	 * Click Tracking
	 *--------------------------------------------------------------*/

	/**
	 * Record a referral click.
	 *
	 * @param array $data Click data.
	 * @return int|false Insert ID or false.
	 */
	public function record_click( $data ) {
		global $wpdb;
		$result = $wpdb->insert(
			$this->clicks_table,
			array(
				'influencer_id' => absint( $data['influencer_id'] ),
				'ip_hash'       => sanitize_text_field( $data['ip_hash'] ?? '' ),
				'device_type'   => sanitize_text_field( $data['device_type'] ?? '' ),
				'referrer_url'  => esc_url_raw( $data['referrer_url'] ?? '' ),
				'clicked_at'    => current_time( 'mysql' ),
			),
			array( '%d', '%s', '%s', '%s', '%s' )
		);

		return false !== $result ? $wpdb->insert_id : false;
	}

	/**
	 * Get clicks with optional filters.
	 *
	 * @param array $args Query arguments.
	 * @return array
	 */
	public function get_clicks( $args = array() ) {
		global $wpdb;
		$defaults = array(
			'per_page'      => 20,
			'offset'        => 0,
			'influencer_id' => 0,
			'date_from'     => '',
			'date_to'       => '',
		);
		$args     = wp_parse_args( $args, $defaults );

		$where  = array( '1=1' );
		$values = array();

		if ( ! empty( $args['influencer_id'] ) ) {
			$where[]  = 'c.influencer_id = %d';
			$values[] = absint( $args['influencer_id'] );
		}
		if ( ! empty( $args['date_from'] ) ) {
			$where[]  = 'c.clicked_at >= %s';
			$values[] = sanitize_text_field( $args['date_from'] ) . ' 00:00:00';
		}
		if ( ! empty( $args['date_to'] ) ) {
			$where[]  = 'c.clicked_at <= %s';
			$values[] = sanitize_text_field( $args['date_to'] ) . ' 23:59:59';
		}

		$where_sql = implode( ' AND ', $where );
		$values[]  = (int) $args['per_page'];
		$values[]  = (int) $args['offset'];

		$sql = $wpdb->prepare(
			"SELECT c.*, i.name AS influencer_name
			 FROM {$this->clicks_table} c
			 LEFT JOIN {$this->influencers_table} i ON c.influencer_id = i.id
			 WHERE {$where_sql}
			 ORDER BY c.clicked_at DESC
			 LIMIT %d OFFSET %d", // phpcs:ignore WordPress.DB.PreparedSQL
			...$values
		);

		return $wpdb->get_results( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	/**
	 * Count clicks.
	 *
	 * @param int    $influencer_id Optional influencer ID.
	 * @param string $date_from     Optional start date.
	 * @param string $date_to       Optional end date.
	 * @return int
	 */
	public function get_click_count( $influencer_id = 0, $date_from = '', $date_to = '' ) {
		global $wpdb;
		$where  = array( '1=1' );
		$values = array();

		if ( ! empty( $influencer_id ) ) {
			$where[]  = 'influencer_id = %d';
			$values[] = absint( $influencer_id );
		}
		if ( ! empty( $date_from ) ) {
			$where[]  = 'clicked_at >= %s';
			$values[] = sanitize_text_field( $date_from ) . ' 00:00:00';
		}
		if ( ! empty( $date_to ) ) {
			$where[]  = 'clicked_at <= %s';
			$values[] = sanitize_text_field( $date_to ) . ' 23:59:59';
		}

		$where_sql = implode( ' AND ', $where );

		if ( ! empty( $values ) ) {
			$count = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$this->clicks_table} WHERE {$where_sql}", // phpcs:ignore WordPress.DB.PreparedSQL
					...$values
				)
			);
		} else {
			$count = $wpdb->get_var( "SELECT COUNT(*) FROM {$this->clicks_table}" );
		}

		return (int) $count;
	}

	/*--------------------------------------------------------------
	 * Order Tracking
	 *--------------------------------------------------------------*/

	/**
	 * Record an order.
	 *
	 * @param array $data Order data.
	 * @return int|false Insert ID or false.
	 */
	public function record_order( $data ) {
		global $wpdb;
		$result = $wpdb->insert(
			$this->orders_table,
			array(
				'influencer_id'     => absint( $data['influencer_id'] ),
				'order_id'          => absint( $data['order_id'] ),
				'product_id'        => absint( $data['product_id'] ?? 0 ),
				'product_name'      => sanitize_text_field( $data['product_name'] ?? '' ),
				'order_total'       => floatval( $data['order_total'] ?? 0 ),
				'city'              => sanitize_text_field( $data['city'] ?? '' ),
				'state'             => sanitize_text_field( $data['state'] ?? '' ),
				'coupon_used'       => sanitize_text_field( $data['coupon_used'] ?? '' ),
				'referral_source'   => sanitize_text_field( $data['referral_source'] ?? '' ),
				'order_status'      => sanitize_text_field( $data['order_status'] ?? '' ),
				'created_at'        => current_time( 'mysql' ),
			),
			array( '%d', '%d', '%d', '%s', '%f', '%s', '%s', '%s', '%s', '%s', '%s' )
		);

		return false !== $result ? $wpdb->insert_id : false;
	}

	/**
	 * Get orders with optional filters.
	 *
	 * @param array $args Query arguments.
	 * @return array
	 */
	public function get_orders( $args = array() ) {
		global $wpdb;
		$defaults = array(
			'per_page'      => 20,
			'offset'        => 0,
			'influencer_id' => 0,
			'date_from'     => '',
			'date_to'       => '',
			'coupon'        => '',
			'product'       => '',
		);
		$args     = wp_parse_args( $args, $defaults );

		$where  = array( '1=1' );
		$values = array();

		if ( ! empty( $args['influencer_id'] ) ) {
			$where[]  = 'o.influencer_id = %d';
			$values[] = absint( $args['influencer_id'] );
		}
		if ( ! empty( $args['date_from'] ) ) {
			$where[]  = 'o.created_at >= %s';
			$values[] = sanitize_text_field( $args['date_from'] ) . ' 00:00:00';
		}
		if ( ! empty( $args['date_to'] ) ) {
			$where[]  = 'o.created_at <= %s';
			$values[] = sanitize_text_field( $args['date_to'] ) . ' 23:59:59';
		}
		if ( ! empty( $args['coupon'] ) ) {
			$where[]  = 'o.coupon_used = %s';
			$values[] = sanitize_text_field( $args['coupon'] );
		}
		if ( ! empty( $args['product'] ) ) {
			$like     = '%' . $wpdb->esc_like( $args['product'] ) . '%';
			$where[]  = 'o.product_name LIKE %s';
			$values[] = $like;
		}

		$where_sql = implode( ' AND ', $where );
		$values[]  = (int) $args['per_page'];
		$values[]  = (int) $args['offset'];

		$sql = $wpdb->prepare(
			"SELECT o.*, i.name AS influencer_name
			 FROM {$this->orders_table} o
			 LEFT JOIN {$this->influencers_table} i ON o.influencer_id = i.id
			 WHERE {$where_sql}
			 ORDER BY o.created_at DESC
			 LIMIT %d OFFSET %d", // phpcs:ignore WordPress.DB.PreparedSQL
			...$values
		);

		return $wpdb->get_results( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	/**
	 * Count orders.
	 *
	 * @param int    $influencer_id Optional.
	 * @param string $date_from     Optional.
	 * @param string $date_to       Optional.
	 * @return int
	 */
	public function get_order_count( $influencer_id = 0, $date_from = '', $date_to = '' ) {
		global $wpdb;
		$where  = array( '1=1' );
		$values = array();

		if ( ! empty( $influencer_id ) ) {
			$where[]  = 'influencer_id = %d';
			$values[] = absint( $influencer_id );
		}
		if ( ! empty( $date_from ) ) {
			$where[]  = 'created_at >= %s';
			$values[] = sanitize_text_field( $date_from ) . ' 00:00:00';
		}
		if ( ! empty( $date_to ) ) {
			$where[]  = 'created_at <= %s';
			$values[] = sanitize_text_field( $date_to ) . ' 23:59:59';
		}

		$where_sql = implode( ' AND ', $where );

		if ( ! empty( $values ) ) {
			$count = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$this->orders_table} WHERE {$where_sql}", // phpcs:ignore WordPress.DB.PreparedSQL
					...$values
				)
			);
		} else {
			$count = $wpdb->get_var( "SELECT COUNT(*) FROM {$this->orders_table}" );
		}

		return (int) $count;
	}

	/**
	 * Check if an order is already tracked.
	 *
	 * @param int $order_id WooCommerce order ID.
	 * @return object|null
	 */
	public function get_order_by_order_id( $order_id ) {
		global $wpdb;
		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$this->orders_table} WHERE order_id = %d LIMIT 1",
				absint( $order_id )
			)
		);
	}

	/*--------------------------------------------------------------
	 * Dashboard & Analytics
	 *--------------------------------------------------------------*/

	/**
	 * Get dashboard stats.
	 *
	 * @param string $date_from Optional start date.
	 * @param string $date_to   Optional end date.
	 * @return array
	 */
	public function get_dashboard_stats( $date_from = '', $date_to = '' ) {
		global $wpdb;
		$where  = array( '1=1' );
		$values = array();

		if ( ! empty( $date_from ) ) {
			$where[]  = 'created_at >= %s';
			$values[] = sanitize_text_field( $date_from ) . ' 00:00:00';
		}
		if ( ! empty( $date_to ) ) {
			$where[]  = 'created_at <= %s';
			$values[] = sanitize_text_field( $date_to ) . ' 23:59:59';
		}

		$where_sql = implode( ' AND ', $where );

		if ( ! empty( $values ) ) {
			$revenue = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COALESCE(SUM(order_total), 0) FROM {$this->orders_table} WHERE {$where_sql}", // phpcs:ignore WordPress.DB.PreparedSQL
					...$values
				)
			);
			$orders_count = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(DISTINCT order_id) FROM {$this->orders_table} WHERE {$where_sql}", // phpcs:ignore WordPress.DB.PreparedSQL
					...$values
				)
			);
		} else {
			$revenue      = $wpdb->get_var( "SELECT COALESCE(SUM(order_total), 0) FROM {$this->orders_table}" );
			$orders_count = $wpdb->get_var( "SELECT COUNT(DISTINCT order_id) FROM {$this->orders_table}" );
		}

		$active_influencers = $this->get_influencer_count( 'active' );

		return array(
			'total_revenue'       => floatval( $revenue ),
			'total_orders'        => (int) $orders_count,
			'active_influencers'  => (int) $active_influencers,
		);
	}

	/**
	 * Get revenue chart data grouped by date.
	 *
	 * @param string $date_from Start date.
	 * @param string $date_to   End date.
	 * @return array
	 */
	public function get_revenue_chart_data( $date_from, $date_to ) {
		global $wpdb;
		if ( empty( $date_from ) ) {
			$date_from = gmdate( 'Y-m-d', strtotime( '-7 days' ) );
		}
		if ( empty( $date_to ) ) {
			$date_to = gmdate( 'Y-m-d' );
		}

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT DATE(created_at) AS date,
						SUM(order_total) AS revenue,
						COUNT(DISTINCT order_id) AS orders
				 FROM {$this->orders_table}
				 WHERE created_at >= %s AND created_at <= %s
				 GROUP BY DATE(created_at)
				 ORDER BY date ASC",
				sanitize_text_field( $date_from ) . ' 00:00:00',
				sanitize_text_field( $date_to ) . ' 23:59:59'
			)
		);

		// Fill in missing dates with zero values.
		$chart_data = array();
		$current    = strtotime( $date_from );
		$end        = strtotime( $date_to );
		$indexed    = array();

		foreach ( $results as $row ) {
			$indexed[ $row->date ] = $row;
		}

		while ( $current <= $end ) {
			$date_key = gmdate( 'Y-m-d', $current );
			$chart_data[] = array(
				'date'    => $date_key,
				'revenue' => isset( $indexed[ $date_key ] ) ? floatval( $indexed[ $date_key ]->revenue ) : 0,
				'orders'  => isset( $indexed[ $date_key ] ) ? (int) $indexed[ $date_key ]->orders : 0,
			);
			$current += DAY_IN_SECONDS;
		}

		return $chart_data;
	}

	/**
	 * Get the top-selling product.
	 *
	 * @param string $date_from Optional.
	 * @param string $date_to   Optional.
	 * @return object|null
	 */
	public function get_top_product( $date_from = '', $date_to = '' ) {
		global $wpdb;
		$where  = array( "product_name != ''" );
		$values = array();

		if ( ! empty( $date_from ) ) {
			$where[]  = 'created_at >= %s';
			$values[] = sanitize_text_field( $date_from ) . ' 00:00:00';
		}
		if ( ! empty( $date_to ) ) {
			$where[]  = 'created_at <= %s';
			$values[] = sanitize_text_field( $date_to ) . ' 23:59:59';
		}

		$where_sql = implode( ' AND ', $where );

		if ( ! empty( $values ) ) {
			return $wpdb->get_row(
				$wpdb->prepare(
					"SELECT product_name, COUNT(*) AS units_sold, SUM(order_total) AS revenue
					 FROM {$this->orders_table}
					 WHERE {$where_sql}
					 GROUP BY product_name
					 ORDER BY units_sold DESC
					 LIMIT 1", // phpcs:ignore WordPress.DB.PreparedSQL
					...$values
				)
			);
		}

		return $wpdb->get_row(
			"SELECT product_name, COUNT(*) AS units_sold, SUM(order_total) AS revenue
			 FROM {$this->orders_table}
			 WHERE {$where_sql}
			 GROUP BY product_name
			 ORDER BY units_sold DESC
			 LIMIT 1" // phpcs:ignore WordPress.DB.PreparedSQL
		);
	}

	/**
	 * Get influencer leaderboard.
	 *
	 * @param int $limit Number of influencers to return.
	 * @return array
	 */
	public function get_leaderboard( $limit = 10 ) {
		global $wpdb;
		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT i.*,
						COALESCE(o.total_orders, 0) AS total_orders,
						COALESCE(o.total_revenue, 0) AS total_revenue,
						COALESCE(c.total_clicks, 0) AS total_clicks
				 FROM {$this->influencers_table} i
				 LEFT JOIN (
					 SELECT influencer_id,
							COUNT(DISTINCT order_id) AS total_orders,
							SUM(order_total) AS total_revenue
					 FROM {$this->orders_table}
					 GROUP BY influencer_id
				 ) o ON i.id = o.influencer_id
				 LEFT JOIN (
					 SELECT influencer_id, COUNT(*) AS total_clicks
					 FROM {$this->clicks_table}
					 GROUP BY influencer_id
				 ) c ON i.id = c.influencer_id
				 ORDER BY total_revenue DESC
				 LIMIT %d",
				absint( $limit )
			)
		);
	}

	/**
	 * Get geographic data.
	 *
	 * @param string $date_from Optional.
	 * @param string $date_to   Optional.
	 * @return array
	 */
	public function get_geographic_data( $date_from = '', $date_to = '' ) {
		global $wpdb;
		$where  = array( "city != '' OR state != ''" );
		$values = array();

		if ( ! empty( $date_from ) ) {
			$where[]  = 'created_at >= %s';
			$values[] = sanitize_text_field( $date_from ) . ' 00:00:00';
		}
		if ( ! empty( $date_to ) ) {
			$where[]  = 'created_at <= %s';
			$values[] = sanitize_text_field( $date_to ) . ' 23:59:59';
		}

		$where_sql = implode( ' AND ', $where );

		if ( ! empty( $values ) ) {
			return $wpdb->get_results(
				$wpdb->prepare(
					"SELECT city, state, COUNT(DISTINCT order_id) AS order_count, SUM(order_total) AS total_revenue
					 FROM {$this->orders_table}
					 WHERE {$where_sql}
					 GROUP BY city, state
					 ORDER BY total_revenue DESC", // phpcs:ignore WordPress.DB.PreparedSQL
					...$values
				)
			);
		}

		return $wpdb->get_results(
			"SELECT city, state, COUNT(DISTINCT order_id) AS order_count, SUM(order_total) AS total_revenue
			 FROM {$this->orders_table}
			 WHERE {$where_sql}
			 GROUP BY city, state
			 ORDER BY total_revenue DESC" // phpcs:ignore WordPress.DB.PreparedSQL
		);
	}

	/**
	 * Get coupon analytics.
	 *
	 * @return array
	 */
	public function get_coupon_stats() {
		global $wpdb;
		return $wpdb->get_results(
			"SELECT o.coupon_used,
					i.name AS influencer_name,
					i.status AS influencer_status,
					COUNT(*) AS usage_count,
					SUM(o.order_total) AS total_revenue
			 FROM {$this->orders_table} o
			 LEFT JOIN {$this->influencers_table} i ON o.influencer_id = i.id
			 WHERE o.coupon_used != ''
			 GROUP BY o.coupon_used, i.name, i.status
			 ORDER BY total_revenue DESC"
		);
	}

	/**
	 * Get stats for a single influencer.
	 *
	 * @param int $influencer_id Influencer ID.
	 * @return array
	 */
	public function get_influencer_stats( $influencer_id ) {
		global $wpdb;
		$id = absint( $influencer_id );

		$order_stats = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT order_id) AS total_orders,
						COALESCE(SUM(order_total), 0) AS total_revenue
				 FROM {$this->orders_table}
				 WHERE influencer_id = %d",
				$id
			)
		);

		$total_clicks = $this->get_click_count( $id );

		$total_orders = $order_stats ? (int) $order_stats->total_orders : 0;
		$conv_rate    = $total_clicks > 0 ? round( ( $total_orders / $total_clicks ) * 100, 1 ) : 0;

		return array(
			'total_orders'     => $total_orders,
			'total_revenue'    => $order_stats ? floatval( $order_stats->total_revenue ) : 0,
			'total_clicks'     => $total_clicks,
			'conversion_rate'  => $conv_rate,
		);
	}

	/**
	 * Get product performance data.
	 *
	 * @param string $date_from Optional.
	 * @param string $date_to   Optional.
	 * @return array
	 */
	public function get_product_performance( $date_from = '', $date_to = '' ) {
		global $wpdb;
		$where  = array( "product_name != ''" );
		$values = array();

		if ( ! empty( $date_from ) ) {
			$where[]  = 'created_at >= %s';
			$values[] = sanitize_text_field( $date_from ) . ' 00:00:00';
		}
		if ( ! empty( $date_to ) ) {
			$where[]  = 'created_at <= %s';
			$values[] = sanitize_text_field( $date_to ) . ' 23:59:59';
		}

		$where_sql = implode( ' AND ', $where );

		if ( ! empty( $values ) ) {
			return $wpdb->get_results(
				$wpdb->prepare(
					"SELECT product_id, product_name, COUNT(*) AS units_sold, SUM(order_total) AS total_revenue
					 FROM {$this->orders_table}
					 WHERE {$where_sql}
					 GROUP BY product_id, product_name
					 ORDER BY total_revenue DESC", // phpcs:ignore WordPress.DB.PreparedSQL
					...$values
				)
			);
		}

		return $wpdb->get_results(
			"SELECT product_id, product_name, COUNT(*) AS units_sold, SUM(order_total) AS total_revenue
			 FROM {$this->orders_table}
			 WHERE {$where_sql}
			 GROUP BY product_id, product_name
			 ORDER BY total_revenue DESC" // phpcs:ignore WordPress.DB.PreparedSQL
		);
	}

	/**
	 * Get count of unique influencers who have clicks.
	 *
	 * @return int
	 */
	public function get_unique_influencers_with_clicks() {
		global $wpdb;
		return (int) $wpdb->get_var( "SELECT COUNT(DISTINCT influencer_id) FROM {$this->clicks_table}" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	}
}
