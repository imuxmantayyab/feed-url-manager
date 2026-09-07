<?php
/**
 * Plugin Name:       Feed URL Manager Pro
 * Plugin URI:        https://www.linkedin.com/in/imuxmantayyab/
 * Description:       Advanced Feed Management system for WordPress with multi-level control (Global, Content Type, URL Rules), SEO discovery control, and seamless Elementor compatibility.
 * Version:           2.0.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            Usman Tayyab
 * Author URI:        https://www.linkedin.com/in/imuxmantayyab/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       feed-url-manager
 * Domain Path:       /languages
 *
 * @package           FeedURLManagerPro
 * @author            Usman Tayyab
 * @copyright         Copyright (c) Usman Tayyab
 * @license           GPL-2.0-or-later
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Define plugin constants.
 */
define( 'FWM_VERSION', '2.0.0' );
define( 'FWM_PLUGIN_FILE', __FILE__ );
define( 'FWM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'FWM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'FWM_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'FWM_SETTINGS_OPTION', 'fwm_settings' );
define( 'FWM_RULES_OPTION', 'fwm_feed_rules' );
define( 'FWM_DEBUG_LOG_OPTION', 'fwm_debug_log' );

/**
 * Require plugin core classes.
 */
require_once FWM_PLUGIN_DIR . 'includes/class-feed-detector.php';
require_once FWM_PLUGIN_DIR . 'includes/class-feed-rules.php';
require_once FWM_PLUGIN_DIR . 'includes/class-feed-response.php';
require_once FWM_PLUGIN_DIR . 'includes/class-feed-discovery.php';
require_once FWM_PLUGIN_DIR . 'includes/class-compatibility.php';
require_once FWM_PLUGIN_DIR . 'includes/class-diagnostics.php';
require_once FWM_PLUGIN_DIR . 'includes/class-settings.php';
require_once FWM_PLUGIN_DIR . 'includes/class-admin.php';
require_once FWM_PLUGIN_DIR . 'includes/class-plugin.php';

/**
 * Activation Hook.
 */
function fwm_activate_plugin( $network_wide = false ) {
	FWM_Settings::activate( $network_wide );
}
register_activation_hook( __FILE__, 'fwm_activate_plugin' );

/**
 * Deactivation Hook.
 */
function fwm_deactivate_plugin( $network_wide = false ) {
	FWM_Settings::deactivate( $network_wide );
}
register_deactivation_hook( __FILE__, 'fwm_deactivate_plugin' );

/**
 * Returns the main instance of FWM_Plugin.
 *
 * @return FWM_Plugin
 */
function fwm_get_plugin() {
	return FWM_Plugin::get_instance();
}

// Bootstrap plugin.
add_action( 'plugins_loaded', 'fwm_init_plugin', 5 );

/**
 * Initialize plugin on plugins_loaded.
 */
function fwm_init_plugin() {
	fwm_get_plugin();
}
