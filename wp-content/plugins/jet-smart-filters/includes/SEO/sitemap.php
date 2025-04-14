<?php
/**
 * SEO Sitemap
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Smart_Filters_SEO_Sitemap' ) ) {

	/**
	 * Define Jet_Smart_Filters_SEO_Sitemap class
	 */
	class Jet_Smart_Filters_SEO_Sitemap {

		public $file_name;

		// Settings
		public $rules;

		/**
		 * Constructor for the class
		 */
		public function __construct() {

			$this->file_name = apply_filters( 'jet-smart-filters/seo-sitemap/xml-file-name', 'sitemap' );

			// Settings
			$this->rules = jet_smart_filters()->settings->get( 'seo_sitemap_rules', array() );
		}

		public function update() {

			$sitemap_settings = $this->get_seo_sitemap_settings();

			if ( ! is_array( $sitemap_settings ) ) {
				return false;
			}

			$site_url    = get_site_url();
			$currentDate = wp_date( 'Y-m-d' );
			$xml         = new DomDocument( '1.0', 'UTF-8' );

			$urlset = $xml->createElement( 'urlset' );
			$urlset->setAttribute( 'xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9' );
			$xml->appendChild( $urlset );
			
			foreach ( $sitemap_settings as $rule ) {
				if ( empty( $rule['url'] || $rule['provider'] || $rule['filters'] ) ) {
					continue;
				}

				$baseUrl = trailingslashit( strpos( $rule['url'], '/' ) !== 0
					? '/' . $rule['url']
					: $rule['url']
				);

				$provider = '?jsf=' . $rule['provider'];
				if ( $rule['query_id'] ) {
					$provider .= ':' . $rule['query_id'];
				}

				$combinations = $this->get_all_filters_combinations_by_rule( $rule );

				foreach ( $combinations as $combination ) {
					$url = $xml->createElement( 'url' );

					// url
					$locElement = $xml->createElement( 'loc', htmlspecialchars( $site_url . $baseUrl . $provider . $combination ) );
					$url->appendChild( $locElement );

					// lastmod
					$lastmodElement = $xml->createElement( 'lastmod', $currentDate );
					$url->appendChild( $lastmodElement );

					// add url element
					$urlset->appendChild( $url );
				}
			}

			$xml->formatOutput = true;
			$xml->save( $this->get_sitemap_path() );
		}

		public function get_all_filters_combinations_by_rule( $rule ) {

			$result       = array();
			$filters_data = array();

			foreach ( $rule['filters'] as $filterId ) {
				$filter_instance = jet_smart_filters()->filter_types->get_filter_instance( $filterId );

				if (
					empty( $filter_instance->args['query_type'] )
					|| empty( $filter_instance->args['query_var'] )
					|| empty( $filter_instance->args['options'] )
				) {
					continue;
				}

				$query_type     = $filter_instance->args['query_type'];
				$query_var      = $filter_instance->args['query_var'];
				$filter_options = array_filter(
					array_map( function( $key, $value ) {
						if ( is_a( $value, 'WP_Term' ) ) {
							return $value->term_id;
						} else if ( isset( $value['value'] ) ) {
							return $value['value'];
						}
						return $key;
					}, array_keys( $filter_instance->args['options'] ), $filter_instance->args['options'] ),
					function ( $value ) {
						return ! empty( $value );
					}
				);

				array_push( $filters_data, array(
					'type'       => $query_type,
					'var'        => $query_var,
					'options'    => $filter_options,
					'var_suffix' => ! empty( $filter_instance->args['query_var_suffix'] )
						? $filter_instance->args['query_var_suffix']
						: false
				) );
			}

			for ( $filterIndex = 0; $filterIndex < count( $filters_data ); $filterIndex++ ) {
				$currentType      = $filters_data[$filterIndex]['type'];
				$currentVar       = $filters_data[$filterIndex]['var'];
				$currentVarSuffix = $filters_data[$filterIndex]['var_suffix'];
				$currentOptions   = array_values( $filters_data[$filterIndex]['options'] );

				if ( $currentVarSuffix ) {
					$currentVar .= '!' . $currentVarSuffix;
				}

				for ( $optionIndex = 0; $optionIndex < count( $currentOptions ); $optionIndex++ ) {
					$currentOption = rawurlencode( $currentOptions[$optionIndex] );

					$combinationData = array(
						$currentType => array (
							$currentVar => array( $currentOption )
						)
					);

					array_push( $result, $combinationData );

					for ($nextfilterIndex = $filterIndex + 1; $nextfilterIndex < count( $currentOptions ); $nextfilterIndex++) { 
						if ( isset( $filters_data[$nextfilterIndex] ) ) {
							$this->get_nested_combinations( $filters_data, $result, $nextfilterIndex, $combinationData );
						}
					}
				}
			}

			$output = array();
			$result = array_slice( $result, 0, apply_filters( 'jet-smart-filters/seo-sitemap/rule-combination-quantity', 1000 ) );

			foreach ( $result as $combinationData ) {
				$combination = '';

				foreach ( $combinationData as $type => $typeData ) {
					switch ( $type ) {
						case 'tax_query':
							$type = 'tax';
							break;
						
						case 'meta_query':
							$type = 'meta';
							break;
					}
					
					$combination .= '&' . $type . '=';

					foreach ( $typeData as $var => $varData ) {
						$combination .= $var . ':' . implode( '%2C', $varData ) . ';';
					}

					$combination = trim( $combination, ';' );
				}

				array_push( $output, $combination );
			}

			return $output;
		}

		private function get_nested_combinations( $data, &$result, $currentIndex = 0, $currentCombinationData = false ) {
			$currentType      = $data[$currentIndex]['type'];
			$currentVar       = $data[$currentIndex]['var'];
			$currentVarSuffix = $data[$currentIndex]['var_suffix'];
			$currentOptions   = array_values( $data[$currentIndex]['options'] );

			if ( $currentVarSuffix ) {
				$currentVar .= '!' . $currentVarSuffix;
			}

			for ( $optionIndex = 0; $optionIndex < count( $currentOptions ); $optionIndex++ ) {
				$currentOption   = rawurlencode( $currentOptions[$optionIndex] );
				$combinationData = $currentCombinationData
					? $currentCombinationData
					: array();

				if ( isset( $combinationData[$currentType] ) ) {
					if ( isset( $combinationData[$currentType][$currentVar] ) ) {
						array_push( $combinationData[$currentType][$currentVar], $currentOption );
					} else {
						$combinationData[$currentType][$currentVar] = array( $currentOption );
					}
				} else {
					$combinationData[$currentType] = array (
						$currentVar => array( $currentOption )
					);
				}

				array_push( $result, $combinationData );

				if ( ! empty( $data[$currentIndex + 1] ) ) {
					$this->get_nested_combinations( $data, $result, $currentIndex + 1, $combinationData );
				}
			}
		}

		public function get_seo_sitemap_settings() {

			return jet_smart_filters()->settings->get( 'seo_sitemap_rules' );
		}

		public function get_filters_options() {

			global $wpdb;

			$sql = "
			SELECT ID as value, post_title as label, type.meta_value as type FROM $wpdb->posts as posts
			LEFT JOIN $wpdb->postmeta as type ON (posts.ID = type.post_ID AND type.meta_key = '_filter_type')
			LEFT JOIN $wpdb->postmeta as is_hierarchical ON (posts.ID = is_hierarchical.post_ID AND is_hierarchical.meta_key = '_is_hierarchical')
			LEFT JOIN $wpdb->postmeta as data_source ON (posts.ID = data_source.post_ID AND data_source.meta_key = '_data_source')
			WHERE posts.post_type = 'jet-smart-filters'
				AND posts.post_status = 'publish'
				AND type.meta_value IN ('checkboxes', 'select', 'radio', 'color-image')
				AND (is_hierarchical.meta_value IS NULL OR is_hierarchical.meta_value = FALSE)
				AND (data_source.meta_value != 'query_builder_switcher')";
			$filters_result = $wpdb->get_results( $sql, ARRAY_A );

			return apply_filters( 'jet-smart-filters/seo-sitemap/filters-options', $filters_result );
		}

		public function get_sitemap_path() {

			$xml_dir_path = jet_smart_filters()->get_upload_dir_path( 'xml' );

			return $xml_dir_path . $this->file_name . '.xml';
		}

		public function get_sitemap_url() {

			$xml_dir_url = jet_smart_filters()->get_upload_dir_url( 'xml' );
			$xml_url     = $xml_dir_url . $this->file_name . '.xml';

			return $xml_url;
		}

		public function process_settings( $settings ) {

			$new_use_seo = $settings['use_seo_sitemap'];
			$old_use_seo = jet_smart_filters()->settings->get( 'use_seo_sitemap', '' );

			if ( $new_use_seo == $old_use_seo ) {
				return;
			}

			if ( filter_var( $new_use_seo, FILTER_VALIDATE_BOOLEAN ) ) {
				$this->update();
			} else {
				$sitemap_xml_path = $this->get_sitemap_path();

				if ( file_exists( $sitemap_xml_path ) ) {
					wp_delete_file( $sitemap_xml_path );
				}
			}
		}
	}
}
