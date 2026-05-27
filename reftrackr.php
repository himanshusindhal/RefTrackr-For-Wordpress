<?php
/**
 * Plugin Name:       RefTrackr
 * Plugin URI:        https://wordpress.org/plugins/reftrackr/
 * Description:       Track influencer-driven sales using referral links and coupon codes for WooCommerce.
 * Version:           1.0.0
 * Author:            Himanshu Sindhal
 * Author URI:        https://profiles.wordpress.org/sindhalhimanshu
 * License:           GPL-2.0+
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       reftrackr
 * Domain Path:       /languages
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * WC requires at least: 5.0
 * WC tested up to:   8.0
 *
 * @package RefTrackr
 */

defined( 'ABSPATH' ) || exit;

/**
 * Plugin constants.
 */
define( 'REFTRACKR_VERSION', '1.0.0' );
define( 'REFTRACKR_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'REFTRACKR_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'REFTRACKR_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Declare WooCommerce HPOS compatibility.
 */
add_action(
	'before_woocommerce_init',
	function () {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		}
	}
);

/**
 * Check for WooCommerce dependency and boot the plugin.
 */
function reftrackr_init() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		add_action( 'admin_notices', 'reftrackr_woocommerce_missing_notice' );
		return;
	}

	// Load dependencies.
	require_once REFTRACKR_PLUGIN_DIR . 'includes/class-reftrackr-activator.php';
	require_once REFTRACKR_PLUGIN_DIR . 'includes/class-reftrackr-deactivator.php';
	require_once REFTRACKR_PLUGIN_DIR . 'includes/class-reftrackr-db.php';
	require_once REFTRACKR_PLUGIN_DIR . 'includes/class-reftrackr-tracker.php';
	require_once REFTRACKR_PLUGIN_DIR . 'includes/class-reftrackr-coupon.php';
	require_once REFTRACKR_PLUGIN_DIR . 'includes/class-reftrackr-woocommerce.php';
	require_once REFTRACKR_PLUGIN_DIR . 'includes/class-reftrackr.php';

	if ( is_admin() ) {
		require_once REFTRACKR_PLUGIN_DIR . 'admin/class-reftrackr-admin.php';
		require_once REFTRACKR_PLUGIN_DIR . 'admin/class-reftrackr-ajax.php';
	} else {
		require_once REFTRACKR_PLUGIN_DIR . 'public/class-reftrackr-public.php';
	}

	$plugin = new RefTrackr();
	$plugin->run();
}
add_action( 'plugins_loaded', 'reftrackr_init' );

/**
 * Display WooCommerce missing notice.
 */
function reftrackr_woocommerce_missing_notice() {
	?>
	<div class="notice notice-error">
		<p>
			<?php
			printf(
				/* translators: %s: WooCommerce plugin name */
				esc_html__( '%1$s requires %2$s to be installed and active.', 'reftrackr' ),
				'<strong>RefTrackr</strong>',
				'<strong>WooCommerce</strong>'
			);
			?>
		</p>
	</div>
	<?php
}

/**
 * Activation hook.
 */
function reftrackr_activate() {
	require_once REFTRACKR_PLUGIN_DIR . 'includes/class-reftrackr-activator.php';
	RefTrackr_Activator::activate();
}
register_activation_hook( __FILE__, 'reftrackr_activate' );

/**
 * Deactivation hook.
 */
function reftrackr_deactivate() {
	require_once REFTRACKR_PLUGIN_DIR . 'includes/class-reftrackr-deactivator.php';
	RefTrackr_Deactivator::deactivate();
}
register_deactivation_hook( __FILE__, 'reftrackr_deactivate' );
