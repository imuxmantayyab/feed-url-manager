<?php
/**
 * Post Types Settings Tab View.
 *
 * @package FeedURLManagerPro
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$disabled_post_types = isset( $settings['disabled_post_types'] ) && is_array( $settings['disabled_post_types'] ) ? $settings['disabled_post_types'] : array();

// Dynamically discover all public post types.
$public_post_types = get_post_types(
	array(
		'public' => true,
	),
	'objects'
);
?>

<form method="post" action="<?php echo esc_url( admin_url( 'options-general.php?page=feed-url-manager' ) ); ?>">
	<?php wp_nonce_field( 'fwm_save_settings_action', 'fwm_save_settings_nonce' ); ?>
	<input type="hidden" name="fwm_current_tab" value="post_types">

	<div class="fwm-card">
		<div class="fwm-card-header">
			<h2><?php esc_html_e( 'Level 2: Dynamic Custom Post Type Feed Controls', 'feed-url-manager' ); ?></h2>
		</div>
		<div class="fwm-card-body">
			<p class="fwm-section-description">
				<?php esc_html_e( 'The plugin dynamically discovers all public post types registered by your active theme and plugins. Toggle any post type to block both archive and individual single item feeds.', 'feed-url-manager' ); ?>
			</p>

			<div class="fwm-settings-table">
				<?php if ( ! empty( $public_post_types ) ) : ?>
					<?php foreach ( $public_post_types as $pt_name => $pt_obj ) : ?>
						<?php
						$is_disabled = in_array( $pt_name, $disabled_post_types, true );
						$count_posts = wp_count_posts( $pt_name );
						$published_count = isset( $count_posts->publish ) ? $count_posts->publish : 0;
						?>
						<div class="fwm-setting-row">
							<div class="fwm-setting-info">
								<label for="fwm_pt_<?php echo esc_attr( $pt_name ); ?>" class="fwm-setting-label">
									<strong><?php echo esc_html( $pt_obj->labels->name ); ?></strong>
									<span class="fwm-badge fwm-badge-secondary fwm-text-xs"><code><?php echo esc_html( $pt_name ); ?></code></span>
									<span class="fwm-text-muted fwm-text-xs">(<?php echo esc_html( sprintf( _n( '%s published item', '%s published items', $published_count, 'feed-url-manager' ), number_format_i18n( $published_count ) ) ); ?>)</span>
								</label>
								<p class="fwm-setting-desc">
									<?php
									printf(
										/* translators: 1: Post type name, 2: Feed endpoint */
										esc_html__( 'Disable feeds for all %1$s entries (e.g. /%2$s/feed/ and singular post feeds).', 'feed-url-manager' ),
										esc_html( strtolower( $pt_obj->labels->name ) ),
										esc_html( $pt_name )
									);
									?>
								</p>
							</div>
							<div class="fwm-setting-control">
								<label class="fwm-toggle-switch">
									<input type="checkbox" id="fwm_pt_<?php echo esc_attr( $pt_name ); ?>" name="fwm_settings[disabled_post_types][]" value="<?php echo esc_attr( $pt_name ); ?>" <?php checked( $is_disabled ); ?>>
									<span class="fwm-slider"></span>
								</label>
							</div>
						</div>
					<?php endforeach; ?>
				<?php else : ?>
					<p class="fwm-empty-state"><?php esc_html_e( 'No public post types detected.', 'feed-url-manager' ); ?></p>
				<?php endif; ?>
			</div>
		</div>
		<div class="fwm-card-footer">
			<button type="submit" class="button button-primary button-hero">
				<?php esc_html_e( 'Save Post Type Settings', 'feed-url-manager' ); ?>
			</button>
		</div>
	</div>
</form>
