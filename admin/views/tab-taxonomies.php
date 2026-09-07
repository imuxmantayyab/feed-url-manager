<?php
/**
 * Taxonomies Settings Tab View.
 *
 * @package FeedURLManagerPro
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$disabled_taxonomies = isset( $settings['disabled_taxonomies'] ) && is_array( $settings['disabled_taxonomies'] ) ? $settings['disabled_taxonomies'] : array();

// Dynamically discover all public taxonomies.
$public_taxonomies = get_taxonomies(
	array(
		'public' => true,
	),
	'objects'
);
?>

<form method="post" action="<?php echo esc_url( admin_url( 'options-general.php?page=feed-url-manager' ) ); ?>">
	<?php wp_nonce_field( 'fwm_save_settings_action', 'fwm_save_settings_nonce' ); ?>
	<input type="hidden" name="fwm_current_tab" value="taxonomies">

	<div class="fwm-card">
		<div class="fwm-card-header">
			<h2><?php esc_html_e( 'Level 2: Dynamic Taxonomy Feed Controls', 'feed-url-manager' ); ?></h2>
		</div>
		<div class="fwm-card-body">
			<p class="fwm-section-description">
				<?php esc_html_e( 'The plugin dynamically discovers all public taxonomies. Toggle any taxonomy to block feeds across all of its terms and archive listings.', 'feed-url-manager' ); ?>
			</p>

			<div class="fwm-settings-table">
				<?php if ( ! empty( $public_taxonomies ) ) : ?>
					<?php foreach ( $public_taxonomies as $tax_name => $tax_obj ) : ?>
						<?php
						$is_disabled = in_array( $tax_name, $disabled_taxonomies, true );
						$term_count  = wp_count_terms( array( 'taxonomy' => $tax_name, 'hide_empty' => false ) );
						$term_count_val = is_wp_error( $term_count ) ? 0 : (int) $term_count;
						?>
						<div class="fwm-setting-row">
							<div class="fwm-setting-info">
								<label for="fwm_tax_<?php echo esc_attr( $tax_name ); ?>" class="fwm-setting-label">
									<strong><?php echo esc_html( $tax_obj->labels->name ); ?></strong>
									<span class="fwm-badge fwm-badge-secondary fwm-text-xs"><code><?php echo esc_html( $tax_name ); ?></code></span>
									<span class="fwm-text-muted fwm-text-xs">(<?php echo esc_html( sprintf( _n( '%s term', '%s terms', $term_count_val, 'feed-url-manager' ), number_format_i18n( $term_count_val ) ) ); ?>)</span>
								</label>
								<p class="fwm-setting-desc">
									<?php
									printf(
										/* translators: 1: Taxonomy name, 2: Example URL pattern */
										esc_html__( 'Disable all feed URLs for %1$s terms (e.g. /%2$s/term-slug/feed/).', 'feed-url-manager' ),
										esc_html( strtolower( $tax_obj->labels->name ) ),
										esc_html( $tax_name )
									);
									?>
								</p>
							</div>
							<div class="fwm-setting-control">
								<label class="fwm-toggle-switch">
									<input type="checkbox" id="fwm_tax_<?php echo esc_attr( $tax_name ); ?>" name="fwm_settings[disabled_taxonomies][]" value="<?php echo esc_attr( $tax_name ); ?>" <?php checked( $is_disabled ); ?>>
									<span class="fwm-slider"></span>
								</label>
							</div>
						</div>
					<?php endforeach; ?>
				<?php else : ?>
					<p class="fwm-empty-state"><?php esc_html_e( 'No public taxonomies detected.', 'feed-url-manager' ); ?></p>
				<?php endif; ?>
			</div>
		</div>
		<div class="fwm-card-footer">
			<button type="submit" class="button button-primary button-hero">
				<?php esc_html_e( 'Save Taxonomy Settings', 'feed-url-manager' ); ?>
			</button>
		</div>
	</div>
</form>
