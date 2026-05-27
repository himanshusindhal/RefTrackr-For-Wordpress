<?php
/**
 * Orders tracking page template.
 *
 * @package RefTrackr
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

// phpcs:ignorefile WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

$db          = new RefTrackr_DB();
$orders      = $db->get_orders( array( 'per_page' => 20 ) );
$influencers = $db->get_influencers( array( 'per_page' => -1 ) );
$total       = $db->get_order_count();
$currency    = get_woocommerce_currency_symbol();
?>

<div class="reftrackr-wrap">

    <div class="reftrackr-header">
        <h1 class="reftrackr-page-title"><?php esc_html_e( 'Orders', 'reftrackr' ); ?></h1>
    </div>

    <!-- Filters -->
    <div class="reftrackr-filters" id="reftrackr-order-filters">
        <div class="reftrackr-filter-group">
            <label class="reftrackr-filter-label" for="reftrackr-filter-influencer"><?php esc_html_e( 'Influencer', 'reftrackr' ); ?></label>
            <select id="reftrackr-filter-influencer" class="reftrackr-filter-select">
                <option value=""><?php esc_html_e( 'All Influencers', 'reftrackr' ); ?></option>
                <?php foreach ( $influencers as $inf ) : ?>
                    <option value="<?php echo esc_attr( $inf->id ); ?>"><?php echo esc_html( $inf->name ); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="reftrackr-filter-group">
            <label class="reftrackr-filter-label" for="reftrackr-filter-date-from"><?php esc_html_e( 'From', 'reftrackr' ); ?></label>
            <input type="date" id="reftrackr-filter-date-from" class="reftrackr-filter-input" />
        </div>
        <div class="reftrackr-filter-group">
            <label class="reftrackr-filter-label" for="reftrackr-filter-date-to"><?php esc_html_e( 'To', 'reftrackr' ); ?></label>
            <input type="date" id="reftrackr-filter-date-to" class="reftrackr-filter-input" />
        </div>
        <div class="reftrackr-filter-group">
            <label class="reftrackr-filter-label" for="reftrackr-filter-coupon"><?php esc_html_e( 'Coupon', 'reftrackr' ); ?></label>
            <input type="text" id="reftrackr-filter-coupon" class="reftrackr-filter-input" placeholder="<?php esc_attr_e( 'Coupon code', 'reftrackr' ); ?>" />
        </div>
        <div class="reftrackr-filter-group reftrackr-filter-group--actions">
            <label class="reftrackr-filter-label">&nbsp;</label>
            <div>
                <button type="button" class="reftrackr-btn reftrackr-btn--primary reftrackr-btn--sm" id="reftrackr-apply-order-filters">
                    <?php esc_html_e( 'Apply', 'reftrackr' ); ?>
                </button>
                <button type="button" class="reftrackr-btn reftrackr-btn--secondary reftrackr-btn--sm" id="reftrackr-reset-order-filters">
                    <?php esc_html_e( 'Reset', 'reftrackr' ); ?>
                </button>
            </div>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="reftrackr-section">
        <div class="reftrackr-table-wrap">
            <?php if ( ! empty( $orders ) ) : ?>
                <table class="reftrackr-table" id="reftrackr-orders-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Order', 'reftrackr' ); ?></th>
                            <th><?php esc_html_e( 'Influencer', 'reftrackr' ); ?></th>
                            <th><?php esc_html_e( 'Product', 'reftrackr' ); ?></th>
                            <th><?php esc_html_e( 'City / State', 'reftrackr' ); ?></th>
                            <th><?php esc_html_e( 'Source', 'reftrackr' ); ?></th>
                            <th><?php esc_html_e( 'Coupon', 'reftrackr' ); ?></th>
                            <th><?php esc_html_e( 'Amount', 'reftrackr' ); ?></th>
                            <th><?php esc_html_e( 'Status', 'reftrackr' ); ?></th>
                            <th><?php esc_html_e( 'Date', 'reftrackr' ); ?></th>
                        </tr>
                    </thead>
                    <tbody id="reftrackr-orders-tbody">
                        <?php foreach ( $orders as $order ) : ?>
                            <tr>
                                <td>
                                    <a href="<?php echo esc_url( admin_url( 'post.php?post=' . absint( $order->order_id ) . '&action=edit' ) ); ?>" target="_blank">
                                        #<?php echo esc_html( absint( $order->order_id ) ); ?>
                                    </a>
                                </td>
                                <td><?php echo esc_html( isset( $order->influencer_name ) ? $order->influencer_name : '—' ); ?></td>
                                <td><?php echo esc_html( $order->product_name ? $order->product_name : '—' ); ?></td>
                                <td>
                                    <?php
                                    $location = array_filter( array( $order->city, $order->state ) );
                                    echo esc_html( ! empty( $location ) ? implode( ', ', $location ) : '—' );
                                    ?>
                                </td>
                                <td>
                                    <?php if ( ! empty( $order->referral_source ) ) : ?>
                                        <span class="reftrackr-badge reftrackr-badge--<?php echo esc_attr( $order->referral_source ); ?>">
                                            <?php echo esc_html( ucfirst( $order->referral_source ) ); ?>
                                        </span>
                                    <?php else : ?>
                                        —
                                    <?php endif; ?>
                                </td>
                                <td><?php echo esc_html( $order->coupon_used ? $order->coupon_used : '—' ); ?></td>
                                <td><?php echo esc_html( $currency ); ?><?php echo esc_html( number_format( floatval( $order->order_total ), 2 ) ); ?></td>
                                <td>
                                    <span class="reftrackr-badge reftrackr-badge--<?php echo esc_attr( sanitize_html_class( $order->order_status ) ); ?>">
                                        <?php echo esc_html( ucfirst( $order->order_status ) ); ?>
                                    </span>
                                </td>
                                <td><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $order->created_at ) ) ); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <?php if ( $total > 20 ) : ?>
                    <div class="reftrackr-pagination" id="reftrackr-orders-pagination" data-total="<?php echo esc_attr( $total ); ?>" data-page="1">
                        <span class="reftrackr-pagination-info">
                            <?php
                            echo esc_html(
                                sprintf(
                                    /* translators: %1$d: shown count, %2$d: total count */
                                    __( 'Showing %1$d of %2$d orders', 'reftrackr' ),
                                    min( 20, $total ),
                                    $total
                                )
                            );
                            ?>
                        </span>
                        <button type="button" class="reftrackr-btn reftrackr-btn--secondary reftrackr-btn--sm reftrackr-load-more" data-type="orders">
                            <?php esc_html_e( 'Load More', 'reftrackr' ); ?>
                        </button>
                    </div>
                <?php endif; ?>

            <?php else : ?>
                <div class="reftrackr-empty-state">
                    <div class="reftrackr-empty-state-icon">
                        <span class="dashicons dashicons-cart"></span>
                    </div>
                    <h3 class="reftrackr-empty-state-title"><?php esc_html_e( 'No orders tracked yet', 'reftrackr' ); ?></h3>
                    <p class="reftrackr-empty-state-text"><?php esc_html_e( 'Orders attributed to influencers will appear here.', 'reftrackr' ); ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>
