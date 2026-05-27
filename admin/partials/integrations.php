<?php
/**
 * Integrations page template.
 *
 * @package RefTrackr
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="reftrackr-wrap">

    <div class="reftrackr-header">
        <h1 class="reftrackr-page-title"><?php esc_html_e( 'Integrations', 'reftrackr' ); ?></h1>
    </div>

    <p class="reftrackr-page-description">
        <?php esc_html_e( 'Connect RefTrackr with your favorite tools and services. More integrations coming soon!', 'reftrackr' ); ?>
    </p>

    <div class="reftrackr-integration-cards">

        <div class="reftrackr-integration-card">
            <div class="reftrackr-integration-icon">
                <span class="dashicons dashicons-media-spreadsheet"></span>
            </div>
            <h3 class="reftrackr-integration-name"><?php esc_html_e( 'CSV Export', 'reftrackr' ); ?></h3>
            <p class="reftrackr-integration-description"><?php esc_html_e( 'Export your influencer data, orders, and reports to CSV files for analysis.', 'reftrackr' ); ?></p>
            <span class="reftrackr-badge reftrackr-badge--coming-soon"><?php esc_html_e( 'Coming Soon', 'reftrackr' ); ?></span>
        </div>

        <div class="reftrackr-integration-card">
            <div class="reftrackr-integration-icon">
                <span class="dashicons dashicons-media-document"></span>
            </div>
            <h3 class="reftrackr-integration-name"><?php esc_html_e( 'PDF Reports', 'reftrackr' ); ?></h3>
            <p class="reftrackr-integration-description"><?php esc_html_e( 'Generate professional PDF reports to share with your team or influencers.', 'reftrackr' ); ?></p>
            <span class="reftrackr-badge reftrackr-badge--coming-soon"><?php esc_html_e( 'Coming Soon', 'reftrackr' ); ?></span>
        </div>

        <div class="reftrackr-integration-card">
            <div class="reftrackr-integration-icon">
                <span class="dashicons dashicons-phone"></span>
            </div>
            <h3 class="reftrackr-integration-name"><?php esc_html_e( 'WhatsApp Notifications', 'reftrackr' ); ?></h3>
            <p class="reftrackr-integration-description"><?php esc_html_e( 'Send automated notifications to influencers via WhatsApp when sales are made.', 'reftrackr' ); ?></p>
            <span class="reftrackr-badge reftrackr-badge--coming-soon"><?php esc_html_e( 'Coming Soon', 'reftrackr' ); ?></span>
        </div>

        <div class="reftrackr-integration-card">
            <div class="reftrackr-integration-icon">
                <span class="dashicons dashicons-format-chat"></span>
            </div>
            <h3 class="reftrackr-integration-name"><?php esc_html_e( 'Slack Integration', 'reftrackr' ); ?></h3>
            <p class="reftrackr-integration-description"><?php esc_html_e( 'Send automated notifications to your team\'s Slack channel whenever a new influencer sale is tracked.', 'reftrackr' ); ?></p>
            <span class="reftrackr-badge reftrackr-badge--coming-soon"><?php esc_html_e( 'Coming Soon', 'reftrackr' ); ?></span>
        </div>

    </div>

</div>
