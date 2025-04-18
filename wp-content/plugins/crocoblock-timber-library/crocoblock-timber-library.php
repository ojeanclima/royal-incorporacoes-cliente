<?php
/**
 * Plugin Name: Crocoblock Timber Library
 * Plugin URI:  https://crocoblock.com/
 * Description: Importing Timber 2.X library to use with Crocoblock plugins or anywhere you need it.
 * Version:     2.0.0
 * Author:      Crocoblock
 * Author URI:  https://crocoblock.com/
 * License:     GPL-3.0+
 * License URI: http://www.gnu.org/licenses/gpl-3.0.txt
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

add_action( 'plugins_loaded', function() {
	require_once plugin_dir_path( __FILE__ ) . '/vendor/autoload.php';
	\Timber\Timber::init();
} );
