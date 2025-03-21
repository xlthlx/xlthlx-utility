<?php
/**
 * Minify HTML.
 *
 * @package  xlthlx_utility
 */

/**
 * Init minify.
 *
 * @return void
 */
function xlt_init_minify_html(): void {
	if ( ! is_user_logged_in() ) {
		ob_start( 'xlt_minify_html_output' );
	}
}

if ( ! ( defined( 'WP_CLI' ) && WP_CLI ) && ! is_admin() ) {
	add_action( 'init', 'xlt_init_minify_html', 1 );
}

/**
 * Minify HTML.
 *
 * @param string $buffer The HTML buffer.
 *
 * @return array|string|string[]
 */
function xlt_minify_html_output( string $buffer ): array|string {
	if ( str_starts_with( ltrim( $buffer ), '<?xml' ) ) {
		return ( $buffer );
	}

	$mod = '/u';

	$buffer = str_replace(
		array( chr( 13 ) . chr( 10 ), chr( 9 ) ),
		array( chr( 10 ), '' ),
		$buffer
	);
	$buffer = str_ireplace(
		array(
			'<script',
			'/script>',
			'<pre',
			'/pre>',
			'<textarea',
			'/textarea>',
			'<style',
			'/style>',
		),
		array(
			'M1N1FY-ST4RT<script',
			'/script>M1N1FY-3ND',
			'M1N1FY-ST4RT<pre',
			'/pre>M1N1FY-3ND',
			'M1N1FY-ST4RT<textarea',
			'/textarea>M1N1FY-3ND',
			'M1N1FY-ST4RT<style',
			'/style>M1N1FY-3ND',
		),
		$buffer
	);
	$split  = explode( 'M1N1FY-3ND', $buffer );
	$buffer = '';
	foreach ( $split as $i_value ) {
		$ii = strpos( $i_value, 'M1N1FY-ST4RT' );
		if ( false !== $ii ) {
			$process = substr( $i_value, 0, $ii );
			$asis    = substr( $i_value, $ii + 12 );
			if ( 0 === strpos( $asis, '<script' ) ) {
				$split2 = explode( chr( 10 ), $asis );
				$asis   = '';
				foreach ( $split2 as $iii_value ) {
					if ( $iii_value ) {
						$asis .= trim( $iii_value ) . chr( 10 );
					}
					if ( str_contains( $iii_value, '//' ) && str_ends_with( trim( $iii_value ), ';' ) ) {
						$asis .= chr( 10 );
					}
				}
				if ( $asis ) {
					$asis = substr( $asis, 0, - 1 );
				}
				$asis = preg_replace(
					'!/\*[^*]*\*+([^/][^*]*\*+)*/!',
					'',
					$asis
				);

				$asis = str_replace(
					array(
						';' . chr( 10 ),
						'>' . chr( 10 ),
						'{' . chr( 10 ),
						'}' . chr( 10 ),
						',' . chr( 10 ),
					),
					array( ';', '>', '{', '}', ',' ),
					$asis
				);

			} elseif ( str_starts_with( $asis, '<style' ) ) {
				$asis = preg_replace(
					array(
						'/\>[^\S ]+' . $mod,
						'/[^\S ]+\<' . $mod,
						'/(\s)+' . $mod,
					),
					array( '>', '<', '\\1' ),
					$asis
				);

				$asis = preg_replace(
					'!/\*[^*]*\*+([^/][^*]*\*+)*/!',
					'',
					$asis
				);

				$asis = str_replace(
					array(
						chr( 10 ),
						' {',
						'{ ',
						' }',
						'} ',
						'( ',
						' )',
						' :',
						': ',
						' ;',
						'; ',
						' ,',
						', ',
						';}',
					),
					array(
						'',
						'{',
						'{',
						'}',
						'}',
						'(',
						')',
						':',
						':',
						';',
						';',
						',',
						',',
						'}',
					),
					$asis
				);
			}
		} else {
			$process = $i_value;
			$asis    = '';
		}
		$process = preg_replace(
			array(
				'/\>[^\S ]+' . $mod,
				'/[^\S ]+\<' . $mod,
				'/(\s)+' . $mod,
			),
			array( '>', '<', '\\1' ),
			$process
		);

		$process = preg_replace(
			'/<!--(?!\s*(?:\[if [^\]]+]|<!|>))(?:(?!-->).)*-->' . $mod,
			'',
			$process
		);

		$buffer .= $process . $asis;
	}
	$buffer = str_replace(
		array(
			chr( 10 ) . '<script',
			chr( 10 ) . '<style',
			'*/' . chr( 10 ),
			'M1N1FY-ST4RT',
		),
		array( '<script', '<style', '*/', '' ),
		$buffer
	);

	if ( 0 === stripos(
		ltrim( $buffer ),
		'<!doctype html>'
	) ) {
		$buffer = str_replace( ' />', '>', $buffer );
	}

	if ( isset( $_SERVER['HTTP_HOST'] ) ) {
		$buffer = str_replace(
			array(
				'https://' . esc_url_raw( wp_unslash( $_SERVER['HTTP_HOST'] ) ) . '/',
				'http://' . esc_url_raw( wp_unslash( $_SERVER['HTTP_HOST'] ) ) . '/',
				'//' . esc_url_raw( wp_unslash( $_SERVER['HTTP_HOST'] ) ) . '/',
			),
			array( '/', '/', '/' ),
			$buffer
		);
	}

	$buffer = str_replace(
		array(
			'<meta property="og:image" content="/',
			'<meta name="twitter:image" content="/',
		),
		array(
			'<meta property="og:image" content="https://xlthlx.com/',
			'<meta name="twitter:image" content="https://xlthlx.com/',
		),
		$buffer
	);

	return ( $buffer );
}
