<?php


namespace JFB_PDF_Modules\FormRecord\DB\Views;

use Jet_Form_Builder\Db_Queries\Query_Builder;
use Jet_Form_Builder\Db_Queries\Views\View_Base;
use Jet_Form_Builder\Exceptions\Query_Builder_Exception;
use JFB_Modules\Form_Record\Models\Record_Model;
use JFB_PDF\Vendor\Auryn\InjectionException;
use JFB_PDF\Vendor\Auryn\Injector;
use JFB_PDF_Modules\FormRecord\DB\Models\FilesToRecordsModel;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class FileByRecord extends View_Base {

	public function table(): string {
		return FilesToRecordsModel::table();
	}

	/**
	 * @param $record_id
	 *
	 * @return \Generator
	 * @throws Query_Builder_Exception
	 */
	public static function get_attachments( $record_id ): \Generator {
		$relation_rows = static::find( array( 'record_id' => $record_id ) )
							   ->query()
							   ->generate_all();

		foreach ( $relation_rows as $relation_row ) {
			yield get_post( $relation_row->attachment_id ?? 0, ARRAY_A );
		}
	}

	public function select_columns(): array {
		return array(
			'attachment_id',
		);
	}

	/**
	 * @return FilesToRecordsModel[]
	 */
	public function get_dependencies(): array {
		return array( new FilesToRecordsModel() );
	}

}
