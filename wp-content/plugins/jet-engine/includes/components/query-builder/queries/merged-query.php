<?php
namespace Jet_Engine\Query_Builder\Queries;

use Jet_Engine\Query_Builder\Manager;
use Jet_Engine\Query_Builder\Helpers\Posts_Per_Page_Manager;

class Merged_Query extends Base_Query {

	public $queries_stack = null;
	public $query_args = null;
	public $merged_items = null;

	/**
	 * Rewrite setup_query method from parent 
	 * to set appropriate query type from 'base_query_type' option for better JSF compatibility
	 * 
	 * @return void
	 */
	public function setup_query() {
		parent::setup_query();
		$this->final_query['_query_type'] = ! empty( $this->final_query['base_query_type'] ) ? $this->final_query['base_query_type'] : 'posts';
	}

	/**
	 * Rewrite Get query type method to return children query type
	 * 
	 * @return [type] [description]
	 */
	public function get_query_type() {
		return ! empty( $this->final_query['base_query_type'] ) ? $this->final_query['base_query_type'] : 'posts';
	}

	/**
	 * Returns queries items
	 *
	 * @return [type] [description]
	 */
	public function _get_items() {

		if ( null !== $this->merged_items ) {
			return $this->merged_items;
		}

		$this->merged_items = [];
		$queries_stack      = $this->get_queries_stack();

		$exclude_duplicates = $this->exclude_duplicates();
		$max_items_per_page = $this->max_items_per_page();

		$exclude_ids = [];
		$current_count = 0;

		foreach ( $queries_stack as $query ) {


			if ( ! empty( $exclude_ids ) ) {
				$this->exclude_ids( $query, $exclude_ids );
			}

			if ( $max_items_per_page ) {

				$query_count = $this->get_items_per_page_for_query( $query );

				if ( $max_items_per_page < $query_count || 0 >= $query_count ) {
					$this->set_items_per_page( $query, $max_items_per_page );
				}
			}
			
			$items = $query->get_items();
			$query_count = count( $items );

			if ( $max_items_per_page && $current_count + $query_count <= $max_items_per_page ) {
				$current_count += $query_count;
			} elseif ( $max_items_per_page && $current_count >= $max_items_per_page ) {
				break;
			} elseif ( $max_items_per_page ) {
				$items = array_slice( $items, 0, $query_count - $current_count );
				$current_count = $max_items_per_page;
			}

			if ( $exclude_duplicates ) {
				$exclude_ids = array_unique( array_merge( $exclude_ids, array_map( function( $item ) {
					return jet_engine()->listings->data->get_current_object_id( $item );
				}, $items ) ) );

			}

			$this->merged_items = array_merge( $this->merged_items, $items );
		}

		return $this->merged_items;

	}

	/**
	 * Reset query to reuse on the same page with the different data
	 * 
	 * @return [type] [description]
	 */
	public function reset_query() {
		$this->queries_stack = null;
		$this->query_args    = null;
		$this->merged_items  = null;
	}

	/**
	 * Returns current query arguments
	 *
	 * @return array
	 */
	public function get_query_args() {

		if ( null === $this->query_args ) {
			$this->query_args = [];
			foreach ( $this->get_queries_stack() as $query ) {
				$query->setup_query();
				$this->query_args = array_merge( $this->query_args, $query->get_query_args() );
			}

		}

		return $this->query_args;

	}


	/**
	 * Returns count of items per page for give query based on query settings
	 * 
	 * @param  [type] $query [description]
	 * @return [type]        [description]
	 */
	public function get_items_per_page_for_query( $query ) {
		
		switch ( $query->query_type ) {
			case 'posts':
				return isset( $query->final_query['posts_per_page'] ) ? floatval( $query->final_query['posts_per_page'] ) : absint( get_option( 'posts_per_page', 10 ) );

			case 'terms':

				$per_page = 0;

				if ( ! empty( $query->final_query['number_per_page'] ) ) {
					$per_page = absint( $query->final_query['number_per_page'] );
				} elseif ( ! empty( $query->final_query['number'] ) ) {
					$per_page = absint( $query->final_query['number'] );
				}

				return $per_page;

			case 'users':
			case 'comments':

				$per_page = 0;

				if ( ! empty( $query->final_query['number'] ) ) {
					$per_page = absint( $query->final_query['number'] );
				}

				return $per_page;
			
			default:
				return apply_filters( 
					'jet-engine/query-builder/merged-query/items-per-page-for-query', 
					-1, $query, $this 
				);
		}

		return -1;
	}

	/**
	 * Set new number of items per page for given query
	 * 
	 * @param [type] $query          [description]
	 * @param [type] $items_per_page [description]
	 */
	public function set_items_per_page( $query, $items_per_page ) {

		switch ( $query->query_type ) {
			case 'posts':
				$query->set_filtered_prop( 'posts_per_page', $items_per_page );
				break;

			case 'terms':
				$query->set_filtered_prop( 'number_per_page', $items_per_page );
				break;

			case 'users':
			case 'comments':
				$query->set_filtered_prop( 'number', $items_per_page );
				break;
			
			default:
				do_action( 
					'jet-engine/query-builder/merged-query/set-items-per-page', 
					$query, $items_per_page, $this 
				);
				break;
		}
		
	}

	/**
	 * Return max number per page for merged query
	 * 
	 * @return [type] [description]
	 */
	public function max_items_per_page() {
		$items_num = isset( $this->final_query['max_items_per_page'] ) ? $this->final_query['max_items_per_page'] : 0;
		return absint( $items_num );
	}

	/**
	 * Exclude IDs depending on query type
	 * 
	 * @param  [type] $query [description]
	 * @param  [type] $ids   [description]
	 * @return [type]        [description]
	 */
	public function exclude_ids( $query, $ids ) {
		switch ( $query->query_type ) {
			case 'posts':
				$query->set_filtered_prop( 'post__not_in', $ids );
				break;

			case 'terms':
			case 'users':
				$query->set_filtered_prop( 'exclude', $ids );
				break;

			case 'comments':
				$query->set_filtered_prop( 'comment__not_in', $ids );
				break;
			
			default:
				do_action( 
					'jet-engine/query-builder/merged-query/exclude-ids', 
					$query, $ids, $this 
				);
				break;
		}
		
	}

	/**
	 * Check if duplicated excluded
	 * 
	 * @return [type] [description]
	 */
	public function exclude_duplicates() {
		return isset( $this->final_query['exclude_duplicates'] ) ? filter_var( $this->final_query['exclude_duplicates'], FILTER_VALIDATE_BOOLEAN ) : false;
	}

	/**
	 * Return stack of queries to get data from
	 * 
	 * @return [type] [description]
	 */
	public function get_queries_stack() {
		
		if ( null === $this->queries_stack ) {
		
			$this->queries_stack = [];

			if ( ! empty( $this->final_query['queries'] ) ) {

				foreach( $this->final_query['queries'] as $query_to_merge ) {

					$query_id = ! empty( $query_to_merge['query_id'] ) ? absint( $query_to_merge['query_id'] ) : false;

					if ( $query_id ) {

						$query = Manager::instance()->get_query_by_id( $query_id );
						$query->setup_query();

						if ( $query ) {
							$this->queries_stack[ $query_id ] = $query;
						}

					}

				}

			}

		}

		return $this->queries_stack;

	}

	public function get_current_items_page() {

		$queries_stack = array_values( $this->get_queries_stack() );

		if ( ! empty( $queries_stack[0] ) ) {
			$queries_stack[0]->setup_query();
			return $queries_stack[0]->get_current_items_page();
		} else {
			return 1;
		}

	}

	/**
	 * Returns total found items count
	 *
	 * @return [type] [description]
	 */
	public function get_items_total_count() {

		$cached = $this->get_cached_data( 'count' );

		if ( false !== $cached ) {
			return $cached;
		}

		$total = 0;

		$this->_get_items();
		$queries_stack = $this->get_queries_stack();

		foreach ( $queries_stack as $query ) {
			$total += $query->get_items_total_count();
		}

		$this->update_query_cache( $total, 'count' );

		return $total;

	}

	/**
	 * Returns count of the items visible per single listing grid loop/page
	 * @return [type] [description]
	 */
	public function get_items_per_page() {
		
		$items_per_page = $this->max_items_per_page();

		if ( 0 < $items_per_page ) {
			return $items_per_page;
		}

		$per_page = 0;
		
		$this->_get_items();

		$queries_stack = $this->get_queries_stack();

		foreach ( $queries_stack as $query ) {
			$per_page += $query->get_items_per_page();
		}

		return $per_page;

	}

	/**
	 * Returns queried items count per page
	 *
	 * @return [type] [description]
	 */
	public function get_items_page_count() {

		$this->_get_items();
		$queries_stack = $this->get_queries_stack();

		$page_count = 0;

		foreach ( $queries_stack as $query ) {
			$page_count += $query->get_items_page_count();
		}

		return $page_count;
	}

	/**
	 * Returns queried items pages count
	 *
	 * @return [type] [description]
	 */
	public function get_items_pages_count() {

		// return the biggest fount value from the each query
		$this->_get_items();
		$queries_stack = $this->get_queries_stack();

		$max_pages = 0;

		foreach ( $queries_stack as $query ) {
			$query_pages_count = $query->get_items_pages_count();
			if ( $max_pages < $query_pages_count ) {
				$max_pages = $query_pages_count;
			}
		}

		return $max_pages;

	}

	public function set_filtered_prop( $prop = '', $value = null ) {

		foreach ( $this->get_queries_stack() as $query ) {
			$query->setup_query();
			$query->set_filtered_prop( $prop, $value );
		}

	}

}
