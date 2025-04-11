<?php

namespace JFB_PDF_Modules\FormConnector\Templates\ValidatorsAdapters;

use Jet_Form_Builder\Classes\Tools;
use JFB_PDF_Modules\Templates\ConditionValidators\Interfaces\AdapterInterface;
use JFB_PDF_Modules\Templates\ConditionValidators\Interfaces;
use JFB_PDF_Modules\Templates\ConditionValidators\InTheListValidator;
use JFB_Modules\Rich_Content;

class InTheList implements AdapterInterface {

	public function is_supported( Interfaces\BaseInterface $validator ): bool {
		return $validator instanceof InTheListValidator;
	}

	/**
	 * @param InTheListValidator $validator
	 *
	 * @return void
	 */
	public function transform( Interfaces\BaseInterface $validator ) {
		$values = $validator->get_compare_list();

		foreach ( $values as &$value ) {
			$value = Rich_Content\Module::rich( Tools::to_string( $value ) );
		}

		$validator->set_compare_list( $values );
	}
}
