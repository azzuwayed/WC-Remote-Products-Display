=== WCRPD - WC Remote Products Display ===
Contributors: Abdullah Alzuwayed
Tags: woocommerce, remote, products, display, cross-site, API
Requires at least: 6.0
Tested up to: 6.3.1
Stable tag: 1.0.0
Requires PHP: 7.4
License: GNU General Public License v3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Showcase WooCommerce products on another WordPress website effortlessly.

== Description ==

WCRPD allows you to effortlessly display WooCommerce products on any WordPress website, eliminating the need for a separate WooCommerce installation. Configure your settings, and then use the `[wcrpd]` shortcode to display products in posts, pages, or widgets.

**Why Choose WCRPD?**
- No need for multiple WooCommerce installations.
- Ideal for advertising products from another store.
- Enhances SEO and user experience by reducing server load.

**Use Cases**
- Promote another store's products on your website.
- Run a lightweight homepage with minimal plugins while your shop resides on a subdomain.

**Core Features**
- Use the `[wcrpd]` shortcode to place products wherever you like.
- Secure connection via the WooCommerce REST API.
- Fetches only "Published" and "Visible" products.
- Auto-links each product back to its original page.

**Advanced Features**
- Limit the number of products displayed per shortcode or globally.
- Filter products by categories per shortcode or globally.
- Localize or change any text via the Loco Translate plugin.

**UI/UX Enhancements**
- Fully responsive product cards for all devices.
- Customizable CSS classes for individual elements:
  - `.wcrpd-products-wrapper`
  - `.wcrpd-product-card`
  - And more...
- Use Elementor or similar page builders for further customization.

**Performance Optimizations**
- Caching for quick data retrieval.
- Rate and timeout limiting for API calls.
- Comprehensive logs for debugging.
- Clean uninstallation: All data traces are removed.

== Installation ==

1. Upload `wcrpd` to `/wp-content/plugins/`.
2. Activate via the 'Plugins' menu in WordPress.
3. Navigate to Settings->WCRPD to configure.
4. Use `[wcrpd]` or `[wcrpd count_limit="4"]` to display products.

== Troubleshooting ==
- If you encounter issues, enable the debug option from plugin settings. Make sure WordPress debug and debug logging are enabled.
- Try to flush cache or reset plugin which are located in the debug settings page.
- For further assistance, visit our support forum.

== Frequently Asked Questions ==

= Do I need WooCommerce installed? =
No, that's the beauty of WCRPD.

= Can I use multiple `[wcrpd]` shortcodes? =
Absolutely, each with its own settings.

== Credits ==
Thanks to the WordPress and WooCommerce communities for their invaluable contributions.

== Screenshots ==

1. Admin Page - API Connection Settings
2. Admin Page - Display Settings (card elements)
3. Admin Page - Display Settings (filters)
4. Admin Page - Debug Settings
5. How to add [wcrpd] shortcode
6. Products display sample (all options)
7. Products display sample (only image and title)
8. How to generate WooCommerce API keys (step 1)
9. How to generate WooCommerce API keys (step 2)

== Changelog ==
= 1.0 =
- Initial release.

== Upgrade Notice ==
= 1.0 =
- First release. No changes yet, but stay tuned for updates.
