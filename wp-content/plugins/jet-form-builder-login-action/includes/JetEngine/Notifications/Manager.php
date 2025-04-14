<?php


namespace Jet_FB_Login\JetEngine\Notifications;


use Jet_FB_Login\Plugin;
use JetLoginCore\JetEngine\NotificationsManager;

class Manager extends NotificationsManager {

	public function register_notification() {
		return array(
			new Notification()
		);
	}

	/**
	 * Register notification assets
	 * @return void
	 */
	public function register_assets() {
		wp_enqueue_script(
			Plugin::instance()->slug,
			Plugin::instance()->plugin_url( 'assets/js/engine.editor.js' ),
			array(),
			Plugin::instance()->get_version(),
			true
		);
	}

	public function plugin_version_compare() {
		return '2.8.3';
	}

	public function on_base_need_update() {
		$this->add_admin_notice( 'warning', __(
			'<b>Warning</b>: <b>JetFormBuilder User Login Action</b> needs <b>JetEngine</b> update.',
			'jet-form-builder-login-action'
		) );
	}

	public function on_base_need_install() {
	}


}