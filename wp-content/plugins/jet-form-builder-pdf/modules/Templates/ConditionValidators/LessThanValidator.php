<?php

namespace JFB_PDF_Modules\Templates\ConditionValidators;

use JFB_PDF_Modules\Templates\ConditionValidators\Interfaces\CompareFieldInterface;
use JFB_PDF_Modules\Templates\ConditionValidators\Traits\CompareFieldTrait;

class LessThanValidator implements CompareFieldInterface {

	use CompareFieldTrait;

	public function is_supported( array $condition ): bool {
		return ( $condition['operator'] ?? '' ) === 'less';
	}

	public function validate(): bool {
		return $this->get_field() < $this->get_compare();
	}

}
