<?php
/**
 * Feed Response & Termination Engine.
 *
 * @package FeedURLManagerPro
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class FWM_Feed_Response
 *
 * Safely handles HTTP status headers, SEO headers, redirects, debug logging,
 * and clean request termination when a feed is blocked.
 */
class FWM_Feed_Response {

	/**
	 * Execute the appropriate response based on the rule evaluation.
	 *
	 * @param array $evaluation_result The result array from FWM_Feed_Rules::evaluate().
	 */
	public static function execute( $evaluation_result ) {
		$settings = FWM_Settings::get_settings();

		// Record debug log if debug mode is active.
		if ( ! empty( $settings['debug_mode'] ) ) {
			self::log_debug_entry( $evaluation_result );
		}

		$response_code = isset( $evaluation_result['response_code'] ) ? (int) $evaluation_result['response_code'] : 404;

		/**
		 * Filter the response code for blocked feeds.
		 *
		 * @since 2.0.0
		 * @param int $response_code HTTP response code (404, 410, 301, 302).
		 * @param array $evaluation_result Full rule evaluation array.
		 */
		$response_code = (int) apply_filters( 'fwm_feed_response_code', $response_code, $evaluation_result );

		// Set X-Robots-Tag header if enabled.
		if ( ! empty( $settings['enable_x_robots_tag'] ) ) {
			if ( ! headers_sent() ) {
				header( 'X-Robots-Tag: noindex, nofollow', true );
			}
		}

		// Prevent caching of blocked feeds if configured.
		if ( ! headers_sent() ) {
			header( 'Cache-Control: no-cache, no-store, must-revalidate, max-age=0' );
			header( 'Pragma: no-cache' );
			header( 'Expires: 0' );
		}

		// Handle Redirects (301 or 302).
		if ( 301 === $response_code || 302 === $response_code ) {
			$redirect_url = ! empty( $evaluation_result['redirect_url'] ) ? $evaluation_result['redirect_url'] : home_url();

			/**
			 * Filter the redirect URL for blocked feeds.
			 *
			 * @since 2.0.0
			 * @param string $redirect_url Target redirect URL.
			 * @param int $response_code 301 or 302.
			 * @param array $evaluation_result Full rule evaluation array.
			 */
			$redirect_url = apply_filters( 'fwm_feed_redirect_url', $redirect_url, $response_code, $evaluation_result );

			wp_safe_redirect( esc_url_raw( $redirect_url ), $response_code );
			exit;
		}

		// Handle 410 Gone.
		if ( 410 === $response_code ) {
			self::render_410();
			exit;
		}

		// Handle 404 Not Found (Default).
		self::render_404();
		exit;
	}

	/**
	 * Send 404 Not Found response and terminate cleanly without RSS XML rendering.
	 */
	private static function render_404() {
		global $wp_query;

		status_header( 404 );
		nocache_headers();

		if ( $wp_query instanceof WP_Query ) {
			$wp_query->set_404();
		}

		// Look for theme's 404 template.
		$template_404 = get_404_template();

		if ( ! empty( $template_404 ) && file_exists( $template_404 ) ) {
			include $template_404;
		} else {
			// Fallback clean HTML 404 page.
			self::render_fallback_page(
				__( '404 - Feed Not Found', 'feed-url-manager' ),
				__( 'The requested feed is disabled or does not exist.', 'feed-url-manager' ),
				404
			);
		}
	}

	/**
	 * Send 410 Gone response.
	 */
	private static function render_410() {
		global $wp_query;

		status_header( 410 );
		nocache_headers();

		if ( $wp_query instanceof WP_Query ) {
			$wp_query->is_404 = false;
		}

		// Look for custom theme 410 template or standard 404 template.
		$template_410 = locate_template( array( '410.php', '404.php' ) );

		if ( ! empty( $template_410 ) && file_exists( $template_410 ) ) {
			include $template_410;
		} else {
			self::render_fallback_page(
				__( '410 - Feed Gone', 'feed-url-manager' ),
				__( 'The requested feed has been permanently removed or disabled.', 'feed-url-manager' ),
				410
			);
		}
	}

	/**
	 * Clean, accessible fallback error page when no theme template is available.
	 *
	 * @param string $title Page title.
	 * @param string $message User friendly explanation.
	 * @param int    $code HTTP status code.
	 */
	private static function render_fallback_page( $title, $message, $code ) {
		if ( ! headers_sent() ) {
			header( 'Content-Type: text/html; charset=' . get_bloginfo( 'charset' ), true, $code );
		}
		?>
		<!DOCTYPE html>
		<html <?php language_attributes(); ?>>
		<head>
			<meta charset="<?php bloginfo( 'charset' ); ?>">
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<meta name="robots" content="noindex, nofollow">
			<title><?php echo esc_html( $title ); ?> - <?php echo esc_html( get_bloginfo( 'name' ) ); ?></title>
			<style>
				body {
					font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
					background: #f0f2f5;
					color: #1e293b;
					display: flex;
					align-items: center;
					justify-content: center;
					min-height: 100vh;
					margin: 0;
					padding: 20px;
					box-sizing: border-box;
				}
				.card {
					background: #ffffff;
					border-radius: 12px;
					padding: 40px;
					max-width: 520px;
					width: 100%;
					box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
					text-align: center;
				}
				.code-badge {
					display: inline-block;
					background: #fee2e2;
					color: #dc2626;
					font-weight: 700;
					font-size: 14px;
					padding: 4px 12px;
					border-radius: 9999px;
					margin-bottom: 16px;
				}
				h1 {
					font-size: 24px;
					margin: 0 0 12px;
					color: #0f172a;
				}
				p {
					font-size: 15px;
					line-height: 1.6;
					color: #64748b;
					margin: 0 0 24px;
				}
				.btn {
					display: inline-block;
					background: #2563eb;
					color: #ffffff;
					text-decoration: none;
					padding: 10px 20px;
					border-radius: 6px;
					font-weight: 600;
					font-size: 14px;
					transition: background 0.2s;
				}
				.btn:hover {
					background: #1d4ed8;
				}
			</style>
		</head>
		<body>
			<div class="card">
				<div class="code-badge"><?php echo esc_html( (string) $code ); ?></div>
				<h1><?php echo esc_html( $title ); ?></h1>
				<p><?php echo esc_html( $message ); ?></p>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn">
					<?php esc_html_e( 'Return to Homepage', 'feed-url-manager' ); ?>
				</a>
			</div>
		</body>
		</html>
		<?php
	}

	/**
	 * Log debug information to option for administrator inspection.
	 *
	 * @param array $evaluation_result
	 */
	private static function log_debug_entry( $evaluation_result ) {
		$log = get_option( FWM_DEBUG_LOG_OPTION, array() );
		if ( ! is_array( $log ) ) {
			$log = array();
		}

		$entry = array(
			'timestamp'     => current_time( 'mysql' ),
			'url'           => isset( $evaluation_result['detection_data']['requested_url'] ) ? $evaluation_result['detection_data']['requested_url'] : '',
			'feed_type'     => isset( $evaluation_result['detection_data']['feed_type'] ) ? $evaluation_result['detection_data']['feed_type'] : '',
			'object_type'   => isset( $evaluation_result['detection_data']['object_type'] ) ? $evaluation_result['detection_data']['object_type'] : '',
			'feed_format'   => isset( $evaluation_result['detection_data']['feed_format'] ) ? $evaluation_result['detection_data']['feed_format'] : '',
			'decision'      => isset( $evaluation_result['decision'] ) ? $evaluation_result['decision'] : '',
			'matched_level' => isset( $evaluation_result['matched_level'] ) ? $evaluation_result['matched_level'] : '',
			'rule_name'     => isset( $evaluation_result['rule_name'] ) ? $evaluation_result['rule_name'] : '',
			'response_code' => isset( $evaluation_result['response_code'] ) ? $evaluation_result['response_code'] : 404,
			'ip'            => isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '',
		);

		// Prepend to top.
		array_unshift( $log, $entry );

		// Keep only last 100 entries.
		if ( count( $log ) > 100 ) {
			$log = array_slice( $log, 0, 100 );
		}

		update_option( FWM_DEBUG_LOG_OPTION, $log, false );
	}
}
