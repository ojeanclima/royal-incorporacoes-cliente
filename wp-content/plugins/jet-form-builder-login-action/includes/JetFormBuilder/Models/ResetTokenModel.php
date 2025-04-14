<?php


namespace Jet_FB_Login\JetFormBuilder\Models;

use Jet_Form_Builder\Db_Queries\Base_Db_Model;
use Jet_Form_Builder\Db_Queries\Exceptions\Sql_Exception;

class ResetTokenModel extends Base_Db_Model {

	/**
	 * @return string
	 */
	public static function table_name(): string {
		return 'reset_passwords';
	}

	/**
	 * @return array
	 */
	public static function schema(): array {
		return array(
			'id'         => 'bigint(20) NOT NULL AUTO_INCREMENT',
			'form_id'    => 'bigint(20) UNSIGNED NOT NULL',
			'user_id'    => 'bigint(20)',
			'hash'       => 'varchar(255)',
			// 'url' | 'code'
			'mode'       => 'tinyint UNSIGNED NOT NULL',
			'created_at' => 'TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP',
		);
	}

	/**
	 * @return array
	 */
	public static function schema_keys(): array {
		return array(
			'id'   => 'primary key',
			'hash' => 'index',
		);
	}

	public function before_delete() {
	}
}
