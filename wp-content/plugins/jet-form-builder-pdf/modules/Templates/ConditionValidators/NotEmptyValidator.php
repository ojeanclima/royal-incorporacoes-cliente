<?php

namespace JFB_PDF_Modules\Templates\ConditionValidators;

class NotEmptyValidator extends EmptyValidator {

	public function is_supported( array $condition ): bool {
		return ( $condition['operator'] ?? '' ) === 'not_empty';
	}

	public function validate(): bool {
		return ! parent::validate();
	}

}
