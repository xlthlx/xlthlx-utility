<?php
/**
 * Toolkit.
 *
 * @package  xlthlx_utility
 */

/**
 * Includes all files from inc directory.
 */
require_once ABSPATH . 'wp-admin/includes/file.php';

$folder = plugin_dir_path( __FILE__ ) . 'inc/';
$files  = list_files( $folder, 1 );
foreach ( $files as $file ) {
	if ( is_file( $file ) ) {
		require_once $file;
	}
}
