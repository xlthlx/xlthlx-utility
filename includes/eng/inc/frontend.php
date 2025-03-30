<?php
/**
 * Frontend functions for English translation.
 *
 * @package  xlthlx_utility
 */

// @codingStandardsIgnoreStart
use Highlight\Highlighter;
use DeepL\DeepLException;
use DeepL\Translator;
// @codingStandardsIgnoreEnd

/**
 * Gets absolute url.
 *
 * @return bool|string
 */
function get_abs_url(): bool|string {
	if ( isset( $_SERVER['HTTP_HOST'] ) && ! is_admin() ) {
		$http_host = str_replace( 'http://', 'https://', esc_url_raw( wp_unslash( $_SERVER['HTTP_HOST'] ) ) );
		if ( ( '' === $http_host ) && isset( $_SERVER['SERVER_NAME'], $_SERVER['SERVER_PORT'] ) ) {
			// Localhost.
			$http_host = get_home_url();
		}
		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		return $http_host . $request_uri;
	}

	return false;
}

/**
 * Translate a piece of string.
 *
 * @param string $element The string to translate.
 *
 * @return array|string
 */
function get_trans( string $element ): array|string {
	$trans_content = '';
	$auth_key      = get_option( 'deepl_auth_key' );

	try {
		$translator = new Translator( $auth_key );
	} catch ( DeepLException $e ) {
		$trans_content = $e->getMessage();
	}

	if ( isset( $translator ) ) {
		try {
			$options       = array(
				'preserve_formatting' => true,
				'tag_handling'        => 'html',
			);
			$trans_content = $translator->translateText( $element, 'it', 'en-GB', $options );
		} catch ( DeepLException $e ) {
			$trans_content = $e->getMessage();
		}
	}

	return $trans_content->text;
}

/**
 * Convert the comment date.
 *
 * @param string $date The date to convert.
 *
 * @return string
 */
function xlt_get_comment_date( string $date ): string {
	global $lang;

	if ( 'en' === $lang ) {
		return gmdate( 'd F Y', strtotime( $date ) ) . ' &ndash; ' . gmdate( 'H:i', strtotime( $date ) );
	}

	return date_i18n( 'd F Y', strtotime( $date ) ) . ' &ndash; ' . gmdate( 'H:i', strtotime( $date ) );
}

/**
 * Translate the title.
 *
 * @param int $post_id The post ID.
 *
 * @return string
 */
function get_title_en( int $post_id = 0 ): string {

	if ( 0 === $post_id ) {
		global $post;
		$post_id = $post->ID;
	}

	if ( is_preview() ) {
		$post_id = get_query_var( 'p' );
	}

	$post_type = get_post_type( $post_id );

	if ( 'post' === $post_type || 'page' === $post_type || 'film' === $post_type || 'tvseries' === $post_type ) {
		if ( ! get_post_meta( $post_id, 'title_en', true ) || get_post_meta( $post_id, 'title_en', true ) === '' ) {

			$this_post  = get_post( $post_id );
			$this_title = get_trans( $this_post->post_title );
			update_post_meta( $post_id, 'title_en', $this_title );
		}
	}

	return get_post_meta( $post_id, 'title_en', true );

}

/**
 * Translate the content.
 *
 * @param int $post_id The post ID.
 *
 * @return string
 * @throws Exception Exception.
 */
function get_content_en( int $post_id = 0 ): string {

	if ( 0 === $post_id ) {
		global $post;
		$post_id = $post->ID;
	}

	if ( is_preview() ) {
		$post_id = get_query_var( 'p' );
	}

	$post_type = get_post_type( $post_id );

	if ( 'post' === $post_type || 'page' === $post_type || 'film' === $post_type || 'tvseries' === $post_type ) {

		if ( ! get_post_meta( $post_id, 'content_en', true ) || get_post_meta( $post_id, 'content_en', true ) === '' ) {

			global $post;
			$blocks = parse_blocks( $post->post_content );
			$output = '';

			foreach ( $blocks as $block ) {

				$block_types = array(
					'core/code',
					'core/freeform',
					'core/heading',
					'core/html',
					'core/list',
					'core/list-item',
					'core/paragraph',
					'core/pullquote',
					'core/quote',
					'core/shortcode',
					'core/table',
				);

				if ( isset( $block['blockName'] ) && '' !== $block['blockName'] && in_array(
					$block['blockName'],
					$block_types,
					true
				) ) {

					$name = $block['blockName'];

					switch ( $name ) {
						case 'core/code':
							$block['innerHTML']       = xlt_trans_code( $block['innerHTML'] );
							$block['innerContent'][0] = $block['innerHTML'];
							break;
						case 'core/quote':
							$block = xlt_trans_quote( $block );
							break;
						case 'core/list':
							$block['innerHTML'] = xlt_trans_list( $block );
							break;
						default:
							$block['innerHTML']       = get_trans( $block['innerHTML'] );
							$block['innerContent'][0] = $block['innerHTML'];
							break;
					}
				}
				$output .= render_block( $block );
			}

			$output .= '<!-- Automagically translated. -->';
			update_post_meta( $post_id, 'content_en', $output );
		}

		return get_post_meta( $post_id, 'content_en', true );
	}

	return '';
}

/**
 * Translate a code block.
 *
 * @param string $code The code content.
 *
 * @return string
 * @throws Exception Exception.
 */
function xlt_trans_code( string $code ): string {

	if ( '' === $code ) {
		$hl = new Highlighter();
		$hl->setAutodetectLanguages(
			array(
				'php',
				'javascript',
				'html',
			)
		);

		$code = str_replace(
			array(
				'<pre class="wp-block-code">',
				'<code>',
				'</code>',
				'</pre>',
			),
			'',
			html_entity_decode( $code, ENT_COMPAT, 'UTF-8' )
		);

		$highlighted = $hl->highlightAuto( $code );

		$code = '<pre class="wp-block-code"><code class="hljs ' . $highlighted->language . '">' . $highlighted->value . '</code></pre>';
		if ( isset( $code->outertext ) ) {
			$code->outertext = apply_filters( 'the_content', $code );
		}
	}

	return $code;
}

/**
 * Translate a quote block.
 *
 * @param array $block The quote block.
 *
 * @return array
 */
function xlt_trans_quote( array $block ): array {

	if ( isset( $block['innerBlocks'] ) ) {
		$i = 0;
		foreach ( $block['innerBlocks'] as $inner ) {

			if ( 'core/paragraph' === $inner['blockName'] ) {
				$block['innerBlocks'][ $i ]['innerHTML']       = get_trans( $inner['innerHTML'] );
				$block['innerBlocks'][ $i ]['innerContent'][0] = $block['innerBlocks'][ $i ]['innerHTML'];
			}

			$y = 0;
			if ( 'core/list' === $inner['blockName'] ) {
				foreach ( $inner['innerBlocks'] as $sub_inner ) {
					$block['innerBlocks'][ $i ]['innerBlocks'][ $y ]['innerHTML']       = get_trans( $sub_inner['innerHTML'] );
					$block['innerBlocks'][ $i ]['innerBlocks'][ $y ]['innerContent'][0] = $block['innerBlocks'][ $i ]['innerBlocks'][ $y ]['innerHTML'];
					$y ++;
				}
			}
			$i ++;

		}
	} else {
		$block['innerHTML']      .= get_trans( $block['innerHTML'] );
		$block['innerContent'][0] = $block['innerHTML'];
	}

	return $block;
}

/**
 * Translate list block.
 *
 * @param array $block The list block.
 *
 * @return array
 */
function xlt_trans_list( array $block ): array {
	if ( isset( $block['innerBlocks'] ) ) {
		$y = 0;
		foreach ( $block['innerBlocks'] as $inner ) {
			$block['innerBlocks'][ $y ]['innerHTML']       = get_trans( $inner['innerHTML'] );
			$block['innerBlocks'][ $y ]['innerContent'][0] = $block['innerBlocks'][ $y ]['innerHTML'];
			$y ++;
		}
	}

	return $block;
}

/**
 * Get language var.
 *
 * @return string
 */
function get_lang(): string {
	$link = get_abs_url();

	$lang = 'it';
	$pos  = strpos( $link, '/en/' );

	if ( false !== $pos ) {
		$lang = 'en';
	}

	return $lang;
}

/**
 * Get url for language switcher.
 *
 * @return string
 */
function get_url_trans(): string {

	$link     = get_abs_url();
	$pos      = strpos( $link, '/en/' );
	$pos_page = strpos( $link, '/page/' );

	if ( is_front_page() ) {
		if ( false === $pos ) {
			if ( false === $pos_page ) {
				$link .= 'en/';
			} else {
				$link = str_replace( '/page/', '/en/page/', $link );
			}
		} else {
			$link = str_replace( 'en/', '', $link );
		}

		return $link;
	}

	if ( is_category() ) {
		if ( false === $pos ) {
			$link = str_replace( '/cat/', '/cat/en/', $link );
		} else {
			$link = str_replace( 'en/', '', $link );
		}

		return $link;
	}

	if ( is_search() ) {
		if ( false === $pos ) {
			if ( false === $pos_page ) {
				$link .= 'en/';
			} else {
				$link = str_replace( '/page/', '/en/page/', $link );
			}
		} else {
			$link = str_replace( 'en/', '', $link );
		}

		return $link;
	}

	if ( false === $pos ) {
		$link .= 'en/';
	} else {
		$link = str_replace( 'en/', '', $link );
	}

	if ( is_preview() ) {
		if ( false === $pos ) {
			$link = get_home_url() . '/en/?p=' . get_query_var( 'p' ) . '&preview=true';
		} else {
			$link = str_replace( '/en/', '/?p=' . get_query_var( 'p' ) . '&preview=true', $link );
		}
	}

	return $link;
}

/**
 * Translate the month of the date.
 *
 * @param int|string $the_date Formatted date string or Unix timestamp if $format is 'U' or 'G'.
 * @param string     $format PHP date format.
 * @param WP_Post    $post The post object.
 *
 * @return string The translated date month.
 */
function xlt_translate_date_month( int|string $the_date, string $format, WP_Post $post ): string {

	global $lang;

	if ( 'en' === $lang ) {
		$datetime = get_the_time( 'm', $post->ID ) . '/01/' . get_the_time( 'Y', $post->ID );

		if ( 'Y' === $format ) {
			return get_the_time( 'Y', $post->ID );
		}

		if ( 'F' === $format ) {
			return gmdate( 'F', strtotime( $datetime ) );
		}

		return get_the_time( 'd', $post->ID ) . ' ' . gmdate( 'F', strtotime( $datetime ) ) . ' ' . get_the_time( 'Y', $post->ID );
	}

	return $the_date;
}

add_filter( 'get_the_date', 'xlt_translate_date_month', 10, 3 );

/**
 * Translate the content.
 *
 * @param string $content Content of the current post.
 *
 * @return string The translated content.
 * @throws Exception Exception.
 */
function xlt_translate_content( string $content ): string {
	global $lang, $post;
	if ( 'en' === $lang ) {
		return get_content_en( $post->ID );
	}

	return $content;
}

add_filter( 'the_content', 'xlt_translate_content' );
