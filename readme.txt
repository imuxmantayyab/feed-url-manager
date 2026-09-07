=== Feed URL Manager Pro ===
Contributors: imuxmantayyab
Donate link: https://www.linkedin.com/in/imuxmantayyab/
Tags: rss, feed, disable feeds, atom, seo, elementor, feed manager, block rss
Requires at least: 5.8
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 2.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Production-grade WordPress RSS, Atom, RDF, and custom feed management system with multi-level control, SEO protection, and guaranteed Elementor compatibility.

== Description ==

**Feed URL Manager Pro** provides complete control over WordPress RSS, Atom, RDF, comments, taxonomy, author, post type, and custom feed URLs at the core request level.

Unlike superficial plugins that merely remove `<link>` tags in HTML `<head>`, Feed URL Manager Pro intercepts incoming HTTP requests directly, prevents feed template execution, emits proper HTTP status codes (404 Not Found, 410 Gone, or 301/302 Redirects), transmits `X-Robots-Tag: noindex, nofollow` headers, and cleanly halts execution before RSS XML or server resources are consumed.

It is built specifically for high-traffic websites, enterprise blogs, WooCommerce stores, and membership portals seeking to prevent content scrapers, resolve Google Search Console feed index bloat, and save server resources.

### 🚀 3 Levels of Granular Control

1. **Level 1 — Global Master Control:** Disable all WordPress feeds across the entire site in a single click (`/feed/`, `/comments/feed/`, `/?feed=rss2`, `/?feed=atom`, `/?feed=rdf`).
2. **Level 2 — Feed Type & Content Type:** Selectively disable feeds for specific public post types (Posts, Pages, Products, Portfolio), public taxonomies (Categories, Tags, Custom Taxonomies), Authors, Comments, or Date archives.
3. **Level 3 — Individual URL Rules Engine:** Create custom matching rules (Exact match, Contains, Wildcard `*`, and Regex) with custom HTTP actions (404, 410, 301, 302).

---

### 🛡️ Elementor & Elementor Pro 100% Compatibility Guarantee

Feed URL Manager Pro is engineered with strict passive isolation. It strictly executes only on verified WordPress feed queries and explicitly excludes:
* Elementor Editor (`/elementor/`, Canvas, Visual Builder)
* Elementor Preview Mode (`?elementor-preview=`)
* Elementor AJAX Actions (`action=elementor_ajax`)
* Elementor REST API endpoints
* Dynamic CSS stylesheet generation (`/uploads/elementor/`)
* Elementor Pro Theme Builder, Popups, Loop Grid, and Forms

---

### 🔍 Search Engine & SEO Protections

* **XML Sitemaps Preserved:** Explicitly isolates RSS/Atom feeds from XML sitemaps (`/wp-sitemap.xml`, Yoast, Rank Math, All in One SEO, SEOPress sitemaps are never blocked).
* **REST API Shielded:** WordPress REST API (`/wp-json/`) remains 100% functional.
* **Auto-Purge Feed Discovery:** Automatically unhooks `<link rel="alternate" type="application/rss+xml">` tags from HTML `<head>` for disabled feeds.
* **X-Robots-Tag Support:** Injects `X-Robots-Tag: noindex, nofollow` header on all blocked feed requests.

---

### 🛠️ Developer Hooks & Extensibility

Feed URL Manager Pro provides documented hooks for advanced customization:

#### 1. `fwm_is_feed_disabled` (Filter)
Override the final feed blocking decision.
```php
add_filter( 'fwm_is_feed_disabled', function( $is_disabled, $detection, $evaluation ) {
    // Keep feed active for specific trusted IP addresses or webhooks
    if ( isset( $_SERVER['REMOTE_ADDR'] ) && '192.168.1.50' === $_SERVER['REMOTE_ADDR'] ) {
        return false; // Do not block
    }
    return $is_disabled;
}, 10, 3 );
```

#### 2. `fwm_detected_feed_type` (Filter)
Filter or extend the feed detection metadata array.

#### 3. `fwm_feed_rule_result` (Filter)
Inspect or modify the rule matching outcome before response execution.

#### 4. `fwm_feed_response_code` (Filter)
Dynamically override the HTTP response status code (404, 410, 301, 302).

#### 5. `fwm_feed_redirect_url` (Filter)
Dynamically alter the redirection target URL for 301/302 redirects.

#### 6. `fwm_remove_feed_discovery` (Filter)
Control whether feed `<link>` tags are removed from `<head>`.

== Installation ==

1. Download or clone `feed-url-manager` to your `/wp-content/plugins/` directory (or upload `feed-url-manager.zip` via **Plugins > Add New > Upload Plugin**).
2. Activate the plugin through the **Plugins** menu in WordPress.
3. Navigate to **Settings > Feed URL Manager** in the WordPress admin panel.
4. Configure your preferred settings (e.g. toggle Global Disable or set selective rules).
5. If using a caching plugin or CDN (LiteSpeed, WP Rocket, Cloudflare), purge your cache to apply changes immediately.

== Frequently Asked Questions ==

= Does this plugin block my XML Sitemap (/wp-sitemap.xml)? =
No. RSS/Atom feeds are fundamentally different from XML Sitemaps. The detection engine explicitly detects and bypasses `/wp-sitemap.xml` and SEO plugin sitemaps (Yoast, Rank Math, AIOSEO).

= Will this break the Elementor visual editor or AJAX actions? =
No. Feed URL Manager Pro features a dedicated compatibility layer that automatically detects Elementor and Elementor Pro requests and ensures the editor, preview frames, and AJAX calls are completely untouched.

= Will search engines de-index my feed URLs? =
Yes. By returning proper HTTP 404 (Not Found) or 410 (Gone) status codes combined with the `X-Robots-Tag: noindex, nofollow` header, search engines will efficiently de-index previous feed URLs from search results.

= Does this plugin create custom database tables? =
No. Feed URL Manager Pro uses standard, lightweight WordPress options to store configuration (`fwm_settings` and `fwm_feed_rules`), keeping your database clean and fast.

= Is WordPress Multisite supported? =
Yes. Settings are managed per site by default to allow different rules across multiple network blogs.

== Screenshots ==

1. **Dashboard:** Live status overview, active protection badges, and execution hierarchy.
2. **General:** Level 1 Global master toggle for all WordPress feeds.
3. **Feed Types:** Granular control over main, comments, author, date, and search feeds.
4. **Post Types:** Dynamic public post type feed manager.
5. **Taxonomies:** Dynamic public taxonomy feed manager.
6. **URL Rules:** Custom URL matching engine with wildcard and regex support.
7. **Elementor Compatibility:** Live detection and subsystem verification matrix.
8. **Diagnostics & Feed Scanner:** Safe simulated feed inspection and debug logging.

== Changelog ==

= 2.0.0 =
* Major release: Complete architectural overhaul for Feed URL Manager Pro V2.
* Implemented 3-level feed management architecture (Global, Content Type, URL Rules).
* Added dynamic public Custom Post Type and Taxonomy detection.
* Added safe internal Feed Scanner and live HTTP single-endpoint tester.
* Added full Elementor and Elementor Pro compatibility layer with zero core modification.
* Added JSON configuration import/export and clean uninstall policy.
* Full PHP 8.0 - 8.3 compatibility.
