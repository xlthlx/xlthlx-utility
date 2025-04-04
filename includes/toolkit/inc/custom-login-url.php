<?php
/**
 * Custom login.
 *
 * @package  xlthlx_utility
 */

$xlt_login = get_option( 'xlt_login_path' );

if ( '' !== $xlt_login ) {
	$wp_login_php = false;
	add_action( 'wp_head', 'ob_start', 1, 0 );

	/**
	 * Check if an url uses trailing slashes.
	 *
	 * @return bool
	 */
	function xlt_use_trailing_slashes(): bool {
		return str_ends_with( get_option( 'permalink_structure' ), '/' );
	}

	/**
	 * Adds trailing slashes to url.
	 *
	 * @param string $string The url.
	 *
	 * @return string
	 */
	function xlt_user_trailingslashit( string $string ): string {
		return xlt_use_trailing_slashes() ? trailingslashit( $string ) : untrailingslashit( $string );
	}

	/**
	 * Define $pagenow content (context).
	 *
	 * @return void
	 */
	function xlt_plugins_loaded(): void {
		global $pagenow, $xlt_login, $wp_login_php;

		$request = isset( $_SERVER['REQUEST_URI'] ) ? wp_parse_url( esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) ) : '';

		if ( ! is_admin() && ( str_contains( $request['path'], 'wp-login.php' ) || ( isset( $request['path'] ) && untrailingslashit( $request['path'] ) === site_url( 'wp-login', 'relative' ) ) ) ) {
			$wp_login_php           = true;
			$_SERVER['REQUEST_URI'] = xlt_user_trailingslashit(
				'/' . str_repeat(
					'-/',
					10
				)
			);
			// @codingStandardsIgnoreStart
			$pagenow = 'index.php';
			// @codingStandardsIgnoreEnd

		} elseif ( ( ! get_option( 'permalink_structure' ) && isset( $_GET['xlt_login'] ) && empty( $_GET['xlt_login'] ) ) || ( isset( $request['path'] ) && untrailingslashit( $request['path'] ) === home_url( $xlt_login, 'relative' ) ) ) {

			// @codingStandardsIgnoreStart
			$pagenow = 'wp-login.php';
			// @codingStandardsIgnoreEnd
		}
	}

	add_action( 'after_setup_theme', 'xlt_plugins_loaded', 1 );

	/**
	 * Redirects to 404 the wp-admin folder if user is not logged in.
	 *
	 * @return void
	 */
	function xlt_wp_loaded(): void {
		global $pagenow, $wp_login_php;

		if ( ! defined( 'DOING_AJAX' ) && is_admin() && ! is_user_logged_in() ) {
			global $wp_query;
			$wp_query->set_404();
			status_header( 404 );
			get_template_part( 404 );
			exit();
		}

		$request = isset( $_SERVER['REQUEST_URI'] ) ? wp_parse_url( esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) ) : '';

		if ( 'wp-login.php' === $pagenow && xlt_user_trailingslashit( $request['path'] ) !== $request['path'] && get_option( 'permalink_structure' ) ) {
			wp_safe_redirect( xlt_user_trailingslashit( xlt_new_login_url() ) . ( ! empty( $_SERVER['QUERY_STRING'] ) ? '?' . esc_url_raw( wp_unslash( $_SERVER['QUERY_STRING'] ) ) : '' ) );
			die;
		}

		if ( $wp_login_php ) {
			$referer   = wp_get_referer();
			$i_referer = wp_parse_url( $referer );
			if ( ! empty( $i_referer['query'] ) && str_contains( $referer, 'wp-activate.php' ) ) {
				parse_str( $referer, $referer['query'] );

				$result = wpmu_activate_signup( $referer['key'] );
				if ( ! empty( $referer['key'] ) && is_wp_error( $result ) && (
						$result->get_error_code() === 'already_active' ||
						$result->get_error_code() === 'blog_taken'
					) ) {
					wp_safe_redirect( xlt_new_login_url() . ( ! empty( $_SERVER['QUERY_STRING'] ) ? '?' . esc_url_raw( wp_unslash( $_SERVER['QUERY_STRING'] ) ) : '' ) );
					die;
				}
			}

			xlt_wp_template_loader();
		} elseif ( 'wp-login.php' === $pagenow ) {

			require ABSPATH . 'wp-login.php';
			die;
		}
	}

	add_action( 'wp_loaded', 'xlt_wp_loaded' );

	/**
	 * Rewrite the request uri.
	 *
	 * @return void
	 */
	function xlt_wp_template_loader(): void {
		global $pagenow;

		// @codingStandardsIgnoreStart
		$pagenow = 'index.php';
		// @codingStandardsIgnoreEnd

		if ( ! defined( 'WP_USE_THEMES' ) ) {
			define( 'WP_USE_THEMES', true );
		}

		wp();

		if ( isset( $_SERVER['REQUEST_URI'] ) && xlt_user_trailingslashit( str_repeat( '-/', 10 ) ) === $_SERVER['REQUEST_URI'] ) {
			$_SERVER['REQUEST_URI'] = xlt_user_trailingslashit( '/wp-login-php/' );
		}

		require_once ABSPATH . WPINC . '/template-loader.php';

		die;
	}

	/**
	 * Filter login.
	 *
	 * @param string      $url The complete site URL including scheme and path.
	 * @param string|null $scheme Scheme to give the site URL context. Accepts 'http', 'https', 'login', 'login_post', 'admin', 'relative' or null.
	 *
	 * @return string
	 */
	function xlt_filter_wp_login_php( string $url, string $scheme = null ): string {
		if ( str_contains( $url, 'wp-login.php' ) ) {
			if ( is_ssl() ) {
				$scheme = 'https';
			}

			$args = explode( '?', $url );

			if ( isset( $args[1] ) ) {
				parse_str( $args[1], $args );
				$url = add_query_arg( $args, xlt_new_login_url( $scheme ) );
			} else {
				$url = xlt_new_login_url( $scheme );
			}
		}

		return $url;
	}

	/**
	 * Filters the site URL.
	 *
	 * @param string      $url The complete site URL including scheme and path.
	 * @param string      $path Path relative to the site URL. Blank string if no path is specified.
	 * @param string|null $scheme Scheme to give the site URL context. Accepts 'http', 'https', 'login', 'login_post', 'admin', 'relative' or null.
	 *
	 * @return string
	 */
	function xlt_site_url( string $url, string $path, ?string $scheme ): string {
		return xlt_filter_wp_login_php( $url, $scheme );
	}

	add_filter( 'site_url', 'xlt_site_url', 10, 4 );

	/**
	 * Redirects to the login.
	 *
	 * @param string $location The path or URL to redirect to.
	 *
	 * @return string
	 */
	function xlt_wp_redirect( string $location ): string {
		return xlt_filter_wp_login_php( $location );
	}

	add_filter( 'wp_redirect', 'xlt_wp_redirect' );

	/**
	 * Sets new login url.
	 *
	 * @param string|null $scheme Scheme to give the site URL context.
	 *
	 * @return string
	 */
	function xlt_new_login_url( string $scheme = null ): string {
		global $xlt_login;
		if ( get_option( 'permalink_structure' ) ) {
			return xlt_user_trailingslashit(
				home_url(
					'/',
					$scheme
				) . $xlt_login
			);
		}

		return home_url( '/', $scheme ) . '?' . $xlt_login;
	}

	/**
	 * Replace the url into the welcome email.
	 *
	 * @param string $value Value of network option.
	 *
	 * @return string|string[]
	 */
	function xlt_welcome_email( string $value ): array|string {
		global $xlt_login;

		return str_replace(
			'wp-login.php',
			trailingslashit( $xlt_login ),
			$value
		);
	}

	add_filter( 'site_option_welcome_email', 'xlt_welcome_email' );

	/**
	 * Removes the 'admin-bar' class from body.
	 *
	 * @param string[] $wp_classes An array of body class names.
	 * @param string[] $extra_classes An array of additional class names added to the body.
	 *
	 * @return array
	 */
	function xlt_admin_bar_body_class( array $wp_classes, array $extra_classes ): array {

		if ( ( is_404() ) && ( ! is_user_logged_in() ) ) {
			$wp_nobar_classes = array_diff( $wp_classes, array( 'admin-bar' ) );

			// Add the extra classes back untouched.
			return array_merge( $wp_nobar_classes, $extra_classes );
		}

		return $wp_classes;

	}

	add_filter( 'body_class', 'xlt_admin_bar_body_class', 10, 2 );

	remove_action( 'template_redirect', 'wp_redirect_admin_locations', 1000 );

}
