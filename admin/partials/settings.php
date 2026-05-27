<?php
/**
 * Settings page template.
 *
 * @package RefTrackr
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

// phpcs:ignorefile WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

$settings          = get_option( 'reftrackr_settings', array() );
$cookie_duration   = isset( $settings['cookie_duration'] ) ? absint( $settings['cookie_duration'] ) : 7;
$tracking_enabled  = isset( $settings['tracking_enabled'] ) ? $settings['tracking_enabled'] : 'yes';
$coupon_attr       = isset( $settings['coupon_attribution'] ) ? $settings['coupon_attribution'] : 'yes';
$currency          = isset( $settings['currency'] ) ? $settings['currency'] : '';
$date_format       = isset( $settings['date_format'] ) ? $settings['date_format'] : 'Y-m-d';

// Determine if cookie duration is a preset or custom.
$preset_durations = array( 1, 7, 14, 30, 60, 90 );
$is_custom        = ! in_array( $cookie_duration, $preset_durations, true );
?>

<div class="reftrackr-wrap">

    <div class="reftrackr-header">
        <h1 class="reftrackr-page-title"><?php esc_html_e( 'Settings', 'reftrackr' ); ?></h1>
    </div>

    <div id="reftrackr-settings-notice"></div>

    <div class="reftrackr-section">
        <form id="reftrackr-settings-form" class="reftrackr-form">
            <?php wp_nonce_field( 'reftrackr_settings_nonce', 'reftrackr_nonce' ); ?>

            <!-- Tracking Settings -->
            <div class="reftrackr-settings-section">
                <h3 class="reftrackr-settings-title"><?php esc_html_e( 'Tracking Settings', 'reftrackr' ); ?></h3>
                <p class="reftrackr-settings-description"><?php esc_html_e( 'Configure how RefTrackr tracks referrals and attributes sales.', 'reftrackr' ); ?></p>

                <div class="reftrackr-form-group">
                    <label class="reftrackr-form-label" for="reftrackr-cookie-duration">
                        <?php esc_html_e( 'Cookie Duration', 'reftrackr' ); ?>
                    </label>
                    <select id="reftrackr-cookie-duration" name="cookie_duration_preset" class="reftrackr-form-select">
                        <option value="1" <?php selected( $cookie_duration, 1 ); ?>><?php esc_html_e( '1 Day', 'reftrackr' ); ?></option>
                        <option value="7" <?php selected( $cookie_duration, 7 ); ?>><?php esc_html_e( '7 Days (Default)', 'reftrackr' ); ?></option>
                        <option value="14" <?php selected( $cookie_duration, 14 ); ?>><?php esc_html_e( '14 Days', 'reftrackr' ); ?></option>
                        <option value="30" <?php selected( $cookie_duration, 30 ); ?>><?php esc_html_e( '30 Days', 'reftrackr' ); ?></option>
                        <option value="60" <?php selected( $cookie_duration, 60 ); ?>><?php esc_html_e( '60 Days', 'reftrackr' ); ?></option>
                        <option value="90" <?php selected( $cookie_duration, 90 ); ?>><?php esc_html_e( '90 Days', 'reftrackr' ); ?></option>
                        <option value="custom" <?php echo $is_custom ? 'selected' : ''; ?>><?php esc_html_e( 'Custom', 'reftrackr' ); ?></option>
                    </select>
                    <div id="reftrackr-custom-duration-wrap" style="<?php echo $is_custom ? '' : 'display:none;'; ?> margin-top: 8px;">
                        <input type="number" id="reftrackr-custom-duration" name="cookie_duration_custom" class="reftrackr-form-input" style="max-width: 120px;"
                               min="1" max="365" value="<?php echo esc_attr( $cookie_duration ); ?>" />
                        <span class="reftrackr-form-help" style="display:inline; margin-left: 8px;"><?php esc_html_e( 'days', 'reftrackr' ); ?></span>
                    </div>
                    <p class="reftrackr-form-help"><?php esc_html_e( 'How long the referral cookie stays in the visitor\'s browser.', 'reftrackr' ); ?></p>
                </div>

                <div class="reftrackr-form-group">
                    <label class="reftrackr-form-label">
                        <?php esc_html_e( 'Referral Tracking', 'reftrackr' ); ?>
                    </label>
                    <label class="reftrackr-toggle">
                        <input type="checkbox" name="tracking_enabled" value="yes" <?php checked( $tracking_enabled, 'yes' ); ?> />
                        <span class="reftrackr-toggle-slider"></span>
                        <span class="reftrackr-toggle-label"><?php esc_html_e( 'Enable referral link tracking', 'reftrackr' ); ?></span>
                    </label>
                </div>

                <div class="reftrackr-form-group">
                    <label class="reftrackr-form-label">
                        <?php esc_html_e( 'Coupon Attribution', 'reftrackr' ); ?>
                    </label>
                    <label class="reftrackr-toggle">
                        <input type="checkbox" name="coupon_attribution" value="yes" <?php checked( $coupon_attr, 'yes' ); ?> />
                        <span class="reftrackr-toggle-slider"></span>
                        <span class="reftrackr-toggle-label"><?php esc_html_e( 'Enable coupon code attribution', 'reftrackr' ); ?></span>
                    </label>
                </div>
            </div>

            <!-- Display Settings -->
            <div class="reftrackr-settings-section">
                <h3 class="reftrackr-settings-title"><?php esc_html_e( 'Display Settings', 'reftrackr' ); ?></h3>
                <p class="reftrackr-settings-description"><?php esc_html_e( 'Customize how data is displayed in the dashboard.', 'reftrackr' ); ?></p>

                <div class="reftrackr-form-row">
                    <div class="reftrackr-form-group">
                        <label class="reftrackr-form-label" for="reftrackr-currency">
                            <?php esc_html_e( 'Currency Symbol Override', 'reftrackr' ); ?>
                        </label>
                        <input type="text" id="reftrackr-currency" name="currency" class="reftrackr-form-input" style="max-width: 80px;"
                               value="<?php echo esc_attr( $currency ); ?>" placeholder="$" />
                        <p class="reftrackr-form-help"><?php esc_html_e( 'Leave empty to use WooCommerce default.', 'reftrackr' ); ?></p>
                    </div>
                    <div class="reftrackr-form-group">
                        <label class="reftrackr-form-label" for="reftrackr-date-format">
                            <?php esc_html_e( 'Date Format', 'reftrackr' ); ?>
                        </label>
                        <select id="reftrackr-date-format" name="date_format" class="reftrackr-form-select">
                            <option value="Y-m-d" <?php selected( $date_format, 'Y-m-d' ); ?>>2026-05-27</option>
                            <option value="d/m/Y" <?php selected( $date_format, 'd/m/Y' ); ?>>27/05/2026</option>
                            <option value="m/d/Y" <?php selected( $date_format, 'm/d/Y' ); ?>>05/27/2026</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="reftrackr-form-actions">
                <button type="submit" class="reftrackr-btn reftrackr-btn--primary" id="reftrackr-save-settings">
                    <?php esc_html_e( 'Save Settings', 'reftrackr' ); ?>
                </button>
            </div>
        </form>
    </div>

</div>
