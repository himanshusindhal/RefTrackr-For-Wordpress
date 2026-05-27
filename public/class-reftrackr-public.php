<?php
/**
 * Public-facing class.
 *
 * @package RefTrackr
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class RefTrackr_Public
 *
 * Handles frontend referral tracking. Minimal footprint.
 */
class RefTrackr_Public {

	/**
	 * Constructor — initialize the tracker.
	 */
	public function __construct() {
		new RefTrackr_Tracker();
	}
}
