<?php

namespace JFB_PDF_Modules\Templates\ConditionValidators\Interfaces;

interface FieldInterface extends BaseInterface {

	/**
	 * @param mixed $value
	 *
	 * @return void
	 */
	public function set_field( $value );

	/**
	 * @return mixed
	 */
	public function get_field();

}
