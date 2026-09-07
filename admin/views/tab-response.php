<?php
/**
 * HTTP Response Settings Tab View.
 *
 * @package FeedURLManagerPro
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$default_response = isset( $settings['default_response'] ) ? (int) $settings['default_response'] : 404;
$redirect_target  = isset( $settings['redirect_target'] ) ? $settings['redirect_target'] : home_url( '/' );
?>

<form method="post" action="<?php echo esc_url( admin_url( 'options-general.php?page=feed-url-manager' ) ); ?>">
	<?php wp_nonce_field( 'fwm_save_settings_action', 'fwm_save_settings_nonce' ); ?>
	<input type="hidden" name="fwm_current_tab" value="response">

	<div class="fwm-card">
		<div class="fwm-card-header">
			<h2><?php esc_html_e( 'HTTP Response & Termination Settings', 'feed-url-manager' ); ?></h2>
		</div>
		<div class="fwm-card-body">
			<p class="fwm-section-description">
				<?php esc_html_e( 'Choose how your server terminates blocked feed requests. Proper HTTP status codes ensure search bots de-index feeds cleanly.', 'feed-url-manager' ); ?>
			</p>

			<div class="fwm-settings-table">
				<!-- Default Action -->
				<div class="fwm-setting-row">
					<div class="fwm-setting-info">
						<label for="fwm_default_response" class="fwm-setting-label">
							<strong><?php esc_html_e( 'Default Block Action', 'feed-url-manager' ); ?></strong>
						</label>
						<p class="fwm-setting-desc">
							<?php esc_html_e( 'Select the HTTP response code returned when a blocked feed is requested.', 'feed-url-manager' ); ?>
						</p>
					</div>
					<div class="fwm-setting-control fwm-w-300">
						<select id="fwm_default_response" name="fwm_settings[default_response]" class="widefat">
							<option value="404" <?php selected( 404, $default_response ); ?>><?php esc_html_e( '404 Not Found (Recommended for SEO)', 'feed-url-manager' ); ?></option>
							<option value="410" <?php selected( 410, $default_response ); ?>><?php esc_html_e( '410 Gone (Permanently Removed)', 'feed-url-manager' ); ?></option>
							<option value="301" <?php selected( 301, $default_response ); ?>><?php esc_html_e( '301 Permanent Redirect', 'feed-url-manager' ); ?></option>
							<option value="302" <?php selected( 302, $default_response ); ?>><?php esc_html_e( '302 Temporary Redirect', 'feed-url-manager' ); ?></option>
						</select>
					</div>
				</div>

				<!-- Redirect Target -->
				<div class="fwm-setting-row" id="fwm-global-redirect-row" style="<?php echo ( 301 === $default_response || 302 === $default_response ) ? '' : 'display: none;'; ?>">
					<div class="fwm-setting-info">
						<label for="fwm_redirect_target" class="fwm-setting-label">
							<strong><?php esc_html_e( 'Default Redirect Destination', 'feed-url-manager' ); ?></strong>
						</label>
						<p class="fwm-setting-desc">
							<?php esc_html_e( 'Target URL where blocked feed visitors/bots will be redirected.', 'feed-url-manager' ); ?>
						</p>
					</div>
					<div class="fwm-setting-control fwm-w-300">
						<input type="url" id="fwm_redirect_target" name="fwm_settings[redirect_target]" class="widefat" value="<?php echo esc_attr( $redirect_target ); ?>" placeholder="<?php echo esc_attr( home_url( '/' ) ); ?>">
					</div>
				</div>
			</div>

			<div class="fwm-callout fwm-callout-info fwm-mt-4">
				<h4><?php esc_html_e( 'Why 404 / 410 is Recommended over Redirects', 'feed-url-manager' ); ?></h4>
				<p class="fwm-text-sm">
					<?php esc_html_e( 'Google and other search engine crawlers interpret 301 redirects of feed URLs as soft-404s or continue crawling them. Returning a standard 404 (Not Found) or 410 (Gone) together with the X-Robots-Tag cleanly signals crawlers to purge the feed URL from search indexes forever.', 'feed-url-manager' ); ?>
				</p>
			</div>
		</div>
		<div class="fwm-card-footer">
			<button type="submit" class="button button-primary button-hero">
				<?php esc_html_e( 'Save Response Settings', 'feed-url-manager' ); ?>
			</button>
		</div>
	</div>
</form>
