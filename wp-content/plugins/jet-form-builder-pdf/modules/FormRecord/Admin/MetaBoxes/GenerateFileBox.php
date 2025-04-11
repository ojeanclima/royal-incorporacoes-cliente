<?php

namespace JFB_PDF_Modules\FormRecord\Admin\MetaBoxes;

use Jet_Form_Builder\Admin\Exceptions\Empty_Box_Exception;
use Jet_Form_Builder\Admin\Single_Pages\Meta_Boxes\Base_Table_Box;
use Jet_Form_Builder\Admin\Table_Views\Column_Base;
use Jet_Form_Builder\Exceptions\Query_Builder_Exception;
use JFB_PDF_Modules\FormRecord\Admin\Columns\AttachmentActions;
use JFB_PDF_Modules\FormRecord\Admin\Columns\FileNameColumn;
use JFB_PDF_Modules\FormRecord\DB\Views\FileByRecord;
use JFB_PDF_Modules\FormRecord\DB\Views\FileByRecordCount;
use JFB_PDF_Modules\RestAPI\Endpoints\FetchAttachments;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die();
}

class GenerateFileBox extends Base_Table_Box {

	protected $footer_heading = false;

	public function get_title(): string {
		return __( 'Generated PDF', 'jet-form-builder-pdf' );
	}

	public function get_columns(): array {
		return array(
			'file'               => new FileNameColumn(),
			Column_Base::ACTIONS => new AttachmentActions(),
		);
	}

	public function get_total(): int {
		return FileByRecordCount::findOne(
			array( 'record_id' => $this->get_id() )
		)->get_count();
	}

	public function get_rest_url(): string {
		return FetchAttachments::dynamic_rest_url(
			array( 'record_id' => $this->get_id() )
		);
	}

	public function get_rest_methods(): string {
		return FetchAttachments::get_methods();
	}

	/**
	 * @param array $args
	 *
	 * @return array
	 * @throws Empty_Box_Exception
	 */
	public function get_raw_list( array $args ): array {
		try {
			return iterator_to_array(
				FileByRecord::get_attachments( $this->get_id() )
			);
		} catch ( Query_Builder_Exception $exception ) {
			throw new Empty_Box_Exception(
				esc_html( $exception->getMessage() ),
				// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
				...$exception->get_additional()
			);
		}
	}
}
