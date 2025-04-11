<?php

namespace JFB_PDF_Modules\Templates\ConditionValidators;

use JFB_PDF\Vendor\Auryn\InjectionException;
use JFB_PDF_Modules\Templates\ConditionValidators\Interfaces\BaseInterface;

class Manager {

	private $factory;
	private $adapter;

	public function __construct(
		Factory $factory,
		AdaptersManager $adapter
	) {
		$this->factory = $factory;
		$this->adapter = $adapter;
	}

	/**
	 * We check the conditions for validity.
	 * They are all divided into groups if there is an OR operator between them.
	 * If among any group all condition were performed,
	 * then the check is instantly terminated with success,
	 * since only one group should be with successful validation.
	 *
	 * If the OR operator is absent, then all conditions must be executed.
	 * Since this is one single group
	 *
	 * @param array $conditions
	 *
	 * @return bool
	 * @throws InjectionException
	 */
	public function validate( array $conditions ): bool {
		$groups      = array();
		$group_index = 0;

		foreach ( $conditions as $condition ) {
			$validator = $this->factory->create_validator( $condition );

			if ( $validator instanceof OrOperator ) {
				// Validate previous group
				if ( $this->is_valid_group( $groups[ $group_index ] ) ) {
					return true;
				}

				++$group_index;

				continue;
			}

			if ( empty( $groups[ $group_index ] ) ) {
				$groups[ $group_index ] = array();
			}

			$groups[ $group_index ][] = $validator;
		}

		$last_group = end( $groups );

		return $last_group && $this->is_valid_group( $last_group );
	}

	/**
	 * @param $group
	 *
	 * @return bool
	 * @throws InjectionException
	 */
	private function is_valid_group( $group ): bool {
		/** @var BaseInterface $validator */
		foreach ( $group as $validator ) {
			if ( $this->run_validator( $validator ) ) {
				continue;
			}

			return false;
		}

		return true;
	}

	/**
	 * @param Interfaces\BaseInterface $validator
	 *
	 * @return bool
	 * @throws InjectionException
	 */
	public function run_validator( Interfaces\BaseInterface $validator ): bool {
		$this->adapter->transform( $validator );

		do_action( 'jet-form-builder-pdf/conditional/before-validate', $validator );

		return $validator->validate();
	}


}
