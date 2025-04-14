<?php


namespace JetLoginCore\JetFormBuilder;


use JetLoginCore\RegisterMetaManager;

abstract class PluginManager {

	use EditorAssetsManager;
	use RegisterMetaManager;
	use WithInit;

	public function on_plugin_init() {
		$this->meta_manager_init();
		$this->assets_init();
	}

}