<?php


namespace JFB_PDF_Modules\FormRecord\DB\Views;

use Jet_Form_Builder\Db_Queries\Query_Builder;
use Jet_Form_Builder\Db_Queries\Views\View_Base;
use JFB_Modules\Form_Record\Models\Record_Model;
use JFB_PDF\Vendor\Auryn\InjectionException;
use JFB_PDF\Vendor\Auryn\Injector;
use JFB_PDF_Modules\FormRecord\DB\Models\FilesToRecordsModel;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class RecordByFileView extends View_Base {

	public function table(): string {
		return FilesToRecordsModel::table();
	}

	public function get_prepared_join( Query_Builder $builder ) {
		parent::get_prepared_join( $builder );

		$files_to_records = FilesToRecordsModel::table();
		$records          = Record_Model::table();

		$builder->join .= "
LEFT JOIN `{$records}` ON 1=1 
	AND `{$records}`.`id` = `{$files_to_records}`.`record_id`
		";
	}

	public function select_columns(): array {
		return Record_Model::schema_columns();
	}

	/**
	 * @return FilesToRecordsModel[]
	 */
	public function get_dependencies(): array {
		return array( new FilesToRecordsModel() );
	}

}
