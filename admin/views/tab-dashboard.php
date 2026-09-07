<?php
/**
 * Dashboard Tab View.
 *
 * @package FeedURLManagerPro
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rules_count           = count( $rules );
$active_rules_count    = 0;
foreach ( $rules as $r ) {
	if ( isset( $r['status'] ) && 'active' === $r['status'] ) {
		$active_rules_count++;
	}
}

$disabled_feed_types  = isset( $settings['disabled_feed_types'] ) ? (array) $settings['disabled_feed_types'] : array();
$disabled_post_types  = isset( $settings['disabled_post_types'] ) ? (array) $settings['disabled_post_types'] : array();
$disabled_taxonomies  = isset( $settings['disabled_taxonomies'] ) ? (array) $settings['disabled_taxonomies'] : array();
$is_global_disabled   = ! empty( $settings['disable_all_feeds'] );

$default_resp_code = isset( $settings['default_response'] ) ? (int) $settings['default_response'] : 404;
$resp_label = '404 Not Found';
if ( 410 === $default_resp_code ) {
	$resp_label = '410 Gone';
} elseif ( 301 === $default_resp_code ) {
	$resp_label = '301 Permanent Redirect';
} elseif ( 302 === $default_resp_code ) {
	$resp_label = '302 Temporary Redirect';
}
?>

<div class="fwm-dashboard-grid">
	<!-- Left Column: Live Status Overview -->
	<div class="fwm-dashboard-main">
		<div class="fwm-card fwm-status-summary-card">
			<div class="fwm-card-header">
				<h2><?php esc_html_e( 'System Feed Status Overview', 'feed-url-manager' ); ?></h2>
				<span class="fwm-badge <?php echo $is_global_disabled ? 'fwm-badge-danger' : ( ( $active_rules_count > 0 || count( $disabled_feed_types ) > 0 ) ? 'fwm-badge-warning' : 'fwm-badge-success' ); ?>">
					<?php echo $is_global_disabled ? esc_html__( 'BLOCKED GLOBALLY', 'feed-url-manager' ) : ( ( $active_rules_count > 0 || count( $disabled_feed_types ) > 0 ) ? esc_html__( 'FILTERED (SELECTIVE)', 'feed-url-manager' ) : esc_html__( 'NORMAL (ALLOWED)', 'feed-url-manager' ) ); ?>
				</span>
			</div>
			<div class="fwm-card-body">
				<div class="fwm-status-indicators-grid">
					<div class="fwm-indicator-box">
						<span class="fwm-indicator-label"><?php esc_html_e( 'Global Feed Status', 'feed-url-manager' ); ?></span>
						<span class="fwm-indicator-value <?php echo $is_global_disabled ? 'fwm-text-danger' : 'fwm-text-success'; ?>">
							<?php echo $is_global_disabled ? '● ' . esc_html__( 'Feeds Disabled', 'feed-url-manager' ) : '○ ' . esc_html__( 'Feeds Enabled', 'feed-url-manager' ); ?>
						</span>
					</div>

					<div class="fwm-indicator-box">
						<span class="fwm-indicator-label"><?php esc_html_e( 'HTTP Response Code', 'feed-url-manager' ); ?></span>
						<span class="fwm-indicator-value fwm-text-primary">
							<?php echo esc_html( $resp_label ); ?>
						</span>
					</div>

					<div class="fwm-indicator-box">
						<span class="fwm-indicator-label"><?php esc_html_e( 'SEO Discovery Tags', 'feed-url-manager' ); ?></span>
						<span class="fwm-indicator-value <?php echo ! empty( $settings['remove_feed_discovery'] ) ? 'fwm-text-success' : 'fwm-text-muted'; ?>">
							<?php echo ! empty( $settings['remove_feed_discovery'] ) ? '✓ ' . esc_html__( 'Removed from <head>', 'feed-url-manager' ) : esc_html__( 'Default WordPress Links', 'feed-url-manager' ); ?>
						</span>
					</div>

					<div class="fwm-indicator-box">
						<span class="fwm-indicator-label"><?php esc_html_e( 'X-Robots-Tag Header', 'feed-url-manager' ); ?></span>
						<span class="fwm-indicator-value <?php echo ! empty( $settings['enable_x_robots_tag'] ) ? 'fwm-text-success' : 'fwm-text-muted'; ?>">
							<?php echo ! empty( $settings['enable_x_robots_tag'] ) ? '✓ ' . esc_html__( 'noindex, nofollow', 'feed-url-manager' ) : esc_html__( 'Disabled', 'feed-url-manager' ); ?>
						</span>
					</div>
				</div>

				<hr class="fwm-divider">

				<h3 class="fwm-subheading"><?php esc_html_e( 'Feed Type Protection Checklist', 'feed-url-manager' ); ?></h3>
				<div class="fwm-checklist-grid">
					<?php
					$feed_checklist = array(
						'main'        => __( 'Main Feed (/feed/)', 'feed-url-manager' ),
						'comments'    => __( 'Comments Feed (/comments/feed/)', 'feed-url-manager' ),
						'category'    => __( 'Category Feeds (/category/*/feed/)', 'feed-url-manager' ),
						'post_tag'    => __( 'Tag Feeds (/tag/*/feed/)', 'feed-url-manager' ),
						'author'      => __( 'Author Feeds (/author/*/feed/)', 'feed-url-manager' ),
						'date'        => __( 'Date Archive Feeds', 'feed-url-manager' ),
						'post_types'  => __( 'Custom Post Type Feeds', 'feed-url-manager' ),
						'taxonomies'  => __( 'Custom Taxonomy Feeds', 'feed-url-manager' ),
					);

					foreach ( $feed_checklist as $key => $label ) :
						$is_item_blocked = false;
						if ( $is_global_disabled ) {
							$is_item_blocked = true;
						} elseif ( 'main' === $key && in_array( 'main', $disabled_feed_types, true ) ) {
							$is_item_blocked = true;
						} elseif ( 'comments' === $key && in_array( 'comments', $disabled_feed_types, true ) ) {
							$is_item_blocked = true;
						} elseif ( 'author' === $key && in_array( 'author', $disabled_feed_types, true ) ) {
							$is_item_blocked = true;
						} elseif ( 'date' === $key && in_array( 'date', $disabled_feed_types, true ) ) {
							$is_item_blocked = true;
						} elseif ( 'category' === $key && in_array( 'category', $disabled_taxonomies, true ) ) {
							$is_item_blocked = true;
						} elseif ( 'post_tag' === $key && in_array( 'post_tag', $disabled_taxonomies, true ) ) {
							$is_item_blocked = true;
						} elseif ( 'post_types' === $key && count( $disabled_post_types ) > 0 ) {
							$is_item_blocked = true;
						} elseif ( 'taxonomies' === $key && count( $disabled_taxonomies ) > 0 ) {
							$is_item_blocked = true;
						}
					?>
						<div class="fwm-checklist-item <?php echo $is_item_blocked ? 'fwm-item-blocked' : 'fwm-item-active'; ?>">
							<span class="fwm-check-icon"><?php echo $is_item_blocked ? '✕' : '✓'; ?></span>
							<span class="fwm-check-text"><?php echo esc_html( $label ); ?></span>
							<span class="fwm-check-status"><?php echo $is_item_blocked ? esc_html__( 'Blocked', 'feed-url-manager' ) : esc_html__( 'Active', 'feed-url-manager' ); ?></span>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>

		<!-- Quick Action Stats -->
		<div class="fwm-stats-row">
			<div class="fwm-stat-card">
				<div class="fwm-stat-num"><?php echo esc_html( (string) $active_rules_count ); ?></div>
				<div class="fwm-stat-label"><?php esc_html_e( 'Active URL Rules', 'feed-url-manager' ); ?></div>
				<a href="<?php echo esc_url( admin_url( 'options-general.php?page=feed-url-manager&tab=url_rules' ) ); ?>" class="fwm-stat-link"><?php esc_html_e( 'Manage Rules →', 'feed-url-manager' ); ?></a>
			</div>
			<div class="fwm-stat-card">
				<div class="fwm-stat-num"><?php echo esc_html( (string) count( $disabled_post_types ) ); ?></div>
				<div class="fwm-stat-label"><?php esc_html_e( 'Disabled Post Types', 'feed-url-manager' ); ?></div>
				<a href="<?php echo esc_url( admin_url( 'options-general.php?page=feed-url-manager&tab=post_types' ) ); ?>" class="fwm-stat-link"><?php esc_html_e( 'Configure →', 'feed-url-manager' ); ?></a>
			</div>
			<div class="fwm-stat-card">
				<div class="fwm-stat-num"><?php echo esc_html( (string) count( $disabled_taxonomies ) ); ?></div>
				<div class="fwm-stat-label"><?php esc_html_e( 'Disabled Taxonomies', 'feed-url-manager' ); ?></div>
				<a href="<?php echo esc_url( admin_url( 'options-general.php?page=feed-url-manager&tab=taxonomies' ) ); ?>" class="fwm-stat-link"><?php esc_html_e( 'Configure →', 'feed-url-manager' ); ?></a>
			</div>
		</div>
	</div>

	<!-- Right Column: Priority & Architecture Information -->
	<div class="fwm-dashboard-sidebar">
		<div class="fwm-card fwm-info-card">
			<div class="fwm-card-header">
				<h3><?php esc_html_e( 'Rule Execution Hierarchy', 'feed-url-manager' ); ?></h3>
			</div>
			<div class="fwm-card-body">
				<p class="fwm-text-sm"><?php esc_html_e( 'Requests are evaluated strictly top-to-bottom. The first matching rule makes the final decision:', 'feed-url-manager' ); ?></p>
				<ol class="fwm-hierarchy-list">
					<li>
						<strong>1. <?php esc_html_e( 'Specific URL Rule', 'feed-url-manager' ); ?></strong>
						<span><?php esc_html_e( 'Exact, Wildcard (*), Contains, Regex', 'feed-url-manager' ); ?></span>
					</li>
					<li>
						<strong>2. <?php esc_html_e( 'Custom Post Type Rule', 'feed-url-manager' ); ?></strong>
						<span><?php esc_html_e( 'Post, Page, Products, Portfolio, etc.', 'feed-url-manager' ); ?></span>
					</li>
					<li>
						<strong>3. <?php esc_html_e( 'Taxonomy Rule', 'feed-url-manager' ); ?></strong>
						<span><?php esc_html_e( 'Categories, Tags, Custom Taxonomies', 'feed-url-manager' ); ?></span>
					</li>
					<li>
						<strong>4. <?php esc_html_e( 'Feed Type Rule', 'feed-url-manager' ); ?></strong>
						<span><?php esc_html_e( 'Main, Comments, Author, Date, Formats', 'feed-url-manager' ); ?></span>
					</li>
					<li>
						<strong>5. <?php esc_html_e( 'Global Rule', 'feed-url-manager' ); ?></strong>
						<span><?php esc_html_e( 'Master Disable All Toggle', 'feed-url-manager' ); ?></span>
					</li>
					<li class="fwm-hierarchy-default">
						<strong>6. <?php esc_html_e( 'Allow Request (Default)', 'feed-url-manager' ); ?></strong>
					</li>
				</ol>
			</div>
		</div>

		<div class="fwm-card fwm-info-card">
			<div class="fwm-card-header">
				<h3><?php esc_html_e( 'Protected Endpoints', 'feed-url-manager' ); ?></h3>
			</div>
			<div class="fwm-card-body fwm-text-sm">
				<p><?php esc_html_e( 'The following subsystems are strictly shielded and never blocked:', 'feed-url-manager' ); ?></p>
				<ul class="fwm-shield-list">
					<li><strong>REST API:</strong> <code>/wp-json/</code></li>
					<li><strong>XML Sitemaps:</strong> <code>/wp-sitemap.xml</code>, Yoast/RankMath sitemaps</li>
					<li><strong>Elementor:</strong> Editor, Preview, Dynamic CSS, AJAX, Forms & Popups</li>
					<li><strong>Standard HTML:</strong> Pages, Posts, Archives & Admin</li>
				</ul>
			</div>
		</div>
	</div>
</div>
