<?php
/**
 * Sitemap.
 *
 * @package xlthlx_utility
 */

/**
 * Remove users from wp-sitemap.xml.
 *
 * @param string $provider Provider.
 * @param string $name Provider name.
 *
 * @return false|string
 */
function xlt_remove_users_from_wp_sitemap( $provider, $name ) {
	return ( 'users' === $name ) ? false : $provider;
}

add_filter( 'wp_sitemaps_add_provider', 'xlt_remove_users_from_wp_sitemap', 10, 2 );
