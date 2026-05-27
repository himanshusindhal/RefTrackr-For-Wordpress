<?php
/**
 * Reports page template.
 *
 * @package RefTrackr
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

// phpcs:ignorefile WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

$db          = new RefTrackr_DB();
$leaderboard = $db->get_leaderboard( 20 );
$geo_data    = $db->get_geographic_data();
$products    = $db->get_product_performance();
$currency    = get_woocommerce_currency_symbol();
?>

<div class="reftrackr-wrap">

    <div class="reftrackr-header">
        <h1 class="reftrackr-page-title"><?php esc_html_e( 'Reports', 'reftrackr' ); ?></h1>
        <div class="reftrackr-header-actions">
            <div class="reftrackr-date-filters" id="reftrackr-report-date-filters">
                <div class="reftrackr-filter-group">
                    <input type="date" id="reftrackr-report-date-from" class="reftrackr-filter-input" />
                </div>
                <span>&mdash;</span>
                <div class="reftrackr-filter-group">
                    <input type="date" id="reftrackr-report-date-to" class="reftrackr-filter-input" />
                </div>
                <button type="button" class="reftrackr-btn reftrackr-btn--primary reftrackr-btn--sm" id="reftrackr-apply-report-filters">
                    <?php esc_html_e( 'Apply', 'reftrackr' ); ?>
                </button>
            </div>
        </div>
    </div>

    <!-- Revenue by Influencer -->
    <div class="reftrackr-section">
        <div class="reftrackr-section-header">
            <h2 class="reftrackr-section-title"><?php esc_html_e( 'Revenue by Influencer', 'reftrackr' ); ?></h2>
        </div>
        <div class="reftrackr-table-wrap">
            <?php if ( ! empty( $leaderboard ) ) : ?>
                <table class="reftrackr-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Influencer', 'reftrackr' ); ?></th>
                            <th><?php esc_html_e( 'Orders', 'reftrackr' ); ?></th>
                            <th><?php esc_html_e( 'Revenue', 'reftrackr' ); ?></th>
                            <th><?php esc_html_e( 'Clicks', 'reftrackr' ); ?></th>
                            <th><?php esc_html_e( 'Conv. Rate', 'reftrackr' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $leaderboard as $inf ) :
                            $clicks    = isset( $inf->total_clicks ) ? absint( $inf->total_clicks ) : 0;
                            $orders    = isset( $inf->total_orders ) ? absint( $inf->total_orders ) : 0;
                            $conv_rate = $clicks > 0 ? round( ( $orders / $clicks ) * 100, 1 ) : 0;
                            ?>
                            <tr>
                                <td><strong><?php echo esc_html( $inf->name ); ?></strong></td>
                                <td><?php echo esc_html( $orders ); ?></td>
                                <td><?php echo esc_html( $currency ); ?><?php echo esc_html( number_format( floatval( $inf->total_revenue ), 2 ) ); ?></td>
                                <td><?php echo esc_html( $clicks ); ?></td>
                                <td><?php echo esc_html( $conv_rate ); ?>%</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <div class="reftrackr-empty-state">
                    <p class="reftrackr-empty-state-text"><?php esc_html_e( 'No revenue data available.', 'reftrackr' ); ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Geographic Breakdown -->
    <div class="reftrackr-section">
        <div class="reftrackr-section-header">
            <h2 class="reftrackr-section-title"><?php esc_html_e( 'Geographic Breakdown', 'reftrackr' ); ?></h2>
        </div>
        <div class="reftrackr-table-wrap">
            <?php if ( ! empty( $geo_data ) ) : ?>
                <table class="reftrackr-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'City', 'reftrackr' ); ?></th>
                            <th><?php esc_html_e( 'State', 'reftrackr' ); ?></th>
                            <th><?php esc_html_e( 'Orders', 'reftrackr' ); ?></th>
                            <th><?php esc_html_e( 'Revenue', 'reftrackr' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $geo_data as $geo ) : ?>
                            <tr>
                                <td><?php echo esc_html( $geo->city ? $geo->city : '—' ); ?></td>
                                <td><?php echo esc_html( $geo->state ? $geo->state : '—' ); ?></td>
                                <td><?php echo esc_html( absint( $geo->order_count ) ); ?></td>
                                <td><?php echo esc_html( $currency ); ?><?php echo esc_html( number_format( floatval( $geo->total_revenue ), 2 ) ); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <div class="reftrackr-empty-state">
                    <p class="reftrackr-empty-state-text"><?php esc_html_e( 'No geographic data available.', 'reftrackr' ); ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Product Performance -->
    <div class="reftrackr-section">
        <div class="reftrackr-section-header">
            <h2 class="reftrackr-section-title"><?php esc_html_e( 'Product Performance', 'reftrackr' ); ?></h2>
        </div>
        <div class="reftrackr-table-wrap">
            <?php if ( ! empty( $products ) ) : ?>
                <table class="reftrackr-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Product', 'reftrackr' ); ?></th>
                            <th><?php esc_html_e( 'Units Sold', 'reftrackr' ); ?></th>
                            <th><?php esc_html_e( 'Revenue', 'reftrackr' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $products as $product ) : ?>
                            <tr>
                                <td><strong><?php echo esc_html( $product->product_name ); ?></strong></td>
                                <td><?php echo esc_html( absint( $product->units_sold ) ); ?></td>
                                <td><?php echo esc_html( $currency ); ?><?php echo esc_html( number_format( floatval( $product->total_revenue ), 2 ) ); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <div class="reftrackr-empty-state">
                    <p class="reftrackr-empty-state-text"><?php esc_html_e( 'No product data available.', 'reftrackr' ); ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>
