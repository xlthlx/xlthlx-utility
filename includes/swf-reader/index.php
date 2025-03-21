<?php
/**
 * Functions to manage the SWF Player.
 *
 * Uses: https://ruffle.rs
 *
 * @package  xlthlx_utility
 */

/**
 * Add SWF to list of allowed mime types and file extensions.
 *
 * @param array $mimes Mime types keyed by the file extension regex corresponding to those types.
 *
 * @return array
 */
function xlt_swf_custom_mime_type( array $mimes ): array {

	$mimes['swf'] = 'application/x-shockwave-flash';

	return $mimes;
}

add_filter( 'upload_mimes', 'xlt_swf_custom_mime_type' );

/**
 * Enqueue blocks variations script.
 *
 * @return void
 */
function xlt_enqueue_post_types_variations(): void {
	wp_enqueue_script(
		'post-types-variations',
		XLT_PLUGIN_URL . 'includes/swf-reader/script.js',
		array(
			'wp-blocks',
			'wp-dom-ready',
			'wp-edit-post',
		),
		filemtime( XLT_PLUGIN_PATH . '/includes/swf-reader/script.js' ),
		array( 'in_footer' => true )
	);
}

add_action( 'enqueue_block_editor_assets', 'xlt_enqueue_post_types_variations' );

/**
 * Render the block variation.
 *
 * @param string $block_content The block content.
 * @param array  $block The full block, including name and attributes.
 *
 * @return string
 */
function xlt_set_swf_player( string $block_content, array $block ): string {
	if ( ! empty( $block_content ) && 'core/file' === $block['blockName'] ) {

		$dom = new DOMDocument();
		$dom->loadHTML( $block_content );

		$tag = $dom->getElementsByTagName( 'a' );
		$url = $tag[0]->getAttribute( 'href' );
		$id  = $tag[0]->getAttribute( 'id' );

		$file_types = array( '.swf' );

		if ( str_replace( $file_types, '', mb_strtolower( $url, 'UTF-8' ) ) !== mb_strtolower( $url, 'UTF-8' ) ) {
			// @codingStandardsIgnoreStart
			return '<div class="wp-block-xlt-swf-player" id="' . $id . '"></div>
					<script>
					let player_id = "' . $id . '";
					let player_file = "' . $url . '";
					</script>
					<script src="' . XLT_PLUGIN_URL . 'includes/swf-reader/config.js" id="ruffle-config"></script>
					';
			// @codingStandardsIgnoreEnd
		}

		return $block_content;
	}

	return $block_content;
}

add_filter( 'render_block', 'xlt_set_swf_player', 10, 2 );

/**
 * Enqueues Ruffle main script.
 *
 * @return void
 */
function xlt_enqueue_ruffle_script(): void {
	if ( is_singular() ) {
		$id = get_the_ID();
		if ( has_block( 'core/file', $id ) ) {
			wp_enqueue_script(
				'ruffle',
				'https://unpkg.com/@ruffle-rs/ruffle',
				array(),
				filemtime( XLT_PLUGIN_PATH . '/includes/swf-reader/script.js' ),
				array( 'in_footer' => true )
			);
		}
	}
}

add_action( 'wp_enqueue_scripts', 'xlt_enqueue_ruffle_script' );
