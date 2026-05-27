<?php
/**
 * Plugin deactivator.
 *
 * @package RefTrackr
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class RefTrackr_Deactivator
 *
 * Minimal cleanup on deactivation. Data is preserved until uninstall.
 */
class RefTrackr_Deactivator {

	/**
	 * Run deactivation routines.
	 *
	 * @return void
	 */
	public static function deactivate() {
		// Clear transients.
		delete_transient( 'reftrackr_dashboard_stats' );
		delete_transient( 'reftrackr_leaderboard' );
	}
}
