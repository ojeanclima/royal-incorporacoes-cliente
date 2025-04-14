<?php


namespace Jet_FB_Login\JetFormBuilder\DbViews;

use Jet_FB_Login\JetFormBuilder\Models\ResetTokenModel;
use Jet_Form_Builder\Db_Queries\Base_Db_Model;
use Jet_Form_Builder\Db_Queries\Query_Builder;
use Jet_Form_Builder\Db_Queries\Views\View_Base;
use Jet_Form_Builder\Exceptions\Query_Builder_Exception;

class ResetTokenView extends View_Base {

	protected $order_by = array(
		array(
			'column' => 'id',
			'sort'   => self::FROM_HIGH_TO_LOW,
		),
	);

	public function table(): string {
		return ResetTokenModel::table();
	}

	public function select_columns(): array {
		return ResetTokenModel::schema_columns();
	}

	/**
	 * @param int $user_id
	 *
	 * @return array
	 * @throws Query_Builder_Exception
	 */
	public static function get_by_uid( int $user_id ): array {
		return static::findOne(
			array(
				'user_id' => $user_id,
			)
		)->query()->query_one();
	}

	/**
	 * @return Base_Db_Model[]
	 */
	public function get_dependencies(): array {
		return array(
			new ResetTokenModel(),
		);
	}

	/**
	 * @return Query_Builder
	 * @throws Query_Builder_Exception
	 */
	public function query(): Query_Builder {
		$this->prepare_dependencies();

		return ( new Query_Builder() )->set_view( $this );
	}
}
