<?php

namespace JFB_PDF_Modules\FormConnector\Templates\ValidatorsAdapters;

use JFB_PDF_Modules\Templates\ConditionValidators\Interfaces\AdapterInterface;
use JFB_PDF_Modules\Templates\ConditionValidators\Interfaces;

class Field implements AdapterInterface {

	public function is_supported( Interfaces\BaseInterface $validator ): bool {
		return $validator instanceof Interfaces\FieldInterface;
	}

	/**
	 * @param Interfaces\FieldInterface $validator
	 *
	 * @return void
	 */
	public function transform( Interfaces\BaseInterface $validator ) {
		$validator->set_field(
			jet_fb_context()->get_value( $validator->get_field() )
		);
	}
}
