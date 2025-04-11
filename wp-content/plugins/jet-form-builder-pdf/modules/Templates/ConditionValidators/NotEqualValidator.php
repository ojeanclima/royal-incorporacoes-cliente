<?php

namespace JFB_PDF_Modules\Templates\ConditionValidators;

class NotEqualValidator extends EqualValidator {

	public function is_supported( array $condition ): bool {
		return ( $condition['operator'] ?? '' ) === 'not_equal';
	}

	public function validate(): bool {
		return ! parent::validate();
	}

}
