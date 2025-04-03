<?php
/**
 * Feed RSS functions for English translation.
 *
 * @package  xlthlx_utility
 */

/**
 * Add custom feed url for English.
 *
 * @return void
 */
function add_custom_feed(): void {
	add_feed( 'english', 'render_custom_feed' );
}

add_action( 'init', 'add_custom_feed' );

/**
 * Render the custom feed for English.
 *
 * @return void
 */
function render_custom_feed(): void {
	global $lang;
	$lang = 'en';

	header( 'Content-Type: application/rss+xml' );
	include ABSPATH . 'wp-includes/feed-rss2.php';
}

/**
 * Filters the blog title for display of the English feed title.
 *
 * @param string $wp_title_rss The current blog title.
 *
 * @return string
 */
function xlt_wp_title_rss( string $wp_title_rss ): string {
	global $lang;

	if ( 'en' === $lang ) {
		return get_option( 'english_title', '' );
	}

	return $wp_title_rss;
}

add_filter( 'wp_title_rss', 'xlt_wp_title_rss' );

/**
 * Filters the English bloginfo for use in RSS feeds.
 *
 * @param string $info Converted string value of the blog information.
 * @param string $show The type of blog information to retrieve.
 *
 * @return string
 */
function xlt_get_bloginfo_rss( string $info, string $show ): string {
	global $lang;

	if ( 'en' === $lang ) {

		$description = get_option( 'english_tagline' ) ? get_option( 'english_tagline' ) : '';

		switch ( $show ) {
			case 'description':
				$info = $description;
				break;
			case 'language':
				$info = 'en-GB';
				break;
		}
	}

	return $info;
}

add_filter( 'get_bloginfo_rss', 'xlt_get_bloginfo_rss', 10, 2 );

/**
 * Filters the post comments feed permalink.
 *
 * @param string $url Post comments feed permalink.
 *
 * @return string
 */
function xlt_post_comments_feed_link( string $url ): string {
	global $lang;

	if ( 'en' === $lang ) {
		return '';
	}

	return $url;
}

add_filter( 'post_comments_feed_link', 'xlt_post_comments_feed_link' );
