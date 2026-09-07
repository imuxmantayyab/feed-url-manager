<?php
/**
 * WordPress Admin Interface & Controllers.
 *
 * @package FeedURLManagerPro
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class FWM_Admin
 *
 * Manages admin menu, settings pages, asset enqueueing, AJAX endpoints,
 * and rule management UI.
 */
class FWM_Admin {

	/**
	 * Admin page hook suffix.
	 *
	 * @var string
	 */
	private static $page_hook = '';

	/**
	 * Initialize admin hooks.
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'register_admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin_assets' ) );
		add_action( 'admin_init', array( __CLASS__, 'handle_settings_save' ) );
		add_action( 'admin_notices', array( __CLASS__, 'render_admin_notices' ) );
		add_filter( 'plugin_action_links_' . FWM_PLUGIN_BASENAME, array( __CLASS__, 'add_plugin_action_links' ) );

		// AJAX Endpoints.
		add_action( 'wp_ajax_fwm_save_rule', array( __CLASS__, 'ajax_save_rule' ) );
		add_action( 'wp_ajax_fwm_delete_rule', array( __CLASS__, 'ajax_delete_rule' ) );
		add_action( 'wp_ajax_fwm_toggle_rule', array( __CLASS__, 'ajax_toggle_rule' ) );
		add_action( 'wp_ajax_fwm_run_feed_scanner', array( __CLASS__, 'ajax_run_feed_scanner' ) );
		add_action( 'wp_ajax_fwm_test_single_feed', array( __CLASS__, 'ajax_test_single_feed' ) );
		add_action( 'wp_ajax_fwm_clear_debug_log', array( __CLASS__, 'ajax_clear_debug_log' ) );
		add_action( 'wp_ajax_fwm_import_config', array( __CLASS__, 'ajax_import_config' ) );
	}

	/**
	 * Register settings page under WordPress "Settings" menu.
	 */
	public static function register_admin_menu() {
		self::$page_hook = add_options_page(
			__( 'Feed URL Manager Pro', 'feed-url-manager' ),
			__( 'Feed URL Manager', 'feed-url-manager' ),
			'manage_options',
			'feed-url-manager',
			array( __CLASS__, 'render_admin_page' )
		);
	}

	/**
	 * Add Settings shortcut link on Plugins list page.
	 *
	 * @param array $links
	 * @return array
	 */
	public static function add_plugin_action_links( $links ) {
		$settings_link = sprintf(
			'<a href="%s">%s</a>',
			esc_url( admin_url( 'options-general.php?page=feed-url-manager' ) ),
			esc_html__( 'Settings', 'feed-url-manager' )
		);
		array_unshift( $links, $settings_link );
		return $links;
	}

	/**
	 * Enqueue styles and scripts strictly on our settings page.
	 *
	 * @param string $hook_suffix
	 */
	public static function enqueue_admin_assets( $hook_suffix ) {
		if ( self::$page_hook !== $hook_suffix ) {
			return;
		}

		wp_enqueue_style(
			'fwm-admin-css',
			FWM_PLUGIN_URL . 'admin/css/admin.css',
			array(),
			FWM_VERSION
		);

		wp_enqueue_script(
			'fwm-admin-js',
			FWM_PLUGIN_URL . 'admin/js/admin.js',
			array( 'jquery' ),
			FWM_VERSION,
			true
		);

		wp_localize_script(
			'fwm-admin-js',
			'fwmAdminData',
			array(
				'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
				'nonce'     => wp_create_nonce( 'fwm_admin_nonce' ),
				'strings'   => array(
					'confirmDeleteRule' => __( 'Are you sure you want to delete this URL rule?', 'feed-url-manager' ),
					'ruleSaved'         => __( 'Rule saved successfully!', 'feed-url-manager' ),
					'ruleDeleted'       => __( 'Rule deleted successfully!', 'feed-url-manager' ),
					'logCleared'        => __( 'Debug log cleared successfully!', 'feed-url-manager' ),
					'copied'            => __( 'Copied to clipboard!', 'feed-url-manager' ),
					'scanning'          => __( 'Scanning feed endpoints...', 'feed-url-manager' ),
					'testing'           => __( 'Testing live HTTP response...', 'feed-url-manager' ),
					'errorOccurred'     => __( 'An error occurred. Please try again.', 'feed-url-manager' ),
					'configImported'    => __( 'Configuration imported successfully! Reloading...', 'feed-url-manager' ),
				),
			)
		);
	}

	/**
	 * Handle standard settings form submission.
	 */
	public static function handle_settings_save() {
		if ( ! isset( $_POST['fwm_save_settings_nonce'] ) ) {
			return;
		}

		if ( ! check_admin_referer( 'fwm_save_settings_action', 'fwm_save_settings_nonce' ) ) {
			wp_die( esc_html__( 'Security check failed.', 'feed-url-manager' ) );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized user.', 'feed-url-manager' ) );
		}

		$raw_data = isset( $_POST['fwm_settings'] ) && is_array( $_POST['fwm_settings'] ) ? $_POST['fwm_settings'] : array();

		// Handle export request if clicked.
		if ( isset( $_POST['fwm_export_action'] ) ) {
			$export_json = FWM_Settings::export_data();
			$filename    = 'feed-url-manager-config-' . gmdate( 'Y-m-d-His' ) . '.json';
			header( 'Content-Type: application/json; charset=utf-8' );
			header( 'Content-Disposition: attachment; filename=' . $filename );
			header( 'Expires: 0' );
			header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
			header( 'Pragma: public' );
			echo $export_json; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			exit;
		}

		FWM_Settings::update_settings( $raw_data );

		$current_tab = isset( $_POST['fwm_current_tab'] ) ? sanitize_key( $_POST['fwm_current_tab'] ) : 'dashboard';
		$redirect_url = add_query_arg(
			array(
				'page'    => 'feed-url-manager',
				'tab'     => $current_tab,
				'updated' => 'true',
			),
			admin_url( 'options-general.php' )
		);

		wp_safe_redirect( $redirect_url );
		exit;
	}

	/**
	 * Render admin notices on the settings page.
	 */
	public static function render_admin_notices() {
		$screen = get_current_screen();
		if ( ! $screen || self::$page_hook !== $screen->id ) {
			return;
		}

		// Settings updated notice.
		if ( isset( $_GET['updated'] ) && 'true' === $_GET['updated'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			?>
			<div class="notice notice-success is-dismissible">
				<p><strong><?php esc_html_e( 'Feed URL Manager Pro settings saved successfully.', 'feed-url-manager' ); ?></strong></p>
			</div>
			<?php
		}

		$settings = FWM_Settings::get_settings();
		$has_blocking = ! empty( $settings['disable_all_feeds'] ) || ! empty( $settings['disabled_feed_types'] ) || ! empty( $settings['disabled_post_types'] ) || ! empty( $settings['disabled_taxonomies'] );

		// Caching reminder notice.
		if ( $has_blocking ) {
			?>
			<div class="notice notice-info is-dismissible">
				<p>
					<strong><?php esc_html_e( 'Cache Purge Notice:', 'feed-url-manager' ); ?></strong>
					<?php esc_html_e( 'Feed blocking rules are active. If feed URLs were previously cached by your CDN or page cache plugin (e.g., LiteSpeed, WP Rocket, Cloudflare), please purge your cache to apply changes immediately to external visitors.', 'feed-url-manager' ); ?>
				</p>
			</div>
			<?php
		}
	}

	/**
	 * Render the main plugin settings page.
	 */
	public static function render_admin_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'dashboard'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$allowed_tabs = array(
			'dashboard'  => __( 'Dashboard', 'feed-url-manager' ),
			'general'    => __( 'General', 'feed-url-manager' ),
			'feed_types' => __( 'Feed Types', 'feed-url-manager' ),
			'post_types' => __( 'Post Types', 'feed-url-manager' ),
			'taxonomies' => __( 'Taxonomies', 'feed-url-manager' ),
			'url_rules'  => __( 'URL Rules', 'feed-url-manager' ),
			'seo'        => __( 'SEO Controls', 'feed-url-manager' ),
			'response'   => __( 'HTTP Response', 'feed-url-manager' ),
			'elementor'  => __( 'Elementor Compatibility', 'feed-url-manager' ),
			'tools'      => __( 'Diagnostics & Tools', 'feed-url-manager' ),
		);

		if ( ! array_key_exists( $active_tab, $allowed_tabs ) ) {
			$active_tab = 'dashboard';
		}

		$settings = FWM_Settings::get_settings();
		$rules    = FWM_Settings::get_rules();

		include FWM_PLUGIN_DIR . 'admin/views/admin-header.php';

		switch ( $active_tab ) {
			case 'dashboard':
				include FWM_PLUGIN_DIR . 'admin/views/tab-dashboard.php';
				break;
			case 'general':
				include FWM_PLUGIN_DIR . 'admin/views/tab-general.php';
				break;
			case 'feed_types':
				include FWM_PLUGIN_DIR . 'admin/views/tab-feed-types.php';
				break;
			case 'post_types':
				include FWM_PLUGIN_DIR . 'admin/views/tab-post-types.php';
				break;
			case 'taxonomies':
				include FWM_PLUGIN_DIR . 'admin/views/tab-taxonomies.php';
				break;
			case 'url_rules':
				include FWM_PLUGIN_DIR . 'admin/views/tab-url-rules.php';
				break;
			case 'seo':
				include FWM_PLUGIN_DIR . 'admin/views/tab-seo.php';
				break;
			case 'response':
				include FWM_PLUGIN_DIR . 'admin/views/tab-response.php';
				break;
			case 'elementor':
				include FWM_PLUGIN_DIR . 'admin/views/tab-elementor.php';
				break;
			case 'tools':
				include FWM_PLUGIN_DIR . 'admin/views/tab-tools.php';
				break;
		}

		echo '</div>'; // close wrap from admin-header
	}

	/**
	 * AJAX: Save or update custom URL rule.
	 */
	public static function ajax_save_rule() {
		check_ajax_referer( 'fwm_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized user.', 'feed-url-manager' ) ) );
		}

		$rule_id      = isset( $_POST['rule_id'] ) ? sanitize_key( $_POST['rule_id'] ) : '';
		$pattern      = isset( $_POST['pattern'] ) ? trim( sanitize_text_field( $_POST['pattern'] ) ) : '';
		$match_type   = isset( $_POST['match_type'] ) ? sanitize_key( $_POST['match_type'] ) : 'exact';
		$action       = isset( $_POST['action_code'] ) ? (int) $_POST['action_code'] : 404;
		$redirect_url = isset( $_POST['redirect_url'] ) ? esc_url_raw( $_POST['redirect_url'] ) : '';
		$status       = isset( $_POST['status'] ) ? sanitize_key( $_POST['status'] ) : 'active';

		if ( empty( $pattern ) ) {
			wp_send_json_error( array( 'message' => __( 'URL pattern cannot be empty.', 'feed-url-manager' ) ) );
		}

		// Validate regex if match type is regex.
		if ( 'regex' === $match_type ) {
			$test_regex = $pattern;
			if ( substr( $test_regex, 0, 1 ) !== '#' && substr( $test_regex, 0, 1 ) !== '/' ) {
				$test_regex = '#' . $test_regex . '#i';
			}
			if ( false === @preg_match( $test_regex, '' ) ) {
				wp_send_json_error( array( 'message' => __( 'Invalid regular expression syntax.', 'feed-url-manager' ) ) );
			}
		}

		$rules = FWM_Settings::get_rules( true );
		$is_new = true;

		if ( ! empty( $rule_id ) ) {
			foreach ( $rules as $index => $r ) {
				if ( isset( $r['id'] ) && $r['id'] === $rule_id ) {
					$rules[ $index ] = array(
						'id'           => $rule_id,
						'pattern'      => $pattern,
						'match_type'   => $match_type,
						'action'       => $action,
						'redirect_url' => $redirect_url,
						'status'       => $status,
					);
					$is_new = false;
					break;
				}
			}
		}

		if ( $is_new ) {
			$rule_id = 'rule_' . uniqid();
			$rules[] = array(
				'id'           => $rule_id,
				'pattern'      => $pattern,
				'match_type'   => $match_type,
				'action'       => $action,
				'redirect_url' => $redirect_url,
				'status'       => $status,
			);
		}

		FWM_Settings::update_rules( $rules );

		wp_send_json_success( array(
			'message' => __( 'URL rule saved successfully.', 'feed-url-manager' ),
			'rule_id' => $rule_id,
		) );
	}

	/**
	 * AJAX: Delete custom URL rule.
	 */
	public static function ajax_delete_rule() {
		check_ajax_referer( 'fwm_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized user.', 'feed-url-manager' ) ) );
		}

		$rule_id = isset( $_POST['rule_id'] ) ? sanitize_key( $_POST['rule_id'] ) : '';

		if ( empty( $rule_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid rule ID.', 'feed-url-manager' ) ) );
		}

		$rules = FWM_Settings::get_rules( true );
		$updated_rules = array();

		foreach ( $rules as $rule ) {
			if ( isset( $rule['id'] ) && $rule['id'] === $rule_id ) {
				continue;
			}
			$updated_rules[] = $rule;
		}

		FWM_Settings::update_rules( $updated_rules );

		wp_send_json_success( array( 'message' => __( 'Rule deleted.', 'feed-url-manager' ) ) );
	}

	/**
	 * AJAX: Toggle custom URL rule status (active / disabled).
	 */
	public static function ajax_toggle_rule() {
		check_ajax_referer( 'fwm_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized user.', 'feed-url-manager' ) ) );
		}

		$rule_id = isset( $_POST['rule_id'] ) ? sanitize_key( $_POST['rule_id'] ) : '';
		$status  = isset( $_POST['status'] ) && in_array( $_POST['status'], array( 'active', 'disabled' ), true ) ? $_POST['status'] : 'active';

		$rules = FWM_Settings::get_rules( true );

		foreach ( $rules as $index => $rule ) {
			if ( isset( $rule['id'] ) && $rule['id'] === $rule_id ) {
				$rules[ $index ]['status'] = $status;
				break;
			}
		}

		FWM_Settings::update_rules( $rules );

		wp_send_json_success( array( 'message' => __( 'Status updated.', 'feed-url-manager' ) ) );
	}

	/**
	 * AJAX: Run safe simulated Feed Scanner.
	 */
	public static function ajax_run_feed_scanner() {
		check_ajax_referer( 'fwm_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized user.', 'feed-url-manager' ) ) );
		}

		$scan_results = FWM_Diagnostics::scan_feed_endpoints();

		wp_send_json_success( array( 'results' => $scan_results ) );
	}

	/**
	 * AJAX: Test single feed URL live.
	 */
	public static function ajax_test_single_feed() {
		check_ajax_referer( 'fwm_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized user.', 'feed-url-manager' ) ) );
		}

		$url = isset( $_POST['url'] ) ? esc_url_raw( $_POST['url'] ) : '';

		if ( empty( $url ) ) {
			wp_send_json_error( array( 'message' => __( 'URL cannot be empty.', 'feed-url-manager' ) ) );
		}

		$result = FWM_Diagnostics::test_single_feed_url( $url );

		if ( empty( $result['success'] ) ) {
			wp_send_json_error( array( 'message' => $result['message'] ) );
		}

		wp_send_json_success( $result );
	}

	/**
	 * AJAX: Clear debug logs.
	 */
	public static function ajax_clear_debug_log() {
		check_ajax_referer( 'fwm_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized user.', 'feed-url-manager' ) ) );
		}

		delete_option( FWM_DEBUG_LOG_OPTION );

		wp_send_json_success( array( 'message' => __( 'Debug log cleared.', 'feed-url-manager' ) ) );
	}

	/**
	 * AJAX: Import configuration JSON.
	 */
	public static function ajax_import_config() {
		check_ajax_referer( 'fwm_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized user.', 'feed-url-manager' ) ) );
		}

		$json_data = isset( $_POST['config_json'] ) ? wp_unslash( $_POST['config_json'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		$import_res = FWM_Settings::import_data( $json_data );

		if ( is_wp_error( $import_res ) ) {
			wp_send_json_error( array( 'message' => $import_res->get_error_message() ) );
		}

		wp_send_json_success( array( 'message' => __( 'Configuration imported successfully.', 'feed-url-manager' ) ) );
	}
}
