<?php
/**
 * Influencers list page template.
 *
 * @package RefTrackr
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

// phpcs:ignorefile WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

$db          = new RefTrackr_DB();
$influencers = $db->get_influencers();
$currency    = get_woocommerce_currency_symbol();
?>

<div class="reftrackr-wrap">

    <div class="reftrackr-header">
        <h1 class="reftrackr-page-title"><?php esc_html_e( 'Influencers', 'reftrackr' ); ?></h1>
        <div class="reftrackr-header-actions">
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=reftrackr-influencers&action=add' ) ); ?>" class="reftrackr-btn reftrackr-btn--primary">
                <span class="dashicons dashicons-plus-alt2"></span>
                <?php esc_html_e( 'Add Influencer', 'reftrackr' ); ?>
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="reftrackr-filters" id="reftrackr-influencer-filters">
        <div class="reftrackr-filter-group">
            <label class="reftrackr-filter-label" for="reftrackr-search"><?php esc_html_e( 'Search', 'reftrackr' ); ?></label>
            <input type="text" id="reftrackr-search" class="reftrackr-filter-input" placeholder="<?php esc_attr_e( 'Search influencers...', 'reftrackr' ); ?>" />
        </div>
        <div class="reftrackr-filter-group">
            <label class="reftrackr-filter-label" for="reftrackr-status-filter"><?php esc_html_e( 'Status', 'reftrackr' ); ?></label>
            <select id="reftrackr-status-filter" class="reftrackr-filter-select">
                <option value=""><?php esc_html_e( 'All', 'reftrackr' ); ?></option>
                <option value="active"><?php esc_html_e( 'Active', 'reftrackr' ); ?></option>
                <option value="paused"><?php esc_html_e( 'Paused', 'reftrackr' ); ?></option>
            </select>
        </div>
    </div>

    <!-- Influencers Table -->
    <div class="reftrackr-section">
        <div class="reftrackr-table-wrap">
            <?php if ( ! empty( $influencers ) ) : ?>
                <table class="reftrackr-table" id="reftrackr-influencers-table">
                    <thead>
                        <tr>
                             <th><?php esc_html_e( 'Influencer', 'reftrackr' ); ?></th>
                            <th><?php esc_html_e( 'Referral Link', 'reftrackr' ); ?></th>
                            <th><?php esc_html_e( 'Coupon', 'reftrackr' ); ?></th>
                            <th><?php esc_html_e( 'Orders', 'reftrackr' ); ?></th>
                            <th><?php esc_html_e( 'Revenue', 'reftrackr' ); ?></th>
                            <th><?php esc_html_e( 'Conv. Rate', 'reftrackr' ); ?></th>
                            <th><?php esc_html_e( 'Status', 'reftrackr' ); ?></th>
                            <th><?php esc_html_e( 'Actions', 'reftrackr' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ( $influencers as $influencer ) :
                            $stats      = $db->get_influencer_stats( $influencer->id );
                            $avatar_url = ! empty( $influencer->email ) ? get_avatar_url( $influencer->email, array( 'size' => 40 ) ) : '';
                            $ref_url    = site_url( '/?ref=' . $influencer->referral_slug );
                            ?>
                            <tr data-id="<?php echo esc_attr( $influencer->id ); ?>" data-status="<?php echo esc_attr( $influencer->status ); ?>" data-name="<?php echo esc_attr( strtolower( $influencer->name ) ); ?>">
                                <td>
                                    <div class="reftrackr-influencer-cell">
                                        <?php if ( $avatar_url ) : ?>
                                            <img src="<?php echo esc_url( $avatar_url ); ?>" alt="" class="reftrackr-avatar" />
                                        <?php endif; ?>
                                        <div>
                                            <strong><?php echo esc_html( $influencer->name ); ?></strong>
                                            <?php if ( ! empty( $influencer->email ) ) : ?>
                                                <small><?php echo esc_html( $influencer->email ); ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <code class="reftrackr-ref-link"><?php echo esc_url( $ref_url ); ?></code>
                                </td>
                                <td><?php echo esc_html( $influencer->coupon_code ? $influencer->coupon_code : '—' ); ?></td>
                                <td><?php echo esc_html( $stats['total_orders'] ); ?></td>
                                <td><?php echo esc_html( $currency ); ?><?php echo esc_html( number_format( $stats['total_revenue'], 2 ) ); ?></td>
                                <td><?php echo esc_html( $stats['conversion_rate'] ); ?>%</td>
                                <td>
                                    <span class="reftrackr-badge reftrackr-badge--<?php echo esc_attr( $influencer->status ); ?>" id="reftrackr-status-<?php echo esc_attr( $influencer->id ); ?>">
                                        <?php echo esc_html( ucfirst( $influencer->status ) ); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="reftrackr-actions-cell">
                                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=reftrackr-influencers&action=edit&id=' . $influencer->id ) ); ?>" class="reftrackr-btn reftrackr-btn--secondary reftrackr-btn--sm" title="<?php esc_attr_e( 'Edit', 'reftrackr' ); ?>">
                                            <span class="dashicons dashicons-edit"></span>
                                        </a>
                                        <button type="button" class="reftrackr-btn reftrackr-btn--secondary reftrackr-btn--sm reftrackr-toggle-status" data-id="<?php echo esc_attr( $influencer->id ); ?>" title="<?php echo 'active' === $influencer->status ? esc_attr__( 'Pause', 'reftrackr' ) : esc_attr__( 'Activate', 'reftrackr' ); ?>">
                                            <span class="dashicons dashicons-<?php echo 'active' === $influencer->status ? 'controls-pause' : 'controls-play'; ?>"></span>
                                        </button>
                                        <button type="button" class="reftrackr-btn reftrackr-btn--danger reftrackr-btn--sm reftrackr-delete-influencer" data-id="<?php echo esc_attr( $influencer->id ); ?>" title="<?php esc_attr_e( 'Delete', 'reftrackr' ); ?>">
                                            <span class="dashicons dashicons-trash"></span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <div class="reftrackr-empty-state">
                    <div class="reftrackr-empty-state-icon">
                        <span class="dashicons dashicons-groups"></span>
                    </div>
                    <h3 class="reftrackr-empty-state-title"><?php esc_html_e( 'No influencers yet', 'reftrackr' ); ?></h3>
                    <p class="reftrackr-empty-state-text"><?php esc_html_e( 'Add your first influencer to start tracking referrals and sales.', 'reftrackr' ); ?></p>
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=reftrackr-influencers&action=add' ) ); ?>" class="reftrackr-btn reftrackr-btn--primary">
                        <?php esc_html_e( 'Add First Influencer', 'reftrackr' ); ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>
