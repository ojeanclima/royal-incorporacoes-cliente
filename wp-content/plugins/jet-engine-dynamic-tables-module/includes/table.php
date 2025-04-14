<?php
namespace Jet_Engine_Dynamic_Tables;

use Jet_Engine\Query_Builder\Manager as Query_Builder;

#[AllowDynamicProperties]
class Table {

	private $table_id   = null;
	private $columns    = array();
	private $inline_css = array();
	private $settings   = array();
	private $query      = null;
	private $query_id   = null;

	public function __construct( $table_id = 0, $table_settings = array() ) {

		$this->table_id = $table_id;

		$table_data = Plugin::instance()->data->get_item_for_edit( $this->table_id );

		$columns = ! empty( $table_data['meta_fields'] ) ? $table_data['meta_fields'] : array();

		if ( isset( $table_data['meta_fields'] ) ) {
			unset( $table_data['meta_fields'] );
		}

		$settings = $table_data;

		// Setup extra-settings
		$settings['rewrite_query'] = isset( $table_settings['rewrite_query'] ) ? filter_var( $table_settings['rewrite_query'], FILTER_VALIDATE_BOOLEAN ) : false;
		$settings['rewrite_query_id'] = isset( $table_settings['rewrite_query_id'] ) ? absint( $table_settings['rewrite_query_id'] ) : 0;

		$this->setup_table( $columns, $settings );

		if ( $this->table_id ) {
			jet_engine()->admin_bar->register_item( 'dynamic-table-' . $this->table_id, array(
				'title'     => $settings['name'],
				'sub_title' => esc_html__( 'Table', 'jet-engine' ),
				'href'      => admin_url( 'admin.php?page=jet-engine-tables&table_action=edit&id=' . $this->table_id ),
			) );
		}

	}

	public function setup_table( $columns = array(), $settings = array() ) {

		$this->settings = $settings;

		$query_id = ! empty( $this->settings['query_id'] ) ? $this->settings['query_id'] : false;

		if ( ! empty( $this->settings['rewrite_query'] ) && ! empty( $this->settings['rewrite_query_id'] ) ) {
			$query_id = $this->settings['rewrite_query_id'];
		}

		$query_id = apply_filters( 'jet-engine/query-builder/listings/query-id', $query_id, null, $settings );

		if ( $query_id ) {
			$this->query    = Query_Builder::instance()->get_query_by_id( $query_id );
			$this->query_id = $query_id;
		}

		$columns = apply_filters( 'jet-engine/data-tables/table-columns', $columns, $this );

		if ( ! empty( $columns ) ) {
			foreach( $columns as $column ) {
				$this->columns[ $column['_id'] ] = $column;
			}
		}

		if ( ! empty( $this->settings['inline_styles'] ) ) {
			foreach ( $this->settings['inline_styles'] as $id => $data ) {

				$heading_css = null;
				$cell_css    = null;

				if ( ! empty( $data['width'] ) ) {
					$heading_css .= 'width:' . $data['width'] . '; min-width:' . $data['width'] . ';';
					$cell_css    .= 'width:' . $data['width'] . '; min-width:' . $data['width'] . ';';
				}

				if ( ! empty( $data['h_v_align'] ) ) {
					$heading_css .= 'vertical-align:' . $data['h_v_align'] . ';';
				}

				if ( ! empty( $data['h_h_align'] ) ) {
					$heading_css .= 'text-align:' . $data['h_h_align'] . ';';
				}

				if ( ! empty( $data['c_v_align'] ) ) {
					$cell_css .= 'vertical-align:' . $data['c_v_align'] . ';';
				}

				if ( ! empty( $data['c_h_align'] ) ) {
					$cell_css .= 'text-align:' . $data['c_h_align'] . ';';
				}

				$this->inline_css[ $id ] = array(
					'th' => '',
					'td' => '',
				);

				if ( $heading_css ) {
					$this->inline_css[ $id ]['th'] = $heading_css;
				}

				if ( $cell_css ) {
					$this->inline_css[ $id ]['td'] = $cell_css;
				}

			}
		}

	}

	public function get_column_css( $id = null, $tag = 'td' ) {
		return ! empty( $this->inline_css[ $id ][ $tag ] ) ? $this->inline_css[ $id ][ $tag ] : false;
	}

	/**
	 * Returns numeric table ID
	 *
	 * @return [type] [description]
	 */
	public function get_id() {
		return $this->table_id;
	}

	/**
	 * Returns query
	 *
	 * @return [type] [description]
	 */
	public function get_query() {
		return $this->query;
	}

	/**
	 * Returns query ID
	 *
	 * @return int|string
	 */
	public function get_query_id() {
		return $this->query_id;
	}

	/**
	 * Returns an array of table rows
	 *
	 * @return [type] [description]
	 */
	public function get_rows() {
		
		$result = array();

		if ( $this->get_query() ) {
			$result = $this->get_query()->get_items();
		}

		return apply_filters( 'jet-engine/data-tables/table-rows', $result, $this );

	}

	public function get_row_contents( $object ) {

		jet_engine()->frontend->setup_data( $object );
		jet_engine()->listings->data->set_listing( jet_engine()->listings->get_new_doc( array(
			'listing_source'    => 'query',
			'listing_post_type' => 'post',
			'listing_tax'       => false,
			'is_main'           => true,
		) ) );

		$res = array();

		foreach ( $this->columns as $id => $column ) {
			$name = ! empty( $column['name'] ) ? $column['name'] : 'column-' . $id;
			$res[] = array(
				'css_class' => $this->get_column_css_class( $name ),
				'content'   => $this->get_column_content( $column ),
				'id'        => $id,
			);
		}

		return $res;
	}

	public function get_column_content( $column ) {

		$res = null;

		switch ( $column['content'] ) {
			case 'raw':
				$res = $this->get_raw_content( $column );
				break;

			case 'template':
				$template_id = $column['template_id'];
				if ( $template_id ) {
					$template_id = apply_filters( 'jet-engine/listings/frontend/rendered-listing-id', $template_id );
					$res = sprintf(
						'<div class="jet-listing-dynamic-post-%2$s %3$s">%1$s</div>',
						jet_engine()->frontend->get_listing_item_content( $template_id ),
						jet_engine()->listings->data->get_current_object_id(),
						apply_filters( 'jet-engine/data-tables/template_class', '' ),
					);
				}
				break;

			default:
				$res = apply_filters( 'jet-engine/data-tables/column-content', $res, $column );
				break;
		}

		return $res;

	}

	public function get_raw_content( $column ) {

		$res    = null;
		$source = ! empty( $column['data_source'] ) ? $column['data_source'] : '';

		switch ( $source ) {
			case 'object':

				$prop = ! empty( $column['object_field'] ) ? $column['object_field'] : false;

				if ( $prop ) {
					$object = jet_engine()->listings->data->get_current_object();
					$res    = jet_engine()->listings->data->get_prop( $prop, $object );
				}

				break;

			case 'meta':

				$key        = ! empty( $column['meta_key'] ) ? $column['meta_key'] : false;
				$custom_key = ! empty( $column['custom_meta_key'] ) ? $column['custom_meta_key'] : false;

				if ( $custom_key ) {
					$key = $custom_key;
				}

				if ( $key ) {
					$res = jet_engine()->listings->data->get_meta( $key );
				}

				break;

			case 'fetched':
				$object = jet_engine()->listings->data->get_current_object();
				$col    = ! empty( $column['fetched_column'] ) ? $column['fetched_column'] : false;
				$res    = isset( $object->$col ) ? $object->$col : false;
				break;
		}

		if ( ! empty( $column['apply_callback'] ) && ! empty( $column['filter_callback'] ) ) {
			$res = jet_engine()->listings->apply_callback( $res, $column['filter_callback'], $column );
		}

		if ( ! empty( $column['customize'] ) && ! empty( $column['customize_format'] ) ) {
			$res = sprintf( $column['customize_format'], $res );
		}

		if ( is_object( $res ) ) {
			$res = get_object_vars( $res );
		}

		if ( is_array( $res ) ) {
			$res = $this->implode_r( ', ', $res );
		}

		return do_shortcode( wp_unslash( $res ) );

	}

	/**
	 * Return recursively imploded array
	 *
	 * @var string
	 */
	public function implode_r( $glue = '', $pieces = array() ) {

		$output    = '';
		$real_glue = '';

		foreach ( $pieces as $piece ) {

			if ( is_array( $piece ) ) {
				$output .= $real_glue . $this->implode_r( $glue, $piece );
			} else {
				$output .= $real_glue . $piece;
			}

			$real_glue = $glue;
		}

		return $output;

	}

	/**
	 * Returns columns CSS class by column name
	 *
	 * @return [type] [description]
	 */
	public function get_column_css_class( $name ) {
		return sanitize_key( $name );
	}

	/**
	 * Returns column headers
	 *
	 * @return [type] [description]
	 */
	public function get_columns_headers() {

		$res = array();

		foreach ( $this->columns as $id => $column ) {
			$name      = ! empty( $column['name'] ) ? $column['name'] : '';
			$css_class = ! empty( $name ) ? $name : 'column-' . $id;

			$res[] = array(
				'css_class' => $this->get_column_css_class( $css_class ),
				'content'   => apply_filters( 'jet-engine/compatibility/translate-string', $name ),
				'id'        => $id,
			);
		}

		return $res;
	}

}
