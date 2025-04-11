<?php

namespace JFB_PDF_Modules\GenerateFileAction\Includes;

use Jet_Form_Builder\Actions\Types\Base;
use Jet_Form_Builder\Form_Messages\Actions\Base_Action_Messages;

class ActionMessages extends Base_Action_Messages {

	public function is_supported( Base $action ): bool {
		return $action instanceof Action;
	}

	protected function messages(): array {
		return array(
			'pdf_file_create' => array(
				'label' => __( 'Error creating file', 'jet-form-builder-pdf' ),
				'value' => 'Error when saving a pdf file, namely when creating it, or a directory for it',
			),
			'pdf_file_save'   => array(
				'label' => __( 'Error creating attachment', 'jet-form-builder-pdf' ),
				'value' => 'Error when saving a pdf file as attachment',
			),
		);
	}
}
