<?php
/**
 * Plugin Name: JetEngine - dynamic tables builder
 * Plugin URI:
 * Description: Dynamic tables builder module for JetEngine.
 * Version:     1.0.10
 * Author:      Crocoblock
 * Author URI:
 * Text Domain: jet-engine-dynamic-tables-module
 * License:     GPL-3.0+
 * License URI: http://www.gnu.org/licenses/gpl-3.0.txt
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die();
}

define( 'JET_ENGINE_DYNAMIC_TABLES_VERSION', '1.0.10' );

define( 'JET_ENGINE_DYNAMIC_TABLES__FILE__', __FILE__ );
define( 'JET_ENGINE_DYNAMIC_TABLES_PLUGIN_BASE', plugin_basename( JET_ENGINE_DYNAMIC_TABLES__FILE__ ) );
define( 'JET_ENGINE_DYNAMIC_TABLES_PATH', plugin_dir_path( JET_ENGINE_DYNAMIC_TABLES__FILE__ ) );
define( 'JET_ENGINE_DYNAMIC_TABLES_URL', plugins_url( '/', JET_ENGINE_DYNAMIC_TABLES__FILE__ ) );

add_action( 'plugins_loaded', 'jet_engine_dynamic_tables_init' );

function jet_engine_dynamic_tables_init() {
	require JET_ENGINE_DYNAMIC_TABLES_PATH . 'includes/plugin.php';
}

function jet_engine_dynamic_tables() {
	return Jet_Engine_Dynamic_Tables\Plugin::instance();
}
