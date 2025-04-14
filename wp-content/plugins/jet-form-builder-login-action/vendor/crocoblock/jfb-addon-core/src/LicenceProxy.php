<?php


namespace JetLoginCore;


use JetLoginCore\JetFormBuilder\WithInit;

class LicenceProxy {

	use WithInit;

	public function plugin_version_compare(): string {
		return '1.4.0';
	}

	public function on_plugin_init() {
		if ( ! is_admin() ) {
			return;
		}
		require_once __DIR__ . DIRECTORY_SEPARATOR . 'JFB_License_Manager.php';

		\JFB_License_Manager::instance();
	}

	public function on_base_need_install() {
	}

	public function on_base_need_update() {
	}
}