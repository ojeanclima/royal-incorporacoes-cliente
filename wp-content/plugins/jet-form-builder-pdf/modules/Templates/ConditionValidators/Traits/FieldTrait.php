<?php

namespace JFB_PDF_Modules\Templates\ConditionValidators\Traits;

trait FieldTrait {

	protected $field;

	/**
	 * @param mixed $value
	 *
	 * @return void
	 */
	public function set_field( $value ) {
		$this->field = $value;
	}

	/**
	 * @return mixed
	 */
	public function get_field() {
		return $this->field;
	}

	public function setup( array $condition ) {
		$this->set_field( $condition['field'] ?? '' );
	}

}
