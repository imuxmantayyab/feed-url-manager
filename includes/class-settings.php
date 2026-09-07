<?php
/**
 * Settings & Storage Management.
 *
 * @package FeedURLManagerPro
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class FWM_Settings
 *
 * Handles defaults, option retrieval, sanitization, multisite support,
 * and configuration export/import.
 */
class FWM_Settings {

	/**
	 * Cached settings array.
	 *
	 * @var array|null
	 */
	private static $cached_settings = null;

	/**
	 * Cached URL rules array.
	 *
	 * @var array|null
	 */
	private static $cached_rules = null;

	/**
	 * Get default plugin settings.
	 *
	 * @return array
	 */
	public static function get_defaults() {
		return array(
			// General / Master Toggle
			'disable_all_feeds'      => 0,

			// Feed Types Disabled
			'disabled_feed_types'    => array(), // e.g. 'main', 'comments', 'author', 'date', 'search'

			// Formats Disabled
			'disabled_formats'       => array(), // e.g. 'rdf', 'atom'

			// Post Types Disabled
			'disabled_post_types'    => array(), // e.g. 'post', 'page', 'product'

			// Taxonomies Disabled
			'disabled_taxonomies'    => array(), // e.g. 'category', 'post_tag'

			// SEO Controls
			'remove_feed_discovery'  => 1, // Remove <link rel="alternate"> tags from wp_head
			'enable_x_robots_tag'    => 1, // Output X-Robots-Tag: noindex, nofollow on blocked feeds

			// Response Controls
			'default_response'       => 404, // 404, 410, 301, 302
			'redirect_target'        => home_url( '/' ),

			// Advanced & Diagnostics
			'debug_mode'             => 0,
			'keep_settings_uninstall'=> 1,
		);
	}

	/**
	 * Get merged plugin settings.
	 *
	 * @param bool $force_refresh
	 * @return array
	 */
	public static function get_settings( $force_refresh = false ) {
		if ( null !== self::$cached_settings && ! $force_refresh ) {
			return self::$cached_settings;
		}

		$defaults = self::get_defaults();
		$options  = get_option( FWM_SETTINGS_OPTION, array() );

		if ( ! is_array( $options ) ) {
			$options = array();
		}

		self::$cached_settings = wp_parse_args( $options, $defaults );
		return self::$cached_settings;
	}

	/**
	 * Update plugin settings.
	 *
	 * @param array $new_settings
	 * @return bool
	 */
	public static function update_settings( $new_settings ) {
		$sanitized = self::sanitize_settings( $new_settings );
		self::$cached_settings = $sanitized;
		return update_option( FWM_SETTINGS_OPTION, $sanitized );
	}

	/**
	 * Sanitize plugin settings array.
	 *
	 * @param array $input
	 * @return array
	 */
	public static function sanitize_settings( $input ) {
		$defaults  = self::get_defaults();
		$sanitized = array();

		// Master toggle
		$sanitized['disable_all_feeds'] = ! empty( $input['disable_all_feeds'] ) ? 1 : 0;

		// Array fields
		$array_fields = array( 'disabled_feed_types', 'disabled_formats', 'disabled_post_types', 'disabled_taxonomies' );
		foreach ( $array_fields as $field ) {
			if ( isset( $input[ $field ] ) && is_array( $input[ $field ] ) ) {
				$sanitized[ $field ] = array_map( 'sanitize_key', $input[ $field ] );
			} else {
				$sanitized[ $field ] = array();
			}
		}

		// SEO
		$sanitized['remove_feed_discovery'] = ! empty( $input['remove_feed_discovery'] ) ? 1 : 0;
		$sanitized['enable_x_robots_tag']   = ! empty( $input['enable_x_robots_tag'] ) ? 1 : 0;

		// Response
		$allowed_responses = array( 404, 410, 301, 302 );
		$response = isset( $input['default_response'] ) ? (int) $input['default_response'] : 404;
		$sanitized['default_response'] = in_array( $response, $allowed_responses, true ) ? $response : 404;

		$redirect_target = isset( $input['redirect_target'] ) ? trim( $input['redirect_target'] ) : home_url( '/' );
		$sanitized['redirect_target'] = esc_url_raw( $redirect_target );
		if ( empty( $sanitized['redirect_target'] ) ) {
			$sanitized['redirect_target'] = home_url( '/' );
		}

		// Debug & Uninstall
		$sanitized['debug_mode']              = ! empty( $input['debug_mode'] ) ? 1 : 0;
		$sanitized['keep_settings_uninstall'] = ! empty( $input['keep_settings_uninstall'] ) ? 1 : 0;

		return $sanitized;
	}

	/**
	 * Get custom URL rules list.
	 *
	 * @param bool $force_refresh
	 * @return array
	 */
	public static function get_rules( $force_refresh = false ) {
		if ( null !== self::$cached_rules && ! $force_refresh ) {
			return self::$cached_rules;
		}

		$rules = get_option( FWM_RULES_OPTION, array() );
		if ( ! is_array( $rules ) ) {
			$rules = array();
		}

		self::$cached_rules = $rules;
		return self::$cached_rules;
	}

	/**
	 * Update URL rules list.
	 *
	 * @param array $rules
	 * @return bool
	 */
	public static function update_rules( $rules ) {
		$sanitized_rules = array();

		if ( is_array( $rules ) ) {
			foreach ( $rules as $rule ) {
				$pattern = isset( $rule['pattern'] ) ? trim( sanitize_text_field( $rule['pattern'] ) ) : '';
				if ( empty( $pattern ) ) {
					continue;
				}

				$match_type = isset( $rule['match_type'] ) && in_array( $rule['match_type'], array( 'exact', 'contains', 'wildcard', 'regex' ), true ) ? $rule['match_type'] : 'exact';

				// If regex, test if syntax is valid before saving.
				if ( 'regex' === $match_type ) {
					$test_regex = $pattern;
					if ( substr( $test_regex, 0, 1 ) !== '#' && substr( $test_regex, 0, 1 ) !== '/' ) {
						$test_regex = '#' . $test_regex . '#i';
					}
					// Test execution
					if ( false === @preg_match( $test_regex, '' ) ) {
						// Invalid regex, fallback to exact match to prevent crashes.
						$match_type = 'exact';
					}
				}

				$action = isset( $rule['action'] ) && in_array( (int) $rule['action'], array( 404, 410, 301, 302 ), true ) ? (int) $rule['action'] : 404;

				$redirect_url = ! empty( $rule['redirect_url'] ) ? esc_url_raw( $rule['redirect_url'] ) : '';

				$status = isset( $rule['status'] ) && in_array( $rule['status'], array( 'active', 'disabled' ), true ) ? $rule['status'] : 'active';

				$id = ! empty( $rule['id'] ) ? sanitize_key( $rule['id'] ) : 'rule_' . uniqid();

				$sanitized_rules[] = array(
					'id'           => $id,
					'pattern'      => $pattern,
					'match_type'   => $match_type,
					'action'       => $action,
					'redirect_url' => $redirect_url,
					'status'       => $status,
				);
			}
		}

		self::$cached_rules = $sanitized_rules;
		return update_option( FWM_RULES_OPTION, $sanitized_rules );
	}

	/**
	 * Plugin activation routine.
	 *
	 * @param bool $network_wide
	 */
	public static function activate( $network_wide = false ) {
		if ( is_multisite() && $network_wide ) {
			global $wpdb;
			$blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
			foreach ( $blog_ids as $blog_id ) {
				switch_to_blog( $blog_id );
				self::init_site_options();
				restore_current_blog();
			}
		} else {
			self::init_site_options();
		}
	}

	/**
	 * Initialize site options with safe defaults if not already present.
	 */
	private static function init_site_options() {
		if ( false === get_option( FWM_SETTINGS_OPTION ) ) {
			add_option( FWM_SETTINGS_OPTION, self::get_defaults() );
		}
		if ( false === get_option( FWM_RULES_OPTION ) ) {
			add_option( FWM_RULES_OPTION, array() );
		}
	}

	/**
	 * Plugin deactivation routine.
	 *
	 * @param bool $network_wide
	 */
	public static function deactivate( $network_wide = false ) {
		// Nothing destructive on deactivation.
	}

	/**
	 * Export all settings and rules as JSON string.
	 *
	 * @return string
	 */
	public static function export_data() {
		$data = array(
			'plugin'    => 'feed-url-manager',
			'version'   => FWM_VERSION,
			'exported'  => current_time( 'mysql' ),
			'settings'  => self::get_settings( true ),
			'rules'     => self::get_rules( true ),
		);

		return wp_json_encode( $data, JSON_PRETTY_PRINT );
	}

	/**
	 * Import settings and rules from JSON string.
	 *
	 * @param string $json_string
	 * @return bool|WP_Error
	 */
	public static function import_data( $json_string ) {
		$data = json_decode( $json_string, true );

		if ( ! is_array( $data ) || empty( $data['plugin'] ) || 'feed-url-manager' !== $data['plugin'] ) {
			return new WP_Error( 'invalid_import_file', __( 'Invalid or corrupted configuration file.', 'feed-url-manager' ) );
		}

		if ( isset( $data['settings'] ) && is_array( $data['settings'] ) ) {
			self::update_settings( $data['settings'] );
		}

		if ( isset( $data['rules'] ) && is_array( $data['rules'] ) ) {
			self::update_rules( $data['rules'] );
		}

		return true;
	}
}
