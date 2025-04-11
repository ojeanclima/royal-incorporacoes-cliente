<?php

namespace JFB_PDF_Modules\Templates\ConditionValidators;

use JFB_PDF\Plugin;
use JFB_PDF\Vendor\Auryn\ConfigException;
use JFB_PDF\Vendor\Auryn\InjectionException;
use JFB_PDF\Vendor\Auryn\Injector;
use JFB_PDF_Modules\Templates\ConditionValidators\Interfaces\AdapterInterface;
use JFB_PDF_Modules\Templates\ConditionValidators\Interfaces\BaseInterface;

class AdaptersManager {

	private $plugin;
	private $adapters = array();

	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * @param BaseInterface $validator
	 *
	 * @return void
	 * @throws InjectionException
	 */
	public function transform( BaseInterface $validator ) {
		foreach ( $this->adapters as $adapter_class_or_object ) {
			$adapter = $this->create_adapter( $adapter_class_or_object );

			if ( ! $adapter->is_supported( $validator ) ) {
				continue;
			}
			$adapter->transform( $validator );
		}
	}


	/**
	 * @param string|AdapterInterface $adapter_class
	 *
	 * @return void
	 * @throws ConfigException
	 */
	public function register( $adapter_class ) {
		if ( is_string( $adapter_class ) ) {
			$this->plugin->get_injector()->share( $adapter_class );
		}

		$this->adapters[] = $adapter_class;
	}

	/**
	 * @param $adapter
	 *
	 * @return mixed
	 * @throws InjectionException
	 */
	private function create_adapter( $adapter ): AdapterInterface {
		if ( ! is_string( $adapter ) ) {
			return $adapter;
		}

		return $this->plugin->get_injector()->make( $adapter );
	}

}
