<?php

namespace JFB_PDF_Modules\Templates\ConditionValidators\Interfaces;

interface CompareFieldInterface extends FieldInterface {

	/**
	 * @param mixed $value
	 *
	 * @return void
	 */
	public function set_compare( $value );

	/**
	 * @return mixed
	 */
	public function get_compare();

}
