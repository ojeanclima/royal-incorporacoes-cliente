<?php

namespace JFB_PDF_Modules\Templates\ConditionValidators;

use JFB_PDF_Modules\Templates\ConditionValidators\Interfaces\BaseInterface;

/**
 * Only for the internal use
 *
 * @see \JFB_PDF_Modules\Templates\ConditionValidators\Manager::validate
 */
class OrOperator implements BaseInterface {

	public function is_supported( array $condition ): bool {
		return ! empty( $condition['or_operator'] ?? '' );
	}

	public function validate(): bool {
		return true;
	}

	public function setup( array $condition ) {
	}
}
