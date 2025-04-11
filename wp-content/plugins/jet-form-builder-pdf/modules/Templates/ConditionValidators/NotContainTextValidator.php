<?php

namespace JFB_PDF_Modules\Templates\ConditionValidators;

class NotContainTextValidator extends ContainTextValidator {

	public function is_supported( array $condition ): bool {
		return ( $condition['operator'] ?? '' ) === 'not_contain';
	}

	public function validate(): bool {
		return ! parent::validate();
	}

}
