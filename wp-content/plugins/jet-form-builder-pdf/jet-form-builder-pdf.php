<?php
/**
 * Plugin Name:         JetFormBuilder PDF Attachment
 * Plugin URI:          https://jetformbuilder.com/
 * Description:         A form addon to convert user-submitted data from a WordPress form to PDF attachments.
 * Version:             1.0.3
 * Author:              Crocoblock
 * Author URI:          https://crocoblock.com/
 * Text Domain:         jet-form-builder-pdf
 * License:             GPL-3.0+
 * License URI:         http://www.gnu.org/licenses/gpl-3.0.txt
 * Domain Path:         /language
 * Requires PHP:        7.1
 * Requires at least:   6.4
 * Requires Plugins:    jetformbuilder
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die();
}

const JFB_PDF_VERSION = '1.0.3';
const JFB_PDF__FILE__ = __FILE__;

define( 'JFB_PDF_PLUGIN_BASE', plugin_basename( JFB_PDF__FILE__ ) );
define( 'JFB_PDF_PATH', plugin_dir_path( JFB_PDF__FILE__ ) );
define( 'JFB_PDF_URL', plugins_url( '/', JFB_PDF__FILE__ ) );

require __DIR__ . '/includes/load.php';
