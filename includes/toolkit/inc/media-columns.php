<?php
/**
 * Media columns.
 *
 * @package  xlthlx_utility
 */

/**
 * Remove comments column.
 *
 * @param array $columns Media columns.
 *
 * @return array
 */
function xlt_media_columns( array $columns ): array {
	unset(
		$columns['comments']
	);

	return $columns;
}

add_filter( 'manage_media_columns', 'xlt_media_columns' );
