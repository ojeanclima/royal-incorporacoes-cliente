<?php

namespace JFB_PDF_Modules\FormConnector\Templates\ValidatorsAdapters;

use Jet_Form_Builder\Classes\Tools;
use JFB_PDF_Modules\Templates\ConditionValidators\Interfaces;
use JFB_PDF_Modules\Templates\ConditionValidators\BetweenValidator;
use JFB_Modules\Rich_Content;
use JFB_PDF_Modules\Templates\ConditionValidators\Interfaces\AdapterInterface;

class Between implements AdapterInterface {

	public function is_supported( Interfaces\BaseInterface $validator ): bool {
		return $validator instanceof BetweenValidator;
	}

	/**
	 * @param BetweenValidator $validator
	 *
	 * @return void
	 */
	public function transform( Interfaces\BaseInterface $validator ) {
		$validator->set_compare_after(
			Rich_Content\Module::rich(
				Tools::to_string( $validator->get_compare_after() )
			)
		);
	}
}
