<?php
/**
 * Coupons page template.
 *
 * @package RefTrackr
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

// phpcs:ignorefile WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

$db           = new RefTrackr_DB();
$coupon_stats = $db->get_coupon_stats();
$currency     = get_woocommerce_currency_symbol();
?>

<div class="reftrackr-wrap">

    <div class="reftrackr-header">
        <h1 class="reftrackr-page-title"><?php esc_html_e( 'Coupons', 'reftrackr' ); ?></h1>
    </div>

    <div class="reftrackr-section">
        <div class="reftrackr-section-header">
            <h2 class="reftrackr-section-title"><?php esc_html_e( 'Coupon Analytics', 'reftrackr' ); ?></h2>
        </div>
        <div class="reftrackr-table-wrap">
            <?php if ( ! empty( $coupon_stats ) ) : ?>
                <table class="reftrackr-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Coupon Code', 'reftrackr' ); ?></th>
                            <th><?php esc_html_e( 'Linked Influencer', 'reftrackr' ); ?></th>
                            <th><?php esc_html_e( 'Usage Count', 'reftrackr' ); ?></th>
                            <th><?php esc_html_e( 'Total Revenue', 'reftrackr' ); ?></th>
                            <th><?php esc_html_e( 'Revenue Per Use', 'reftrackr' ); ?></th>
                            <th><?php esc_html_e( 'Status', 'reftrackr' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $coupon_stats as $stat ) :
                            $usage   = absint( $stat->usage_count );
                            $revenue = floatval( $stat->total_revenue );
                            $per_use = $usage > 0 ? $revenue / $usage : 0;
                            ?>
                            <tr>
                                <td><code><?php echo esc_html( $stat->coupon_used ); ?></code></td>
                                <td><?php echo esc_html( $stat->influencer_name ? $stat->influencer_name : '—' ); ?></td>
                                <td><?php echo esc_html( $usage ); ?></td>
                                <td><?php echo esc_html( $currency ); ?><?php echo esc_html( number_format( $revenue, 2 ) ); ?></td>
                                <td><?php echo esc_html( $currency ); ?><?php echo esc_html( number_format( $per_use, 2 ) ); ?></td>
                                <td>
                                    <?php if ( ! empty( $stat->influencer_status ) ) : ?>
                                        <span class="reftrackr-badge reftrackr-badge--<?php echo esc_attr( $stat->influencer_status ); ?>">
                                            <?php echo esc_html( ucfirst( $stat->influencer_status ) ); ?>
                                        </span>
                                    <?php else : ?>
                                        <span class="reftrackr-badge reftrackr-badge--paused"><?php esc_html_e( 'Unlinked', 'reftrackr' ); ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <div class="reftrackr-empty-state">
                    <div class="reftrackr-empty-state-icon">
                        <span class="dashicons dashicons-tickets-alt"></span>
                    </div>
                    <h3 class="reftrackr-empty-state-title"><?php esc_html_e( 'No coupon data yet', 'reftrackr' ); ?></h3>
                    <p class="reftrackr-empty-state-text"><?php esc_html_e( 'Coupon analytics will appear here once orders are placed with influencer coupon codes.', 'reftrackr' ); ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>
