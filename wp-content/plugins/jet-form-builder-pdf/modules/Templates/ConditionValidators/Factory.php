<?php

namespace JFB_PDF_Modules\Templates\ConditionValidators;

use JFB_PDF\Plugin;
use JFB_PDF\Vendor\Auryn\ConfigException;
use JFB_PDF\Vendor\Auryn\InjectionException;
use JFB_PDF\Vendor\Auryn\Injector;
use JFB_PDF_Modules\Templates\ConditionValidators\Interfaces\BaseInterface;

class Factory {

	private $plugin;

	/**
	 * @param Plugin $plugin
	 *
	 * @throws ConfigException
	 */
	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;

		$this->share_validators();
	}

	/**
	 * @param array $condition
	 *
	 * @return BaseInterface
	 * @throws InjectionException
	 */
	public function create_validator( array $condition ): BaseInterface {
		foreach ( $this->declare_validators() as $validator_class ) {
			/** @var BaseInterface $validator */
			$validator = $this->plugin->get_injector()->make( $validator_class );

			if ( ! $validator->is_supported( $condition ) ) {
				continue;
			}

			$validator->setup( $condition );

			return $validator;
		}

		return $this->plugin->get_injector()->make( AlwaysValidValidator::class );
	}

	private function declare_validators(): \Generator {
		yield EqualValidator::class;
		yield NotEqualValidator::class;

		yield EmptyValidator::class;
		yield NotEmptyValidator::class;

		yield GreaterThanValidator::class;
		yield GreaterThanOrEqualValidator::class;

		yield LessThanValidator::class;
		yield LessThanOrEqualValidator::class;

		yield BetweenValidator::class;
		yield NotBetweenValidator::class;

		yield InTheListValidator::class;
		yield NotInTheListValidator::class;

		yield ContainTextValidator::class;
		yield NotContainTextValidator::class;
	}

	/**
	 * @return void
	 * @throws ConfigException
	 */
	private function share_validators() {
		$this->plugin->get_injector()->share( AlwaysValidValidator::class );

		foreach ( $this->declare_validators() as $validator_class ) {
			$this->plugin->get_injector()->share( $validator_class );
		}
	}

}
