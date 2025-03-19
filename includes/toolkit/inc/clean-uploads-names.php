<?php
/**
 * Clean uploads names.
 *
 * @package  xlthlx_utility
 */

/**
 * Clean the filename.
 *
 * @param array $file The file information including the filename in $file['name'].
 *
 * @return array The file information with the cleaned or original filename.
 */
function xlt_upload_filter( $file ) {

	$original_filename = pathinfo( $file['name'] );
	set_transient(
		'_clean_image_filenames_original_filename',
		$original_filename['filename'],
		60
	);

	$input = array( 'ß', '·' );

	$output = array( 'ss', '.' );

	$path         = pathinfo( $file['name'] );
	$new_filename = preg_replace(
		'/.' . $path['extension'] . '$/',
		'',
		$file['name']
	);
	$new_filename = str_replace( $input, $output, $new_filename );
	$file['name'] = sanitize_title( $new_filename ) . '.' . $path['extension'];

	return $file;
}

/**
 * Set attachment title to original filename.
 *
 * @param int $attachment_id Attachment post ID.
 *
 * @return void
 */
function xlt_update_attachment_title( $attachment_id ) {

	$original_filename = get_transient( '_clean_image_filenames_original_filename' );

	if ( $original_filename ) {
		wp_update_post(
			array(
				'ID'         => $attachment_id,
				'post_title' => $original_filename,
			)
		);
		delete_transient( '_clean_image_filenames_original_filename' );
	}
}

if ( is_admin() ) {
	add_action( 'wp_handle_upload_prefilter', 'xlt_upload_filter' );
	add_action( 'add_attachment', 'xlt_update_attachment_title' );
}
