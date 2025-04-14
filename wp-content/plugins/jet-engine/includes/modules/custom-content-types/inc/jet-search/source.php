<?php
namespace Jet_Engine\Modules\Custom_Content_Types\Jet_Search;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * CCT Source Class
 */
class Source extends \Jet_Search\Search_Sources\Base {

	/**
	 * Source name
	 *
	 * @var string
	 */
	protected $source_name = null;

	/**
	 * CCT Instance
	 * @var Jet_Engine\Modules\Custom_Content_Types\Factory
	 */
	protected $cct_instance = null;

	/**
	 * Indicates whether the source has a listing.
	 *
	 * @var bool
	 */
	protected $has_listing = true;

	/**
	 * Set given CCT instance into $this->cct_instance
	 *
	 * @param Jet_Engine\Modules\Custom_Content_Types\Factory $cct_instance CCT instance.
	 */
	public function set_cct_instance( $cct_instance ) {
		$this->cct_instance = $cct_instance;
		$this->source_name  = 'cct_' . $this->cct_instance->get_arg( 'slug' );
	}

	/**
	 * Returns source human-readable name
	 *
	 * @return string The label of the source.
	 */
	public function get_label() {
		return $this->cct_instance ? $this->cct_instance->get_arg( 'name' ) : false;
	}

	/**
	 * Returns the priority of the source.
	 *
	 * @return int The priority of the source.
	 */
	public function get_priority() {

		$priority = apply_filters( 'jet-search/ajax-search/search-source/cct/' . $this->source_name . '/priority', -1 );

		if ( ! is_int( $priority ) || 0 === $priority ) {
			return -1;
		}

		return $priority;
	}

	/**
	 * Retrieves the query result list.
	 * Sets the items list and results count based on the query.
	 */
	public function build_items_list() {

		$items = $this->get_query_result();

		if ( empty( $items ) ) {
			$this->results_count = 0;
			return;
		}

		$name             = $this->get_name();
		$title_field_name = 'search_source_' . $name . '_title_field';
		$url_field_name   = 'search_source_' . $name . '_url_field';

		$title_field = ! empty( $this->args[ $title_field_name ] ) ? $this->args[ $title_field_name ] : '_ID';
		$url_field   = ! empty( $this->args[ $url_field_name ] ) ? $this->args[ $url_field_name ] : 'cct_single_post_id';

		foreach ( $items as $item ) {

			$name = isset( $item->$title_field ) ? $item->$title_field : '#' . $item['_ID'];
			$url  = isset( $item->$url_field ) ? $item->$url_field : false;

			if ( $url && 0 < absint( $url ) ) {
				$url = get_permalink( $url );
			} elseif ( $url ) {
				$url = esc_url( $url );
			}

			$this->items_list[] = apply_filters(
				'jet-search/ajax-search/search-source/cct/' . $this->source_name . '/search-result-list-item',
				array(
					'name' => $name,
					'url'  => $url,
				),
				$item
			);
		}

		$this->items_list = apply_filters(
			'jet-search/ajax-search/search-source/cct/' . $this->source_name . '/search-result-list',
			$this->items_list,
			$this
		);

		$this->results_count = count( $this->items_list );

	}

	/**
	 * Retrieves the query result based on the search string and other parameters.
	 *
	 * @param int    $limit The number of results to return.
	 * @return mixed The query result.
	 */
	public function get_query_result( $limit = null ) {

		$limit = null != $limit ? $limit : $this->limit;
		$items = array();

		if ( $this->cct_instance ) {

			$flag = \OBJECT;
			$this->cct_instance->get_db()->set_format_flag( $flag );

			$items = $this->cct_instance->get_db()->query(
				array( '_cct_search' => array(
					'keyword' => esc_sql( $this->search_string )
				) ),
				$this->limit
			);
		}

		return $items;
	}

	/**
	 * Optional additional editor general controls.
	 *
	 * @return array Empty array by default, can be overridden by child classes.
	 */
	public function additional_editor_general_controls() {

		$name = $this->get_name();

		return array(
			'section_additional_sources' => array(
				'search_source_' . $name . '_title_field' => array(
					'label'       => __( 'Title Field', 'jet-engine' ),
					'description' => __( 'Set ID/name of CCT field to use as result title', 'jet-engine' ),
					'type'        => 'text',
					'default'     => '',
					'condition' => array(
						'search_source_' . $name . '!' => '',
					),
				),
				'search_source_' . $name . '_url_field' => array(
					'label'       => __( 'URL Field', 'jet-engine' ),
					'description' => __( 'Required field! Set ID/name of CCT field to use as result URL. If leave it empty - plugin will automatically try to use related single post URL, if found it', 'jet-engine' ),
					'type'        => 'text',
					'default'     => '',
					'condition' => array(
						'search_source_' . $name . '!' => '',
					),
				),
			),
		);
	}

}
