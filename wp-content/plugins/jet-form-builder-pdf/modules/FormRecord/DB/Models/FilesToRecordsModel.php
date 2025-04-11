<?php

namespace JFB_PDF_Modules\FormRecord\DB\Models;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

use Jet_Form_Builder\Db_Queries\Base_Db_Constraint;
use Jet_Form_Builder\Db_Queries\Base_Db_Model;
use JFB_Modules\Form_Record\Constraints\Record_Model_Constraint;

class FilesToRecordsModel extends Base_Db_Model {


	public static function table_name(): string {
		return 'attachments_to_records';
	}

	public static function schema(): array {
		return array(
			'id'            => 'bigint(20) NOT NULL AUTO_INCREMENT',
			'attachment_id' => 'bigint(20) NOT NULL',
			'record_id'     => 'bigint(20) NOT NULL',
		);
	}

	public static function schema_keys(): array {
		return array(
			'id'            => 'primary key',
			'attachment_id' => 'index',
			'record_id'     => 'index',
		);
	}

	/**
	 * @return array|Base_Db_Constraint[]
	 */
	public function foreign_relations(): array {
		return array(
			new Record_Model_Constraint(),
		);
	}

}
