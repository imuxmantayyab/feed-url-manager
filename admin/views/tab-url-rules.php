<?php
/**
 * URL Rules Settings Tab View.
 *
 * @package FeedURLManagerPro
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="fwm-card">
	<div class="fwm-card-header fwm-flex-between">
		<div>
			<h2><?php esc_html_e( 'Level 3: Custom Feed URL Rules', 'feed-url-manager' ); ?></h2>
			<p class="fwm-card-subtitle">
				<?php esc_html_e( 'URL rules take the highest execution priority (Level 1 in the hierarchy) and allow targeting exact paths, partial matches, wildcards (*), or regular expressions.', 'feed-url-manager' ); ?>
			</p>
		</div>
		<button type="button" class="button button-primary" id="fwm-btn-add-rule">
			<span class="dashicons dashicons-plus-alt2"></span> <?php esc_html_e( 'Add Feed Rule', 'feed-url-manager' ); ?>
		</button>
	</div>

	<div class="fwm-card-body fwm-p-0">
		<table class="wp-list-table widefat fixed striped fwm-rules-table" id="fwm-rules-table">
			<thead>
				<tr>
					<th scope="col" class="column-pattern" style="width: 35%;"><?php esc_html_e( 'URL Pattern', 'feed-url-manager' ); ?></th>
					<th scope="col" class="column-match" style="width: 15%;"><?php esc_html_e( 'Match Type', 'feed-url-manager' ); ?></th>
					<th scope="col" class="column-action" style="width: 15%;"><?php esc_html_e( 'HTTP Action', 'feed-url-manager' ); ?></th>
					<th scope="col" class="column-status" style="width: 15%;"><?php esc_html_e( 'Status', 'feed-url-manager' ); ?></th>
					<th scope="col" class="column-actions" style="width: 20%; text-align: right;"><?php esc_html_e( 'Actions', 'feed-url-manager' ); ?></th>
				</tr>
			</thead>
			<tbody id="fwm-rules-tbody">
				<?php if ( ! empty( $rules ) && is_array( $rules ) ) : ?>
					<?php foreach ( $rules as $rule ) : ?>
						<?php
						$rule_id      = isset( $rule['id'] ) ? esc_attr( $rule['id'] ) : 'rule_' . uniqid();
						$pattern      = isset( $rule['pattern'] ) ? esc_html( $rule['pattern'] ) : '';
						$match_type   = isset( $rule['match_type'] ) ? esc_html( ucfirst( $rule['match_type'] ) ) : 'Exact';
						$action_code  = isset( $rule['action'] ) ? (int) $rule['action'] : 404;
						$redirect_url = isset( $rule['redirect_url'] ) ? esc_url( $rule['redirect_url'] ) : '';
						$status       = isset( $rule['status'] ) && 'active' === $rule['status'] ? 'active' : 'disabled';
						$is_active    = ( 'active' === $status );
						?>
						<tr id="fwm-row-<?php echo esc_attr( $rule_id ); ?>" data-rule-id="<?php echo esc_attr( $rule_id ); ?>" data-pattern="<?php echo esc_attr( $rule['pattern'] ); ?>" data-match-type="<?php echo esc_attr( $rule['match_type'] ); ?>" data-action="<?php echo esc_attr( $action_code ); ?>" data-redirect-url="<?php echo esc_attr( $redirect_url ); ?>" data-status="<?php echo esc_attr( $status ); ?>">
							<td class="column-pattern">
								<code><?php echo $pattern; ?></code>
								<?php if ( ! empty( $redirect_url ) && ( 301 === $action_code || 302 === $action_code ) ) : ?>
									<div class="fwm-text-xs fwm-text-muted">
										↳ <?php echo esc_html( $redirect_url ); ?>
									</div>
								<?php endif; ?>
							</td>
							<td class="column-match">
								<span class="fwm-badge fwm-badge-info"><?php echo $match_type; ?></span>
							</td>
							<td class="column-action">
								<?php if ( 404 === $action_code ) : ?>
									<span class="fwm-badge fwm-badge-danger">404 Not Found</span>
								<?php elseif ( 410 === $action_code ) : ?>
									<span class="fwm-badge fwm-badge-dark">410 Gone</span>
								<?php elseif ( 301 === $action_code ) : ?>
									<span class="fwm-badge fwm-badge-warning">301 Redirect</span>
								<?php elseif ( 302 === $action_code ) : ?>
									<span class="fwm-badge fwm-badge-warning">302 Redirect</span>
								<?php endif; ?>
							</td>
							<td class="column-status">
								<label class="fwm-toggle-switch fwm-toggle-sm">
									<input type="checkbox" class="fwm-rule-toggle-status" data-rule-id="<?php echo esc_attr( $rule_id ); ?>" <?php checked( $is_active ); ?>>
									<span class="fwm-slider"></span>
								</label>
								<span class="fwm-status-text fwm-text-xs <?php echo $is_active ? 'fwm-text-success' : 'fwm-text-muted'; ?>">
									<?php echo $is_active ? esc_html__( 'Active', 'feed-url-manager' ) : esc_html__( 'Disabled', 'feed-url-manager' ); ?>
								</span>
							</td>
							<td class="column-actions" style="text-align: right;">
								<button type="button" class="button button-small fwm-btn-edit-rule" data-rule-id="<?php echo esc_attr( $rule_id ); ?>">
									<?php esc_html_e( 'Edit', 'feed-url-manager' ); ?>
								</button>
								<button type="button" class="button button-small button-link-delete fwm-btn-delete-rule" data-rule-id="<?php echo esc_attr( $rule_id ); ?>">
									<?php esc_html_e( 'Delete', 'feed-url-manager' ); ?>
								</button>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php else : ?>
					<tr id="fwm-no-rules-row">
						<td colspan="5" style="text-align: center; padding: 30px;" class="fwm-text-muted">
							<?php esc_html_e( 'No individual feed URL rules defined yet. Click "+ Add Feed Rule" above to create one.', 'feed-url-manager' ); ?>
						</td>
					</tr>
				<?php endif; ?>
			</tbody>
		</table>
	</div>
</div>

<!-- Modal Dialog: Add / Edit Feed Rule -->
<div id="fwm-rule-modal" class="fwm-modal-backdrop" style="display: none;">
	<div class="fwm-modal-dialog">
		<div class="fwm-modal-header">
			<h3 id="fwm-modal-title"><?php esc_html_e( 'Add Feed URL Rule', 'feed-url-manager' ); ?></h3>
			<button type="button" class="fwm-modal-close" id="fwm-modal-close-btn">&times;</button>
		</div>
		<form id="fwm-rule-form">
			<input type="hidden" id="fwm-rule-id" name="rule_id" value="">
			<div class="fwm-modal-body">
				<div class="fwm-form-group">
					<label for="fwm-rule-pattern"><strong><?php esc_html_e( 'URL Pattern', 'feed-url-manager' ); ?></strong> <span class="fwm-required">*</span></label>
					<input type="text" id="fwm-rule-pattern" name="pattern" class="widefat" placeholder="/category/news/feed/ or /services/*/feed/" required>
					<p class="fwm-text-xs fwm-text-muted">
						<?php esc_html_e( 'Examples: /feed/, /comments/feed/, /category/news/feed/, /services/*/feed/', 'feed-url-manager' ); ?>
					</p>
				</div>

				<div class="fwm-form-row">
					<div class="fwm-form-group fwm-flex-1">
						<label for="fwm-rule-match-type"><strong><?php esc_html_e( 'Match Type', 'feed-url-manager' ); ?></strong></label>
						<select id="fwm-rule-match-type" name="match_type" class="widefat">
							<option value="exact"><?php esc_html_e( 'Exact Match (e.g. /category/news/feed/)', 'feed-url-manager' ); ?></option>
							<option value="contains"><?php esc_html_e( 'Contains (e.g. news/feed)', 'feed-url-manager' ); ?></option>
							<option value="wildcard"><?php esc_html_e( 'Wildcard (e.g. /category/*/feed/)', 'feed-url-manager' ); ?></option>
							<option value="regex"><?php esc_html_e( 'Regular Expression (Regex - Advanced)', 'feed-url-manager' ); ?></option>
						</select>
					</div>

					<div class="fwm-form-group fwm-flex-1">
						<label for="fwm-rule-action"><strong><?php esc_html_e( 'HTTP Action', 'feed-url-manager' ); ?></strong></label>
						<select id="fwm-rule-action" name="action_code" class="widefat">
							<option value="404"><?php esc_html_e( '404 Not Found (Recommended)', 'feed-url-manager' ); ?></option>
							<option value="410"><?php esc_html_e( '410 Gone', 'feed-url-manager' ); ?></option>
							<option value="301"><?php esc_html_e( '301 Permanent Redirect', 'feed-url-manager' ); ?></option>
							<option value="302"><?php esc_html_e( '302 Temporary Redirect', 'feed-url-manager' ); ?></option>
						</select>
					</div>
				</div>

				<div id="fwm-regex-warning" class="fwm-callout fwm-callout-warning" style="display: none;">
					<strong><?php esc_html_e( 'Advanced Regex Warning:', 'feed-url-manager' ); ?></strong>
					<?php esc_html_e( 'Ensure your regular expression is valid. An incorrect regex pattern can cause unintended matching.', 'feed-url-manager' ); ?>
				</div>

				<div id="fwm-redirect-url-group" class="fwm-form-group" style="display: none;">
					<label for="fwm-rule-redirect-url"><strong><?php esc_html_e( 'Redirect Destination URL', 'feed-url-manager' ); ?></strong></label>
					<input type="url" id="fwm-rule-redirect-url" name="redirect_url" class="widefat" placeholder="<?php echo esc_attr( home_url( '/' ) ); ?>">
				</div>

				<div class="fwm-form-group">
					<label for="fwm-rule-status"><strong><?php esc_html_e( 'Status', 'feed-url-manager' ); ?></strong></label>
					<select id="fwm-rule-status" name="status" class="widefat">
						<option value="active"><?php esc_html_e( 'Active (Enforce Rule)', 'feed-url-manager' ); ?></option>
						<option value="disabled"><?php esc_html_e( 'Disabled (Ignore Rule)', 'feed-url-manager' ); ?></option>
					</select>
				</div>
			</div>
			<div class="fwm-modal-footer">
				<button type="button" class="button" id="fwm-modal-cancel-btn"><?php esc_html_e( 'Cancel', 'feed-url-manager' ); ?></button>
				<button type="submit" class="button button-primary" id="fwm-modal-save-btn"><?php esc_html_e( 'Save Rule', 'feed-url-manager' ); ?></button>
			</div>
		</form>
	</div>
</div>
