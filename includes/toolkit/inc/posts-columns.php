<?php
/**
 * Post columns.
 *
 * @package  xlthlx_utility
 */

/**
 * Adds Thumbnail column for posts
 *
 * @param array $columns The post columns.
 *
 * @return array
 */
function xlt_posts_columns( array $columns ): array {
	$post_type = get_post_type();
	if ( 'post' === $post_type ) {
		unset( $columns['date'] );

		$columns = array_merge(
			$columns,
			array(
				'thumbs'   => __( 'Miniatura', 'xlthlx' ),
				'modified' => __( 'Data ultima modifica', 'xlthlx' ),
				'date'     => __( 'Date', 'xlthlx' ),
			)
		);
	}

	return $columns;
}

/**
 * Sets content for Thumbnail column and date
 *
 * @param string $column_name The column name.
 * @param int    $id The post ID.
 */
function xlt_posts_custom_columns( string $column_name, int $id ): void {
	if ( 'thumbs' === $column_name ) {
		echo get_the_post_thumbnail( $id, 'thumbnail' );
	}
	if ( 'modified' === $column_name ) {
		echo esc_attr( ucfirst( get_the_modified_time( 'd/m/Y', $id ) ) . ' alle ' . get_the_modified_time( 'H:i', $id ) );
	}
	if ( 'date' === $column_name ) {
		echo get_the_date( $id );
	}
}

if ( is_admin() ) {
	add_filter( 'manage_posts_columns', 'xlt_posts_columns', 999999 );
	add_action( 'manage_posts_custom_column', 'xlt_posts_custom_columns', 999999, 2 );
}
