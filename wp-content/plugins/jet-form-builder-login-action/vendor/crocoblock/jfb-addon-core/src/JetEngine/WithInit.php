<?php


namespace JetLoginCore\JetEngine;


use JetLoginCore\WithBasePluginInit;

trait WithInit {

	use WithBasePluginInit;

	final public function base_condition(): bool {
		return function_exists( 'jet_engine' );
	}

	public function plugin_version_compare(): string {
		return '2.8.0';
	}

	public function can_init(): bool {
		return version_compare( jet_engine()->get_version(), $this->plugin_version_compare(), '>=' );
	}

}