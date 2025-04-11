<?php


namespace JFB_PDF_Modules\FormRecord\Admin\Columns;

use Jet_Form_Builder\Admin\Table_Views\Column_Advanced_Base;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class FileNameColumn extends Column_Advanced_Base {

	protected $column = 'post_title';

	public function get_label(): string {
		return '';
	}

	public function get_value( array $record = array() ) {
		$value = parent::get_value( $record );

		return $value . '.pdf';
	}


}
