<?php


namespace JetLoginCore\JetEngine;


trait RegisterFormTabs {

	use WithInit;

	abstract public function tabs(): array;

	public function plugin_version_compare(): string {
		return '2.8.3';
	}

	public function customize_init( $callable ) {
		add_action( 'jet-engine/forms/init', $callable );
	}

	public function on_plugin_init() {
		add_filter( 'jet-engine/dashboard/form-tabs', function ( $tabs ) {
			$tabs = array_merge( $tabs, $this->tabs() );

			return $tabs;
		} );
	}
}