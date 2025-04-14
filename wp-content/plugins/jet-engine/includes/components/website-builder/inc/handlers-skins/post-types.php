<?php
namespace Jet_Engine\Website_Builder\Handlers_Skins;

use \Jet_Engine\Website_Builder\Handler;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Post_Types extends Base {

	/**
	 * Get skin ID
	 *
	 * @return string
	 */
	public function get_id() {
		return Handler::POST_TYPES_ID;
	}

	/**
	 * Get skin title
	 *
	 * @return string
	 */
	public function get_title() {
		return esc_html__( 'Post Types', 'jet-engine' );
	}

	/**
	 * Render single results row
	 *
	 * @param  array  $row Results data row.
	 * @return string
	 */
	public function skin_data_row( $row = [] ) {

		$result = '';

		$post_type_edit = add_query_arg(
			[
				'page' => 'jet-engine-cpt',
				'cpt_action' => 'edit',
				'id' => $row['id'],
			],
			admin_url( 'admin.php' )
		);

		$post_type_posts = self::get_items_url( $row['slug'] );

		$result .= $this->data_row_title(
			$row['name'],
			sprintf( '<a href="%1$s">%2$s</a>', $post_type_edit, __( 'Edit', 'jet-engine' ) )
		);

		$items = [
			$this->data_row_item( sprintf(
				'<a href="%1$s">%2$s</a>',
				$post_type_posts, __( 'Add posts', 'jet-engine' )
			) ),
		];

		if( ! empty( $row['query_id'] ) ) {

			$query_url = add_query_arg( [
				'page'         => 'jet-engine-query',
				'query_action' => 'edit',
				'id'           => absint( $row['query_id'] ),
			], admin_url( 'admin.php' ) );

			$items[] = $this->data_row_item( sprintf(
				'<a href="%1$s">%2$s</a>',
				$query_url, __( 'Query', 'jet-engine' )
			) );
		}

		if( ! empty( $row['lsting_url'] ) ) {
			$items[] = $this->data_row_item( sprintf(
				'<a href="%1$s">%2$s</a>',
				$row['lsting_url'], __( 'Base Listing', 'jet-engine' )
			) );
		}

		$result .= $this->data_row_content( implode( '|', $items ) );

		return $result;
	}

	/**
	 * Get URL to items page of given CPT by slug
	 * @return [type] [description]
	 */
	public static function get_items_url( $slug ) {
		return add_query_arg(
			[
				'post_type' => $slug,
			],
			admin_url( 'edit.php' )
		);
	}

	/**
	 * Get realted tutorials
	 *
	 * @return array
	 */
	public function get_tuts() {
		return [
			[
				'url'   => 'https://crocoblock.com/knowledge-base/features/query-builder-overview/',
				'label' => 'Query Builder Overview',
			],
			[
				'url'   => 'https://crocoblock.com/knowledge-base/jetengine/how-to-create-query-builder-listing-template/',
				'label' => 'How to Create a Listing For Query',
			],
		];
	}
}
