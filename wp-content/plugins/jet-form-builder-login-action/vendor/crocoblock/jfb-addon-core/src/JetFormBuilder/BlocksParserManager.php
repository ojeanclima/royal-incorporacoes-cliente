<?php


namespace JetLoginCore\JetFormBuilder;


abstract class BlocksParserManager {

	use WithInit;

	abstract public function parsers(): array;

	public function on_plugin_init() {
		add_filter( 'jet-form-builder/parsers-request/register', function ( $tabs ) {
			$tabs = array_merge( $tabs, $this->parsers() );

			return $tabs;
		} );
	}

}