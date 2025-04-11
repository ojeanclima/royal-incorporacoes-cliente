<?php

namespace JFB_PDF_Modules\Templates\ConditionValidators;

use JFB_PDF_Modules\Templates\ConditionValidators\Interfaces\CompareFieldInterface;
use JFB_PDF_Modules\Templates\ConditionValidators\Traits\CompareFieldTrait;

class NotBetweenValidator extends BetweenValidator {

	public function is_supported( array $condition ): bool {
		return ( $condition['operator'] ?? '' ) === 'not_between';
	}

	public function validate(): bool {
		return (
			$this->get_field() < $this->get_compare() || $this->get_compare_after() < $this->get_field()
		);
	}

}
