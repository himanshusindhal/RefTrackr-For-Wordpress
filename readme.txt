=== RefTrackr ===
Contributors: sindhalhimanshu
Tags: woocommerce, influencer, referral, tracking, coupon
Requires at least: 5.8
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Track influencer-driven sales using referral links and coupon codes for WooCommerce.

== Description ==

RefTrackr is a WooCommerce influencer tracking plugin that helps brands and store owners track influencer-driven sales using referral links and coupon codes.

**Core Features:**

* **Influencer Management** — Add, edit, pause, and manage your influencers with ease.
* **Referral Link Tracking** — Generate unique referral links (e.g., yoursite.com/?ref=sara) and track clicks.
* **Coupon Attribution** — Assign coupon codes to influencers and automatically attribute sales.
* **Order Tracking** — Track products sold, order amounts, customer city/state, and referral sources.
* **Analytics Dashboard** — Beautiful SaaS-style dashboard with revenue charts, leaderboards, and key metrics.
* **Geographic Tracking** — See which cities and states your influencer-driven orders come from.
* **Reports** — Revenue by influencer, geographic breakdown, and product performance reports.
* **Configurable Settings** — Set cookie duration, toggle tracking, and customize attribution rules.

**Attribution Priority:**

1. Coupon code (highest priority)
2. Referral cookie
3. Last referral click

**Requirements:**

* WordPress 5.8 or higher
* WooCommerce 5.0 or higher
* PHP 7.4 or higher

== Installation ==

1. Upload the `reftrackr` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Make sure WooCommerce is installed and active.
4. Navigate to **RefTrackr** in the admin sidebar to access the dashboard.
5. Add your first influencer and start tracking!

== Frequently Asked Questions ==

= Does this plugin require WooCommerce? =

Yes, RefTrackr requires WooCommerce to be installed and active. It tracks WooCommerce orders attributed to influencers.

= How does referral tracking work? =

When a visitor clicks a referral link (e.g., yoursite.com/?ref=sara), a cookie is stored in their browser. If they make a purchase within the cookie duration period, the order is attributed to that influencer.

= What is the default cookie duration? =

The default cookie duration is 7 days. You can change this in RefTrackr > Settings.

= Can an influencer have both a referral link and a coupon code? =

Yes. Each influencer can have a unique referral slug and an assigned coupon code. Coupon attribution takes priority over referral cookie attribution.

= Does RefTrackr store personal data? =

RefTrackr stores minimal data. IP addresses are hashed for privacy. Customer city and state are derived from WooCommerce order data. No invasive tracking is used.

= Is this plugin GDPR compliant? =

RefTrackr uses cookies for referral tracking. The cookie duration is configurable and can be disclosed in your privacy policy. No sensitive personal data is stored.

== Changelog ==

= 1.0.0 =
* Initial release.
* Influencer management (add, edit, delete, pause).
* Referral link tracking with configurable cookie duration.
* Coupon code attribution.
* WooCommerce order tracking.
* Analytics dashboard with revenue charts.
* Influencer leaderboard.
* Geographic sales tracking.
* Reports page.
* Settings page.

== Upgrade Notice ==

= 1.0.0 =
Initial release of RefTrackr.
