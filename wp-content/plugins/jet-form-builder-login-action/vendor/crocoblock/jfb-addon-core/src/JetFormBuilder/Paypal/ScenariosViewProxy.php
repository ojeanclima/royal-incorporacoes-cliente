<?php


namespace JetLoginCore\JetFormBuilder\Paypal;


use JetLoginCore\JetFormBuilder\WithInit;

abstract class ScenariosViewProxy {

	use WithInit;

	abstract public function scenarios(): array;

	public function plugin_version_compare(): string {
		return '2.0.0';
	}

	public function on_plugin_init() {
		add_filter(
			'jet-form-builder/gateways/paypal/scenarios-view',
			array( $this, 'register_scenarios' )
		);
	}

	public function register_scenarios( $views ) {
		$views = array_merge( $views, $this->scenarios() );

		return $views;
	}


}