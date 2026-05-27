<?php
/**
 * Admin class.
 *
 * @package RefTrackr
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class RefTrackr_Admin
 *
 * Handles admin menu registration, asset enqueue, and page rendering.
 */
class RefTrackr_Admin {

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	private $version;

	/**
	 * Constructor.
	 *
	 * @param string $version Plugin version.
	 */
	public function __construct( $version ) {
		$this->version = $version;
	}

	/**
	 * Enqueue admin styles.
	 *
	 * @param string $hook Current admin page hook.
	 * @return void
	 */
	public function enqueue_styles( $hook ) {
		if ( false === strpos( $hook, 'reftrackr' ) ) {
			return;
		}

		wp_enqueue_style(
			'reftrackr-admin',
			REFTRACKR_PLUGIN_URL . 'assets/css/reftrackr-admin.css',
			array(),
			$this->version
		);

		// Dashicons (should already be loaded in admin).
		wp_enqueue_style( 'dashicons' );
	}

	/**
	 * Enqueue admin scripts.
	 *
	 * @param string $hook Current admin page hook.
	 * @return void
	 */
	public function enqueue_scripts( $hook ) {
		if ( false === strpos( $hook, 'reftrackr' ) ) {
			return;
		}

		wp_enqueue_script(
			'reftrackr-admin',
			REFTRACKR_PLUGIN_URL . 'assets/js/reftrackr-admin.js',
			array( 'jquery' ),
			$this->version,
			true
		);

		wp_localize_script(
			'reftrackr-admin',
			'reftrackr_ajax',
			array(
				'ajax_url'        => admin_url( 'admin-ajax.php' ),
				'nonce'           => wp_create_nonce( 'reftrackr_ajax_nonce' ),
				'currency_symbol' => function_exists( 'get_woocommerce_currency_symbol' ) ? get_woocommerce_currency_symbol() : '$',
				'site_url'        => site_url( '/' ),
				'admin_url'       => admin_url( 'admin.php' ),
				'strings'         => array(
					'confirm_delete'  => __( 'Are you sure you want to delete this influencer? This action cannot be undone.', 'reftrackr' ),
					'saving'          => __( 'Saving...', 'reftrackr' ),
					'saved'           => __( 'Changes saved successfully!', 'reftrackr' ),
					'error'           => __( 'An error occurred. Please try again.', 'reftrackr' ),
					'required_name'   => __( 'Name is required.', 'reftrackr' ),
					'required_slug'   => __( 'Referral slug is required.', 'reftrackr' ),
					'deleted'         => __( 'Influencer deleted successfully.', 'reftrackr' ),
					'status_updated'  => __( 'Status updated successfully.', 'reftrackr' ),
					'influencer_added'   => __( 'Influencer added successfully!', 'reftrackr' ),
					'influencer_updated' => __( 'Influencer updated successfully!', 'reftrackr' ),
					'no_data'         => __( 'No data available for the selected period.', 'reftrackr' ),
				),
			)
		);
	}

	/**
	 * Register admin menu and submenu pages.
	 *
	 * @return void
	 */
	public function add_admin_menu() {
		add_menu_page(
			__( 'RefTrackr', 'reftrackr' ),
			__( 'RefTrackr', 'reftrackr' ),
			'manage_woocommerce',
			'reftrackr',
			array( $this, 'render_dashboard_page' ),
			'dashicons-chart-area',
			55
		);

		add_submenu_page( 'reftrackr', __( 'Dashboard', 'reftrackr' ), __( 'Dashboard', 'reftrackr' ), 'manage_woocommerce', 'reftrackr', array( $this, 'render_dashboard_page' ) );
		add_submenu_page( 'reftrackr', __( 'Influencers', 'reftrackr' ), __( 'Influencers', 'reftrackr' ), 'manage_woocommerce', 'reftrackr-influencers', array( $this, 'render_influencers_page' ) );
		add_submenu_page( 'reftrackr', __( 'Orders', 'reftrackr' ), __( 'Orders', 'reftrackr' ), 'manage_woocommerce', 'reftrackr-orders', array( $this, 'render_orders_page' ) );
		add_submenu_page( 'reftrackr', __( 'Coupons', 'reftrackr' ), __( 'Coupons', 'reftrackr' ), 'manage_woocommerce', 'reftrackr-coupons', array( $this, 'render_coupons_page' ) );
		add_submenu_page( 'reftrackr', __( 'Referrals', 'reftrackr' ), __( 'Referrals', 'reftrackr' ), 'manage_woocommerce', 'reftrackr-referrals', array( $this, 'render_referrals_page' ) );
		add_submenu_page( 'reftrackr', __( 'Reports', 'reftrackr' ), __( 'Reports', 'reftrackr' ), 'manage_woocommerce', 'reftrackr-reports', array( $this, 'render_reports_page' ) );
		add_submenu_page( 'reftrackr', __( 'Settings', 'reftrackr' ), __( 'Settings', 'reftrackr' ), 'manage_woocommerce', 'reftrackr-settings', array( $this, 'render_settings_page' ) );
		add_submenu_page( 'reftrackr', __( 'Integrations', 'reftrackr' ), __( 'Integrations', 'reftrackr' ), 'manage_woocommerce', 'reftrackr-integrations', array( $this, 'render_integrations_page' ) );
	}

	/**
	 * Add plugin action links.
	 *
	 * @param array $links Existing links.
	 * @return array
	 */
	public function add_plugin_action_links( $links ) {
		$custom = array(
			'<a href="' . esc_url( admin_url( 'admin.php?page=reftrackr' ) ) . '">' . esc_html__( 'Dashboard', 'reftrackr' ) . '</a>',
			'<a href="' . esc_url( admin_url( 'admin.php?page=reftrackr-settings' ) ) . '">' . esc_html__( 'Settings', 'reftrackr' ) . '</a>',
		);
		return array_merge( $custom, $links );
	}

	/**
	 * Render dashboard page.
	 */
	public function render_dashboard_page() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'reftrackr' ) );
		}
		include REFTRACKR_PLUGIN_DIR . 'admin/partials/dashboard.php';
	}

	/**
	 * Render influencers page.
	 */
	public function render_influencers_page() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'reftrackr' ) );
		}

		$action = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		if ( 'add' === $action || 'edit' === $action ) {
			include REFTRACKR_PLUGIN_DIR . 'admin/partials/influencer-form.php';
		} else {
			include REFTRACKR_PLUGIN_DIR . 'admin/partials/influencers.php';
		}
	}

	/**
	 * Render orders page.
	 */
	public function render_orders_page() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'reftrackr' ) );
		}
		include REFTRACKR_PLUGIN_DIR . 'admin/partials/orders.php';
	}

	/**
	 * Render coupons page.
	 */
	public function render_coupons_page() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'reftrackr' ) );
		}
		include REFTRACKR_PLUGIN_DIR . 'admin/partials/coupons.php';
	}

	/**
	 * Render referrals page.
	 */
	public function render_referrals_page() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'reftrackr' ) );
		}
		include REFTRACKR_PLUGIN_DIR . 'admin/partials/referrals.php';
	}

	/**
	 * Render reports page.
	 */
	public function render_reports_page() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'reftrackr' ) );
		}
		include REFTRACKR_PLUGIN_DIR . 'admin/partials/reports.php';
	}

	/**
	 * Render settings page.
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'reftrackr' ) );
		}
		include REFTRACKR_PLUGIN_DIR . 'admin/partials/settings.php';
	}

	/**
	 * Render integrations page.
	 */
	public function render_integrations_page() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'reftrackr' ) );
		}
		include REFTRACKR_PLUGIN_DIR . 'admin/partials/integrations.php';
	}
}
