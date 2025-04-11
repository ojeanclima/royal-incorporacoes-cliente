<?php

namespace JFB_PDF_Modules\Templates\ConditionValidators;

class NotInTheListValidator extends InTheListValidator {

	public function is_supported( array $condition ): bool {
		return ( $condition['operator'] ?? '' ) === 'not_in_list';
	}

	public function validate(): bool {
		return ! parent::validate();
	}

}
