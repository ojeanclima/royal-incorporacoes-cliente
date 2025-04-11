<?php

namespace JFB_PDF_Modules\Templates\ConditionValidators\Interfaces;

interface BaseInterface {

	public function is_supported( array $condition ): bool;

	public function validate(): bool;

	public function setup( array $condition );

}
