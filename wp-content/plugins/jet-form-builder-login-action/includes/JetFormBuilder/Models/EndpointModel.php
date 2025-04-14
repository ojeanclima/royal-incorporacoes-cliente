<?php


namespace Jet_FB_Login\JetFormBuilder\Models;

use Jet_Form_Builder\Db_Queries\Base_Db_Model;
use Jet_Form_Builder\Db_Queries\Exceptions\Sql_Exception;

/*

// save in 'key' column
$key = bin2hex( random_bytes( 4 ) );

// hash secret key & save in 'hash' column
$hash_secret_key = '$wp_hasher->HashPassword($secret_key)';

$secret_key = bin2hex( random_bytes( 4 ) );
$md5Str = md5( $key );

// make url endpoint:
var_dump(
	sprintf(
		'We look for: %s',
		$key
	),
	sprintf(
		'%s?jfb_hook=%s,%s',
		'https://example.com/',
		$md5Str,
		$secret_key
	),
);

// Results:
// string(21) "We look for: 83fe6a15"
// string(71) "https://example.com/?jfb_hook=fca773acfa5cb5996d2381efa28ef87c,f968ef4e"

*/

class HooksModel extends Base_Db_Model {

	/**
	 * @return string
	 */
	public static function table_name(): string {
		return 'hooks';
	}

	/**
	 * @return array
	 */
	public static function schema(): array {
		return array(
			'id'         => 'bigint(20) NOT NULL AUTO_INCREMENT',
			'form_id'    => 'bigint(20) UNSIGNED NOT NULL',
			'user_id'    => 'bigint(20)',
			// slug of action (it has nothing to do with the actions used in the form.)
			'action'     => 'varchar(100)',
			'key'        => 'varchar(100)',
			'hash'       => 'varchar(255)',
			// How many times has it been done?
			'exec_count' => 'int(11)',
			// How many times can be done
			'limit_exec' => 'int(11)',
			// The number of seconds since the date in the created_at column
			'lifespan'   => 'int(11)',
			// if set, `lifespan` will be ignored
			'expire_at'  => 'TIMESTAMP',
			'created_at' => 'TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP',
			'updated_at' => 'TIMESTAMP',
		);
	}

	/**
	 * @return array
	 */
	public static function schema_keys(): array {
		return array(
			'id'     => 'primary key',
			'action' => 'index',
			'hash'   => 'index',
		);
	}

	public function before_delete() {
	}
}

