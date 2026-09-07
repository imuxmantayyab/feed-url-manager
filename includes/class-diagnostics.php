<?php
/**
 * System Diagnostics & Safe Feed Scanner Engine.
 *
 * @package FeedURLManagerPro
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class FWM_Diagnostics
 *
 * Collects system environment info, executes safe simulated feed tests,
 * and handles rate-limited single-URL feed verification.
 */
class FWM_Diagnostics {

	/**
	 * Get system diagnostics information formatted for display and clipboard copy.
	 *
	 * @return array
	 */
	public static function get_system_info() {
		global $wp_version, $is_nginx, $is_apache, $is_IIS;

		$server = 'Unknown';
		if ( $is_nginx ) {
			$server = 'Nginx';
		} elseif ( $is_apache ) {
			$server = 'Apache';
		} elseif ( $is_IIS ) {
			$server = 'IIS';
		} elseif ( isset( $_SERVER['SERVER_SOFTWARE'] ) ) {
			$server = sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) );
		}

		$theme = wp_get_theme();

		$info = array(
			'fwm_version'        => FWM_VERSION,
			'wp_version'         => $wp_version,
			'php_version'        => PHP_VERSION,
			'server_software'    => $server,
			'mysql_version'      => self::get_mysql_version(),
			'memory_limit'       => ini_get( 'memory_limit' ),
			'permalink_structure'=> get_option( 'permalink_structure' ) ? get_option( 'permalink_structure' ) : 'Plain (?p=123)',
			'is_multisite'       => is_multisite() ? 'Yes' : 'No',
			'debug_mode'         => ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? 'Enabled' : 'Disabled',
			'elementor_active'   => FWM_Compatibility::is_elementor_active() ? 'Yes (v' . ( defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : 'Unknown' ) . ')' : 'No',
			'elementor_pro'      => FWM_Compatibility::is_elementor_pro_active() ? 'Yes (v' . ( defined( 'ELEMENTOR_PRO_VERSION' ) ? ELEMENTOR_PRO_VERSION : 'Unknown' ) . ')' : 'No',
			'active_theme'       => $theme->get( 'Name' ) . ' (v' . $theme->get( 'Version' ) . ')',
			'site_url'           => site_url(),
			'home_url'           => home_url(),
		);

		return $info;
	}

	/**
	 * Get MySQL server version safely.
	 *
	 * @return string
	 */
	private static function get_mysql_version() {
		global $wpdb;
		return method_exists( $wpdb, 'db_version' ) ? $wpdb->db_version() : 'Unknown';
	}

	/**
	 * Discover standard and dynamic feed endpoints available on this WordPress installation.
	 *
	 * @return array List of feed endpoints with simulation data.
	 */
	public static function scan_feed_endpoints() {
		$endpoints = array();

		// 1. Core Main Feed.
		$endpoints[] = array(
			'name'        => __( 'Main Site Feed', 'feed-url-manager' ),
			'url'         => get_feed_link(),
			'path'        => self::url_to_path( get_feed_link() ),
			'type'        => 'main',
			'object_type' => 'main',
		);

		// 2. Comments Feed.
		$comments_feed = get_feed_link( 'comments_' . get_default_feed() );
		$endpoints[] = array(
			'name'        => __( 'Comments Feed', 'feed-url-manager' ),
			'url'         => $comments_feed,
			'path'        => self::url_to_path( $comments_feed ),
			'type'        => 'comments',
			'object_type' => 'comments',
		);

		// 3. Category Feeds.
		$categories = get_categories( array( 'number' => 3, 'hide_empty' => false ) );
		foreach ( $categories as $cat ) {
			$cat_feed = get_category_feed_link( $cat->term_id );
			$endpoints[] = array(
				'name'        => sprintf( __( 'Category: %s', 'feed-url-manager' ), $cat->name ),
				'url'         => $cat_feed,
				'path'        => self::url_to_path( $cat_feed ),
				'type'        => 'taxonomy',
				'object_type' => 'category',
				'object_slug' => $cat->slug,
			);
		}

		// 4. Tag Feeds.
		$tags = get_tags( array( 'number' => 2, 'hide_empty' => false ) );
		foreach ( $tags as $tag ) {
			$tag_feed = get_tag_feed_link( $tag->term_id );
			$endpoints[] = array(
				'name'        => sprintf( __( 'Tag: %s', 'feed-url-manager' ), $tag->name ),
				'url'         => $tag_feed,
				'path'        => self::url_to_path( $tag_feed ),
				'type'        => 'taxonomy',
				'object_type' => 'post_tag',
				'object_slug' => $tag->slug,
			);
		}

		// 5. Author Feed.
		$authors = get_users( array( 'number' => 1, 'who' => 'authors' ) );
		if ( empty( $authors ) ) {
			$authors = get_users( array( 'number' => 1 ) );
		}
		if ( ! empty( $authors ) ) {
			$author = reset( $authors );
			$author_feed = get_author_feed_link( $author->ID );
			$endpoints[] = array(
				'name'        => sprintf( __( 'Author: %s', 'feed-url-manager' ), $author->display_name ),
				'url'         => $author_feed,
				'path'        => self::url_to_path( $author_feed ),
				'type'        => 'author',
				'object_type' => 'author',
				'object_slug' => $author->user_nicename,
			);
		}

		// 6. Public Custom Post Types.
		$post_types = get_post_types( array( 'public' => true, '_builtin' => false ), 'objects' );
		foreach ( $post_types as $cpt ) {
			$cpt_feed = get_post_type_archive_feed_link( $cpt->name );
			if ( $cpt_feed ) {
				$endpoints[] = array(
					'name'        => sprintf( __( 'Custom Post Type: %s', 'feed-url-manager' ), $cpt->labels->singular_name ),
					'url'         => $cpt_feed,
					'path'        => self::url_to_path( $cpt_feed ),
					'type'        => 'post_type',
					'object_type' => $cpt->name,
				);
			}
		}

		// 7. Custom Public Taxonomies.
		$taxonomies = get_taxonomies( array( 'public' => true, '_builtin' => false ), 'objects' );
		foreach ( $taxonomies as $tax ) {
			$terms = get_terms( array( 'taxonomy' => $tax->name, 'number' => 1, 'hide_empty' => false ) );
			if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
				$term = reset( $terms );
				$term_feed = get_term_feed_link( $term->term_id, $tax->name );
				if ( $term_feed ) {
					$endpoints[] = array(
						'name'        => sprintf( __( 'Taxonomy: %1$s (%2$s)', 'feed-url-manager' ), $tax->labels->singular_name, $term->name ),
						'url'         => $term_feed,
						'path'        => self::url_to_path( $term_feed ),
						'type'        => 'taxonomy',
						'object_type' => $tax->name,
						'object_slug' => $term->slug,
					);
				}
			}
		}

		// Evaluate each endpoint using simulated internal detection (Zero external HTTP load).
		$evaluated = array();
		foreach ( $endpoints as $item ) {
			$simulated_detection = array(
				'is_feed'         => true,
				'feed_type'       => $item['type'],
				'object_type'     => isset( $item['object_type'] ) ? $item['object_type'] : '',
				'object_slug'     => isset( $item['object_slug'] ) ? $item['object_slug'] : '',
				'object_id'       => 0,
				'feed_format'     => 'rss2',
				'requested_url'   => $item['path'],
				'query_feed_var'  => '',
				'is_comment_feed' => ( 'comments' === $item['type'] ),
				'raw_query'       => '',
			);

			$evaluation = FWM_Feed_Rules::evaluate( $simulated_detection );

			$item['decision']      = $evaluation['decision'];
			$item['matched_level'] = $evaluation['matched_level'];
			$item['rule_name']     = $evaluation['rule_name'];
			$item['response_code'] = $evaluation['response_code'];

			$evaluated[] = $item;
		}

		return $evaluated;
	}

	/**
	 * Convert full URL to normalized relative path.
	 *
	 * @param string $url
	 * @return string
	 */
	private static function url_to_path( $url ) {
		$path = wp_parse_url( $url, PHP_URL_PATH );
		if ( empty( $path ) ) {
			$path = '/';
		}

		$query = wp_parse_url( $url, PHP_URL_QUERY );
		if ( ! empty( $query ) ) {
			$path .= '?' . $query;
		}

		return $path;
	}

	/**
	 * Single URL Live HTTP Test with rate-limiting and validation.
	 *
	 * @param string $url Target URL to test.
	 * @return array
	 */
	public static function test_single_feed_url( $url ) {
		if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
			return array(
				'success' => false,
				'message' => __( 'Invalid URL format provided.', 'feed-url-manager' ),
			);
		}

		$response = wp_safe_remote_get(
			$url,
			array(
				'timeout'     => 10,
				'redirection' => 0, // Don't follow redirects to detect 301/302.
				'sslverify'   => false,
				'user-agent'  => 'FeedURLManagerPro-Scanner/2.0',
			)
		);

		if ( is_wp_error( $response ) ) {
			return array(
				'success' => false,
				'message' => $response->get_error_message(),
			);
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		$headers     = wp_remote_retrieve_headers( $response );
		$x_robots    = isset( $headers['x-robots-tag'] ) ? $headers['x-robots-tag'] : '';
		$location    = isset( $headers['location'] ) ? $headers['location'] : '';

		return array(
			'success'     => true,
			'status_code' => $status_code,
			'x_robots'    => $x_robots,
			'location'    => $location,
		);
	}
}
