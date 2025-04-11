<?php

namespace JFB_PDF_Modules\FormRecord\Admin\Columns;

use Jet_Form_Builder\Admin\Table_Views\Actions\View_Single_Action;
use Jet_Form_Builder\Admin\Table_Views\Columns\Base_Row_Actions_Column;
use JFB_PDF_Modules\FormRecord\Admin\MetaBoxes\Actions\DeleteAttachment;
use JFB_PDF_Modules\FormRecord\Admin\MetaBoxes\Actions\EditAttachment;
use JFB_PDF_Modules\FormRecord\Admin\MetaBoxes\Actions\ViewAttachment;

class AttachmentActions extends Base_Row_Actions_Column {

	/**
	 * @return View_Single_Action[]
	 */
	protected function get_actions(): array {
		return array(
			new ViewAttachment(),
			new EditAttachment(),
			new DeleteAttachment(),
		);
	}
}
