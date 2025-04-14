<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die();
}

/**
 * JetSmartFilters apply tax auery query var
 */
class Jet_Smart_Filters_Tax_Query_Var {

	public $prefix = '_tax_query::';
	public $type   = 'tax_query';

	public function __construct() {

		add_filter( 'jet-smart-filters/filter-instance/args', array( $this, 'filter_instance_replace_var' ) );
		add_filter( 'jet-smart-filters/indexer/indexing-filter-data', array( $this, 'indexing_filter_data_replace_var' ) );
		add_filter( 'jet-smart-filters/indexer/filter-source', array( $this, 'indexer_filter_source_replace_var' ) );
	}

	public function filter_instance_replace_var( $filter_args ) {

		if (
			empty( $filter_args['query_var'] ) || strpos( $filter_args['query_var'], $this->prefix ) === false
			||
			( isset( $filter_args['_data_source'] ) && $filter_args['_data_source'] === 'taxonomies' )
		) {
			return $filter_args;
		}

		$filter_args['query_type'] = $this->type;
		$filter_args['query_var']  = str_replace( $this->prefix, '', $filter_args['query_var'] );

		return $filter_args;
	}

	public function indexing_filter_data_replace_var( $filter_data ) {

		if (
			empty( $filter_data['_query_var'] ) || strpos( $filter_data['_query_var'], $this->prefix ) === false
			||
			( isset( $filter_data['_data_source'] ) && $filter_data['_data_source'] === 'taxonomies' )
		) {
			return $filter_data;
		}

		$filter_data['_data_source']     = 'taxonomies';
		$filter_data['_source_taxonomy'] = str_replace( $this->prefix, '', $filter_data['_query_var'] );

		return $filter_data;
	}

	public function indexer_filter_source_replace_var( $filter_source ) {

		if (
			empty( $filter_source['_query_var'][0] ) || strpos( $filter_source['_query_var'][0], $this->prefix ) === false
			||
			( isset( $filter_source['_data_source'][0] ) && $filter_source['_data_source'][0] === 'taxonomies' )
		) {
			return $filter_source;
		}

		$filter_source['_data_source'][0]     = 'taxonomies';
		$filter_source['_source_taxonomy'][0] = str_replace( $this->prefix, '', $filter_source['_query_var'][0] );

		return $filter_source;
	}
}
