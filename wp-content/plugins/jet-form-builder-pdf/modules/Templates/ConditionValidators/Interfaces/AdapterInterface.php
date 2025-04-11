<?php

namespace JFB_PDF_Modules\Templates\ConditionValidators\Interfaces;

use JFB_PDF_Modules\Templates\ConditionValidators\Interfaces;

interface AdapterInterface {

	public function is_supported( Interfaces\BaseInterface $validator ): bool;

	public function transform( Interfaces\BaseInterface $validator );

}
