<?php
/**
 * Main Plugin Orchestrator.
 *
 * @package FeedURLManagerPro
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class FWM_Plugin
 *
 * Coordinates plugin lifecycle, early feed interception, response triggering,
 * and component initialization.
 */
class FWM_Plugin {

	/**
	 * Single instance of this class.
	 *
	 * @var FWM_Plugin|null
	 */
	private static $instance = null;

	/**
	 * Main instance accessor.
	 *
	 * @return FWM_Plugin
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->init_hooks();
	}

	/**
	 * Register core WordPress hooks.
	 */
	private function init_hooks() {
		// Internationalization.
		add_action( 'init', array( $this, 'load_textdomain' ) );

		// Initialize SEO Feed Discovery Manager.
		FWM_Feed_Discovery::init();

		// Intercept feed requests early during template_redirect.
		add_action( 'template_redirect', array( $this, 'intercept_feed_request' ), 1 );

		// Fallback for native do_feed actions.
		$feed_types = array( 'feed', 'rdf', 'rss', 'rss2', 'atom' );
		foreach ( $feed_types as $feed_type ) {
			add_action( 'do_feed_' . $feed_type, array( $this, 'intercept_do_feed' ), 1 );
		}
		add_action( 'do_feed', array( $this, 'intercept_do_feed' ), 1 );

		// Initialize Admin Interface if in wp-admin.
		if ( is_admin() ) {
			FWM_Admin::init();
		}
	}

	/**
	 * Load plugin textdomain for translations.
	 */
	public function load_textdomain() {
		load_plugin_textdomain(
			'feed-url-manager',
			false,
			dirname( FWM_PLUGIN_BASENAME ) . '/languages/'
		);
	}

	/**
	 * Primary request interceptor hooked into template_redirect.
	 */
	public function intercept_feed_request() {
		// Detect if this is a feed request.
		$detection = FWM_Feed_Detector::detect();

		if ( empty( $detection['is_feed'] ) ) {
			return; // Not a feed request; pass through untouched.
		}

		$this->handle_feed_evaluation( $detection );
	}

	/**
	 * Fallback interceptor hooked into do_feed actions.
	 */
	public function intercept_do_feed() {
		$detection = FWM_Feed_Detector::detect();
		$detection['is_feed'] = true;

		$this->handle_feed_evaluation( $detection );
	}

	/**
	 * Evaluate feed rules and execute response if blocked.
	 *
	 * @param array $detection
	 */
	private function handle_feed_evaluation( $detection ) {
		// Evaluate through Rules Engine.
		$evaluation = FWM_Feed_Rules::evaluate( $detection );

		$is_disabled = ( FWM_Feed_Rules::ACTION_BLOCK === $evaluation['decision'] );

		/**
		 * Filter whether the detected feed is disabled.
		 *
		 * @since 2.0.0
		 * @param bool $is_disabled True to block feed, false to allow.
		 * @param array $detection Feed detection data.
		 * @param array $evaluation Rule evaluation data.
		 */
		$is_disabled = apply_filters( 'fwm_is_feed_disabled', $is_disabled, $detection, $evaluation );

		if ( $is_disabled ) {
			// Execute HTTP response (404, 410, or redirect) and cleanly terminate.
			FWM_Feed_Response::execute( $evaluation );
		}
	}
}
