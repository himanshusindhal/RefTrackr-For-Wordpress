<?php
/**
 * Add/Edit influencer form template.
 *
 * @package RefTrackr
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

// phpcs:ignorefile WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

$editing    = false;
$influencer = null;

if ( isset( $_GET['action'] ) && 'edit' === sanitize_text_field( wp_unslash( $_GET['action'] ) ) && isset( $_GET['id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
	$db         = new RefTrackr_DB();
	$influencer = $db->get_influencer( absint( $_GET['id'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
	if ( $influencer ) {
		$editing = true;
	}
}
?>

<div class="reftrackr-wrap">

    <div class="reftrackr-header">
        <h1 class="reftrackr-page-title">
            <?php echo $editing ? esc_html__( 'Edit Influencer', 'reftrackr' ) : esc_html__( 'Add New Influencer', 'reftrackr' ); ?>
        </h1>
        <div class="reftrackr-header-actions">
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=reftrackr-influencers' ) ); ?>" class="reftrackr-btn reftrackr-btn--secondary">
                <span class="dashicons dashicons-arrow-left-alt"></span>
                <?php esc_html_e( 'Back to Influencers', 'reftrackr' ); ?>
            </a>
        </div>
    </div>

    <div class="reftrackr-section">
        <div id="reftrackr-form-notice"></div>

        <form id="reftrackr-influencer-form" class="reftrackr-form">
            <?php wp_nonce_field( 'reftrackr_influencer_nonce', 'reftrackr_nonce' ); ?>

            <?php if ( $editing ) : ?>
                <input type="hidden" name="influencer_id" value="<?php echo esc_attr( $influencer->id ); ?>" />
            <?php endif; ?>

            <div class="reftrackr-form-row">
                <div class="reftrackr-form-group">
                    <label class="reftrackr-form-label" for="reftrackr-name">
                        <?php esc_html_e( 'Name', 'reftrackr' ); ?> <span class="required">*</span>
                    </label>
                    <input type="text" id="reftrackr-name" name="name" class="reftrackr-form-input" required
                           value="<?php echo $editing ? esc_attr( $influencer->name ) : ''; ?>"
                           placeholder="<?php esc_attr_e( 'e.g. Sara Johnson', 'reftrackr' ); ?>" />
                </div>
                <div class="reftrackr-form-group">
                    <label class="reftrackr-form-label" for="reftrackr-email">
                        <?php esc_html_e( 'Email', 'reftrackr' ); ?>
                    </label>
                    <input type="email" id="reftrackr-email" name="email" class="reftrackr-form-input"
                           value="<?php echo $editing ? esc_attr( $influencer->email ) : ''; ?>"
                           placeholder="<?php esc_attr_e( 'sara@example.com', 'reftrackr' ); ?>" />
                    <p class="reftrackr-form-help"><?php esc_html_e( 'Used for Gravatar avatar display.', 'reftrackr' ); ?></p>
                </div>
            </div>

            <div class="reftrackr-form-row">
                <div class="reftrackr-form-group">
                    <label class="reftrackr-form-label" for="reftrackr-instagram">
                        <?php esc_html_e( 'Instagram Handle', 'reftrackr' ); ?>
                    </label>
                    <input type="text" id="reftrackr-instagram" name="instagram_handle" class="reftrackr-form-input"
                           value="<?php echo $editing ? esc_attr( $influencer->instagram_handle ) : ''; ?>"
                           placeholder="<?php esc_attr_e( '@saracreates', 'reftrackr' ); ?>" />
                </div>
                <div class="reftrackr-form-group">
                    <label class="reftrackr-form-label" for="reftrackr-slug">
                        <?php esc_html_e( 'Referral Slug', 'reftrackr' ); ?> <span class="required">*</span>
                    </label>
                    <input type="text" id="reftrackr-slug" name="referral_slug" class="reftrackr-form-input" required
                           value="<?php echo $editing ? esc_attr( $influencer->referral_slug ) : ''; ?>"
                           placeholder="<?php esc_attr_e( 'sara', 'reftrackr' ); ?>" />
                    <p class="reftrackr-form-help" id="reftrackr-slug-preview">
                        <?php
                        if ( $editing && ! empty( $influencer->referral_slug ) ) {
                            printf(
                                /* translators: %s: referral URL */
                                esc_html__( 'Referral URL: %s', 'reftrackr' ),
                                esc_url( site_url( '/?ref=' . $influencer->referral_slug ) )
                            );
                        } else {
                            esc_html_e( 'Auto-generated from name if left empty.', 'reftrackr' );
                        }
                        ?>
                    </p>
                </div>
            </div>

            <div class="reftrackr-form-row">
                <div class="reftrackr-form-group">
                    <label class="reftrackr-form-label" for="reftrackr-coupon">
                        <?php esc_html_e( 'Coupon Code', 'reftrackr' ); ?>
                    </label>
                    <input type="text" id="reftrackr-coupon" name="coupon_code" class="reftrackr-form-input"
                           value="<?php echo $editing ? esc_attr( $influencer->coupon_code ) : ''; ?>"
                           placeholder="<?php esc_attr_e( 'SARA20', 'reftrackr' ); ?>" />
                    <p class="reftrackr-form-help"><?php esc_html_e( 'Must match an existing WooCommerce coupon code.', 'reftrackr' ); ?></p>
                </div>
            </div>

            <div class="reftrackr-form-actions">
                <button type="submit" class="reftrackr-btn reftrackr-btn--primary" id="reftrackr-submit-btn">
                    <?php echo $editing ? esc_html__( 'Update Influencer', 'reftrackr' ) : esc_html__( 'Add Influencer', 'reftrackr' ); ?>
                </button>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=reftrackr-influencers' ) ); ?>" class="reftrackr-btn reftrackr-btn--secondary">
                    <?php esc_html_e( 'Cancel', 'reftrackr' ); ?>
                </a>
            </div>
        </form>
    </div>

</div>
