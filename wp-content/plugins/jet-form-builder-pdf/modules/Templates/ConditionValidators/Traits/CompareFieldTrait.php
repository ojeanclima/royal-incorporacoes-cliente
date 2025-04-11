<?php

namespace JFB_PDF_Modules\Templates\ConditionValidators\Traits;

trait CompareFieldTrait {

	use FieldTrait {
		FieldTrait::setup as private field_setup;
	}

	protected $compare;

	/**
	 * @param $compare
	 *
	 * @return void
	 */
	public function set_compare( $compare ) {
		$this->compare = $compare;
	}

	/**
	 * @return mixed
	 */
	public function get_compare() {
		return $this->compare;
	}

	public function setup( array $condition ) {
		$this->field_setup( $condition );
		$this->set_compare( $condition['compare'] ?? '' );
	}

}
