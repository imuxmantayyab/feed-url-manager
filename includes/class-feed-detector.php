<?php
/**
 * Feed Detection Engine.
 *
 * @package FeedURLManagerPro
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class FWM_Feed_Detector
 *
 * Responsible for accurate, low-overhead detection of feed requests at the WordPress core level,
 * with strict non-interference for REST API, XML Sitemaps, HTML pages, and Elementor.
 */
class FWM_Feed_Detector {

	/**
	 * Cached detection result for the current request.
	 *
	 * @var array|null
	 */
	private static $detection_result = null;

	/**
	 * Main entry point for detecting feed requests.
	 *
	 * @param WP_Query|null $query Optional custom query object. Defaults to global $wp_query.
	 * @return array Array with detection details.
	 */
	public static function detect( $query = null ) {
		if ( null !== self::$detection_result && null === $query ) {
			return self::$detection_result;
		}

		// Initial default structure.
		$result = array(
			'is_feed'         => false,
			'feed_type'       => '',
			'object_type'     => '',
			'object_slug'     => '',
			'object_id'       => 0,
			'feed_format'     => 'rss2',
			'requested_url'   => self::get_requested_url_path(),
			'query_feed_var'  => '',
			'is_comment_feed' => false,
			'raw_query'       => self::get_query_string(),
		);

		// Step 1: Immediate Safety Exemptions (REST API, Sitemaps, Elementor, Admin).
		if ( self::is_exempt_request() ) {
			if ( null === $query ) {
				self::$detection_result = $result;
			}
			return $result;
		}

		global $wp_query;
		$target_query = ( $query instanceof WP_Query ) ? $query : $wp_query;

		// Step 2: WordPress Query-Level Feed Flag Checks.
		$is_feed = false;
		$feed_format = '';

		if ( $target_query && is_object( $target_query ) ) {
			if ( $target_query->is_feed() ) {
				$is_feed = true;
				$feed_format = (string) $target_query->get( 'feed' );
			}
		}

		// Fallback checking global function is_feed().
		if ( ! $is_feed && function_exists( 'is_feed' ) && is_feed() ) {
			$is_feed = true;
			$feed_format = (string) get_query_var( 'feed' );
		}

		// Step 3: URL Pattern & Query String Fallback Detection (if query variables are being set).
		$url_path = $result['requested_url'];
		$query_string = $result['raw_query'];

		if ( ! $is_feed ) {
			// Check query parameter ?feed=rss2 | ?feed=atom | ?feed=rdf | ?feed=rss
			if ( ! empty( $_GET['feed'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$is_feed = true;
				$feed_format = sanitize_key( $_GET['feed'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			} elseif ( preg_match( '#(/feed/?|/feed/(rss2|atom|rdf|rss)/?)$#i', $url_path, $matches ) ) {
				// Path ends with /feed/ or /feed/[format]/
				$is_feed = true;
				if ( ! empty( $matches[2] ) ) {
					$feed_format = strtolower( $matches[2] );
				}
			}
		}

		// Normalize default feed format if empty.
		if ( empty( $feed_format ) || '1' === $feed_format || 'feed' === $feed_format ) {
			$feed_format = get_default_feed();
			if ( empty( $feed_format ) ) {
				$feed_format = 'rss2';
			}
		}

		$result['feed_format'] = $feed_format;

		if ( ! $is_feed ) {
			if ( null === $query ) {
				self::$detection_result = $result;
			}
			return $result;
		}

		$result['is_feed'] = true;

		// Step 4: Classify Specific Feed Types.
		$feed_classification = self::classify_feed_type( $target_query, $url_path );
		$result = array_merge( $result, $feed_classification );

		/**
		 * Filter the detected feed result.
		 *
		 * @since 2.0.0
		 * @param array $result Detection result array.
		 * @param WP_Query|null $target_query Active query object.
		 */
		$result = apply_filters( 'fwm_detected_feed_type', $result, $target_query );

		if ( null === $query ) {
			self::$detection_result = $result;
		}

		return $result;
	}

	/**
	 * Checks if the current request is strictly exempt from feed processing.
	 *
	 * @return bool True if exempt, false otherwise.
	 */
	public static function is_exempt_request() {
		// 1. Never block wp-admin requests (except AJAX calls triggered explicitly by plugin tools).
		if ( is_admin() && ! wp_doing_ajax() ) {
			return true;
		}

		// 2. Never block REST API requests.
		if ( self::is_rest_request() ) {
			return true;
		}

		// 3. Never block XML Sitemaps.
		if ( self::is_sitemap_request() ) {
			return true;
		}

		// 4. Never block Elementor Editor, Preview, Dynamic CSS, or Elementor AJAX requests.
		if ( self::is_elementor_request() ) {
			return true;
		}

		// 5. Never block WP Cron.
		if ( defined( 'DOING_CRON' ) && DOING_CRON ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if current request is a REST API call.
	 *
	 * @return bool
	 */
	public static function is_rest_request() {
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return true;
		}

		$rest_prefix = function_exists( 'rest_get_url_prefix' ) ? rest_get_url_prefix() : 'wp-json';
		$uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';

		if ( false !== strpos( $uri, '/' . $rest_prefix . '/' ) || false !== strpos( $uri, 'rest_route=' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if current request is an XML Sitemap.
	 *
	 * @return bool
	 */
	public static function is_sitemap_request() {
		$uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		$path = wp_parse_url( $uri, PHP_URL_PATH );

		if ( empty( $path ) ) {
			return false;
		}

		// Core sitemaps (/wp-sitemap.xml, /wp-sitemap-posts-post-1.xml, etc.)
		if ( preg_match( '#wp-sitemap(-[a-z0-9_-]+)?\.xml$#i', $path ) ) {
			return true;
		}

		// Yoast, Rank Math, All in One SEO, XML Sitemap & Google News, etc.
		if ( preg_match( '#(sitemap(-[a-z0-9_-]+)?\.xml|sitemap_index\.xml|sitemap\.xsl)$#i', $path ) ) {
			return true;
		}

		// Query param sitemap
		if ( isset( $_GET['sitemap'] ) || isset( $_GET['sitemap_sub_type'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return true;
		}

		return false;
	}

	/**
	 * Check if current request is an Elementor Editor, Preview, or AJAX request.
	 *
	 * @return bool
	 */
	public static function is_elementor_request() {
		// Elementor Editor or Preview mode.
		if ( isset( $_GET['elementor-preview'] ) || isset( $_GET['elementor_library'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return true;
		}

		// Elementor action requests.
		if ( isset( $_GET['action'] ) && 0 === strpos( sanitize_key( $_GET['action'] ), 'elementor' ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return true;
		}
		if ( isset( $_POST['action'] ) && 0 === strpos( sanitize_key( $_POST['action'] ), 'elementor' ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			return true;
		}

		// Elementor CSS generation request or assets.
		$uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		if ( false !== strpos( $uri, '/uploads/elementor/' ) || false !== strpos( $uri, 'elementor-pro' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Classify the specific feed type and associated object.
	 *
	 * @param WP_Query|null $query The query object.
	 * @param string        $url_path The clean requested URL path.
	 * @return array
	 */
	private static function classify_feed_type( $query, $url_path ) {
		$data = array(
			'feed_type'       => 'main',
			'object_type'     => '',
			'object_slug'     => '',
			'object_id'       => 0,
			'is_comment_feed' => false,
		);

		// 1. Comments Feed Checks (Site-wide or post-specific).
		if ( $query && is_object( $query ) && $query->is_comment_feed() ) {
			$data['is_comment_feed'] = true;
			if ( $query->is_singular() ) {
				$post = $query->get_queried_object();
				$data['feed_type']   = 'post_comments';
				$data['object_type'] = is_object( $post ) ? $post->post_type : 'post';
				$data['object_slug'] = is_object( $post ) ? $post->post_name : '';
				$data['object_id']   = is_object( $post ) ? $post->ID : 0;
			} else {
				$data['feed_type']   = 'comments';
				$data['object_type'] = 'comments';
			}
			return $data;
		}

		// URI check for site comments feed: /comments/feed/
		if ( preg_match( '#/comments/feed/?$#i', $url_path ) || ( isset( $_GET['feed'] ) && 'comments-rss2' === $_GET['feed'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$data['is_comment_feed'] = true;
			$data['feed_type']       = 'comments';
			$data['object_type']     = 'comments';
			return $data;
		}

		// 2. Taxonomy / Archive Feeds (Category, Tag, Custom Taxonomies).
		if ( $query && is_object( $query ) ) {
			if ( $query->is_category() ) {
				$cat = $query->get_queried_object();
				$data['feed_type']   = 'taxonomy';
				$data['object_type'] = 'category';
				$data['object_slug'] = is_object( $cat ) ? $cat->slug : '';
				$data['object_id']   = is_object( $cat ) ? $cat->term_id : 0;
				return $data;
			}

			if ( $query->is_tag() ) {
				$tag = $query->get_queried_object();
				$data['feed_type']   = 'taxonomy';
				$data['object_type'] = 'post_tag';
				$data['object_slug'] = is_object( $tag ) ? $tag->slug : '';
				$data['object_id']   = is_object( $tag ) ? $tag->term_id : 0;
				return $data;
			}

			if ( $query->is_tax() ) {
				$term = $query->get_queried_object();
				$data['feed_type']   = 'taxonomy';
				$data['object_type'] = is_object( $term ) ? $term->taxonomy : '';
				$data['object_slug'] = is_object( $term ) ? $term->slug : '';
				$data['object_id']   = is_object( $term ) ? $term->term_id : 0;
				return $data;
			}

			// 3. Author Archive Feed.
			if ( $query->is_author() ) {
				$author = $query->get_queried_object();
				$data['feed_type']   = 'author';
				$data['object_type'] = 'author';
				$data['object_slug'] = is_object( $author ) ? $author->user_nicename : '';
				$data['object_id']   = is_object( $author ) ? $author->ID : 0;
				return $data;
			}

			// 4. Date Archive Feed.
			if ( $query->is_date() ) {
				$data['feed_type']   = 'date';
				$data['object_type'] = 'date';
				return $data;
			}

			// 5. Search Results Feed.
			if ( $query->is_search() ) {
				$data['feed_type']   = 'search';
				$data['object_type'] = 'search';
				return $data;
			}

			// 6. Post Type Archive or Single Post Feed.
			if ( $query->is_post_type_archive() ) {
				$post_type = $query->get( 'post_type' );
				if ( is_array( $post_type ) ) {
					$post_type = reset( $post_type );
				}
				$data['feed_type']   = 'post_type';
				$data['object_type'] = (string) $post_type;
				$data['object_slug'] = (string) $post_type;
				return $data;
			}

			if ( $query->is_singular() ) {
				$post = $query->get_queried_object();
				$data['feed_type']   = 'post_type';
				$data['object_type'] = is_object( $post ) ? $post->post_type : 'post';
				$data['object_slug'] = is_object( $post ) ? $post->post_name : '';
				$data['object_id']   = is_object( $post ) ? $post->ID : 0;
				return $data;
			}
		}

		// Fallback URL pattern inspection if query object is not yet fully populated.
		if ( preg_match( '#/category/([^/]+)/feed/?#i', $url_path, $matches ) ) {
			$data['feed_type']   = 'taxonomy';
			$data['object_type'] = 'category';
			$data['object_slug'] = sanitize_title( $matches[1] );
			return $data;
		}

		if ( preg_match( '#/tag/([^/]+)/feed/?#i', $url_path, $matches ) ) {
			$data['feed_type']   = 'taxonomy';
			$data['object_type'] = 'post_tag';
			$data['object_slug'] = sanitize_title( $matches[1] );
			return $data;
		}

		if ( preg_match( '#/author/([^/]+)/feed/?#i', $url_path, $matches ) ) {
			$data['feed_type']   = 'author';
			$data['object_type'] = 'author';
			$data['object_slug'] = sanitize_title( $matches[1] );
			return $data;
		}

		// Default is Main Site Feed.
		$data['feed_type']   = 'main';
		$data['object_type'] = 'main';

		return $data;
	}

	/**
	 * Get normalized requested URL path (relative to site root).
	 *
	 * @return string Normalized path, e.g. '/feed/' or '/category/news/feed/'
	 */
	public static function get_requested_url_path() {
		$uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '/';
		$path = wp_parse_url( $uri, PHP_URL_PATH );

		if ( empty( $path ) ) {
			return '/';
		}

		// Remove site home subdirectory if WP is installed in subfolder.
		$home_path = wp_parse_url( home_url(), PHP_URL_PATH );
		if ( ! empty( $home_path ) && '/' !== $home_path ) {
			$home_path = rtrim( $home_path, '/' );
			if ( 0 === strpos( $path, $home_path ) ) {
				$path = substr( $path, strlen( $home_path ) );
			}
		}

		return '/' . ltrim( $path, '/' );
	}

	/**
	 * Get raw query string safely.
	 *
	 * @return string
	 */
	public static function get_query_string() {
		return isset( $_SERVER['QUERY_STRING'] ) ? sanitize_text_field( wp_unslash( $_SERVER['QUERY_STRING'] ) ) : '';
	}

	/**
	 * Reset cached detection (useful for unit testing or sub-queries).
	 */
	public static function reset_cache() {
		self::$detection_result = null;
	}
}
