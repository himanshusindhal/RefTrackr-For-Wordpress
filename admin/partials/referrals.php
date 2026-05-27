<?php
/**
 * Referrals/clicks tracking page template.
 *
 * @package RefTrackr
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

// phpcs:ignorefile WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

$db           = new RefTrackr_DB();
$clicks       = $db->get_clicks( array( 'per_page' => 20 ) );
$total_clicks = $db->get_click_count();
$influencers  = $db->get_influencers( array( 'per_page' => -1 ) );

// Count unique influencers with clicks.
$unique_influencers = 0;
if ( $total_clicks > 0 ) {
	$unique_influencers = $db->get_unique_influencers_with_clicks();
}
?>

<div class="reftrackr-wrap">

    <div class="reftrackr-header">
        <h1 class="reftrackr-page-title"><?php esc_html_e( 'Referrals', 'reftrackr' ); ?></h1>
    </div>

    <!-- Summary Cards -->
    <div class="reftrackr-cards reftrackr-cards--half">
        <div class="reftrackr-card">
            <div class="reftrackr-card-icon reftrackr-card-icon--sales">
                <span class="dashicons dashicons-admin-links"></span>
            </div>
            <div class="reftrackr-card-content">
                <div class="reftrackr-card-value"><?php echo esc_html( number_format( $total_clicks ) ); ?></div>
                <div class="reftrackr-card-label"><?php esc_html_e( 'Total Clicks', 'reftrackr' ); ?></div>
            </div>
        </div>
        <div class="reftrackr-card">
            <div class="reftrackr-card-icon reftrackr-card-icon--influencers">
                <span class="dashicons dashicons-groups"></span>
            </div>
            <div class="reftrackr-card-content">
                <div class="reftrackr-card-value"><?php echo esc_html( $unique_influencers ); ?></div>
                <div class="reftrackr-card-label"><?php esc_html_e( 'Influencers with Clicks', 'reftrackr' ); ?></div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="reftrackr-filters" id="reftrackr-referral-filters">
        <div class="reftrackr-filter-group">
            <label class="reftrackr-filter-label" for="reftrackr-ref-influencer"><?php esc_html_e( 'Influencer', 'reftrackr' ); ?></label>
            <select id="reftrackr-ref-influencer" class="reftrackr-filter-select">
                <option value=""><?php esc_html_e( 'All Influencers', 'reftrackr' ); ?></option>
                <?php foreach ( $influencers as $inf ) : ?>
                    <option value="<?php echo esc_attr( $inf->id ); ?>"><?php echo esc_html( $inf->name ); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="reftrackr-filter-group">
            <label class="reftrackr-filter-label" for="reftrackr-ref-date-from"><?php esc_html_e( 'From', 'reftrackr' ); ?></label>
            <input type="date" id="reftrackr-ref-date-from" class="reftrackr-filter-input" />
        </div>
        <div class="reftrackr-filter-group">
            <label class="reftrackr-filter-label" for="reftrackr-ref-date-to"><?php esc_html_e( 'To', 'reftrackr' ); ?></label>
            <input type="date" id="reftrackr-ref-date-to" class="reftrackr-filter-input" />
        </div>
        <div class="reftrackr-filter-group reftrackr-filter-group--actions">
            <label class="reftrackr-filter-label">&nbsp;</label>
            <div>
                <button type="button" class="reftrackr-btn reftrackr-btn--primary reftrackr-btn--sm" id="reftrackr-apply-ref-filters">
                    <?php esc_html_e( 'Apply', 'reftrackr' ); ?>
                </button>
                <button type="button" class="reftrackr-btn reftrackr-btn--secondary reftrackr-btn--sm" id="reftrackr-reset-ref-filters">
                    <?php esc_html_e( 'Reset', 'reftrackr' ); ?>
                </button>
            </div>
        </div>
    </div>

    <!-- Clicks Table -->
    <div class="reftrackr-section">
        <div class="reftrackr-table-wrap">
            <?php if ( ! empty( $clicks ) ) : ?>
                <table class="reftrackr-table" id="reftrackr-clicks-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Influencer', 'reftrackr' ); ?></th>
                            <th><?php esc_html_e( 'Device', 'reftrackr' ); ?></th>
                            <th><?php esc_html_e( 'Referrer URL', 'reftrackr' ); ?></th>
                            <th><?php esc_html_e( 'Date / Time', 'reftrackr' ); ?></th>
                        </tr>
                    </thead>
                    <tbody id="reftrackr-clicks-tbody">
                        <?php foreach ( $clicks as $click ) :
                            $device_icon = 'desktop' === $click->device_type ? '🖥️' : ( 'mobile' === $click->device_type ? '📱' : '📱' );
                            if ( 'tablet' === $click->device_type ) {
                                $device_icon = '📋';
                            }
                            ?>
                            <tr>
                                <td><?php echo esc_html( isset( $click->influencer_name ) ? $click->influencer_name : '—' ); ?></td>
                                <td>
                                    <span title="<?php echo esc_attr( ucfirst( $click->device_type ) ); ?>">
                                        <?php echo esc_html( $device_icon ); ?> <?php echo esc_html( ucfirst( $click->device_type ) ); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ( ! empty( $click->referrer_url ) ) : ?>
                                        <a href="<?php echo esc_url( $click->referrer_url ); ?>" target="_blank" rel="noopener"><?php echo esc_html( wp_parse_url( $click->referrer_url, PHP_URL_HOST ) ); ?></a>
                                    <?php else : ?>
                                        <?php esc_html_e( 'Direct', 'reftrackr' ); ?>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $click->clicked_at ) ) ); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <?php if ( $total_clicks > 20 ) : ?>
                    <div class="reftrackr-pagination" id="reftrackr-clicks-pagination" data-total="<?php echo esc_attr( $total_clicks ); ?>" data-page="1">
                        <button type="button" class="reftrackr-btn reftrackr-btn--secondary reftrackr-btn--sm reftrackr-load-more" data-type="referrals">
                            <?php esc_html_e( 'Load More', 'reftrackr' ); ?>
                        </button>
                    </div>
                <?php endif; ?>

            <?php else : ?>
                <div class="reftrackr-empty-state">
                    <div class="reftrackr-empty-state-icon">
                        <span class="dashicons dashicons-admin-links"></span>
                    </div>
                    <h3 class="reftrackr-empty-state-title"><?php esc_html_e( 'No referral clicks yet', 'reftrackr' ); ?></h3>
                    <p class="reftrackr-empty-state-text"><?php esc_html_e( 'Clicks will appear here when visitors use influencer referral links.', 'reftrackr' ); ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>
