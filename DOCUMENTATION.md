# Feed URL Manager Pro (V2) — User & Administrator Guide

**Plugin Version:** 2.0.0  
**Author:** Usman Tayyab ([LinkedIn Profile](https://www.linkedin.com/in/imuxmantayyab/))  
**Text Domain / Plugin Slug:** `feed-url-manager`

---

## 📖 Table of Contents

1. [Introduction & Purpose](#1-introduction--purpose)
2. [Why Manage WordPress Feeds?](#2-why-manage-wordpress-feeds)
3. [Installation & Setup](#3-installation--setup)
4. [3 Levels of Feed Control Explained](#4-3-levels-of-feed-control-explained)
5. [Complete Admin Settings Guide (Tab by Tab)](#5-complete-admin-settings-guide-tab-by-tab)
   - [Tab 1: Dashboard](#tab-1-dashboard)
   - [Tab 2: General (Global Control)](#tab-2-general-global-control)
   - [Tab 3: Feed Types](#tab-3-feed-types)
   - [Tab 4: Post Types](#tab-4-post-types)
   - [Tab 5: Taxonomies](#tab-5-taxonomies)
   - [Tab 6: URL Rules](#tab-6-url-rules)
   - [Tab 7: SEO Controls](#tab-7-seo-controls)
   - [Tab 8: HTTP Response](#tab-8-http-response)
   - [Tab 9: Elementor Compatibility](#tab-9-elementor-compatibility)
   - [Tab 10: Diagnostics & Tools](#tab-10-diagnostics--tools)
6. [Real-World Use Cases & Recommended Setups](#6-real-world-use-cases--recommended-setups)
   - [Use Case A: Standard Business or Corporate Website](#use-case-a-standard-business-or-corporate-website)
   - [Use Case B: WooCommerce & E-Commerce Store](#use-case-b-woocommerce--e-commerce-store)
   - [Use Case C: Content Site Protecting Against Scrapers](#use-case-c-content-site-protecting-against-scrapers)
7. [Elementor & Elementor Pro Compatibility](#7-elementor--elementor-pro-compatibility)
8. [Caching & CDN Purging Instructions](#8-caching--cdn-purging-instructions)
9. [Troubleshooting & Frequently Asked Questions (FAQ)](#9-troubleshooting--frequently-asked-questions-faq)
10. [Developer Filters & Customization](#10-developer-filters--customization)

---

## 1. Introduction & Purpose

**Feed URL Manager Pro** provides complete, granular control over native WordPress RSS, Atom, RDF, comments, taxonomy, author, post type, and custom feed URLs.

Unlike simple snippets or plugins that merely remove `<link>` tags in your website's `<head>`, Feed URL Manager Pro works at the **WordPress core request level**. When a feed request is blocked:
- It terminates the request cleanly before any feed templates or RSS XML are rendered.
- It returns standard, SEO-compliant HTTP status codes (`404 Not Found`, `410 Gone`, or `301/302 Redirects`).
- It injects `X-Robots-Tag: noindex, nofollow` HTTP headers so search engine bots immediately purge feed URLs from their index.
- It saves significant server memory and CPU by preventing unnecessary database queries.

---

## 2. Why Manage WordPress Feeds?

By default, WordPress automatically creates hundreds (or thousands) of RSS and Atom feed endpoints:
- Main posts feed (`/feed/`)
- Site-wide and per-post comments feeds (`/comments/feed/`, `/post-slug/feed/`)
- Category and Tag feeds (`/category/news/feed/`, `/tag/design/feed/`)
- Author archive feeds (`/author/admin/feed/`)
- Date archives and search result feeds (`/2026/09/feed/`, `/?s=search&feed=rss2`)
- Custom post type & custom taxonomy feeds (`/products/feed/`, `/services/feed/`)

### Key Problems Solved by Feed URL Manager Pro:
1. **Search Engine Index Bloat:** Search engines frequently index duplicate RSS XML feeds instead of canonical web pages, diluting page ranking and wasting crawl budget.
2. **Automated Content Scraping:** Content scraping bots monitor `/feed/` to automatically copy your new articles the second they are published.
3. **Server Resource Conservation:** Automated feed readers and bots constantly polling RSS endpoints consume valuable PHP/MySQL capacity.

---

## 3. Installation & Setup

### Option 1: Install from WordPress Admin
1. Go to **WordPress Admin > Plugins > Add New**.
2. Click **Upload Plugin** at the top.
3. Choose the `feed-url-manager.zip` file and click **Install Now**.
4. Click **Activate Plugin**.

### Option 2: Manual FTP / Direct Installation
1. Upload the `feed-url-manager` folder to your server directory: `/wp-content/plugins/`.
2. Go to **Plugins > Installed Plugins** and activate **Feed URL Manager Pro**.

### Accessing Settings
Once activated, navigate to:
👉 **WordPress Admin > Settings > Feed URL Manager**

---

## 4. 3 Levels of Feed Control Explained

Feed URL Manager Pro organizes feed management into 3 intuitive levels:

```
┌────────────────────────────────────────────────────────┐
│  Level 1: Global Master Switch                         │
│  Disable all WordPress native feeds in one click.      │
└───────────────────────────┬────────────────────────────┘
                            │
┌───────────────────────────▼────────────────────────────┐
│  Level 2: Content Type & Taxonomy Selection            │
│  Selectively disable Post Types, Taxonomies, Authors,  │
│  Comments, Date archives, or outdated formats.         │
└───────────────────────────┬────────────────────────────┘
                            │
┌───────────────────────────▼────────────────────────────┐
│  Level 3: Custom URL Rules Engine                      │
│  Create targeted rules for specific feed URLs using    │
│  Exact match, Contains, Wildcards (*), or Regex.       │
└────────────────────────────────────────────────────────┘
```

### Execution Priority Hierarchy:
When a visitor or bot visits a URL, the plugin checks rules in strict order:
1. **Specific URL Rule:** (Highest Priority — overrides everything below)
2. **Custom Post Type Rule:** (Checks if the post type's feed is blocked)
3. **Custom Taxonomy Rule:** (Checks if the taxonomy's feed is blocked)
4. **Feed Type Rule:** (Checks if main, comments, author, date, or format is blocked)
5. **Global Master Rule:** (Checks if Global Blocking is enabled)
6. **Allow Request:** (Default fallback if no blocking rule matches)

---

## 5. Complete Admin Settings Guide (Tab by Tab)

### Tab 1: Dashboard
- **System Feed Status Overview:** Displays at a glance whether feeds are `Allowed`, `Filtered (Selective)`, or `Blocked Globally`.
- **Feed Type Protection Checklist:** Live checklist showing real-time status of Main, Comments, Categories, Tags, Authors, Custom Post Types, and Custom Taxonomies.
- **Quick Stats:** Displays the count of active URL rules, disabled post types, and disabled taxonomies.
- **Execution Hierarchy Chart:** Clear visual reference of rule priority order.

---

### Tab 2: General (Global Control)
- **Disable All WordPress Feeds (Toggle):** 
  - **OFF (Default):** Normal WordPress feed behavior or selective rules apply.
  - **ON:** Instantly disables all native WordPress feed endpoints across the entire site (`/feed/`, `/comments/feed/`, `/?feed=rss2`, `/?feed=atom`, `/?feed=rdf`, categories, tags, authors, etc.).
- **Preservation Assurance:** XML Sitemaps (`/wp-sitemap.xml`), REST API (`/wp-json/`), Elementor, and regular HTML web pages remain 100% active and protected.

---

### Tab 3: Feed Types
Allows selective disabling of core WordPress feed types:
- **Main Site Feed:** Blocks `/feed/`, `/feed/rss2/`, and `/?feed=rss2`.
- **Comments Feed:** Blocks site comments `/comments/feed/` and single post comments feeds.
- **Author Feeds:** Blocks author archive feeds `/author/username/feed/`.
- **Date Archive Feeds:** Blocks year/month/day feeds (e.g. `/2026/09/feed/`).
- **Search Results Feeds:** Blocks search query feeds `/?s=query&feed=rss2`.
- **Legacy Formats:** Disable obsolete formats:
  - RDF / RSS 1.0 (`/feed/rdf/`)
  - Atom (`/feed/atom/`)
  - RSS 0.92 (`/feed/rss/`)

---

### Tab 4: Post Types
- **Dynamic Post Type Discovery:** Automatically lists all public post types registered on your site (e.g., *Posts*, *Pages*, *Products*, *Portfolio*, *Services*, *Events*, etc.).
- **Individual Toggles:** Toggle feed blocking on or off for each post type individually.
- **Published Count:** Shows published entry count for reference.

---

### Tab 5: Taxonomies
- **Dynamic Taxonomy Discovery:** Automatically detects all registered public taxonomies (e.g., *Categories*, *Post Tags*, *Product Categories*, *Project Types*, etc.).
- **Individual Toggles:** Toggle feed blocking across all terms of any specific taxonomy.

---

### Tab 6: URL Rules
The interactive Rules Engine allows you to define targeted rules for specific feed URLs.

#### How to Add a Custom Rule:
1. Click the **"+ Add Feed Rule"** button.
2. In the modal, configure:
   - **URL Pattern:** Enter the path (e.g., `/category/news/feed/` or `/services/*/feed/`).
   - **Match Type:**
     - `Exact Match`: Targets the exact URL path.
     - `Contains`: Matches any URL containing the specified substring.
     - `Wildcard`: Uses `*` to match variable segments (e.g., `/category/*/feed/`).
     - `Regular Expression (Regex)`: Advanced pattern matching for power users.
   - **HTTP Action:**
     - `404 Not Found`: Returns standard 404 error (Best for SEO).
     - `410 Gone`: Informs bots the resource is permanently deleted.
     - `301 Permanent Redirect`: Redirects visitor/bot to a specified URL.
     - `302 Temporary Redirect`: Temporarily redirects to a specified URL.
   - **Redirect Destination:** Specify where to redirect if 301/302 is chosen.
   - **Status:** Set to `Active` or `Disabled`.
3. Click **Save Rule**.

---

### Tab 7: SEO Controls
- **Remove Feed Discovery Links from `<head>` (Recommended: ON):** Automatically removes `<link rel="alternate" type="application/rss+xml">` tags from your HTML source, stopping search engines from discovering disabled feeds.
- **Send X-Robots-Tag HTTP Header (Recommended: ON):** Sends `X-Robots-Tag: noindex, nofollow` HTTP response headers on blocked feeds to command search bots to purge the URL from indexes.

---

### Tab 8: HTTP Response
- **Default Block Action:** Choose what status code the server returns when a feed is blocked:
  - `404 Not Found` *(Recommended)*
  - `410 Gone`
  - `301 Permanent Redirect`
  - `302 Temporary Redirect`
- **Default Redirect Destination:** The fallback destination URL if 301 or 302 redirects are selected.

---

### Tab 9: Elementor Compatibility
- **Status Dashboard:** Shows real-time detection for *Elementor* and *Elementor Pro*.
- **Subsystem Verification:** Confirms that Elementor Visual Editor, Live Preview (`?elementor-preview=`), Elementor AJAX, REST endpoints, Dynamic CSS, Theme Builder, Popups, and Forms are strictly shielded from feed blocking.

---

### Tab 10: Diagnostics & Tools
- **Feed Scanner:** Analyzes your site's standard and dynamic feed endpoints and calculates their expected HTTP response using active rules (zero heavy external requests).
- **Single URL Live HTTP Tester:** Enter any URL to send a safe, rate-limited test request and verify the exact live HTTP status code and `X-Robots-Tag` headers.
- **Debug Mode & Request Inspector:** Enable to log the last 100 blocked feed requests with timestamps, IP addresses, matching rules, and status codes.
- **System Diagnostics:** Technical report of WordPress version, PHP, Permalinks, Server Software, and Theme for support. Click **"Copy Diagnostics"** for easy sharing.
- **Config Export / Import:** Download a JSON snapshot of your rules or import configuration onto another WordPress site.
- **Keep Settings on Uninstall:** Option to keep or permanently delete database settings upon removing the plugin.

---

## 6. Real-World Use Cases & Recommended Setups

### Use Case A: Standard Business or Corporate Website
**Goal:** Disable all RSS feeds completely, eliminate crawl waste in Google Search Console, and prevent content scrapers.
- **General Tab:** Turn `Disable All WordPress Feeds` **ON**.
- **SEO Tab:** Keep `Remove Feed Discovery Links` **ON** and `Send X-Robots-Tag` **ON**.
- **Response Tab:** Keep `404 Not Found` **Selected**.

---

### Use Case B: WooCommerce & E-Commerce Store
**Goal:** Allow main blog post feeds for subscribers, but disable WooCommerce product feeds, product category feeds, and product review comment feeds.
- **General Tab:** Keep `Disable All WordPress Feeds` **OFF**.
- **Post Types Tab:** Toggle **Products** to `Disable Feed`.
- **Taxonomies Tab:** Toggle **Product categories** and **Product tags** to `Disable Feed`.
- **Feed Types Tab:** Toggle **Comments Feed** to `Disable Feed`.

---

### Use Case C: Content Site Protecting Against Scrapers
**Goal:** Block the main `/feed/` and author feeds where scrapers pull articles, while allowing specific category feeds for partners via custom rules.
- **General Tab:** Keep `Disable All WordPress Feeds` **OFF**.
- **Feed Types Tab:** Disable `Main Site Feed` and `Author Feeds`.
- **URL Rules Tab:** Create specific rules with wildcard exceptions or custom actions for partner feeds.

---

## 7. Elementor & Elementor Pro Compatibility

Feed URL Manager Pro is engineered with **100% passive request isolation**:
- It never modifies any Elementor core files.
- It only triggers when WordPress confirms the request is genuinely a feed query.
- It explicitly detects and bypasses:
  - Elementor Editor (`/elementor/` and visual canvas)
  - Elementor Preview frames (`?elementor-preview=`)
  - Elementor AJAX calls (`action=elementor_ajax`)
  - Dynamic stylesheet regeneration (`/uploads/elementor/`)
  - Elementor Pro Popups, Theme Builder, and Forms

---

## 8. Caching & CDN Purging Instructions

> [!NOTE]
> **Important Cache Notice:**  
> Feed URL Manager Pro controls what your WordPress server generates dynamically. If a feed URL was previously visited and cached by your caching plugin (LiteSpeed, WP Rocket, WP Super Cache, W3 Total Cache) or CDN (Cloudflare, Fastly, BunnyCDN), external visitors will receive the cached static file until the cache expires.

### How to Apply Changes Immediately:
1. **WP Rocket / LiteSpeed / WP Super Cache:** Click **"Clear / Purge All Cache"** in your WordPress admin bar.
2. **Cloudflare:** Go to Cloudflare Dashboard > **Caching** > **Configuration** > Click **"Purge Everything"**.
3. **Browser Testing:** Open a private/incognito browser window and test your feed URL (e.g. `https://yoursite.com/feed/`) to verify the 404 response.

---

## 9. Troubleshooting & Frequently Asked Questions (FAQ)

### Q: Why does `/feed/` still load after I disabled feeds?
**A:** This is almost always caused by an external page cache or CDN cache. Please purge your WordPress page cache and CDN (Cloudflare), then test the URL in an Incognito/Private browsing window.

### Q: Will this block my XML Sitemap (`/wp-sitemap.xml`)?
**A:** No. The plugin explicitly distinguishes between RSS/Atom feeds and XML Sitemaps. XML Sitemaps (including Yoast, Rank Math, and All in One SEO sitemaps) will never be blocked.

### Q: Will this break my REST API endpoints?
**A:** No. Requests to `/wp-json/` are strictly exempted from feed blocking logic.

### Q: Why is 404 recommended over 301 Redirect for disabled feeds?
**A:** Redirecting feed URLs to your homepage causes Google Search Console to register "Soft 404" warnings and can lead to search bots repeatedly re-crawling the URL. Returning a true `404 Not Found` with `X-Robots-Tag: noindex, nofollow` instructs search engines to permanently remove the URL from their index.

---

## 10. Developer Filters & Customization

Developers can customize plugin behavior via WordPress filters:

### 1. `fwm_is_feed_disabled`
Override feed blocking decisions conditionally (e.g. allow specific IPs or API tokens):
```php
add_filter( 'fwm_is_feed_disabled', function( $is_disabled, $detection, $evaluation ) {
    // Whitelist a specific staging IP address or API client
    if ( isset( $_SERVER['REMOTE_ADDR'] ) && '203.0.113.195' === $_SERVER['REMOTE_ADDR'] ) {
        return false; // Do not block
    }
    return $is_disabled;
}, 10, 3 );
```

### 2. `fwm_detected_feed_type`
Modify feed detection parameters during request parsing.

### 3. `fwm_feed_response_code`
Dynamically alter the HTTP response code (404, 410, 301, 302).

### 4. `fwm_feed_redirect_url`
Dynamically change the redirection destination URL for 301/302 redirects.

### 5. `fwm_remove_feed_discovery`
Programmatically control whether `<link rel="alternate">` tags are removed from HTML `<head>`.

---

*Feed URL Manager Pro — Built by Usman Tayyab.*
