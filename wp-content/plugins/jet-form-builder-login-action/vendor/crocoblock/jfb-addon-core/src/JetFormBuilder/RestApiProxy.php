<?php


namespace JetLoginCore\JetFormBuilder;


abstract class RestApiProxy {

	use WithInit;

	abstract public function routes(): array;

	public function get_priority(): int {
		return 20;
	}

	public function plugin_version_compare(): string {
		return '2.1.0';
	}

	protected function get_controller_instance() {
		return new class extends \Jet_Form_Builder\Rest_Api\Rest_Api_Controller_Base {

			private $static = array();

			public function set_static( $routes ) {
				$this->static = $routes;

				return $this;
			}

			public function routes(): array {
				return $this->static;
			}
		};
	}

	public function on_plugin_init() {
		$controller = $this->get_controller_instance()->set_static( $this->routes() );

		add_action(
			'rest_api_init',
			array( $controller, 'register_routes' ),
			$this->get_priority()
		);
	}


}