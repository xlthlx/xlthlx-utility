<?php
/**
 * Page columns.
 *
 * @package  xlthlx_utility
 */

/**
 * Remove comments column and adds Template column for pages
 *
 * @param array $columns The pages columns.
 *
 * @return array $columns
 */
function xlt_page_column_views( array $columns ): array {
	unset( $columns['comments'], $columns['date'] );

	return array_merge(
		$columns,
		array(
			'page-layout' => __( 'Template', 'xlthlx' ),
			'modified'    => __( 'Data ultima modifica', 'xlthlx' ),
			'date'        => __( 'Date', 'xlthlx' ),
		)
	);

}

/**
 * Sets content for Template column and date
 *
 * @param string $column_name The column name.
 * @param int    $id The post ID.
 */
function xlt_page_custom_column_views( string $column_name, int $id ): void {
	if ( 'page-layout' === $column_name ) {
		$set_template = get_post_meta(
			get_the_ID(),
			'_wp_page_template',
			true
		);
		if ( ( 'default' === $set_template ) || ( '' === $set_template ) ) {
			$set_template = 'Default';
		}
		$templates = wp_get_theme()->get_page_templates();
		foreach ( $templates as $key => $value ) :
			if ( ( $set_template === $key ) && ( '' === $set_template ) ) {
				$set_template = $value;
			}
		endforeach;

		echo esc_attr( $set_template );
	}
	if ( 'modified' === $column_name ) {
		echo esc_attr( ucfirst( get_the_modified_time( 'd/m/Y', $id ) ) . ' alle ' . get_the_modified_time( 'H:i', $id ) );
	}
	if ( 'date' === $column_name ) {
		echo esc_attr( get_the_modified_time( 'D, d M Y H:i:s', $id ) );
	}
}

if ( is_admin() ) {
	add_filter( 'manage_pages_columns', 'xlt_page_column_views', 9999 );
	add_action( 'manage_pages_custom_column', 'xlt_page_custom_column_views', 9999, 2 );
}
