<?php

namespace JFB_PDF_Modules\FormConnector\Templates\ValidatorsAdapters;

use Jet_Form_Builder\Classes\Tools;
use JFB_PDF_Modules\Templates\ConditionValidators\Interfaces\AdapterInterface;
use JFB_PDF_Modules\Templates\ConditionValidators\Interfaces;
use JFB_Modules\Rich_Content;

class Compare implements AdapterInterface {

	public function is_supported( Interfaces\BaseInterface $validator ): bool {
		return $validator instanceof Interfaces\CompareFieldInterface;
	}

	/**
	 * @param Interfaces\CompareFieldInterface $validator
	 *
	 * @return void
	 */
	public function transform( Interfaces\BaseInterface $validator ) {
		$validator->set_compare(
			Rich_Content\Module::rich(
				Tools::to_string( $validator->get_compare() )
			)
		);
	}
}
