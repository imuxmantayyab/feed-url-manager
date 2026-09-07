<?php
/**
 * Feed URL Manager Pro - Secure Plugin Uninstallation.
 *
 * @package FeedURLManagerPro
 */

// If uninstall not called from WordPress, exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$settings = get_option( 'fwm_settings', array() );
$keep_settings = ! empty( $settings['keep_settings_uninstall'] );

// If administrator chose not to keep settings, delete all database records.
if ( ! $keep_settings ) {
	if ( is_multisite() ) {
		global $wpdb;
		$blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
		foreach ( $blog_ids as $blog_id ) {
			switch_to_blog( $blog_id );
			delete_option( 'fwm_settings' );
			delete_option( 'fwm_feed_rules' );
			delete_option( 'fwm_debug_log' );
			restore_current_blog();
		}
	} else {
		delete_option( 'fwm_settings' );
		delete_option( 'fwm_feed_rules' );
		delete_option( 'fwm_debug_log' );
	}
}
