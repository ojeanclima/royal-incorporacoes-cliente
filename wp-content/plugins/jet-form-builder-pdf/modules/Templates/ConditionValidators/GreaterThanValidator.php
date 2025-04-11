<?php

namespace JFB_PDF_Modules\Templates\ConditionValidators;

use JFB_PDF_Modules\Templates\ConditionValidators\Interfaces\CompareFieldInterface;
use JFB_PDF_Modules\Templates\ConditionValidators\Traits\CompareFieldTrait;

class GreaterThanValidator implements CompareFieldInterface {

	use CompareFieldTrait;

	public function is_supported( array $condition ): bool {
		return ( $condition['operator'] ?? '' ) === 'greater';
	}

	public function validate(): bool {
		return $this->get_field() > $this->get_compare();
	}

}
