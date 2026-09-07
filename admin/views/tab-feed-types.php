<?php
/**
 * Feed Types Settings Tab View.
 *
 * @package FeedURLManagerPro
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$disabled_feed_types = isset( $settings['disabled_feed_types'] ) && is_array( $settings['disabled_feed_types'] ) ? $settings['disabled_feed_types'] : array();
$disabled_formats    = isset( $settings['disabled_formats'] ) && is_array( $settings['disabled_formats'] ) ? $settings['disabled_formats'] : array();
?>

<form method="post" action="<?php echo esc_url( admin_url( 'options-general.php?page=feed-url-manager' ) ); ?>">
	<?php wp_nonce_field( 'fwm_save_settings_action', 'fwm_save_settings_nonce' ); ?>
	<input type="hidden" name="fwm_current_tab" value="feed_types">

	<div class="fwm-card">
		<div class="fwm-card-header">
			<h2><?php esc_html_e( 'Level 2: Core Feed Types', 'feed-url-manager' ); ?></h2>
		</div>
		<div class="fwm-card-body">
			<p class="fwm-section-description">
				<?php esc_html_e( 'Selectively disable specific core feed types while keeping other feeds functional.', 'feed-url-manager' ); ?>
			</p>

			<div class="fwm-settings-table">
				<!-- Main Feed -->
				<div class="fwm-setting-row">
					<div class="fwm-setting-info">
						<label for="fwm_feed_main" class="fwm-setting-label"><?php esc_html_e( 'Main Site Feed', 'feed-url-manager' ); ?></label>
						<p class="fwm-setting-desc"><?php esc_html_e( 'Blocks the primary site feed endpoints: /feed/, /feed/rss2/, and /?feed=rss2.', 'feed-url-manager' ); ?></p>
					</div>
					<div class="fwm-setting-control">
						<label class="fwm-toggle-switch">
							<input type="checkbox" id="fwm_feed_main" name="fwm_settings[disabled_feed_types][]" value="main" <?php checked( in_array( 'main', $disabled_feed_types, true ) ); ?>>
							<span class="fwm-slider"></span>
						</label>
					</div>
				</div>

				<!-- Comments Feed -->
				<div class="fwm-setting-row">
					<div class="fwm-setting-info">
						<label for="fwm_feed_comments" class="fwm-setting-label"><?php esc_html_e( 'Comments Feed', 'feed-url-manager' ); ?></label>
						<p class="fwm-setting-desc"><?php esc_html_e( 'Blocks site-wide and post-level comments feeds: /comments/feed/, /?feed=comments-rss2, and /post-slug/feed/.', 'feed-url-manager' ); ?></p>
					</div>
					<div class="fwm-setting-control">
						<label class="fwm-toggle-switch">
							<input type="checkbox" id="fwm_feed_comments" name="fwm_settings[disabled_feed_types][]" value="comments" <?php checked( in_array( 'comments', $disabled_feed_types, true ) ); ?>>
							<span class="fwm-slider"></span>
						</label>
					</div>
				</div>

				<!-- Author Feeds -->
				<div class="fwm-setting-row">
					<div class="fwm-setting-info">
						<label for="fwm_feed_author" class="fwm-setting-label"><?php esc_html_e( 'Author Feeds', 'feed-url-manager' ); ?></label>
						<p class="fwm-setting-desc"><?php esc_html_e( 'Blocks author-specific feed archives: /author/username/feed/.', 'feed-url-manager' ); ?></p>
					</div>
					<div class="fwm-setting-control">
						<label class="fwm-toggle-switch">
							<input type="checkbox" id="fwm_feed_author" name="fwm_settings[disabled_feed_types][]" value="author" <?php checked( in_array( 'author', $disabled_feed_types, true ) ); ?>>
							<span class="fwm-slider"></span>
						</label>
					</div>
				</div>

				<!-- Date Archive Feeds -->
				<div class="fwm-setting-row">
					<div class="fwm-setting-info">
						<label for="fwm_feed_date" class="fwm-setting-label"><?php esc_html_e( 'Date Archive Feeds', 'feed-url-manager' ); ?></label>
						<p class="fwm-setting-desc"><?php esc_html_e( 'Blocks year/month/day archive feeds: /2026/09/feed/.', 'feed-url-manager' ); ?></p>
					</div>
					<div class="fwm-setting-control">
						<label class="fwm-toggle-switch">
							<input type="checkbox" id="fwm_feed_date" name="fwm_settings[disabled_feed_types][]" value="date" <?php checked( in_array( 'date', $disabled_feed_types, true ) ); ?>>
							<span class="fwm-slider"></span>
						</label>
					</div>
				</div>

				<!-- Search Results Feeds -->
				<div class="fwm-setting-row">
					<div class="fwm-setting-info">
						<label for="fwm_feed_search" class="fwm-setting-label"><?php esc_html_e( 'Search Results Feeds', 'feed-url-manager' ); ?></label>
						<p class="fwm-setting-desc"><?php esc_html_e( 'Blocks search query feeds: /?s=search-term&feed=rss2.', 'feed-url-manager' ); ?></p>
					</div>
					<div class="fwm-setting-control">
						<label class="fwm-toggle-switch">
							<input type="checkbox" id="fwm_feed_search" name="fwm_settings[disabled_feed_types][]" value="search" <?php checked( in_array( 'search', $disabled_feed_types, true ) ); ?>>
							<span class="fwm-slider"></span>
						</label>
					</div>
				</div>
			</div>

			<hr class="fwm-divider">

			<h3 class="fwm-subheading"><?php esc_html_e( 'Legacy Feed Formats', 'feed-url-manager' ); ?></h3>
			<p class="fwm-section-description">
				<?php esc_html_e( 'Disable outdated feed protocol standards without affecting modern RSS 2.0 feeds.', 'feed-url-manager' ); ?>
			</p>

			<div class="fwm-settings-table">
				<!-- RDF / RSS 1.0 -->
				<div class="fwm-setting-row">
					<div class="fwm-setting-info">
						<label for="fwm_format_rdf" class="fwm-setting-label"><?php esc_html_e( 'RDF / RSS 1.0 (/feed/rdf/)', 'feed-url-manager' ); ?></label>
						<p class="fwm-setting-desc"><?php esc_html_e( 'Blocks RDF Resource Description Framework feeds.', 'feed-url-manager' ); ?></p>
					</div>
					<div class="fwm-setting-control">
						<label class="fwm-toggle-switch">
							<input type="checkbox" id="fwm_format_rdf" name="fwm_settings[disabled_formats][]" value="rdf" <?php checked( in_array( 'rdf', $disabled_formats, true ) ); ?>>
							<span class="fwm-slider"></span>
						</label>
					</div>
				</div>

				<!-- Atom -->
				<div class="fwm-setting-row">
					<div class="fwm-setting-info">
						<label for="fwm_format_atom" class="fwm-setting-label"><?php esc_html_e( 'Atom (/feed/atom/)', 'feed-url-manager' ); ?></label>
						<p class="fwm-setting-desc"><?php esc_html_e( 'Blocks Atom Syndication Format feeds.', 'feed-url-manager' ); ?></p>
					</div>
					<div class="fwm-setting-control">
						<label class="fwm-toggle-switch">
							<input type="checkbox" id="fwm_format_atom" name="fwm_settings[disabled_formats][]" value="atom" <?php checked( in_array( 'atom', $disabled_formats, true ) ); ?>>
							<span class="fwm-slider"></span>
						</label>
					</div>
				</div>

				<!-- RSS 0.92 -->
				<div class="fwm-setting-row">
					<div class="fwm-setting-info">
						<label for="fwm_format_rss" class="fwm-setting-label"><?php esc_html_e( 'RSS 0.92 (/feed/rss/)', 'feed-url-manager' ); ?></label>
						<p class="fwm-setting-desc"><?php esc_html_e( 'Blocks legacy RSS 0.92 feeds.', 'feed-url-manager' ); ?></p>
					</div>
					<div class="fwm-setting-control">
						<label class="fwm-toggle-switch">
							<input type="checkbox" id="fwm_format_rss" name="fwm_settings[disabled_formats][]" value="rss" <?php checked( in_array( 'rss', $disabled_formats, true ) ); ?>>
							<span class="fwm-slider"></span>
						</label>
					</div>
				</div>
			</div>
		</div>
		<div class="fwm-card-footer">
			<button type="submit" class="button button-primary button-hero">
				<?php esc_html_e( 'Save Feed Type Settings', 'feed-url-manager' ); ?>
			</button>
		</div>
	</div>
</form>
