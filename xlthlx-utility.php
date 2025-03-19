<?php
/**
 * Xlthlx Utility
 *
 * @category  Plugin
 * @package   xlthlx_utility
 * @author    xlthlx <xlthlx@gmail.com>
 * @copyright 2025 xlthlx (email: xlthlx at gmail.com)
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GPL 3
 * @link      https://github.com/xlthlx/xlthlx-utility/
 *
 * @wordpress-plugin
 * Plugin Name:       xlthlx Utility
 * Plugin URI:        https://github.com/xlthlx/xlthlx-utility/
 * Description:       Sets of extra functionalities for xlthlx.com theme.
 * Version:           1.0.0
 * Requires at least: 6.7
 * Requires PHP:      8.2
 * Author:            xlthlx
 * Author URI:        https://profiles.wordpress.org/xlthlx/
 * License:           GPLv3+
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       xlthlx
 *
 * xlthlx Utility is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * xlthlx Utility is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with xlthlx Utility. If not, see https://www.gnu.org/licenses/gpl-3.0.html.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

require_once __DIR__ . '/vendor.phar';
require_once __DIR__ . '/includes/cmb2/init.php';

define( 'XLT_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'XLT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once XLT_PLUGIN_PATH . 'includes/eng/index.php';
require_once XLT_PLUGIN_PATH . 'includes/newsletter/index.php';
require_once XLT_PLUGIN_PATH . 'includes/swf-reader/index.php';
require_once XLT_PLUGIN_PATH . 'includes/theme/index.php';
require_once XLT_PLUGIN_PATH . 'includes/toolkit/index.php';
