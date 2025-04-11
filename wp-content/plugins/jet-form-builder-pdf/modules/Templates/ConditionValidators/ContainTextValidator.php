<?php

namespace JFB_PDF_Modules\Templates\ConditionValidators;

use JFB_PDF_Modules\Templates\ConditionValidators\Interfaces\CompareFieldInterface;
use JFB_PDF_Modules\Templates\ConditionValidators\Traits\CompareFieldTrait;

class ContainTextValidator implements CompareFieldInterface {

	use CompareFieldTrait;

	public function is_supported( array $condition ): bool {
		return ( $condition['operator'] ?? '' ) === 'contain';
	}

	public function validate(): bool {
		return false !== strpos( (string) $this->get_field(), (string) $this->get_compare() );
	}

}
