<?php
/**
 * Admin Header & Tab Navigation.
 *
 * @package FeedURLManagerPro
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$is_active_block = ! empty( $settings['disable_all_feeds'] ) || ! empty( $settings['disabled_feed_types'] ) || ! empty( $settings['disabled_post_types'] ) || ! empty( $settings['disabled_taxonomies'] ) || ! empty( $rules );
?>
<div class="wrap fwm-admin-wrap">
	<div class="fwm-header-banner">
		<div class="fwm-header-title-area">
			<div class="fwm-logo-icon">
				<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
					<path d="M4 11a9 9 0 0 1 9 9"></path>
					<path d="M4 4a16 16 0 0 1 16 16"></path>
					<circle cx="5" cy="19" r="1"></circle>
					<line x1="2" y1="2" x2="22" y2="22" stroke="#ef4444" stroke-width="2.5"></line>
				</svg>
			</div>
			<div>
				<h1><?php esc_html_e( 'Feed URL Manager Pro', 'feed-url-manager' ); ?> <span class="fwm-version-pill">v<?php echo esc_html( FWM_VERSION ); ?></span></h1>
				<p class="fwm-header-desc"><?php esc_html_e( 'Granular WordPress RSS/Atom/RDF Feed Control, Request Filtering & SEO Protection', 'feed-url-manager' ); ?></p>
			</div>
		</div>
		<div class="fwm-header-status-badge">
			<?php if ( ! empty( $settings['disable_all_feeds'] ) ) : ?>
				<span class="fwm-badge fwm-badge-danger">
					<span class="fwm-status-dot fwm-status-dot-danger"></span>
					<?php esc_html_e( 'Global Feed Blocking: ACTIVE', 'feed-url-manager' ); ?>
				</span>
			<?php elseif ( $is_active_block ) : ?>
				<span class="fwm-badge fwm-badge-warning">
					<span class="fwm-status-dot fwm-status-dot-warning"></span>
					<?php esc_html_e( 'Selective Rules: ACTIVE', 'feed-url-manager' ); ?>
				</span>
			<?php else : ?>
				<span class="fwm-badge fwm-badge-success">
					<span class="fwm-status-dot fwm-status-dot-success"></span>
					<?php esc_html_e( 'Feeds Allowed (Default)', 'feed-url-manager' ); ?>
				</span>
			<?php endif; ?>
		</div>
	</div>

	<nav class="nav-tab-wrapper fwm-nav-tabs">
		<?php foreach ( $allowed_tabs as $tab_id => $tab_label ) : ?>
			<?php
			$tab_url = add_query_arg(
				array(
					'page' => 'feed-url-manager',
					'tab'  => $tab_id,
				),
				admin_url( 'options-general.php' )
			);
			$active_class = ( $active_tab === $tab_id ) ? 'nav-tab-active' : '';
			?>
			<a href="<?php echo esc_url( $tab_url ); ?>" class="nav-tab <?php echo esc_attr( $active_class ); ?>">
				<?php echo esc_html( $tab_label ); ?>
			</a>
		<?php endforeach; ?>
	</nav>

	<div class="fwm-tab-content-container">
