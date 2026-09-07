<?php
/**
 * Diagnostics, Feed Scanner & Tools Tab View.
 *
 * @package FeedURLManagerPro
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$system_info = FWM_Diagnostics::get_system_info();
$debug_log   = get_option( FWM_DEBUG_LOG_OPTION, array() );
if ( ! is_array( $debug_log ) ) {
	$debug_log = array();
}
?>

<div class="fwm-tools-grid">
	<!-- Section 1: Safe Feed Scanner -->
	<div class="fwm-card fwm-mb-4">
		<div class="fwm-card-header fwm-flex-between">
			<div>
				<h2><?php esc_html_e( 'Feed Scanner', 'feed-url-manager' ); ?></h2>
				<p class="fwm-card-subtitle"><?php esc_html_e( 'Inspect your website for native feed endpoints and preview their calculated HTTP response based on your active rules without heavy external requests.', 'feed-url-manager' ); ?></p>
			</div>
			<button type="button" class="button button-primary" id="fwm-btn-run-scanner">
				<span class="dashicons dashicons-search"></span> <?php esc_html_e( 'Scan Endpoints Now', 'feed-url-manager' ); ?>
			</button>
		</div>
		<div class="fwm-card-body">
			<div id="fwm-scanner-results" style="display: none;">
				<table class="wp-list-table widefat fixed striped">
					<thead>
						<tr>
							<th style="width: 30%;"><?php esc_html_e( 'Feed Name / Type', 'feed-url-manager' ); ?></th>
							<th style="width: 35%;"><?php esc_html_e( 'Endpoint Path', 'feed-url-manager' ); ?></th>
							<th style="width: 15%;"><?php esc_html_e( 'Decision', 'feed-url-manager' ); ?></th>
							<th style="width: 20%;"><?php esc_html_e( 'Applied Rule', 'feed-url-manager' ); ?></th>
						</tr>
					</thead>
					<tbody id="fwm-scanner-tbody">
						<!-- Populated via AJAX -->
					</tbody>
				</table>
			</div>
			<div id="fwm-scanner-placeholder" class="fwm-empty-state">
				<p><?php esc_html_e( 'Click "Scan Endpoints Now" above to analyze your site\'s feed URLs against active rules.', 'feed-url-manager' ); ?></p>
			</div>

			<hr class="fwm-divider">

			<!-- Single Live HTTP Tester -->
			<h3 class="fwm-subheading"><?php esc_html_e( 'Manual Live HTTP Verification (Single URL)', 'feed-url-manager' ); ?></h3>
			<p class="fwm-section-description"><?php esc_html_e( 'Send a single rate-limited test request to verify the exact live HTTP headers returned by your web server.', 'feed-url-manager' ); ?></p>
			<div class="fwm-live-test-form">
				<input type="url" id="fwm-test-url-input" class="regular-text" placeholder="<?php echo esc_attr( home_url( '/feed/' ) ); ?>" value="<?php echo esc_attr( home_url( '/feed/' ) ); ?>">
				<button type="button" class="button" id="fwm-btn-test-live"><?php esc_html_e( 'Test Live URL', 'feed-url-manager' ); ?></button>
			</div>
			<div id="fwm-live-test-result" class="fwm-mt-2" style="display: none;"></div>
		</div>
	</div>

	<!-- Section 2: Debug Mode & Request Log -->
	<div class="fwm-card fwm-mb-4">
		<div class="fwm-card-header fwm-flex-between">
			<div>
				<h2><?php esc_html_e( 'Debug Mode & Live Request Inspector', 'feed-url-manager' ); ?></h2>
				<p class="fwm-card-subtitle"><?php esc_html_e( 'Record feed decisions made by the engine in real-time. Only visible to site administrators.', 'feed-url-manager' ); ?></p>
			</div>
			<div>
				<button type="button" class="button button-link-delete" id="fwm-btn-clear-log" <?php echo empty( $debug_log ) ? 'disabled' : ''; ?>>
					<?php esc_html_e( 'Clear Log', 'feed-url-manager' ); ?>
				</button>
			</div>
		</div>
		<div class="fwm-card-body">
			<form method="post" action="<?php echo esc_url( admin_url( 'options-general.php?page=feed-url-manager' ) ); ?>" class="fwm-mb-4">
				<?php wp_nonce_field( 'fwm_save_settings_action', 'fwm_save_settings_nonce' ); ?>
				<input type="hidden" name="fwm_current_tab" value="tools">
				<div class="fwm-setting-row">
					<div class="fwm-setting-info">
						<label for="fwm_debug_mode" class="fwm-setting-label"><strong><?php esc_html_e( 'Enable Debug Logging', 'feed-url-manager' ); ?></strong></label>
						<p class="fwm-setting-desc"><?php esc_html_e( 'When active, the plugin logs the last 100 blocked feed requests and matching decisions.', 'feed-url-manager' ); ?></p>
					</div>
					<div class="fwm-setting-control">
						<label class="fwm-toggle-switch">
							<input type="checkbox" id="fwm_debug_mode" name="fwm_settings[debug_mode]" value="1" <?php checked( ! empty( $settings['debug_mode'] ) ); ?> onchange="this.form.submit();">
							<span class="fwm-slider"></span>
						</label>
					</div>
				</div>
			</form>

			<div class="fwm-log-table-container">
				<table class="wp-list-table widefat fixed striped">
					<thead>
						<tr>
							<th style="width: 15%;"><?php esc_html_e( 'Timestamp', 'feed-url-manager' ); ?></th>
							<th style="width: 30%;"><?php esc_html_e( 'Requested URL', 'feed-url-manager' ); ?></th>
							<th style="width: 15%;"><?php esc_html_e( 'Feed Type', 'feed-url-manager' ); ?></th>
							<th style="width: 10%;"><?php esc_html_e( 'Decision', 'feed-url-manager' ); ?></th>
							<th style="width: 20%;"><?php esc_html_e( 'Matched Rule', 'feed-url-manager' ); ?></th>
							<th style="width: 10%;"><?php esc_html_e( 'Response', 'feed-url-manager' ); ?></th>
						</tr>
					</thead>
					<tbody id="fwm-log-tbody">
						<?php if ( ! empty( $debug_log ) ) : ?>
							<?php foreach ( $debug_log as $entry ) : ?>
								<tr>
									<td><small><?php echo esc_html( $entry['timestamp'] ); ?></small></td>
									<td><code><?php echo esc_html( $entry['url'] ); ?></code></td>
									<td><?php echo esc_html( ucfirst( $entry['feed_type'] ) ); ?></td>
									<td>
										<span class="fwm-badge <?php echo 'block' === $entry['decision'] ? 'fwm-badge-danger' : 'fwm-badge-success'; ?>">
											<?php echo esc_html( strtoupper( $entry['decision'] ) ); ?>
										</span>
									</td>
									<td><?php echo esc_html( $entry['rule_name'] ); ?></td>
									<td><strong><?php echo esc_html( (string) $entry['response_code'] ); ?></strong></td>
								</tr>
							<?php endforeach; ?>
						<?php else : ?>
							<tr>
								<td colspan="6" class="fwm-text-center fwm-text-muted" style="padding: 24px;">
									<?php echo empty( $settings['debug_mode'] ) ? esc_html__( 'Debug Mode is currently OFF. Turn on the switch above to start capturing requests.', 'feed-url-manager' ) : esc_html__( 'No feed requests recorded yet.', 'feed-url-manager' ); ?>
								</td>
							</tr>
						<?php endif; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>

	<!-- Section 3: System Diagnostics -->
	<div class="fwm-card fwm-mb-4">
		<div class="fwm-card-header fwm-flex-between">
			<div>
				<h2><?php esc_html_e( 'System Diagnostics', 'feed-url-manager' ); ?></h2>
				<p class="fwm-card-subtitle"><?php esc_html_e( 'Technical environment summary for debugging and technical support.', 'feed-url-manager' ); ?></p>
			</div>
			<button type="button" class="button" id="fwm-btn-copy-diagnostics">
				<span class="dashicons dashicons-clipboard"></span> <?php esc_html_e( 'Copy Diagnostics', 'feed-url-manager' ); ?>
			</button>
		</div>
		<div class="fwm-card-body">
			<table class="wp-list-table widefat fixed striped">
				<tbody>
					<?php foreach ( $system_info as $key => $val ) : ?>
						<tr>
							<td style="width: 35%;"><strong><?php echo esc_html( ucwords( str_replace( '_', ' ', $key ) ) ); ?></strong></td>
							<td style="width: 65%;"><code><?php echo esc_html( $val ); ?></code></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<textarea id="fwm-diagnostics-raw" style="display: none;"><?php echo esc_textarea( wp_json_encode( $system_info, JSON_PRETTY_PRINT ) ); ?></textarea>
		</div>
	</div>

	<!-- Section 4: Configuration Import / Export & Uninstall Policy -->
	<div class="fwm-card">
		<div class="fwm-card-header">
			<h2><?php esc_html_e( 'Configuration Backup, Migration & Clean Uninstall', 'feed-url-manager' ); ?></h2>
		</div>
		<div class="fwm-card-body">
			<div class="fwm-import-export-grid">
				<div>
					<h4><?php esc_html_e( 'Export Settings & Rules', 'feed-url-manager' ); ?></h4>
					<p class="fwm-text-sm"><?php esc_html_e( 'Download a JSON snapshot of all your settings, disabled post types, taxonomies, and custom URL rules to import into another WordPress installation.', 'feed-url-manager' ); ?></p>
					<form method="post" action="<?php echo esc_url( admin_url( 'options-general.php?page=feed-url-manager' ) ); ?>">
						<?php wp_nonce_field( 'fwm_save_settings_action', 'fwm_save_settings_nonce' ); ?>
						<input type="hidden" name="fwm_export_action" value="1">
						<button type="submit" class="button button-secondary">
							<span class="dashicons dashicons-download"></span> <?php esc_html_e( 'Download Config JSON', 'feed-url-manager' ); ?>
						</button>
					</form>
				</div>

				<div>
					<h4><?php esc_html_e( 'Import Configuration', 'feed-url-manager' ); ?></h4>
					<p class="fwm-text-sm"><?php esc_html_e( 'Paste a previously exported configuration JSON string below to restore or migrate rules.', 'feed-url-manager' ); ?></p>
					<textarea id="fwm-import-textarea" class="widefat" rows="3" placeholder='{"plugin": "feed-url-manager", ...}'></textarea>
					<button type="button" class="button button-secondary fwm-mt-2" id="fwm-btn-import">
						<span class="dashicons dashicons-upload"></span> <?php esc_html_e( 'Import Configuration', 'feed-url-manager' ); ?>
					</button>
				</div>
			</div>

			<hr class="fwm-divider">

			<form method="post" action="<?php echo esc_url( admin_url( 'options-general.php?page=feed-url-manager' ) ); ?>">
				<?php wp_nonce_field( 'fwm_save_settings_action', 'fwm_save_settings_nonce' ); ?>
				<input type="hidden" name="fwm_current_tab" value="tools">
				<div class="fwm-setting-row">
					<div class="fwm-setting-info">
						<label for="fwm_keep_settings_uninstall" class="fwm-setting-label"><strong><?php esc_html_e( 'Keep Settings on Plugin Deletion', 'feed-url-manager' ); ?></strong></label>
						<p class="fwm-setting-desc"><?php esc_html_e( 'If disabled, all rules, options, and logs will be permanently deleted from the database upon deleting the plugin from the WordPress Plugins page.', 'feed-url-manager' ); ?></p>
					</div>
					<div class="fwm-setting-control">
						<label class="fwm-toggle-switch">
							<input type="checkbox" id="fwm_keep_settings_uninstall" name="fwm_settings[keep_settings_uninstall]" value="1" <?php checked( ! empty( $settings['keep_settings_uninstall'] ) ); ?>>
							<span class="fwm-slider"></span>
						</label>
					</div>
				</div>
				<button type="submit" class="button button-primary fwm-mt-2"><?php esc_html_e( 'Save Uninstall Policy', 'feed-url-manager' ); ?></button>
			</form>
		</div>
	</div>
</div>
