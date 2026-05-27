<?php
/**
 * The core plugin class.
 *
 * @package RefTrackr
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class RefTrackr
 *
 * Orchestrates all plugin hooks and dependencies.
 */
class RefTrackr {

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	protected $version;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->version = REFTRACKR_VERSION;
	}

	/**
	 * Boot the plugin by registering all hooks.
	 *
	 * @return void
	 */
	public function run() {
		$this->define_woocommerce_hooks();

		if ( is_admin() ) {
			$this->define_admin_hooks();
		} else {
			$this->define_public_hooks();
		}
	}

	/**
	 * Register WooCommerce integration hooks.
	 *
	 * @return void
	 */
	private function define_woocommerce_hooks() {
		$woocommerce = new RefTrackr_WooCommerce();
		// Hooks are registered in the WooCommerce class constructor.
	}

	/**
	 * Register admin-facing hooks.
	 *
	 * @return void
	 */
	private function define_admin_hooks() {
		$admin = new RefTrackr_Admin( $this->version );

		add_action( 'admin_enqueue_scripts', array( $admin, 'enqueue_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $admin, 'enqueue_scripts' ) );
		add_action( 'admin_menu', array( $admin, 'add_admin_menu' ) );
		add_filter( 'plugin_action_links_' . REFTRACKR_PLUGIN_BASENAME, array( $admin, 'add_plugin_action_links' ) );

		// AJAX handlers.
		new RefTrackr_Ajax();
	}

	/**
	 * Register public-facing hooks.
	 *
	 * @return void
	 */
	private function define_public_hooks() {
		new RefTrackr_Public();
	}
}
