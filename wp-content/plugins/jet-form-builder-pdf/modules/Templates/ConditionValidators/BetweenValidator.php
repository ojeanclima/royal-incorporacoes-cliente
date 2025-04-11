<?php

namespace JFB_PDF_Modules\Templates\ConditionValidators;

use JFB_PDF_Modules\Templates\ConditionValidators\Interfaces\CompareFieldInterface;
use JFB_PDF_Modules\Templates\ConditionValidators\Traits\CompareFieldTrait;

class BetweenValidator implements CompareFieldInterface {

	use CompareFieldTrait {
		CompareFieldTrait::setup as private base_setup;
	}

	/**
	 * @var mixed
	 */
	private $compare_after;

	public function is_supported( array $condition ): bool {
		return ( $condition['operator'] ?? '' ) === 'between';
	}

	public function validate(): bool {
		return (
			$this->get_compare() < $this->get_field() && $this->get_field() < $this->get_compare_after()
		);
	}

	public function setup( array $condition ) {
		$this->base_setup( $condition );

		$this->set_compare_after( $condition['compareAfter'] ?? '' );
	}

	/**
	 * @param mixed $compare_after
	 */
	public function set_compare_after( $compare_after ) {
		$this->compare_after = $compare_after;
	}

	/**
	 * @return mixed
	 */
	public function get_compare_after() {
		return $this->compare_after;
	}

}
