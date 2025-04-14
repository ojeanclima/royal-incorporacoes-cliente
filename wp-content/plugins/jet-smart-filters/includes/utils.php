<?php
/**
 * Utils class
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Smart_Filters_Utils' ) ) {
	/**
	 * Define Jet_Smart_Filters_Utils class
	 */
	class Jet_Smart_Filters_Utils {
		/**
		 * Сhecks if the filter exists and that it is published
		 */
		public function is_filter_published( $filter_id ) {

			if ( empty( $filter_id ) ) {
				return false;
			}

			global $wpdb;

			$filter_id = intval( $filter_id );

			$query = $wpdb->prepare(
				"SELECT ID FROM {$wpdb->posts} 
				WHERE post_type = 'jet-smart-filters'
				AND post_status = 'publish'
				AND ID = %d",
				$filter_id
			);

			$published_filter_id = $wpdb->get_var( $query );

			return $published_filter_id ? true : false;
		}

		/**
		 * Returns only published filters from the passed IDs.
		 */
		public function select_published_filters( $filter_ids ) {

			if ( ! is_array( $filter_ids ) || empty( $filter_ids ) ) {
				return array();
			}

			global $wpdb;

			$filter_ids_string = implode( ',', array_map( 'intval', $filter_ids ) );
		
			$query = "SELECT ID FROM {$wpdb->posts}
					  WHERE post_type = 'jet-smart-filters'
					  AND post_status = 'publish'
					  AND ID IN ($filter_ids_string)";
		
			$published_filters = $wpdb->get_col( $query );
		
			return $published_filters;
		}

		/**
		 * Returns HTML as string from file
		 */
		public function get_file_html( $path ) {

			ob_start();
			include jet_smart_filters()->plugin_path( $path );
			return ob_get_clean();
		}

		/**
		 * Returns template content as string
		 */
		public function get_template_html( $template ) {

			ob_start();
			include jet_smart_filters()->get_template( $template );
			$html = ob_get_clean();

			return preg_replace('~>\\s+<~m', '><', $html);
		}

		/**
		 * Returns parsed template
		 */
		public function template_parse( $template ) {

			$html_template = $this->get_template_html( $template );

			preg_match_all( '/\/%(.+?)%\//', $html_template, $matches, PREG_SET_ORDER );
			foreach ( $matches as $item ) {
				$prefix = ! preg_match( '/(if|for|else|{|})/', $item[0] ) ? 'echo ' : '';
				$html_template = str_replace( $item[0], '<?php ' . $prefix . trim( $item[1] ) . ' ?>', $html_template );
			}

			return $html_template;
		}

		/**
		 * Returns parsed template
		 */
		public function template_replace_with_value( $template, $value ) {

			$html_template = $this->get_template_html( $template );

			return preg_replace('/\/\s*%\s*\$value\s*%\s*\//', $value, $html_template );
		}

		/**
		 * Creates an associative array with value and label from input data of any type( $data = String / Array )
		 */
		public function сreate_option_data( $key, $data ) {

			$option = array(
				'value' => $key,
				'label' => $data
			);

			if ( is_array( $data ) ) {
				$option['label'] = $key;

				// merge option properties
				$option = array_merge( $option, $data );
			}

			return $option;
		}

		/**
		 * Generates HTML date attributes from array
		 */
		public function generate_data_attrs( $data ) {

			if ( ! is_array( $data ) ) {
				return '';
			}

			$data_attrs = '';

			foreach ( $data as $key => $value ) {
				$data_attrs .= 'data-' . $key . '="' . htmlspecialchars( $value, ENT_QUOTES ) . '" ';
			}

			return trim( $data_attrs );
		}

		/**
		 * Returns image size array in slug => name format
		 */
		public function get_image_sizes() {

			global $_wp_additional_image_sizes;

			$sizes  = get_intermediate_image_sizes();
			$result = array();

			foreach ( $sizes as $size ) {
				if ( in_array( $size, array( 'thumbnail', 'medium', 'medium_large', 'large' ) ) ) {
					$result[ $size ] = ucwords( trim( str_replace( array( '-', '_' ), array( ' ', ' ' ), $size ) ) );
				} else {
					$result[ $size ] = sprintf(
						'%1$s (%2$sx%3$s)',
						ucwords( trim( str_replace( array( '-', '_' ), array( ' ', ' ' ), $size ) ) ),
						$_wp_additional_image_sizes[ $size ]['width'],
						$_wp_additional_image_sizes[ $size ]['height']
					);
				}
			}

			return array_merge( array( 'full' => esc_html__( 'Full', 'jet-woo-builder' ), ), $result );
		}

		/**
		 * Returns additional providers
		 */
		public function get_additional_providers( $settings ) {

			if ( empty( $settings['additional_providers_enabled'] ) ) {
				return '';
			}

			if ( ! empty( $settings['additional_providers_list'] ) ) {
				$additional_providers = $settings['additional_providers_list'];
			} else if ( ! empty( $settings['additional_providers'] ) ) {
				// backward compatibility
				$additional_providers = array_map( function ( $additional_provider ) {
					return array( 'additional_provider' => $additional_provider );
				}, $settings['additional_providers'] );
			} else {
				return '';
			}

			$output_data      = [];
			$default_query_id = ! empty( $settings['query_id'] ) ? $settings['query_id'] : 'default';

			foreach ( $additional_providers as $additional_provider ) {
				$provider = ! empty( $additional_provider['additional_provider'] ) ? $additional_provider['additional_provider'] : false;
				$query_id = ! empty( $additional_provider['additional_query_id'] ) ? $additional_provider['additional_query_id'] : $default_query_id;

				if ( $provider ) {
					$output_data[] = $provider . ( $query_id ? '/' . $query_id : '' );
				}
			}

			return $output_data ? htmlspecialchars( json_encode( $output_data ) ) : '';
		}

		/**
		 * Merge Query Args
		 */
		public function merge_query_args( $current_query_args, $new_query_args ) {

			$merged_keys = array( 'tax_query', 'meta_query', 'post__not_in' );
			$merged_keys = apply_filters( 'jet-smart-filter/utils/merge-query-args/merged-keys', $merged_keys );

			foreach ( $new_query_args as $key => $value ) {
				if ( in_array( $key, $merged_keys ) && ! empty( $current_query_args[$key] ) ) {
					$value = array_merge( $current_query_args[$key], $value );

					if ( 'post__not_in' === $key ) {
						$value = array_unique( $value );
					}
				}

				if ( 'post__in' === $key ) {
					if ( ! is_array( $value ) ) {
						$value = ! empty( $value ) ? array( $value ) : array();
					}

					if ( ! empty( $current_query_args[ $key ] ) ) {
						$value = array_intersect( $current_query_args[ $key ], $value );

						if ( empty( $value ) ) {
							$value = array( PHP_INT_MAX );
						}
					}
				}

				$current_query_args[$key] = $value;
			}

			return $current_query_args;
		}

		/**
		 * Insert in array after key
		 */
		public function array_insert_after( $source = array(), $after = null, $insert = array() ) {

			$index  = array_search( $after, array_keys( $source ) );

			if ( false === $index ) {
				return $source + $insert;
			}

			$offset = $index + 1;

			return array_slice( $source, 0, $offset, true ) + $insert + array_slice( $source, $offset, null, true );
		}

		/**
		 * Returns URL with filters applied
		 */
		public function get_filtered_url( $base_url = false, $query_id = null, $provider = '', $args = array() ) {

			$query_args = array();

			foreach ( $args as $arg ) {
				$value     = $arg['value'];
				$query_var = $arg['query_var'];

				switch ( $arg['query_type'] ) {
					case 'tax_query':
						$query_type = 'tax';

						break;

					case 'meta_query':
						$query_type = 'meta';

						break;

					case 'date_query':
						$query_type = 'date';
						$query_var  = false;
						$value      = str_replace( '/', '-', $value );

						break;

					case 'sort':
						$query_var = false;
						break;

					case '_s':
						$query_var = false;
						break;

					default:
						$query_type = $arg['query_type'];
						break;
				}

				switch ( $arg['filter_type'] ) {
					case 'range':
					case 'check-range':
						$query_var .= '!' . $arg['filter_type'];

						break;

					case 'date-range':
					case 'date-period':
						if ( 'meta' === $query_type ) {
							$query_var .= '!date';
						}

						break;

					case 'pagination':
						$query_type = 'pagenum';

						break;

					case 'search':
						if ( 'meta' === $query_type ) {
							$query_type = '_s';
							$value     .= '!meta=' . $query_var;
							$query_var  = false;
						}

						break;

					default:
						if ( ! empty( $arg['suffix'] ) )
							$query_var .= '!' . $arg['suffix'];

						break;
				}

				if ( $query_var ) {
					$value = $query_var . ':' . $value;
				}

				if ( ! isset( $query_args[ $query_type ] ) ) {
					$query_args[ $query_type ] = $value;
				} else {
					if ( ! is_array( $query_args[ $query_type ] ) ) {
						$query_args[ $query_type ] = array( $query_args[ $query_type ] );
					}

					$query_args[ $query_type ][] = $value;
				}
			}

			/**
			 * @todo Merge smae keys and process hierarchy
			 */
			$url_type = jet_smart_filters()->settings->url_structure_type;

			if ( ! $base_url ) {
				$base_url = $_SERVER['REQUEST_URI'];
			}

			$name_parts = array( $provider );

			if ( $query_id ) {
				$name_parts[] = $query_id;
			}

			$provider_name = implode( ':', $name_parts );
			$result        = trailingslashit( $base_url );

			switch ( $url_type ) {
				case 'permalink':
					$result .= 'jsf/' . $provider_name . '/';

					if ( isset( $query_args['_s'] ) ) {
						$query_args['search'] = $query_args['_s'];
						unset( $query_args['_s'] );
					}

					foreach ( $query_args as $key => $value ) {
						$result .= urlencode( $key ) . '/' . urlencode( $value );
					}

					break;

				default:
					foreach ( $query_args as $key => $data ) {
						if ( is_array( $data ) ) {
							$query_args[ $key ] = implode( ';', $data );
						}
					}

					$query_args = array_merge( array( 'jsf' => $provider_name ), $query_args );
					$result     = add_query_arg( $query_args, $result );

					break;
			}

			return jet_smart_filters()->URL_aliases->use_url_aliases
				? jet_smart_filters()->URL_aliases->apply_aliases_to_url( $result )
				: $result;
		}

		/**
		 * Adds a condition for the control
		 */
		public function add_control_condition( $settings_list, $control_key, $condition_key, $condition_value ) {

			if ( ! isset( $settings_list[$control_key] ) ) {
				return $settings_list;
			}

			if ( ! isset( $settings_list[$control_key]['conditions'] ) ) {
				$settings_list[$control_key]['conditions'] = array();
			}

			if ( ! isset( $settings_list[$control_key]['conditions'][$condition_key] ) ) {
				$settings_list[$control_key]['conditions'][$condition_key] = $condition_value;
			} else {
				if ( ! is_array( $settings_list[$control_key]['conditions'][$condition_key] ) ) {
					$current_condition_value = $settings_list[$control_key]['conditions'][$condition_key];

					$settings_list[$control_key]['conditions'][$condition_key] = array( $current_condition_value );
				}
				
				array_push( $settings_list[$control_key]['conditions'][$condition_key], $condition_value );
			}

			return $settings_list;
		}

		/**
		 * Returns file content
		 */
		public function get_file_content( $file_path ) {

			if ( ! file_exists( $file_path ) ) {
				return false;
			}

			ob_start();
			include $file_path;

			return ob_get_clean();
		}

		/**
		 * Convert hex color to rgba
		 */
		public function hex2rgba( $color, $opacity = false ) {
 
			$default = 'rgb(0,0,0)';
		 
			//Return default if no color provided
			if ( empty( $color ) ) {
				return $default;
			}
		 
			//Sanitize $color if "#" is provided 
			if ( $color[0] == '#' ) {
				$color = substr( $color, 1 );
			}
		
			//Check if color has 6 or 3 characters and get values
			if ( strlen( $color ) == 6 ) {
				$hex = array( $color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5] );
			} elseif ( strlen( $color ) == 3 ) {
				$hex = array( $color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2] );
			} else {
				return $default;
			}
		
			//Convert hexadec to rgb
			$rgb = array_map( 'hexdec', $hex );
		
			//Check if opacity is set(rgba or rgb)
			if ( $opacity ) {
				if ( abs( $opacity ) > 1 ) {
					$opacity = 1;
				}
				$output = 'rgba(' . implode( ",", $rgb ). ',' . $opacity . ')';
			} else {
				$output = 'rgb(' . implode( ",", $rgb ) . ')';
			}
		
			//Return rgb(a) color string
			return $output;
		}

		/**
		 * Checks if the current request is a WP REST API request
		 */
		public function is_rest_request() {

			$rest_url    = wp_parse_url( trailingslashit( rest_url() ) );
			$current_url = wp_parse_url( add_query_arg( array() ) );
			
			return strpos( $current_url['path'] ?? '/', $rest_url['path'], 0 ) === 0;
		}

		/**
		 * Recursive stripslashes
		 */
		public function stripslashes( $value ) {
			$value = is_array($value) ?
						array_map( array( $this, 'stripslashes' ), $value ) :
						stripslashes($value);

			return $value;
		}

		/**
		 * Сonvert array with term slugs to ids
		 */
		public function convert_term_slugs_to_ids( $array ) {

			global $wpdb;
		
			// collect all slugs into one array
			$slugs         = [];
			$flatten_array = function( $value ) use ( &$slugs ) {
				if ( is_string( $value ) ) {
					$slugs[] = $value;
				}
			};

			array_walk_recursive( $array, $flatten_array );
		
			// if the array with slugs is empty
			if ( empty( $slugs ) ) {
				return $array;
			}

			// get all term IDs by slugs
			$placeholders = implode( ',', array_fill( 0, count( $slugs ), '%s' ) );
			$query        = $wpdb->prepare(
				"SELECT t.term_id, t.slug FROM {$wpdb->terms} t
				WHERE t.slug IN ($placeholders)",
				...$slugs
			);

			$terms = $wpdb->get_results( $query );
	
			// convert the result to the format [slug => term_id]
			$term_map = [];

			foreach ( $terms as $term ) {
				$term_map[$term->slug] = $term->term_id;
			}
	
			// recursively replace slugs with term_id in the original array
			array_walk_recursive( $array, function( &$value ) use ( $term_map ) {
				if (is_string( $value ) && isset( $term_map[$value] ) ) {
					$value = $term_map[$value];
				}
			});
		
			return $array;
		}

		/**
		 * Function to merge arrays by the key of the first array and the value of the second
		 */
		function mergeArraysByKeyAndValue( $firstArray, $secondArray, $valueKey = 'value' ) {

			$resultArray = [];
		
			foreach ( $firstArray as $key => $item ) {
				foreach ( $secondArray as $secondItem ) {
					if ( ! isset( $secondItem[$valueKey] ) || $secondItem[$valueKey] != $key ) {
						continue;
					}

					foreach ( $secondItem as $secondKey => $secondValue ) {
						if ( ! array_key_exists( $secondKey, $item ) || ( ! empty( $secondValue ) && empty( $item[$secondKey] ) ) ) {
							$item[$secondKey] = $secondValue;
						}
					}

					$resultArray[] = $item;

					break;
				}
			}
		
			return $resultArray;
		}
	}
}
