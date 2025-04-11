<?php

namespace JFB_PDF_Modules\Templates\ConditionValidators;

use JFB_PDF_Modules\Templates\ConditionValidators\Interfaces\BaseInterface;

/**
 * Only for the internal use
 *
 * @see \JFB_PDF_Modules\Templates\ConditionValidators\Manager::create_validator
 */
class AlwaysValidValidator implements BaseInterface {

	public function is_supported( array $condition ): bool {
		return true;
	}

	public function validate(): bool {
		return true;
	}

	public function setup( array $condition ) {
	}
}
