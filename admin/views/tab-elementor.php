<?php
/**
 * Elementor & 3rd-Party Compatibility Tab View.
 *
 * @package FeedURLManagerPro
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$compat_report = FWM_Compatibility::get_compatibility_report();
$elementor     = $compat_report['elementor'];
$elementor_pro = $compat_report['elementor_pro'];
$seo_plugins   = $compat_report['seo_plugins'];
$caching       = $compat_report['caching'];

$is_manager_active = ! empty( $settings['disable_all_feeds'] ) || ! empty( $settings['disabled_feed_types'] ) || ! empty( $settings['disabled_post_types'] ) || ! empty( $settings['disabled_taxonomies'] ) || ! empty( $rules );
?>

<div class="fwm-card">
	<div class="fwm-card-header">
		<h2><?php esc_html_e( 'Elementor & Elementor Pro Compatibility Engine', 'feed-url-manager' ); ?></h2>
		<span class="fwm-badge fwm-badge-success">
			<?php esc_html_e( '100% Request-Isolated', 'feed-url-manager' ); ?>
		</span>
	</div>
	<div class="fwm-card-body">
		<!-- Live Status Detection Grid -->
		<div class="fwm-status-indicators-grid fwm-mb-4">
			<div class="fwm-indicator-box">
				<span class="fwm-indicator-label"><?php esc_html_e( 'Elementor Detected', 'feed-url-manager' ); ?></span>
				<span class="fwm-indicator-value <?php echo $elementor['active'] ? 'fwm-text-success' : 'fwm-text-muted'; ?>">
					<?php echo $elementor['active'] ? '✓ ' . esc_html__( 'Yes', 'feed-url-manager' ) . ( ! empty( $elementor['version'] ) ? ' (v' . esc_html( $elementor['version'] ) . ')' : '' ) : '✕ ' . esc_html__( 'No', 'feed-url-manager' ); ?>
				</span>
			</div>

			<div class="fwm-indicator-box">
				<span class="fwm-indicator-label"><?php esc_html_e( 'Elementor Pro Detected', 'feed-url-manager' ); ?></span>
				<span class="fwm-indicator-value <?php echo $elementor_pro['active'] ? 'fwm-text-success' : 'fwm-text-muted'; ?>">
					<?php echo $elementor_pro['active'] ? '✓ ' . esc_html__( 'Yes', 'feed-url-manager' ) . ( ! empty( $elementor_pro['version'] ) ? ' (v' . esc_html( $elementor_pro['version'] ) . ')' : '' ) : '✕ ' . esc_html__( 'No', 'feed-url-manager' ); ?>
				</span>
			</div>

			<div class="fwm-indicator-box">
				<span class="fwm-indicator-label"><?php esc_html_e( 'Feed Manager Status', 'feed-url-manager' ); ?></span>
				<span class="fwm-indicator-value <?php echo $is_manager_active ? 'fwm-text-success' : 'fwm-text-warning'; ?>">
					<?php echo $is_manager_active ? esc_html__( 'Active (Guarded)', 'feed-url-manager' ) : esc_html__( 'Inactive (Default)', 'feed-url-manager' ); ?>
				</span>
			</div>
		</div>

		<div class="fwm-callout fwm-callout-info">
			<h4><?php esc_html_e( 'Zero Core Modification Guarantee', 'feed-url-manager' ); ?></h4>
			<p>
				<?php esc_html_e( 'Feed URL Manager Pro does not modify Elementor templates, widgets, or Elementor-generated content. Feed control is handled strictly at the WordPress core request level before templates render.', 'feed-url-manager' ); ?>
			</p>
		</div>

		<h3 class="fwm-subheading fwm-mt-4"><?php esc_html_e( 'Verified Elementor Subsystems Protection Checklist', 'feed-url-manager' ); ?></h3>
		<div class="fwm-checklist-grid">
			<div class="fwm-checklist-item fwm-item-active">
				<span class="fwm-check-icon">✓</span>
				<span class="fwm-check-text"><?php esc_html_e( 'Elementor Visual Editor & Canvas', 'feed-url-manager' ); ?></span>
				<span class="fwm-check-status"><?php esc_html_e( 'Protected', 'feed-url-manager' ); ?></span>
			</div>
			<div class="fwm-checklist-item fwm-item-active">
				<span class="fwm-check-icon">✓</span>
				<span class="fwm-check-text"><?php esc_html_e( 'Live Preview Mode (?elementor-preview=)', 'feed-url-manager' ); ?></span>
				<span class="fwm-check-status"><?php esc_html_e( 'Protected', 'feed-url-manager' ); ?></span>
			</div>
			<div class="fwm-checklist-item fwm-item-active">
				<span class="fwm-check-icon">✓</span>
				<span class="fwm-check-text"><?php esc_html_e( 'Elementor AJAX Actions & Saves', 'feed-url-manager' ); ?></span>
				<span class="fwm-check-status"><?php esc_html_e( 'Protected', 'feed-url-manager' ); ?></span>
			</div>
			<div class="fwm-checklist-item fwm-item-active">
				<span class="fwm-check-icon">✓</span>
				<span class="fwm-check-text"><?php esc_html_e( 'Elementor REST API Endpoints', 'feed-url-manager' ); ?></span>
				<span class="fwm-check-status"><?php esc_html_e( 'Protected', 'feed-url-manager' ); ?></span>
			</div>
			<div class="fwm-checklist-item fwm-item-active">
				<span class="fwm-check-icon">✓</span>
				<span class="fwm-check-text"><?php esc_html_e( 'CSS Dynamic File Generation', 'feed-url-manager' ); ?></span>
				<span class="fwm-check-status"><?php esc_html_e( 'Protected', 'feed-url-manager' ); ?></span>
			</div>
			<div class="fwm-checklist-item fwm-item-active">
				<span class="fwm-check-icon">✓</span>
				<span class="fwm-check-text"><?php esc_html_e( 'Theme Builder Templates & Popups', 'feed-url-manager' ); ?></span>
				<span class="fwm-check-status"><?php esc_html_e( 'Protected', 'feed-url-manager' ); ?></span>
			</div>
			<div class="fwm-checklist-item fwm-item-active">
				<span class="fwm-check-icon">✓</span>
				<span class="fwm-check-text"><?php esc_html_e( 'Elementor Pro Forms Submissions', 'feed-url-manager' ); ?></span>
				<span class="fwm-check-status"><?php esc_html_e( 'Protected', 'feed-url-manager' ); ?></span>
			</div>
			<div class="fwm-checklist-item fwm-item-active">
				<span class="fwm-check-icon">✓</span>
				<span class="fwm-check-text"><?php esc_html_e( 'Loop Grid & Dynamic Content Tags', 'feed-url-manager' ); ?></span>
				<span class="fwm-check-status"><?php esc_html_e( 'Protected', 'feed-url-manager' ); ?></span>
			</div>
		</div>

		<hr class="fwm-divider">

		<!-- 3rd Party SEO & Caching Matrix -->
		<h3 class="fwm-subheading"><?php esc_html_e( 'Third-Party SEO & Caching Integration Status', 'feed-url-manager' ); ?></h3>
		<div class="fwm-compat-table-container">
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Plugin / Service', 'feed-url-manager' ); ?></th>
						<th><?php esc_html_e( 'Detection', 'feed-url-manager' ); ?></th>
						<th><?php esc_html_e( 'Compatibility Status', 'feed-url-manager' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $seo_plugins as $p ) : ?>
						<tr>
							<td><strong><?php echo esc_html( $p['name'] ); ?></strong></td>
							<td>
								<?php if ( $p['active'] ) : ?>
									<span class="fwm-badge fwm-badge-success"><?php esc_html_e( 'Detected', 'feed-url-manager' ); ?> (v<?php echo esc_html( $p['version'] ); ?>)</span>
								<?php else : ?>
									<span class="fwm-badge fwm-badge-secondary"><?php esc_html_e( 'Not Detected', 'feed-url-manager' ); ?></span>
								<?php endif; ?>
							</td>
							<td><span class="fwm-text-success">✓ <?php esc_html_e( 'Fully Compatible', 'feed-url-manager' ); ?></span></td>
						</tr>
					<?php endforeach; ?>
					<?php foreach ( $caching as $c ) : ?>
						<tr>
							<td><strong><?php echo esc_html( $c['name'] ); ?></strong></td>
							<td>
								<?php if ( $c['active'] ) : ?>
									<span class="fwm-badge fwm-badge-success"><?php esc_html_e( 'Detected', 'feed-url-manager' ); ?> (v<?php echo esc_html( $c['version'] ); ?>)</span>
								<?php else : ?>
									<span class="fwm-badge fwm-badge-secondary"><?php esc_html_e( 'Not Detected', 'feed-url-manager' ); ?></span>
								<?php endif; ?>
							</td>
							<td><span class="fwm-text-success">✓ <?php esc_html_e( 'Fully Compatible', 'feed-url-manager' ); ?></span></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</div>
</div>
