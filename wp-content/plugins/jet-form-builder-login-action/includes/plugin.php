<?php

namespace Jet_FB_Login;


use Jet_FB_Login\JetEngine\Notifications\Manager as JEManager;
use Jet_FB_Login\JetFormBuilder\Actions\Manager as JFBManager;
use Jet_FB_Login\JetFormBuilder\Blocks\Manager as JFBBlocksManager;
use Jet_FB_Login\JetFormBuilder\Parsers\Manager as JFBParsersManager;
use Jet_FB_Login\JetFormBuilder\ActionsMessages\Manager as JFBActionsMessagesManager;
use Jet_FB_Login\JetFormBuilder\RenderStatesManager;
use JetLoginCore\LicenceProxy;

if ( ! defined( 'WPINC' ) ) {
	die();
}

class Plugin {
	/**
	 * Instance.
	 *
	 * Holds the plugin instance.
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 *
	 * @var Plugin
	 */
	public static $instance = null;

	public $slug = 'jet-form-builder-login-action';

	public function __construct() {
		JFBManager::register();
		JEManager::register();
		JFBActionsMessagesManager::register();

		RenderStatesManager::register();
		LicenceProxy::register();

		JFBBlocksManager::register();
		JFBParsersManager::register();
	}

	public function get_version(): string {
		return JET_FB_LOGIN_ACTION_VERSION;
	}

	public function plugin_url( $path ): string {
		return JET_FB_LOGIN_ACTION_URL . $path;
	}

	public function plugin_dir( $path = '' ): string {
		return JET_FB_LOGIN_ACTION_PATH . $path;
	}

	/**
	 * Instance.
	 *
	 * Ensures only one instance of the plugin class is loaded or can be loaded.
	 *
	 * @return Plugin An instance of the class.
	 * @since 1.0.0
	 * @access public
	 * @static
	 *
	 */
	public static function instance(): Plugin {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

}

Plugin::instance();