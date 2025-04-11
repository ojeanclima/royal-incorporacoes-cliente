<?php

namespace JFB_PDF_Modules\Templates\ConditionValidators;

use JFB_PDF_Modules\Templates\ConditionValidators\Interfaces\FieldInterface;
use JFB_PDF_Modules\Templates\ConditionValidators\Traits\FieldTrait;

class InTheListValidator implements FieldInterface {

	use FieldTrait {
		FieldTrait::setup as private field_setup;
	}

	/**
	 * @var array
	 */
	private $compare_list = array();

	public function is_supported( array $condition ): bool {
		return ( $condition['operator'] ?? '' ) === 'in_list';
	}

	public function validate(): bool {
		return in_array( (string) $this->get_field(), $this->get_compare_list(), true );
	}

	public function setup( array $condition ) {
		$this->field_setup( $condition );

		$this->set_compare_list( $condition['compareList'] ?? array() );
	}

	/**
	 * @return array
	 */
	public function get_compare_list(): array {
		return $this->compare_list;
	}

	/**
	 * @param array $compare_list
	 */
	public function set_compare_list( array $compare_list ) {
		$this->compare_list = $compare_list;
	}

}
