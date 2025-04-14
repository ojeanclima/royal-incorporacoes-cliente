<?php
/**
 * Plugin Name: JetFormBuilder User Login Action
 * Plugin URI:  https://jetformbuilder.com/addons/user-login/
 * Description: A post-submit action addon allowing users to log in by the provided email/login & password.
 * Version:     2.0.5
 * Author:      Crocoblock
 * Author URI:  https://crocoblock.com/
 * Text Domain: jet-form-builder-user-login-action
 * License:     GPL-3.0+
 * License URI: http://www.gnu.org/licenses/gpl-3.0.txt
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die();
}

define( 'JET_FB_LOGIN_ACTION_VERSION', '2.0.5' );

define( 'JET_FB_LOGIN_ACTION__FILE__', __FILE__ );
define( 'JET_FB_LOGIN_ACTION_PLUGIN_BASE', plugin_basename( __FILE__ ) );
define( 'JET_FB_LOGIN_ACTION_PATH', plugin_dir_path( __FILE__ ) );
define( 'JET_FB_LOGIN_ACTION_URL', plugins_url( '/', __FILE__ ) );

require JET_FB_LOGIN_ACTION_PATH . 'vendor/autoload.php';

if ( version_compare( PHP_VERSION, '7.0.0', '>=' ) ) {
	add_action( 'plugins_loaded', function () {
		require JET_FB_LOGIN_ACTION_PATH . 'includes/plugin.php';
	}, 100 );
} else {
	add_action( 'admin_notices', function () {
		$class   = 'notice notice-error';
		$message = __(
			'<b>Error:</b> <b>JetFormBuilder User Login Action</b> plugin requires a PHP version ">= 7.0.0" to work properly!',
			'jet-form-builder-login-action'
		);
		printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), wp_kses_post( $message ) );
	} );
}

