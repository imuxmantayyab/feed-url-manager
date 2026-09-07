<?php
/**
 * 3rd-Party & Elementor Compatibility Engine.
 *
 * @package FeedURLManagerPro
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class FWM_Compatibility
 *
 * Provides detection, status reports, and safe guards for Elementor,
 * Elementor Pro, SEO plugins, Caching plugins, and WordPress core APIs.
 */
class FWM_Compatibility {

	/**
	 * Check if Elementor (Core) is active.
	 *
	 * @return bool
	 */
	public static function is_elementor_active() {
		return did_action( 'elementor/loaded' ) || defined( 'ELEMENTOR_VERSION' ) || class_exists( '\Elementor\Plugin' );
	}

	/**
	 * Check if Elementor Pro is active.
	 *
	 * @return bool
	 */
	public static function is_elementor_pro_active() {
		return defined( 'ELEMENTOR_PRO_VERSION' ) || class_exists( '\ElementorPro\Plugin' );
	}

	/**
	 * Get comprehensive third-party plugin compatibility diagnostics.
	 *
	 * @return array
	 */
	public static function get_compatibility_report() {
		$report = array(
			'elementor'     => array(
				'name'      => 'Elementor',
				'active'    => self::is_elementor_active(),
				'version'   => defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : '',
				'status'    => self::is_elementor_active() ? 'compatible' : 'not_detected',
				'notes'     => __( 'Feed URL Manager operates at the WordPress request level and completely bypasses Elementor editor, preview, AJAX, REST, and CSS generation.', 'feed-url-manager' ),
			),
			'elementor_pro' => array(
				'name'      => 'Elementor Pro',
				'active'    => self::is_elementor_pro_active(),
				'version'   => defined( 'ELEMENTOR_PRO_VERSION' ) ? ELEMENTOR_PRO_VERSION : '',
				'status'    => self::is_elementor_pro_active() ? 'compatible' : 'not_detected',
				'notes'     => __( 'Fully compatible with Theme Builder, Popups, Loop Grid, Dynamic Tags, and Forms.', 'feed-url-manager' ),
			),
			'seo_plugins'   => self::get_seo_plugins_status(),
			'caching'       => self::get_caching_plugins_status(),
		);

		return $report;
	}

	/**
	 * Detect active SEO plugins and their compatibility.
	 *
	 * @return array
	 */
	public static function get_seo_plugins_status() {
		$plugins = array();

		// Rank Math
		$rank_math_active = defined( 'RANK_MATH_VERSION' ) || class_exists( 'RankMath' );
		$plugins['rank_math'] = array(
			'name'    => 'Rank Math SEO',
			'active'  => $rank_math_active,
			'version' => defined( 'RANK_MATH_VERSION' ) ? RANK_MATH_VERSION : '',
			'status'  => $rank_math_active ? 'compatible' : 'not_detected',
		);

		// Yoast SEO
		$yoast_active = defined( 'WPSEO_VERSION' ) || class_exists( 'WPSEO_Options' );
		$plugins['yoast'] = array(
			'name'    => 'Yoast SEO',
			'active'  => $yoast_active,
			'version' => defined( 'WPSEO_VERSION' ) ? WPSEO_VERSION : '',
			'status'  => $yoast_active ? 'compatible' : 'not_detected',
		);

		// All in One SEO
		$aioseo_active = defined( 'AIOSEO_VERSION' );
		$plugins['aioseo'] = array(
			'name'    => 'All in One SEO',
			'active'  => $aioseo_active,
			'version' => defined( 'AIOSEO_VERSION' ) ? AIOSEO_VERSION : '',
			'status'  => $aioseo_active ? 'compatible' : 'not_detected',
		);

		// The SEO Framework
		$tsf_active = defined( 'THE_SEO_FRAMEWORK_VERSION' );
		$plugins['tsf'] = array(
			'name'    => 'The SEO Framework',
			'active'  => $tsf_active,
			'version' => defined( 'THE_SEO_FRAMEWORK_VERSION' ) ? THE_SEO_FRAMEWORK_VERSION : '',
			'status'  => $tsf_active ? 'compatible' : 'not_detected',
		);

		return $plugins;
	}

	/**
	 * Detect active Caching plugins and CDNs.
	 *
	 * @return array
	 */
	public static function get_caching_plugins_status() {
		$plugins = array();

		// LiteSpeed Cache
		$lscache = defined( 'LSCWP_V' );
		$plugins['litespeed'] = array(
			'name'    => 'LiteSpeed Cache',
			'active'  => $lscache,
			'version' => defined( 'LSCWP_V' ) ? LSCWP_V : '',
		);

		// WP Rocket
		$wp_rocket = defined( 'WP_ROCKET_VERSION' );
		$plugins['wp_rocket'] = array(
			'name'    => 'WP Rocket',
			'active'  => $wp_rocket,
			'version' => defined( 'WP_ROCKET_VERSION' ) ? WP_ROCKET_VERSION : '',
		);

		// WP Super Cache
		$wpsc = defined( 'WPSC_VERSION' );
		$plugins['wpsc'] = array(
			'name'    => 'WP Super Cache',
			'active'  => $wpsc,
			'version' => defined( 'WPSC_VERSION' ) ? WPSC_VERSION : '',
		);

		// W3 Total Cache
		$w3tc = defined( 'W3TC' );
		$plugins['w3tc'] = array(
			'name'    => 'W3 Total Cache',
			'active'  => $w3tc,
			'version' => defined( 'W3TC_VERSION' ) ? W3TC_VERSION : '',
		);

		// Cloudflare
		$cloudflare = defined( 'CLOUDFLARE_VERSION' );
		$plugins['cloudflare'] = array(
			'name'    => 'Cloudflare',
			'active'  => $cloudflare,
			'version' => defined( 'CLOUDFLARE_VERSION' ) ? CLOUDFLARE_VERSION : '',
		);

		return $plugins;
	}
}
