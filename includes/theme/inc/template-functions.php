<?php
/**
 * Functions which enhance the theme by hooking into WordPress.
 *
 * @package  xlthlx_utility
 */

// @codingStandardsIgnoreStart
use Highlight\Highlighter;

// @codingStandardsIgnoreEnd

/**
 * Removes WP Logo and comments in the admin bar.
 *
 * @return void
 */
function xlt_remove_admin_bar_wp_logo(): void {
	global $wp_admin_bar;
	$wp_admin_bar->remove_node( 'wp-logo' );
	$wp_admin_bar->remove_node( 'comments' );
}

add_action( 'wp_before_admin_bar_render', 'xlt_remove_admin_bar_wp_logo', 20 );

/**
 * Removes the version from the admin footer.
 *
 * @return void
 */
function xlt_admin_footer_remove(): void {
	remove_filter( 'update_footer', 'core_update_footer' );
}

add_action( 'admin_menu', 'xlt_admin_footer_remove' );

/**
 * Remove the Thank you text in the admin footer.
 *
 * @return string The content that will be printed.
 */
function xlt_custom_admin_footer_text(): string {
	return '';
}

add_filter( 'admin_footer_text', 'xlt_custom_admin_footer_text' );

/**
 * Modify the rendering of code Gutenberg block.
 *
 * @param string $block_content The block content.
 * @param array  $block The full block, including name and attributes.
 *
 * @return string
 * @throws Exception Exception.
 */
function xlt_render_code_block( string $block_content, array $block ): string {
	if ( 'core/code' !== $block['blockName'] ) {
		return $block_content;
	}

	return xlt_render_code( $block_content );
}

add_filter( 'render_block', 'xlt_render_code_block', 10, 2 );

/**
 * Renders the block type output for given attributes.
 *
 * @param string $content Optional. Block content. Default empty string.
 *
 * @return string Rendered block type output.
 * @throws Exception Exception.
 */
function xlt_render_code( string $content ): string {

	$hl = new Highlighter();
	$hl->setAutodetectLanguages( array( 'php', 'javascript', 'html' ) );

	$content = str_replace(
		array(
			'<pre class="wp-block-code">',
			'<code>',
			'</code>',
			'</pre>',
		),
		'',
		html_entity_decode( $content, ENT_COMPAT, 'UTF-8' )
	);

	$highlighted = $hl->highlightAuto( trim( $content ) );

	if ( $highlighted ) {
		$content = '<pre class="wp-block-code"><code class="hljs ' . $highlighted->language . '">' . $highlighted->value . '</code></pre>';
		$content = apply_filters( 'the_content', $content );
	}

	return $content;
}

/**
 * Comment Field Order.
 *
 * @param array $fields The comment fields.
 *
 * @return array
 */
function xlt_comment_fields_custom_order( array $fields ): array {

	$comment_field = $fields['comment'];
	$author_field  = $fields['author'];
	$email_field   = $fields['email'];
	$url_field     = $fields['url'];

	unset( $fields['comment'], $fields['author'], $fields['email'], $fields['url'], $fields['cookies'] );

	$fields['comment'] = $comment_field;
	$fields['author']  = $author_field;
	$fields['email']   = $email_field;
	$fields['url']     = $url_field;

	return $fields;
}

add_filter( 'comment_form_fields', 'xlt_comment_fields_custom_order' );

/**
 * Redirect en comments to the correct url.
 *
 * @param string $location The 'redirect_to' URI sent via $_POST.
 * @param object $comment Comment object.
 *
 * @return string
 */
function xlt_en_comment_redirect( string $location, object $comment ): string {
	if ( ! isset( $comment ) || empty( $comment->comment_post_ID ) ) {
		return $location;
	}

	// @codingStandardsIgnoreStart
	if ( isset( $_POST['en_redirect_to'] ) ) {
		$location = get_permalink( $comment->comment_post_ID ) . 'en/#comment-' . $comment->comment_ID;
	}

	// @codingStandardsIgnoreEnd

	return $location;
}

add_filter( 'comment_post_redirect', 'xlt_en_comment_redirect', 10, 2 );

/**
 * Hide SEO settings meta box for posts.
 *
 * @return void
 */
function xlt_hide_slim_seo_meta_box(): void {
	$context = apply_filters( 'slim_seo_meta_box_context', 'normal' );
	remove_meta_box( 'slim-seo', null, $context );
}

add_action( 'add_meta_boxes', 'xlt_hide_slim_seo_meta_box', 20 );

/**
 * Change the title separator.
 *
 * @return string
 */
function xlt_document_title_separator(): string {
	return '|';
}

add_filter( 'document_title_separator', 'xlt_document_title_separator' );

/**
 * Removes tags from blog posts.
 *
 * @return void
 */
function xlt_unregister_tags(): void {
	unregister_taxonomy_for_object_type( 'post_tag', 'post' );
}

add_action( 'init', 'xlt_unregister_tags' );

/**
 * Send 404 to Plausible.
 *
 * @return void
 */
function xlt_404_plausible(): void {
	if ( is_404() ) {
		?>
		<script>plausible('404', {props: {path: document.location.pathname}})</script>
		<?php
	}
}

add_action( 'wp_head', 'xlt_404_plausible' );

/**
 * Custom Admin colour scheme.
 *
 * @return void
 */
function xlt_admin_color_scheme(): void {

	wp_admin_css_color(
		'xlthlx',
		__( 'Xlthlx', 'xlthlx' ),
		XLT_PLUGIN_URL . 'includes/theme/css/color-scheme.min.css',
		array( '#1e2327', '#fff', '#92285e', '#6667ab' ),
		array(
			'base'    => '#ffffff',
			'focus'   => '#92285e',
			'current' => '#ffffff',
		)
	);
}

add_action( 'admin_init', 'xlt_admin_color_scheme' );

/**
 * Hide SEO and description columns.
 *
 * @param string[] $columns An associative array of column headings.
 *
 * @return string[]
 */
function xlt_hide_seo_columns( array $columns ): array {
	unset( $columns['meta_title'], $columns['meta_description'], $columns['description'], $columns['noindex'], $columns['index'] );

	return $columns;
}

add_filter( 'manage_page_posts_columns', 'xlt_hide_seo_columns', 20 );
add_filter( 'manage_post_posts_columns', 'xlt_hide_seo_columns', 20 );
add_filter( 'manage_edit-category_columns', 'xlt_hide_seo_columns', 20 );

/**
 * Add column Description.
 *
 * @param string[] $post_columns An associative array of column headings.
 *
 * @return string[]
 */
function xlt_add_remove_link_columns( array $post_columns ): array {

	$post_columns['link_description'] = 'Descrizione';

	unset( $post_columns['rel'], $post_columns['rating'], $post_columns['visible'] );

	return $post_columns;
}

/**
 * Display column content.
 *
 * @param string $column_name The name of the column to display.
 * @param int    $post_id The current post ID.
 *
 * @return void
 */
function xlt_add_link_columns_data( string $column_name, int $post_id ): void {

	if ( 'link_description' === $column_name ) {
		$val = get_bookmark_field( 'link_description', $post_id );
		if ( empty( $val ) ) {
			return;
		}

		echo esc_attr( $val );
	}
}

/**
 * All hooks for custom columns.
 */
function xlt_setup_columns(): void {
	add_filter( 'manage_link-manager_columns', 'xlt_add_remove_link_columns' );
	add_action( 'manage_link_custom_column', 'xlt_add_link_columns_data', 10, 2 );
}

add_action( 'load-link-manager.php', 'xlt_setup_columns' );

/**
 * Add a link to the WP Toolbar for the English version.
 *
 * @param object $wp_admin_bar The WP_Admin_Bar instance, passed by reference.
 *
 * @return void
 */
function xlt_en_toolbar_link( object $wp_admin_bar ): void {

	global $pagenow;
	$href = get_permalink( get_query_var( 'post' ) );

	if ( str_contains( $href, '?p=' ) ) {
		$href = str_replace( get_home_url() . '/', get_home_url() . '/en/', $href );
	} else {
		$href .= 'en/';
	}

	if ( 'post.php' === $pagenow && is_admin() ) {
		$args = array(
			'id'    => 'view-english',
			'title' => 'Visualizza articolo in Inglese',
			'href'  => $href,
			'meta'  => array(
				'class' => 'ab-item',
				'title' => 'Visualizza articolo in Inglese',
			),
		);
		$wp_admin_bar->add_node( $args );
	}
}

add_action( 'admin_bar_menu', 'xlt_en_toolbar_link', 999 );

/**
 * Callback function for DeepL Auth Key Field.
 *
 * @param array $val Field Options.
 *
 * @return void
 */
function xlt_deepl_auth_key_callback_function( array $val ): void {
	$id          = $val['id'];
	$option_name = $val['option_name'];
	?>
	<input class="postform" type="password" name="<?php echo esc_attr( $option_name ); ?>" id="<?php echo esc_attr( $id ); ?>" value="<?php echo esc_attr( get_option( $option_name ) ); ?>"/>
	<?php
}

/**
 * Callback function for Make it snow field.
 *
 * @param array $val Field Options.
 *
 * @return void
 */
function xlt_make_it_snow_callback_function( array $val ): void {
	$id          = $val['id'];
	$option_name = $val['option_name'];
	?>
	<select name="<?php echo esc_attr( $option_name ); ?>" id="<?php echo esc_attr( $id ); ?>">
		<option value="" <?php selected( get_option( $option_name ), '' ); ?>>No</option>
		<option value="yes" <?php selected( get_option( $option_name ), 'yes' ); ?>>Yes</option>
	</select>
	<?php
}

/**
 * Callback function for Login path field.
 *
 * @param array $val Field Options.
 *
 * @return void
 */
function xlt_login_path_callback_function( array $val ): void {
	$id          = $val['id'];
	$option_name = $val['option_name'];
	?>
	<input class="postform" type="text" name="<?php echo esc_attr( $option_name ); ?>" id="<?php echo esc_attr( $id ); ?>" value="<?php echo esc_attr( get_option( $option_name ) ); ?>"/>
	<?php
}

/**
 * Add fields to Writing Settings Admin.
 *
 * @return void
 */
function xlt_add_fields_to_writing_admin_page(): void {

	register_setting(
		'writing',
		'deepl_auth_key',
		array(
			'show_in_rest' => true,
		)
	);

	register_setting(
		'writing',
		'make_it_snow',
		array(
			'show_in_rest' => true,
		)
	);

	register_setting(
		'writing',
		'xlt_login_path',
		array(
			'show_in_rest' => true,
		)
	);

	add_settings_field(
		'deepl_auth_key_settings',
		'DeepL Auth Key',
		'xlt_deepl_auth_key_callback_function',
		'writing',
		'default',
		array(
			'id'          => 'deepl_auth_key',
			'option_name' => 'deepl_auth_key',
		)
	);

	add_settings_field(
		'make_it_snow_settings',
		'Make it snow',
		'xlt_make_it_snow_callback_function',
		'writing',
		'default',
		array(
			'id'          => 'make_it_snow',
			'option_name' => 'make_it_snow',
		)
	);

	add_settings_field(
		'xlt_login_path_settings',
		'Login url',
		'xlt_login_path_callback_function',
		'writing',
		'default',
		array(
			'id'          => 'xlt_login_path',
			'option_name' => 'xlt_login_path',
		)
	);
}

add_action( 'admin_menu', 'xlt_add_fields_to_writing_admin_page' );

/**
 * Iframe shortcode.
 *
 * @param array $atts Array of attributes.
 *
 * @return string The iframe HTML.
 */
function xlt_add_shortcode_iframe( array $atts ): string {
	$defaults = array(
		'src'         => '',
		'width'       => '100%',
		'height'      => '500',
		'scrolling'   => 'yes',
		'frameborder' => '0',
	);

	foreach ( $defaults as $default => $value ) {
		if ( ! array_key_exists( $default, $atts ) ) {
			$atts[ $default ] = $value;
		}
	}

	$html = '<iframe';
	foreach ( $atts as $attr => $value ) {

		if ( strtolower( $attr ) === 'src' ) {
			$value = esc_url( $value );
		}

		if ( strtolower( $attr ) !== 'onload' && strtolower( $attr ) !== 'onpageshow' && strtolower( $attr ) !== 'onclick' ) {
			if ( '' !== $value ) {
				$html .= ' ' . esc_attr( $attr ) . '="' . esc_attr( $value ) . '"';
			} else {
				$html .= ' ' . esc_attr( $attr );
			}
		}
	}
	$html .= '></iframe>' . "\n";

	return $html;
}

// @codingStandardsIgnoreStart
add_shortcode( 'iframe', 'xlt_add_shortcode_iframe' );
// @codingStandardsIgnoreEnd

/**
 * Add settings field.
 *
 * @return void
 */
function xlt_add_english_fields_settings(): void {
	register_setting( 'general', 'english_title', 'esc_attr' );
	register_setting( 'general', 'english_tagline', 'esc_attr' );

	add_settings_field( 'english_title', '<label for="english_title">' . __( 'English title', 'xlthlx' ) . '</label>', 'xlt_english_title_field', 'general' );
	add_settings_field( 'english_tagline', '<label for="english_tagline">' . __( 'English tagline', 'xlthlx' ) . '</label>', 'xlt_english_tagline_field', 'general' );
}

/**
 * English title field.
 *
 * @return void
 */
function xlt_english_title_field(): void {
	$english_title = get_option( 'english_title', '' );
	echo '<input aria-describedby="title-english" class="regular-text" type="text" id="english_title" name="english_title" value="' . esc_attr( $english_title ) . '" />';
	echo '<p class="description" id="title-english">Website title.</p>';
}

/**
 * English tagline field.
 *
 * @return void
 */
function xlt_english_tagline_field(): void {
	$english_tagline = get_option( 'english_tagline', '' );
	echo '<input aria-describedby="tagline-english" class="regular-text" type="text" id="english_tagline" name="english_tagline" value="' . esc_attr( $english_tagline ) . '" />';
	echo '<p class="description" id="tagline-english">In a few words, explain what this site is about.</p>';
}

add_filter( 'admin_init', 'xlt_add_english_fields_settings' );

/**
 * Allowed tags into excerpt.
 *
 * @return string
 */
function xlt_allowedtags(): string {
	return '<p>,<br>,<em>,<i>,<ul>,<ol>,<li>,<p>,<img>,<video>,<audio>,<figure>,<picture>,<source>';
}

/**
 * Set up an excerpt from $content.
 *
 * @param string $xlt_excerpt The post excerpt.
 *
 * @return string
 * @throws Exception Exception.
 */
function xlt_custom_wp_trim_excerpt( string $xlt_excerpt ): string {
	$raw_excerpt = $xlt_excerpt;

	if ( '' === $xlt_excerpt ) {

		global $lang, $post;
		$xlt_excerpt = get_the_content( '' );

		if ( isset( $post ) && 'en' === $lang ) {
			$xlt_excerpt = get_content_en( $post->ID );
		}

		$xlt_excerpt = strip_shortcodes( $xlt_excerpt );
		$xlt_excerpt = apply_filters( 'the_content', $xlt_excerpt );
		$xlt_excerpt = str_replace( ']]>', ']]&gt;', $xlt_excerpt );
		$xlt_excerpt = strip_tags( $xlt_excerpt, xlt_allowedtags() );

		$excerpt_word_count = 60;
		apply_filters( 'excerpt_length', $excerpt_word_count );
		$tokens         = array();
		$excerpt_output = '';
		$count          = 0;

		preg_match_all( '/(<[^>]+>|[^<>\s]+)\s*/u', $xlt_excerpt, $tokens );

		foreach ( $tokens[0] as $token ) {

			if ( $count >= $excerpt_word_count && preg_match( '/[,;?.!]\s*$/uS', $token ) ) {
				$excerpt_output .= trim( $token );
				break;
			}

			$count ++;
			$excerpt_output .= $token;
		}

		$xlt_excerpt = trim( force_balance_tags( $excerpt_output ) );

		$xlt_excerpt = str_replace( '<p></p>', '', $xlt_excerpt );

		$excerpt_end = '...';
		apply_filters( 'excerpt_more', ' ' . $excerpt_end );

		$pos    = strrpos( $xlt_excerpt, '</' );
		$figure = strrpos( $xlt_excerpt, '</figure>' );
		if ( false !== $pos && false === $figure ) {
			$xlt_excerpt = substr_replace( $xlt_excerpt, $excerpt_end, $pos, 0 );
		}

		return $xlt_excerpt;

	}

	return apply_filters( 'xlt_custom_wp_trim_excerpt', $xlt_excerpt, $raw_excerpt );
}

remove_filter( 'get_the_excerpt', 'wp_trim_excerpt' );
add_filter( 'get_the_excerpt', 'xlt_custom_wp_trim_excerpt' );

/**
 * Insert minified JS for the snow into footer.
 *
 * @return void
 */
function xlt_make_it_snow(): void {
	$make_it_snow = get_option( 'make_it_snow' );
	if ( 'yes' === $make_it_snow ) {
		$snow        = XLT_PLUGIN_PATH . 'includes/theme/js/snow.min.js';
		$script_snow = xlt_get_file_content( $snow );
		// @codingStandardsIgnoreStart
		echo '<script type="text/javascript">' . $script_snow . '</script>';
		// @codingStandardsIgnoreEnd
	}
}

add_action( 'wp_footer', 'xlt_make_it_snow' );

/**
 * Change the Feed RSS text for Slim SEO.
 *
 * @param string $text The Feed RSS text for Slim SEO.
 *
 * @return string
 */
function xlt_change_rss_feed_text( string $text ): string {
	global $lang;

	return ( 'en' === $lang ) ? 'Read more &raquo;' : 'Continua a leggere &raquo;';
}

add_filter( 'slim_seo_feed_text', 'xlt_change_rss_feed_text' );

/**
 * Remove Akismet inline style.
 *
 * @return void
 */
function xlt_remove_akismet_style() {
	wp_styles()->add_data( 'akismet-widget-style', 'after', '' );
}

add_action( 'wp_print_styles', 'xlt_remove_akismet_style' );
