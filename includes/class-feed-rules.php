<?php
/**
 * Feed Rules Evaluation Engine.
 *
 * @package FeedURLManagerPro
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class FWM_Feed_Rules
 *
 * Evaluates incoming feed detection details against defined rules in hierarchical priority:
 * 1. Specific URL Rule
 * 2. Post Type Rule
 * 3. Taxonomy Rule
 * 4. Feed Type Rule
 * 5. Global Rule
 * 6. Allow request (Default)
 */
class FWM_Feed_Rules {

	/**
	 * Decision constant for blocking feed.
	 */
	const ACTION_BLOCK = 'block';

	/**
	 * Decision constant for allowing feed.
	 */
	const ACTION_ALLOW = 'allow';

	/**
	 * Evaluate the feed request and return decision data.
	 *
	 * @param array $detection The detection array from FWM_Feed_Detector.
	 * @return array Decision evaluation result.
	 */
	public static function evaluate( $detection ) {
		$result = array(
			'decision'       => self::ACTION_ALLOW,
			'matched_level'  => '',
			'rule_id'        => '',
			'rule_name'      => '',
			'response_code'  => 404,
			'redirect_url'   => '',
			'reason'         => 'No matching blocking rule found.',
			'detection_data' => $detection,
		);

		// If not even a feed request, immediately allow.
		if ( empty( $detection['is_feed'] ) ) {
			return $result;
		}

		$settings = FWM_Settings::get_settings();
		$rules    = FWM_Settings::get_rules();

		// Default response fallback from settings.
		$default_response = isset( $settings['default_response'] ) ? (int) $settings['default_response'] : 404;
		$default_redirect = isset( $settings['redirect_target'] ) ? esc_url_raw( $settings['redirect_target'] ) : home_url();

		// -------------------------------------------------------------
		// LEVEL 1: Specific URL Rules (Highest Priority)
		// -------------------------------------------------------------
		$url_rule_match = self::match_url_rules( $detection['requested_url'], $rules, $default_response, $default_redirect );
		if ( false !== $url_rule_match ) {
			$result['decision']      = $url_rule_match['status'] === 'disabled' ? self::ACTION_ALLOW : self::ACTION_BLOCK;
			$result['matched_level'] = 'specific_url';
			$result['rule_id']       = $url_rule_match['id'];
			$result['rule_name']     = sprintf(
				/* translators: 1: URL pattern, 2: Match type */
				__( 'URL Rule: "%1$s" (%2$s)', 'feed-url-manager' ),
				$url_rule_match['pattern'],
				$url_rule_match['match_type']
			);
			$result['response_code'] = (int) $url_rule_match['action'];
			$result['redirect_url']  = ! empty( $url_rule_match['redirect_url'] ) ? $url_rule_match['redirect_url'] : $default_redirect;
			$result['reason']        = __( 'Matched custom URL rule.', 'feed-url-manager' );

			return self::apply_filters( $result );
		}

		// -------------------------------------------------------------
		// LEVEL 2: Post Type Rules
		// -------------------------------------------------------------
		if ( 'post_type' === $detection['feed_type'] || 'post_comments' === $detection['feed_type'] ) {
			$post_type = ! empty( $detection['object_type'] ) ? $detection['object_type'] : 'post';
			$disabled_post_types = isset( $settings['disabled_post_types'] ) && is_array( $settings['disabled_post_types'] ) ? $settings['disabled_post_types'] : array();

			if ( in_array( $post_type, $disabled_post_types, true ) ) {
				$post_type_obj = get_post_type_object( $post_type );
				$label = $post_type_obj ? $post_type_obj->labels->singular_name : $post_type;

				$result['decision']      = self::ACTION_BLOCK;
				$result['matched_level'] = 'post_type';
				$result['rule_id']       = 'post_type_' . $post_type;
				$result['rule_name']     = sprintf(
					/* translators: %s: Post type label */
					__( 'Post Type: %s', 'feed-url-manager' ),
					$label
				);
				$result['response_code'] = $default_response;
				$result['redirect_url']  = $default_redirect;
				$result['reason']        = sprintf(
					/* translators: %s: Post type */
					__( 'Feeds for post type "%s" are disabled in settings.', 'feed-url-manager' ),
					$label
				);

				return self::apply_filters( $result );
			}
		}

		// -------------------------------------------------------------
		// LEVEL 3: Taxonomy Rules
		// -------------------------------------------------------------
		if ( 'taxonomy' === $detection['feed_type'] ) {
			$taxonomy = ! empty( $detection['object_type'] ) ? $detection['object_type'] : 'category';
			$disabled_taxonomies = isset( $settings['disabled_taxonomies'] ) && is_array( $settings['disabled_taxonomies'] ) ? $settings['disabled_taxonomies'] : array();

			if ( in_array( $taxonomy, $disabled_taxonomies, true ) ) {
				$tax_obj = get_taxonomy( $taxonomy );
				$label = $tax_obj ? $tax_obj->labels->singular_name : $taxonomy;

				$result['decision']      = self::ACTION_BLOCK;
				$result['matched_level'] = 'taxonomy';
				$result['rule_id']       = 'taxonomy_' . $taxonomy;
				$result['rule_name']     = sprintf(
					/* translators: %s: Taxonomy label */
					__( 'Taxonomy: %s', 'feed-url-manager' ),
					$label
				);
				$result['response_code'] = $default_response;
				$result['redirect_url']  = $default_redirect;
				$result['reason']        = sprintf(
					/* translators: %s: Taxonomy */
					__( 'Feeds for taxonomy "%s" are disabled in settings.', 'feed-url-manager' ),
					$label
				);

				return self::apply_filters( $result );
			}
		}

		// -------------------------------------------------------------
		// LEVEL 4: Specific Feed Type Rules
		// -------------------------------------------------------------
		$disabled_feed_types = isset( $settings['disabled_feed_types'] ) && is_array( $settings['disabled_feed_types'] ) ? $settings['disabled_feed_types'] : array();

		// Check standard feed categories (main, comments, author, date, search).
		if ( in_array( $detection['feed_type'], $disabled_feed_types, true ) ) {
			$result['decision']      = self::ACTION_BLOCK;
			$result['matched_level'] = 'feed_type';
			$result['rule_id']       = 'feed_type_' . $detection['feed_type'];
			$result['rule_name']     = sprintf(
				/* translators: %s: Feed type identifier */
				__( 'Feed Type: %s', 'feed-url-manager' ),
				ucwords( str_replace( '_', ' ', $detection['feed_type'] ) )
			);
			$result['response_code'] = $default_response;
			$result['redirect_url']  = $default_redirect;
			$result['reason']        = sprintf(
				/* translators: %s: Feed type */
				__( 'Feed type "%s" is disabled in settings.', 'feed-url-manager' ),
				$detection['feed_type']
			);

			return self::apply_filters( $result );
		}

		// Check feed format rule (rdf, atom, rss, etc.).
		$disabled_formats = isset( $settings['disabled_formats'] ) && is_array( $settings['disabled_formats'] ) ? $settings['disabled_formats'] : array();
		if ( in_array( $detection['feed_format'], $disabled_formats, true ) ) {
			$result['decision']      = self::ACTION_BLOCK;
			$result['matched_level'] = 'feed_format';
			$result['rule_id']       = 'feed_format_' . $detection['feed_format'];
			$result['rule_name']     = sprintf(
				/* translators: %s: Format name */
				__( 'Feed Format: %s', 'feed-url-manager' ),
				strtoupper( $detection['feed_format'] )
			);
			$result['response_code'] = $default_response;
			$result['redirect_url']  = $default_redirect;
			$result['reason']        = sprintf(
				/* translators: %s: Format */
				__( 'Feed format "%s" is disabled in settings.', 'feed-url-manager' ),
				strtoupper( $detection['feed_format'] )
			);

			return self::apply_filters( $result );
		}

		// -------------------------------------------------------------
		// LEVEL 5: Global Rule (Disable All Feeds)
		// -------------------------------------------------------------
		if ( ! empty( $settings['disable_all_feeds'] ) ) {
			$result['decision']      = self::ACTION_BLOCK;
			$result['matched_level'] = 'global';
			$result['rule_id']       = 'global_disable_all';
			$result['rule_name']     = __( 'Global: All Feeds Disabled', 'feed-url-manager' );
			$result['response_code'] = $default_response;
			$result['redirect_url']  = $default_redirect;
			$result['reason']        = __( 'Global Feed Blocking is enabled.', 'feed-url-manager' );

			return self::apply_filters( $result );
		}

		// -------------------------------------------------------------
		// LEVEL 6: Allow Request (Default Fallback)
		// -------------------------------------------------------------
		return self::apply_filters( $result );
	}

	/**
	 * Match requested URL against defined custom URL rules.
	 *
	 * @param string $requested_url The URL path requested.
	 * @param array  $rules List of custom URL rules.
	 * @param int    $default_response Default response code.
	 * @param string $default_redirect Default redirect URL.
	 * @return array|false Matched rule array or false if none matched.
	 */
	public static function match_url_rules( $requested_url, $rules, $default_response = 404, $default_redirect = '' ) {
		if ( empty( $rules ) || ! is_array( $rules ) ) {
			return false;
		}

		$normalized_url = '/' . trim( $requested_url, '/' ) . '/';
		$clean_url_path = trim( $requested_url, '/' );

		foreach ( $rules as $rule ) {
			// Skip inactive rules.
			if ( empty( $rule['status'] ) || 'active' !== $rule['status'] ) {
				continue;
			}

			$pattern    = trim( $rule['pattern'] );
			$match_type = ! empty( $rule['match_type'] ) ? $rule['match_type'] : 'exact';

			$is_matched = false;

			switch ( $match_type ) {
				case 'exact':
					$normalized_pattern = '/' . trim( $pattern, '/' ) . '/';
					if ( $normalized_pattern === $normalized_url || '/' . trim( $pattern, '/' ) === '/' . $clean_url_path ) {
						$is_matched = true;
					}
					break;

				case 'contains':
					$clean_pattern = trim( $pattern, '/' );
					if ( ! empty( $clean_pattern ) && false !== stripos( $clean_url_path, $clean_pattern ) ) {
						$is_matched = true;
					}
					break;

				case 'wildcard':
					// Convert glob wildcard '*' to regex '.*'.
					$regex = '#^' . str_replace( '\*', '.*', preg_quote( trim( $pattern, '/' ), '#' ) ) . '$#i';
					if ( preg_match( $regex, $clean_url_path ) ) {
						$is_matched = true;
					}
					break;

				case 'regex':
					// Advanced regex match with safe error handling.
					$regex = $pattern;
					// Wrap in delimiters if missing.
					if ( substr( $regex, 0, 1 ) !== '#' && substr( $regex, 0, 1 ) !== '/' ) {
						$regex = '#' . $regex . '#i';
					}

					// Test regex safely without throwing fatal error.
					$test_match = @preg_match( $regex, $requested_url );
					if ( false !== $test_match && 1 === $test_match ) {
						$is_matched = true;
					}
					break;
			}

			if ( $is_matched ) {
				return array(
					'id'           => isset( $rule['id'] ) ? $rule['id'] : md5( $pattern ),
					'pattern'      => $pattern,
					'match_type'   => $match_type,
					'action'       => ! empty( $rule['action'] ) ? (int) $rule['action'] : $default_response,
					'redirect_url' => ! empty( $rule['redirect_url'] ) ? esc_url_raw( $rule['redirect_url'] ) : $default_redirect,
					'status'       => $rule['status'],
				);
			}
		}

		return false;
	}

	/**
	 * Apply developer filter before returning final rule evaluation.
	 *
	 * @param array $result
	 * @return array
	 */
	private static function apply_filters( $result ) {
		/**
		 * Filter the final feed rule evaluation decision.
		 *
		 * @since 2.0.0
		 * @param array $result Rule decision data array.
		 */
		return apply_filters( 'fwm_feed_rule_result', $result );
	}
}
