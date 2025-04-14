<?php
namespace Jet_Engine\Website_Builder\Handlers_Skins;

use \Jet_Engine\Website_Builder\Handler;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Relations extends Base {

	/**
	 * Get skin ID
	 *
	 * @return string
	 */
	public function get_id() {
		return Handler::RELATIONS_ID;
	}

	/**
	 * Get skin title
	 *
	 * @return string
	 */
	public function get_title() {
		return esc_html__( 'Relations', 'jet-engine' );
	}

	/**
	 * Render single results row
	 *
	 * @param  array  $row Results data row.
	 * @return string
	 */
	public function skin_data_row( $row = [] ) {

		$result = '';

		$rel_edit = add_query_arg(
			[
				'page' => 'jet-engine-relations',
				'cpt_relation_action' => 'edit',
				'id' => $row['id'],
			],
			admin_url( 'admin.php' )
		);

		$rel_parents  = self::get_items_url( $row['parent'] );
		$rel_children = self::get_items_url( $row['children'] );

		$result .= $this->data_row_title(
			$row['name'],
			sprintf( '<a href="%1$s">%2$s</a>', $rel_edit, __( 'Edit', 'jet-engine' ) )
		);

		$items = [];

		if ( $rel_parents ) {
			$items[] = $this->data_row_item( sprintf(
				'<a href="%1$s">%2$s</a>',
				$rel_parents, __( 'Parent Items', 'jet-engine' )
			) );
		}

		if ( $rel_children ) {
			$items[] = $this->data_row_item( sprintf(
				'<a href="%1$s">%2$s</a>',
				$rel_children, __( 'Children Items', 'jet-engine' )
			) );
		}

		$result .= $this->data_row_content( implode( '|', $items ) );

		return $result;
	}

	/**
	 * Get URL to items page.
	 *
	 * @param  array  $data [description]
	 * @return [type]       [description]
	 */
	public static function get_items_url( $data = [] ) {
		switch ( $data['type'] ) {
			case 'cct':
				return CCT::get_items_url( $data['slug'] );

			case 'taxonomy':
				return Taxonomies::get_items_url( $data['slug'] );

			case 'post_type':
				return Post_Types::get_items_url( $data['slug'] );
		}
	}

	/**
	 * Get realted tutorials
	 *
	 * @return array
	 */
	public function get_tuts() {
		return [
			[
				'url'   => 'https://crocoblock.com/knowledge-base/jetengine/jetengine-macros-for-wordpress-relations/',
				'label' => 'How to Query Related Items',
			],
			[
				'url'   => 'https://crocoblock.com/knowledge-base/jetengine/jetengine-how-to-create-relationships-between-posts/',
				'label' => 'How to Create a New Relations',
			],
		];
	}
}
