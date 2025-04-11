<?php

namespace JFB_PDF_Modules\Templates\ConditionValidators;

use JFB_PDF_Modules\Templates\ConditionValidators\Interfaces\FieldInterface;
use JFB_PDF_Modules\Templates\ConditionValidators\Traits\FieldTrait;

class EmptyValidator implements FieldInterface {

	use FieldTrait;

	public function is_supported( array $condition ): bool {
		return ( $condition['operator'] ?? '' ) === 'empty';
	}

	public function validate(): bool {
		return empty( $this->get_field() );
	}

}
