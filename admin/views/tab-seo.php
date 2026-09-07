<?php
/**
 * SEO Settings Tab View.
 *
 * @package FeedURLManagerPro
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<form method="post" action="<?php echo esc_url( admin_url( 'options-general.php?page=feed-url-manager' ) ); ?>">
	<?php wp_nonce_field( 'fwm_save_settings_action', 'fwm_save_settings_nonce' ); ?>
	<input type="hidden" name="fwm_current_tab" value="seo">

	<div class="fwm-card">
		<div class="fwm-card-header">
			<h2><?php esc_html_e( 'SEO & Search Engine Indexing Controls', 'feed-url-manager' ); ?></h2>
		</div>
		<div class="fwm-card-body">
			<p class="fwm-section-description">
				<?php esc_html_e( 'Control how search engines discover, crawl, and index feed endpoints across your website to prevent index bloat and scrapers.', 'feed-url-manager' ); ?>
			</p>

			<div class="fwm-settings-table">
				<!-- Remove Discovery Links -->
				<div class="fwm-setting-row">
					<div class="fwm-setting-info">
						<label for="fwm_remove_feed_discovery" class="fwm-setting-label">
							<strong><?php esc_html_e( 'Remove Feed Discovery Links from <head>', 'feed-url-manager' ); ?></strong>
						</label>
						<p class="fwm-setting-desc">
							<?php esc_html_e( 'Removes native <link rel="alternate" type="application/rss+xml"> tags from your HTML source, stopping search bots and feed readers from auto-discovering disabled feeds.', 'feed-url-manager' ); ?>
						</p>
					</div>
					<div class="fwm-setting-control">
						<label class="fwm-toggle-switch">
							<input type="checkbox" id="fwm_remove_feed_discovery" name="fwm_settings[remove_feed_discovery]" value="1" <?php checked( ! empty( $settings['remove_feed_discovery'] ) ); ?>>
							<span class="fwm-slider"></span>
						</label>
					</div>
				</div>

				<!-- X-Robots-Tag Header -->
				<div class="fwm-setting-row">
					<div class="fwm-setting-info">
						<label for="fwm_enable_x_robots_tag" class="fwm-setting-label">
							<strong><?php esc_html_e( 'Send X-Robots-Tag HTTP Header', 'feed-url-manager' ); ?></strong>
						</label>
						<p class="fwm-setting-desc">
							<?php esc_html_e( 'Transmits "X-Robots-Tag: noindex, nofollow" HTTP response headers when a blocked feed endpoint is requested, commanding search engines not to index the URL.', 'feed-url-manager' ); ?>
						</p>
					</div>
					<div class="fwm-setting-control">
						<label class="fwm-toggle-switch">
							<input type="checkbox" id="fwm_enable_x_robots_tag" name="fwm_settings[enable_x_robots_tag]" value="1" <?php checked( ! empty( $settings['enable_x_robots_tag'] ) ); ?>>
							<span class="fwm-slider"></span>
						</label>
					</div>
				</div>
			</div>

			<div class="fwm-callout fwm-callout-success fwm-mt-4">
				<h4><?php esc_html_e( 'SEO Best Practices for Feeds', 'feed-url-manager' ); ?></h4>
				<ul class="fwm-feature-list">
					<li><strong><?php esc_html_e( 'Preventing Index Bloat:', 'feed-url-manager' ); ?></strong> <?php esc_html_e( 'WordPress generates hundreds of feed URLs (categories, tags, comments, author pages). Blocking them prevents Google from indexing low-value duplicate RSS XML.', 'feed-url-manager' ); ?></li>
					<li><strong><?php esc_html_e( 'Scraper Defense:', 'feed-url-manager' ); ?></strong> <?php esc_html_e( 'Automated content scrapers scrape /feed/ endpoints to steal fresh articles instantly. Disabling feeds removes their primary extraction vector.', 'feed-url-manager' ); ?></li>
					<li><strong><?php esc_html_e( 'Crawl Budget Optimization:', 'feed-url-manager' ); ?></strong> <?php esc_html_e( 'Search engine bots focus crawl budget exclusively on your high-value canonical pages and sitemaps.', 'feed-url-manager' ); ?></li>
				</ul>
			</div>
		</div>
		<div class="fwm-card-footer">
			<button type="submit" class="button button-primary button-hero">
				<?php esc_html_e( 'Save SEO Settings', 'feed-url-manager' ); ?>
			</button>
		</div>
	</div>
</form>
