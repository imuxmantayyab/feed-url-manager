<?php
/**
 * SEO & Feed Discovery Links Manager.
 *
 * @package FeedURLManagerPro
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class FWM_Feed_Discovery
 *
 * Manages removal and filtering of WordPress feed discovery tags in the HTML <head>
 * without breaking legitimate sitemaps or other alternate head links.
 */
class FWM_Feed_Discovery {

	/**
	 * Initialize feed discovery hooks.
	 */
	public static function init() {
		add_action( 'wp_head', array( __CLASS__, 'process_head_discovery' ), 1 );
		add_filter( 'feed_links_show_posts_feed', array( __CLASS__, 'filter_posts_feed_link' ) );
		add_filter( 'feed_links_show_comments_feed', array( __CLASS__, 'filter_comments_feed_link' ) );
		add_filter( 'feed_links_extra_args', array( __CLASS__, 'filter_extra_feed_links' ) );
	}

	/**
	 * Process wp_head hooks for removing feed discovery tags.
	 */
	public static function process_head_discovery() {
		$settings = FWM_Settings::get_settings();

		$remove_all = ! empty( $settings['disable_all_feeds'] ) || ! empty( $settings['remove_feed_discovery'] );

		/**
		 * Filter whether to remove all feed discovery tags.
		 *
		 * @since 2.0.0
		 * @param bool $remove_all Whether to unhook feed discovery.
		 * @param array $settings Plugin settings.
		 */
		$remove_all = apply_filters( 'fwm_remove_feed_discovery', $remove_all, $settings );

		if ( $remove_all ) {
			// Remove general feed links (main post feed and main comments feed).
			remove_action( 'wp_head', 'feed_links', 2 );

			// Remove extra feed links (category, tag, taxonomy, author, search, post comment feeds).
			remove_action( 'wp_head', 'feed_links_extra', 3 );
		}
	}

	/**
	 * Filter whether to show the main posts feed link in wp_head.
	 *
	 * @param bool $show Current show status.
	 * @return bool
	 */
	public static function filter_posts_feed_link( $show ) {
		$settings = FWM_Settings::get_settings();

		if ( ! empty( $settings['disable_all_feeds'] ) || ! empty( $settings['remove_feed_discovery'] ) ) {
			return false;
		}

		$disabled_feeds = isset( $settings['disabled_feed_types'] ) && is_array( $settings['disabled_feed_types'] ) ? $settings['disabled_feed_types'] : array();
		if ( in_array( 'main', $disabled_feeds, true ) ) {
			return false;
		}

		return $show;
	}

	/**
	 * Filter whether to show the main comments feed link in wp_head.
	 *
	 * @param bool $show Current show status.
	 * @return bool
	 */
	public static function filter_comments_feed_link( $show ) {
		$settings = FWM_Settings::get_settings();

		if ( ! empty( $settings['disable_all_feeds'] ) || ! empty( $settings['remove_feed_discovery'] ) ) {
			return false;
		}

		$disabled_feeds = isset( $settings['disabled_feed_types'] ) && is_array( $settings['disabled_feed_types'] ) ? $settings['disabled_feed_types'] : array();
		if ( in_array( 'comments', $disabled_feeds, true ) ) {
			return false;
		}

		return $show;
	}

	/**
	 * Filter extra feed discovery links (categories, tags, taxonomies, singular comments).
	 *
	 * @param array $args Array of arguments for feed_links_extra.
	 * @return array
	 */
	public static function filter_extra_feed_links( $args ) {
		$settings = FWM_Settings::get_settings();

		if ( ! empty( $settings['disable_all_feeds'] ) || ! empty( $settings['remove_feed_discovery'] ) ) {
			return array();
		}

		// Check if current context is category/tag/tax and disabled.
		$disabled_taxonomies = isset( $settings['disabled_taxonomies'] ) && is_array( $settings['disabled_taxonomies'] ) ? $settings['disabled_taxonomies'] : array();
		$disabled_post_types = isset( $settings['disabled_post_types'] ) && is_array( $settings['disabled_post_types'] ) ? $settings['disabled_post_types'] : array();
		$disabled_feed_types = isset( $settings['disabled_feed_types'] ) && is_array( $settings['disabled_feed_types'] ) ? $settings['disabled_feed_types'] : array();

		if ( is_category() && in_array( 'category', $disabled_taxonomies, true ) ) {
			return array();
		}

		if ( is_tag() && in_array( 'post_tag', $disabled_taxonomies, true ) ) {
			return array();
		}

		if ( is_tax() ) {
			$term = get_queried_object();
			if ( is_object( $term ) && in_array( $term->taxonomy, $disabled_taxonomies, true ) ) {
				return array();
			}
		}

		if ( is_author() && in_array( 'author', $disabled_feed_types, true ) ) {
			return array();
		}

		if ( is_date() && in_array( 'date', $disabled_feed_types, true ) ) {
			return array();
		}

		if ( is_search() && in_array( 'search', $disabled_feed_types, true ) ) {
			return array();
		}

		if ( is_singular() ) {
			$post = get_queried_object();
			if ( is_object( $post ) && in_array( $post->post_type, $disabled_post_types, true ) ) {
				return array();
			}
		}

		return $args;
	}
}
