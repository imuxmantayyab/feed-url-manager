<?php
/**
 * General Settings Tab View.
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
	<input type="hidden" name="fwm_current_tab" value="general">

	<div class="fwm-card">
		<div class="fwm-card-header">
			<h2><?php esc_html_e( 'Level 1: Global Feed Management', 'feed-url-manager' ); ?></h2>
		</div>
		<div class="fwm-card-body">
			<p class="fwm-section-description">
				<?php esc_html_e( 'The global master toggle immediately blocks every native WordPress RSS, Atom, RDF, comments, taxonomy, author, and archive feed across the entire site.', 'feed-url-manager' ); ?>
			</p>

			<div class="fwm-setting-row fwm-highlight-row">
				<div class="fwm-setting-info">
					<label for="fwm_disable_all_feeds" class="fwm-setting-label">
						<strong><?php esc_html_e( 'Disable All WordPress Feeds', 'feed-url-manager' ); ?></strong>
					</label>
					<p class="fwm-setting-desc">
						<?php esc_html_e( 'When enabled, all incoming requests to WordPress feed endpoints will be intercepted and responded to with the configured HTTP status code (Default: 404 Not Found).', 'feed-url-manager' ); ?>
					</p>
				</div>
				<div class="fwm-setting-control">
					<label class="fwm-toggle-switch">
						<input type="checkbox" id="fwm_disable_all_feeds" name="fwm_settings[disable_all_feeds]" value="1" <?php checked( ! empty( $settings['disable_all_feeds'] ) ); ?>>
						<span class="fwm-slider"></span>
					</label>
				</div>
			</div>

			<div class="fwm-callout fwm-callout-info">
				<h4><?php esc_html_e( 'What does the Global Master Toggle Cover?', 'feed-url-manager' ); ?></h4>
				<ul class="fwm-feature-list">
					<li><code>/feed/</code>, <code>/feed/rss/</code>, <code>/feed/rss2/</code>, <code>/feed/atom/</code>, <code>/feed/rdf/</code></li>
					<li><code>/comments/feed/</code> & singular post comments feeds</li>
					<li><code>/?feed=rss</code>, <code>/?feed=rss2</code>, <code>/?feed=atom</code>, <code>/?feed=rdf</code></li>
					<li>All category, tag, author, and date archive feeds</li>
					<li>All Custom Post Type (CPT) and Custom Taxonomy feeds</li>
				</ul>
				<p class="fwm-text-sm fwm-mt-2">
					<strong><?php esc_html_e( 'Explicit Exclusions:', 'feed-url-manager' ); ?></strong>
					<?php esc_html_e( 'XML Sitemaps (/wp-sitemap.xml), REST API (/wp-json/), Elementor assets/editor/AJAX, and regular web pages are never blocked.', 'feed-url-manager' ); ?>
				</p>
			</div>
		</div>
		<div class="fwm-card-footer">
			<button type="submit" class="button button-primary button-hero">
				<?php esc_html_e( 'Save General Settings', 'feed-url-manager' ); ?>
			</button>
		</div>
	</div>
</form>
