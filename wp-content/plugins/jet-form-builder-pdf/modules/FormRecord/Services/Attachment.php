<?php

namespace JFB_PDF_Modules\FormRecord\Services;

use Jet_Form_Builder\Exceptions\Query_Builder_Exception;
use JFB_PDF_Modules\FormRecord\DB\Views\FileByRecord;

class Attachment {

	/**
	 * @param array $ids
	 *
	 * @return void
	 * @throws Query_Builder_Exception
	 */
	public function delete( array $ids ) {
		FileByRecord::delete(
			array(
				array(
					'type'   => 'in',
					'values' => array( 'attachment_id', $ids ),
				),
			)
		);
	}

}
