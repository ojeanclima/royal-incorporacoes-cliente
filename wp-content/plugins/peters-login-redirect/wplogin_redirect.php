<?php
/*
Plugin Name: LoginWP (Formerly Peter's Login Redirect)
Plugin URI: https://loginwp.com
Description: Redirect users to different URLs based on their role, capability and more.
Version: 3.0.8.6
Author: LoginWP Team
Author URI: https://loginwp.com
Text Domain: peters-login-redirect
Domain Path: /languages
License: GPL-2.0+
*/

use LoginWP\Core\Core;

require __DIR__ . '/vendor/autoload.php';

define('PTR_LOGINWP_SYSTEM_FILE_PATH', __FILE__);
define('PTR_LOGINWP_VERSION_NUMBER', '3.0.8.6');

add_action('init', function () {
    load_plugin_textdomain('peters-login-redirect', false, dirname(plugin_basename(__FILE__)) . '/languages');
});

Core::get_instance();