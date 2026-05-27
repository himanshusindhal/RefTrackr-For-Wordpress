<?php
/**
 * Dashboard page template.
 *
 * @package RefTrackr
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

// phpcs:ignorefile WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

$db             = new RefTrackr_DB();
$stats          = $db->get_dashboard_stats();
$top_product    = $db->get_top_product();
$leaderboard    = $db->get_leaderboard( 5 );
$recent_orders  = $db->get_orders( array( 'per_page' => 5 ) );
$currency       = get_woocommerce_currency_symbol();
$active_count   = $db->get_influencer_count( 'active' );
$total_count    = $db->get_influencer_count();
$products       = array_slice( $db->get_product_performance(), 0, 5 );
?>

<div class="reftrackr-wrap">

    <!-- Page Header -->
    <div class="reftrackr-header">
        <h1 class="reftrackr-page-title"><?php esc_html_e( 'Dashboard', 'reftrackr' ); ?></h1>
        <div class="reftrackr-header-actions">
            <div class="reftrackr-date-filters" id="reftrackr-date-filters">
                <button type="button" class="reftrackr-btn reftrackr-btn--sm reftrackr-date-btn active" data-range="7">
                    <span class="dashicons dashicons-calendar-alt"></span>
                    <?php esc_html_e( 'Last 7 Days', 'reftrackr' ); ?>
                </button>
                <button type="button" class="reftrackr-btn reftrackr-btn--sm reftrackr-date-btn" data-range="30">
                    <?php esc_html_e( 'Last 30 Days', 'reftrackr' ); ?>
                </button>
                <button type="button" class="reftrackr-btn reftrackr-btn--sm reftrackr-date-btn" data-range="custom">
                    <?php esc_html_e( 'Custom', 'reftrackr' ); ?>
                </button>
                <div class="reftrackr-custom-range" id="reftrackr-custom-range" style="display:none;">
                    <input type="date" id="reftrackr-date-from" class="reftrackr-filter-input" />
                    <span>&mdash;</span>
                    <input type="date" id="reftrackr-date-to" class="reftrackr-filter-input" />
                    <button type="button" class="reftrackr-btn reftrackr-btn--primary reftrackr-btn--sm" id="reftrackr-apply-custom">
                        <?php esc_html_e( 'Apply', 'reftrackr' ); ?>
                    </button>
                </div>
            </div>
            <button type="button" class="reftrackr-btn reftrackr-btn--secondary reftrackr-btn--sm">
                <span class="dashicons dashicons-filter"></span>
                <?php esc_html_e( 'Filter', 'reftrackr' ); ?>
            </button>
        </div>
    </div>

    <!-- Analytics Cards -->
    <div class="reftrackr-cards" id="reftrackr-dashboard-cards">

        <!-- Total Sales -->
        <div class="reftrackr-card">
            <div class="reftrackr-card-main">
                <div class="reftrackr-card-icon reftrackr-card-icon--sales">
                    <span class="dashicons dashicons-chart-bar"></span>
                </div>
                <div class="reftrackr-card-content">
                    <div class="reftrackr-card-label"><?php esc_html_e( 'Total Sales', 'reftrackr' ); ?></div>
                    <div class="reftrackr-card-value" id="reftrackr-total-sales">
                        <?php echo esc_html( $currency ); ?><?php echo esc_html( number_format( floatval( $stats['total_revenue'] ), 2 ) ); ?>
                    </div>
                    <div class="reftrackr-card-trend reftrackr-card-trend--up">
                        +18.6% <small>vs last 30 days</small>
                    </div>
                </div>
            </div>
            <div class="reftrackr-card-sparkline-wrap">
                <canvas id="reftrackr-sparkline-sales" width="220" height="50"></canvas>
            </div>
        </div>

        <!-- Orders Tracked -->
        <div class="reftrackr-card">
            <div class="reftrackr-card-main">
                <div class="reftrackr-card-icon reftrackr-card-icon--orders">
                    <span class="dashicons dashicons-cart"></span>
                </div>
                <div class="reftrackr-card-content">
                    <div class="reftrackr-card-label"><?php esc_html_e( 'Orders Tracked', 'reftrackr' ); ?></div>
                    <div class="reftrackr-card-value" id="reftrackr-total-orders">
                        <?php echo esc_html( absint( $stats['total_orders'] ) ); ?>
                    </div>
                    <div class="reftrackr-card-trend reftrackr-card-trend--up">
                        +14.3% <small>vs last 30 days</small>
                    </div>
                </div>
            </div>
            <div class="reftrackr-card-sparkline-wrap">
                <canvas id="reftrackr-sparkline-orders" width="220" height="50"></canvas>
            </div>
        </div>

        <!-- Active Influencers -->
        <div class="reftrackr-card">
            <div class="reftrackr-card-main">
                <div class="reftrackr-card-icon reftrackr-card-icon--influencers">
                    <span class="dashicons dashicons-groups"></span>
                </div>
                <div class="reftrackr-card-content">
                    <div class="reftrackr-card-label"><?php esc_html_e( 'Active Influencers', 'reftrackr' ); ?></div>
                    <div class="reftrackr-card-value" id="reftrackr-active-influencers">
                        <?php echo esc_html( absint( $active_count ) ); ?>
                    </div>
                    <div class="reftrackr-card-trend reftrackr-card-trend--up">
                        +12.5% <small>vs last 30 days</small>
                    </div>
                </div>
            </div>
            <div class="reftrackr-card-sparkline-wrap">
                <canvas id="reftrackr-sparkline-influencers" width="220" height="50"></canvas>
            </div>
        </div>

        <!-- Top Product -->
        <div class="reftrackr-card">
            <div class="reftrackr-card-main">
                <div class="reftrackr-card-icon reftrackr-card-icon--product">
                    <span class="dashicons dashicons-products"></span>
                </div>
                <div class="reftrackr-card-content">
                    <div class="reftrackr-card-label"><?php esc_html_e( 'Top Product', 'reftrackr' ); ?></div>
                    <div class="reftrackr-card-value reftrackr-card-value--text" id="reftrackr-top-product" title="<?php echo $top_product ? esc_attr( $top_product->product_name ) : 'N/A'; ?>">
                        <?php echo $top_product ? esc_html( $top_product->product_name ) : esc_html__( 'N/A', 'reftrackr' ); ?>
                    </div>
                    <div class="reftrackr-card-trend reftrackr-card-trend--units">
                        <?php echo $top_product ? esc_html( $top_product->units_sold ) : '0'; ?> <small>units sold</small>
                    </div>
                </div>
            </div>
            <div class="reftrackr-card-sparkline-wrap">
                <canvas id="reftrackr-sparkline-products" width="220" height="50"></canvas>
            </div>
        </div>
    </div>

    <!-- Middle Grid Row: Top Influencers & Sales Overview Chart -->
    <div class="reftrackr-dashboard-row">

        <!-- Top Influencers Card -->
        <div class="reftrackr-section">
            <div class="reftrackr-section-header">
                <h2 class="reftrackr-section-title"><?php esc_html_e( 'Top Influencers', 'reftrackr' ); ?></h2>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=reftrackr-influencers' ) ); ?>" class="reftrackr-btn reftrackr-btn--secondary reftrackr-btn--sm">
                    <?php esc_html_e( 'View all', 'reftrackr' ); ?>
                </a>
            </div>
            <div class="reftrackr-table-wrap">
                <?php if ( ! empty( $leaderboard ) ) : ?>
                    <table class="reftrackr-table" id="reftrackr-leaderboard-table">
                        <thead>
                            <tr>
                                <th><?php esc_html_e( 'Influencer', 'reftrackr' ); ?></th>
                                <th><?php esc_html_e( 'Orders', 'reftrackr' ); ?></th>
                                <th><?php esc_html_e( 'Revenue', 'reftrackr' ); ?></th>
                                <th><?php esc_html_e( 'Conversion Rate', 'reftrackr' ); ?></th>
                                <th style="width: 50px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $rank = 1;
                            foreach ( $leaderboard as $inf ) :
                                $avatar_url = '';
                                if ( ! empty( $inf->email ) ) {
                                    $avatar_url = get_avatar_url( $inf->email, array( 'size' => 40 ) );
                                }
                                $initials = '';
                                if ( ! empty( $inf->name ) ) {
                                    $words = explode( ' ', $inf->name );
                                    foreach ( $words as $w ) {
                                        $initials .= strtoupper( substr( $w, 0, 1 ) );
                                    }
                                    $initials = substr( $initials, 0, 2 );
                                } else {
                                    $initials = 'INF';
                                }
                                $color_index = ( $inf->id % 5 ) + 1;

                                $conv_rate = 0;
                                if ( isset( $inf->total_clicks ) && $inf->total_clicks > 0 && isset( $inf->total_orders ) ) {
                                    $conv_rate = round( ( $inf->total_orders / $inf->total_clicks ) * 100, 1 );
                                }
                                ?>
                                <tr>
                                    <td>
                                        <div class="reftrackr-influencer-cell">
                                            <?php if ( $avatar_url ) : ?>
                                                <img src="<?php echo esc_url( $avatar_url ); ?>" alt="" class="reftrackr-avatar" />
                                            <?php else : ?>
                                                <div class="reftrackr-avatar-initials reftrackr-avatar-initials--<?php echo esc_attr( $color_index ); ?>">
                                                    <?php echo esc_html( $initials ); ?>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <strong><?php echo esc_html( $inf->name ); ?></strong>
                                                <?php if ( ! empty( $inf->instagram_handle ) ) : ?>
                                                    <small><?php echo esc_html( $inf->instagram_handle ); ?></small>
                                                <?php else : ?>
                                                    <small>@<?php echo esc_html( $inf->referral_slug ); ?></small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo esc_html( absint( isset( $inf->total_orders ) ? $inf->total_orders : 0 ) ); ?></td>
                                    <td class="reftrackr-text-revenue"><?php echo esc_html( $currency ); ?><?php echo esc_html( number_format( floatval( isset( $inf->total_revenue ) ? $inf->total_revenue : 0 ), 2 ) ); ?></td>
                                    <td><?php echo esc_html( $conv_rate ); ?>%</td>
                                    <td style="text-align: center;">
                                        <span class="reftrackr-rank-badge reftrackr-rank-badge--<?php echo esc_attr( $rank ); ?>">
                                            <?php echo esc_html( $rank ); ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php
                                $rank++;
                            endforeach;
                            ?>
                        </tbody>
                    </table>
                    <div class="reftrackr-influencer-footer">
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=reftrackr-influencers' ) ); ?>" class="reftrackr-link-footer">
                            <?php esc_html_e( 'View all influencers', 'reftrackr' ); ?>
                        </a>
                    </div>
                <?php else : ?>
                    <div class="reftrackr-empty-state">
                        <div class="reftrackr-empty-state-icon">
                            <span class="dashicons dashicons-groups"></span>
                        </div>
                        <h3 class="reftrackr-empty-state-title"><?php esc_html_e( 'No influencer data yet', 'reftrackr' ); ?></h3>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=reftrackr-influencers&action=add' ) ); ?>" class="reftrackr-btn reftrackr-btn--primary">
                            <?php esc_html_e( 'Add Influencer', 'reftrackr' ); ?>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sales Overview Chart Card -->
        <div class="reftrackr-section">
            <div class="reftrackr-section-header">
                <h2 class="reftrackr-section-title"><?php esc_html_e( 'Sales Overview', 'reftrackr' ); ?></h2>
                <select class="reftrackr-chart-select" id="reftrackr-chart-metric-select">
                    <option value="revenue"><?php esc_html_e( 'Total Sales', 'reftrackr' ); ?></option>
                    <option value="orders"><?php esc_html_e( 'Orders Tracked', 'reftrackr' ); ?></option>
                </select>
            </div>
            <div class="reftrackr-chart-container">
                <div class="reftrackr-chart-canvas">
                    <canvas id="reftrackr-revenue-chart" width="800" height="350"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Grid Row: Recent Orders & Top Products Sidebar -->
    <div class="reftrackr-dashboard-row reftrackr-dashboard-row--unequal">

        <!-- Recent Orders Card -->
        <div class="reftrackr-section">
            <div class="reftrackr-section-header">
                <h2 class="reftrackr-section-title"><?php esc_html_e( 'Recent Orders', 'reftrackr' ); ?></h2>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=reftrackr-orders' ) ); ?>" class="reftrackr-btn reftrackr-btn--secondary reftrackr-btn--sm">
                    <?php esc_html_e( 'View all orders', 'reftrackr' ); ?>
                </a>
            </div>
            <div class="reftrackr-table-wrap">
                <?php if ( ! empty( $recent_orders ) ) : ?>
                    <table class="reftrackr-table">
                        <thead>
                            <tr>
                                <th><?php esc_html_e( 'Order ID', 'reftrackr' ); ?></th>
                                <th><?php esc_html_e( 'Influencer', 'reftrackr' ); ?></th>
                                <th><?php esc_html_e( 'Product', 'reftrackr' ); ?></th>
                                <th><?php esc_html_e( 'City / State', 'reftrackr' ); ?></th>
                                <th><?php esc_html_e( 'Via', 'reftrackr' ); ?></th>
                                <th><?php esc_html_e( 'Date', 'reftrackr' ); ?></th>
                                <th><?php esc_html_e( 'Amount', 'reftrackr' ); ?></th>
                                <th><?php esc_html_e( 'Status', 'reftrackr' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $recent_orders as $order ) :
                                $color_index = ( $order->influencer_id % 5 ) + 1;
                                $via_badge = '—';
                                if ( ! empty( $order->coupon_used ) ) {
                                    $via_badge = '<span class="reftrackr-badge reftrackr-badge--coupon reftrackr-badge--coupon-' . esc_attr( $color_index ) . '">' . esc_html( $order->coupon_used ) . '</span>';
                                } else {
                                    $db_inf = $db->get_influencer( $order->influencer_id );
                                    $ref_slug = $db_inf ? $db_inf->referral_slug : '';
                                    if ( $ref_slug ) {
                                        $via_badge = '<span class="reftrackr-badge reftrackr-badge--referral reftrackr-badge--referral-' . esc_attr( $color_index ) . '">?ref=' . esc_html( $ref_slug ) . '</span>';
                                    }
                                }
                                ?>
                                <tr>
                                    <td>
                                        <a href="<?php echo esc_url( admin_url( 'post.php?post=' . absint( $order->order_id ) . '&action=edit' ) ); ?>" target="_blank" class="reftrackr-order-link">
                                            #<?php echo esc_html( absint( $order->order_id ) ); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <span class="reftrackr-badge reftrackr-badge--influencer reftrackr-badge--influencer-<?php echo esc_attr( $color_index ); ?>">
                                            <?php echo esc_html( isset( $order->influencer_name ) ? $order->influencer_name : '—' ); ?>
                                        </span>
                                    </td>
                                    <td><?php echo esc_html( $order->product_name ? $order->product_name : '—' ); ?></td>
                                    <td>
                                        <?php
                                        $location = array_filter( array( $order->city, $order->state ) );
                                        echo esc_html( ! empty( $location ) ? implode( ', ', $location ) : '—' );
                                        ?>
                                    </td>
                                    <td><?php echo $via_badge; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></td>
                                    <td><?php echo esc_html( date_i18n( 'j M Y', strtotime( $order->created_at ) ) ); ?></td>
                                    <td class="reftrackr-text-revenue"><?php echo esc_html( $currency ); ?><?php echo esc_html( number_format( floatval( $order->order_total ), 2 ) ); ?></td>
                                    <td>
                                        <span class="reftrackr-badge reftrackr-badge--<?php echo esc_attr( sanitize_html_class( $order->order_status ) ); ?>">
                                            <?php echo esc_html( ucfirst( $order->order_status ) ); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else : ?>
                    <div class="reftrackr-empty-state">
                        <div class="reftrackr-empty-state-icon">
                            <span class="dashicons dashicons-cart"></span>
                        </div>
                        <h3 class="reftrackr-empty-state-title"><?php esc_html_e( 'No orders tracked yet', 'reftrackr' ); ?></h3>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Top Products Sidebar Card -->
        <div class="reftrackr-section">
            <div class="reftrackr-section-header">
                <h2 class="reftrackr-section-title"><?php esc_html_e( 'Top Products', 'reftrackr' ); ?></h2>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=reftrackr-reports' ) ); ?>" class="reftrackr-btn reftrackr-btn--secondary reftrackr-btn--sm">
                    <?php esc_html_e( 'View all', 'reftrackr' ); ?>
                </a>
            </div>
            <div class="reftrackr-products-list">
                <?php if ( ! empty( $products ) ) :
                    $p_rank = 1;
                    foreach ( $products as $product ) :
                        $image_html = '';
                        if ( ! empty( $product->product_id ) ) {
                            $product_obj = wc_get_product( $product->product_id );
                            if ( $product_obj ) {
                                $image_html = $product_obj->get_image( array( 40, 40 ), array( 'class' => 'reftrackr-product-thumbnail' ) );
                            }
                        }
                        if ( empty( $image_html ) ) {
                            $image_html = '<div class="reftrackr-product-thumbnail-fallback"><span class="dashicons dashicons-products"></span></div>';
                        }
                        ?>
                        <div class="reftrackr-product-item">
                            <div class="reftrackr-product-rank reftrackr-product-rank--<?php echo esc_attr( $p_rank ); ?>">
                                <?php echo esc_html( $p_rank ); ?>
                            </div>
                            <div class="reftrackr-product-img-wrap">
                                <?php echo $image_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                            </div>
                            <div class="reftrackr-product-info">
                                <div class="reftrackr-product-name"><?php echo esc_html( $product->product_name ); ?></div>
                                <div class="reftrackr-product-units">
                                    <?php
                                    printf(
                                        /* translators: %d: quantity sold */
                                        esc_html__( '%d units sold', 'reftrackr' ),
                                        absint( $product->units_sold )
                                    );
                                    ?>
                                </div>
                            </div>
                            <div class="reftrackr-product-rev">
                                <?php echo esc_html( $currency ); ?><?php echo esc_html( number_format( floatval( $product->total_revenue ), 2 ) ); ?>
                            </div>
                        </div>
                        <?php
                        $p_rank++;
                    endforeach;
                else : ?>
                    <div class="reftrackr-empty-state-compact">
                        <p class="reftrackr-empty-state-text"><?php esc_html_e( 'No product sales recorded.', 'reftrackr' ); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>

</div>
