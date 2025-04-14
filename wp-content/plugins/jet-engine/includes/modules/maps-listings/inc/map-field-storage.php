<?php
namespace Jet_Engine\Modules\Maps_Listings;

use \Jet_Engine\CPT\Custom_Tables\Manager as Custom_Tables;

class Map_Field_Storage {

	public $map_field;
	public $fields = [];
	public $regular_fields = [];
	public $group_fields = [];
	public $preload = [];
	public $notices = [];

	/**
	 * Constructor for the class
	 * 
	 * @param Map_Field $map_field Map_Field instance
	 */
	public function __construct( $map_field ) {

		$this->map_field = $map_field;

		add_filter( 'jet-engine/custom-meta-tables/storage-data', [ $this, 'register_storage_fields' ], 10, 2 );
		add_filter( 'jet-engine/maps-listings/map-field-prefix', [ $this, 'ensure_fields_prefix' ], 10, 2 );

		add_filter(
			'jet-engine/maps-listings/geosearch/posts/get-geo-query',
			[ $this, 'prevent_default_geo_query' ],
			10, 2
		);

		add_filter(
			'jet-engine/maps-listings/geosearch/posts-custom-storage/get-geo-query',
			[ $this, 'ensure_custom_storage_geo_query' ],
			10, 2
		);

		add_action( 'jet-engine/custom-meta-tables/meta-storage/on-init', [ $this, 'on_meta_storage_init' ] );

		add_filter( 'jet-engine/maps-listing/settings/js', [ $this, 'add_js_data' ] );
		add_filter( 'jet-engine/maps-listing/settings/save-response/additional-data', [ $this, 'add_response_data' ], 10, 2 );

	}

	/**
	 * Add warning text to response after saving Map Listings settings
	 * 
	 * @param  string $data     Additional response data
	 * @param  string $settings Map Listing module JS Settings
	 * @return array            Additional response data
	 */
	public function add_response_data( $data, $settings ) {

		if ( empty( $settings['enable_preload_meta'] ) ) {
			return $data;
		}

		$data['preloadWarnings'] = $this->get_warning_text();

		return $data;
	}

	/**
	 * Add warning text to Map Listing dashboard JS settings
	 * 
	 * @param  string $settings Map Listing module JS Settings            
	 * @return array            Map Listing module JS Settings
	 */
	public function add_js_data( $settings ) {
		$preload = Module::instance()->settings->get( 'enable_preload_meta' );

		if ( ! $preload ) {
			return $settings;
		}

		$settings['preloadWarnings'] = $this->get_warning_text();

		return $settings;
	}

	/**
	 * Get warning text if there are CPTs with custom storage enabled
	 *             
	 * @return string Warning text; empty string if no post types with custom storage enabled
	 */
	public function get_warning_text() {
		$custom_storage_types = array();

		foreach ( jet_engine()->cpt->data->get_items() as $cpt ) {
			$args = maybe_unserialize( $cpt['args'] );

			if ( $cpt['status'] === 'publish' && is_array( $args ) && ! empty( $args['custom_storage'] ) ) {
				$custom_storage_types[ $cpt['id'] ] = $cpt['slug'];
			}
		}

		if ( empty( $custom_storage_types ) ) {
			return '';
		}

		$links = array();

		foreach ( $custom_storage_types as $id => $type ) {
			$links[] = sprintf(
				'<a href="%s" target="_blank">%s</a>',
				admin_url( 'admin.php?page=jet-engine-cpt&cpt_action=edit&id=' . $id ),
				$type
			);
		}

		$result  = esc_html__( 'The following post types have custom storage enabled: ', 'jet-engine' );
		$result .= '<br>';
		$result .= implode(', ', $links );
		$result .= '<br>';
		$result .= esc_html__( 'They should be resaved after saving preset settings if you are using preload for them. Otherwise, DB schema will remain unchanged and no data will be stored for preloaded fields.', 'jet-engine' );
		$result .= '<br>';
		$result .= esc_html__( 'The combined length of preload group (including \'+\' sign) should not exceed 59 characters.', 'jet-engine' );
		$result .= '<br><br>';
		$result .= esc_html__( 'Note, that if you delete those post type fields from preload, or modify/delete their field groups, or disable Map Listing module - data from those fields will be lost on post type resave as DB schema will be changed.', 'jet-engine' );
		$result .= '<br><br>';

		return $result;
	}

	/**
	 * Add hooks on Meta Storage init
	 *
	 * @param  \Jet_Engine\CPT\Custom_Tables\Meta_Storage $storage Meta_Storage instance
	 *             
	 * @return void
	 */
	public function on_meta_storage_init( $storage ) {

		// replace '+' in preload field group with '_' to ensure data is read/written from/to custom storage
		add_filter(
			'jet-engine/maps-listing/preload/field-name',
			function( $field_name, $object ) use ( $storage ) {
				$object_id = jet_engine()->listings->data->get_current_object_id( $object );

				if ( $storage->is_custom_field_from_storage( $storage->object_type, $object_id, str_replace( '+', '_', $field_name ) ) ) {
					$field_name = str_replace( '+', '_', $field_name );
				}
		
				return $field_name;
			},
			10,
			2
		);

		$storage_fields = $storage->fields;

		// update group address columns with a full address
		add_action(
			'jet-engine/maps-listings/preload/after-group-preload',
			function( $post_id ) use ( $storage_fields ) {
				if ( empty( $this->group_fields ) ) {
					return;
				}
				
				foreach ( $this->group_fields as $field_name => $meta_fields ) {
					$group = [];

					foreach ( $meta_fields as $field ) {
						if ( false === array_search( $field, $storage_fields ) ) {
							continue 2;
						}
		
						$group[] = get_post_meta( $post_id, $field, true );
					}
			
					$group = array_filter( $group );
		
					update_post_meta( $post_id, $field_name, implode( ', ', $group ) );
				}
			}
		);

		$object_slug = $storage->object_slug;

		add_filter(
			'jet-engine/custom-meta-tables/prepared_fields',
			function( $fields ) use ( $object_slug ) {
				return $this->add_service_columns( $fields, $object_slug );
			}
		);
	}

	/**
	 * Add service map field columns
	 *
	 * @param  array                                     $fields  Array of fields
	 * @param \Jet_Engine\CPT\Custom_Tables\Meta_Storage $storage Meta_Storage instance
	 * 
	 * @return array $fields Modified array of fields
	 */
	public function add_service_columns( $fields, $object_slug ) {
		
		if ( empty( $fields['raw'] ) || empty( $fields['as_columns'] ) ) {
			return $fields;
		}

		$map_fields = array_column(
			array_filter(
				$fields['raw'],
				function( $field ) {
					return ! empty( $field['type'] ) && $field['type'] === 'map';
				}
			),
			'name'
		);

		foreach ( $map_fields as $field_name ) {
			$service_fields = $this->get_service_columns_array( $field_name );

			foreach ( $service_fields as $s_field ) {
				$fields['as_columns'][] = $s_field;
			}
		}

		$fields = $this->add_preload_service_columns( $fields, $object_slug );
		
		return $fields;
	}

	/**
	 * Add service map field columns from preload settings
	 *
	 * @param  array                                     $fields  Array of fields
	 * @param \Jet_Engine\CPT\Custom_Tables\Meta_Storage $storage Meta_Storage instance
	 * @return array $fields Modified array of fields
	 */
	public function add_preload_service_columns( $fields, $object_slug ) {
		$preload = Module::instance()->settings->get( 'enable_preload_meta' );

		if ( ! $preload ) {
			return $fields;
		}

		$preload_fields = Module::instance()->settings->get( 'preload_meta' );

		if ( empty( $preload_fields ) ) {
			return $fields;
		}

		$preload_fields = explode( ',', $preload_fields );
		$preload_fields = array_map( 'trim', $preload_fields );
		$preload_fields = array_filter(
			$preload_fields,
			function ( $group ) {
				return false !== strpos( $group, '+' );
			}
		);

		if ( empty( $preload_fields ) ) {
			return $fields;
		}

		foreach ( $preload_fields as $field ) {
			preg_match( '/(?:[^:]+\+)+(?:.+)/', $field, $matches );

			if ( ! empty( $matches ) ) {
				$group = $matches[0];
				$meta_fields = explode( '+', $group );
				foreach ( $meta_fields as $meta_field ) {
					if ( false === array_search( $meta_field, $fields['as_columns'] ) ) {
						continue 2;
					}
				}

				$group = str_replace( '+', '_', $group );
				
				// if ( ! $this->check_db_name( $group, $storage ) ) {
				// 	$this->add_notice( $field, $storage );
				// 	continue;
				// }

				$fields['as_columns'][] = $group;
				$this->fields[ $group ] = array(
					'name'        => $group,
					'object_slug' => $object_slug,
				);
				$this->group_fields[ $group ] = $meta_fields;

				$service_fields = $this->get_service_columns_array( $group );

				foreach ( $service_fields as $s_field ) {
					$fields['as_columns'][] = $s_field;
				}
			}
		}

		return $fields;
	}

	/**
	 * Check if DB column name is valid
	 *
	 * @param  string                                    $column  Column name
	 * @param \Jet_Engine\CPT\Custom_Tables\Meta_Storage $storage Meta_Storage instance
	 * @return bool Is valid DB column name?
	 */
	public function check_db_name( $column, $storage ) {
		$max_column  = $column . '_hash';
		$valid       = apply_filters( 'jet-engine/maps-listing/custom-storage/volumn-valid', strlen( $max_column ) <= 64, $column, $storage );
		return $valid;
	}

	/**
	 * Add notice on post type update
	 *
	 * @param  string                                    $group   Preload fields group
	 * @param \Jet_Engine\CPT\Custom_Tables\Meta_Storage $storage Meta_Storage instance
	 * @return bool Is valid DB column name?
	 */
	public function add_notice( $group, $storage ) {
		$object_type = $storage->object_type;

		if ( isset( $this->notices[ $object_type . $group ] ) ) {
			return;
		}

		$this->notices[ $object_type . $group ] = true;

		$group = str_replace( '+', '+&ZeroWidthSpace;', $group );

		switch ( $object_type ) {
			case 'post':
				jet_engine()->cpt->add_notice(
					'critical_error',
					"Cannot add columns for preload group: {$group}. Group length should not exceed 59 characters, not counting the group prefix. Please, go to Map Listing module preload settings and correct the preload group."
				);
				break;
		}
	}

	/**
	 * Get service column names array for the given field
	 *
	 * @param  string $name Meta field name              
	 * @return array        Array of service column names
	 */
	public function get_service_columns_array( $name ) {
		$suffixes = array(
			'_hash',
			'_lat',
			'_lng',
		);

		return array_map(
			function( $suffix ) use ( $name ) {
				return $name . $suffix;
			},
			$suffixes
		);
	}

	/**
	 * Prevent processing custom storage geo query as default
	 *
	 * @param  array            $geo_query Geo query params
	 * @param  \WP_Query        $query     WP_Query instance
	 * @return array|false                 Array of geo query params; false if geo query should not be modified
	 */
	public function prevent_default_geo_query( $geo_query, $query ) {

		if ( empty( $geo_query ) ) {
			return $geo_query;
		}

		if ( $this->is_custom_storage_geo_query( $geo_query, $query ) ) {
			$geo_query = false;
		}

		return $geo_query;
	}

	/**
	 * Allow to process custom storage geo queries by Posts_Custom_Storage class
	 *
	 * @param  array      $geo_query Geo query params
	 * @param  \WP_Query  $query     WP_Query instance
	 * @return array                 Geo query params
	 */
	public function ensure_custom_storage_geo_query( $geo_query, $query ) {

		$raw_geo_query = $query->get( 'geo_query' );

		if ( empty( $raw_geo_query ) ) {
			return $geo_query;
		}

		$raw_geo_query = $this->is_custom_storage_geo_query( $raw_geo_query, $query );

		if ( $raw_geo_query ) {
			$geo_query = $raw_geo_query;
		}

		return $geo_query;
	}

	public function check_raw_field( $geo_query, $post_type ) {
		if ( false !== strpos( $geo_query['raw_field'], '+' ) ) {
			$group = explode( '+', $geo_query['raw_field'] );

			foreach ( $group as $field ) {
				$geo_query['raw_field'] = $field;

				if ( ! isset( $this->regular_fields[ $field ] ) || ! in_array( $this->regular_fields[ $field ]['object_slug'] ?? '', $post_type ) ) {
					return false;
				}
			}

			return true;
		}

		return ! empty( $geo_query['raw_field'] )
		&& isset( $this->fields[ $geo_query['raw_field'] ] )
		&& in_array( $this->fields[ $geo_query['raw_field'] ]['object_slug'], $post_type );
	}

	public function check_separate_fields( $geo_query, $post_type ) {
		return ! empty( $geo_query['lat_field'] )
		&& ! empty( $geo_query['lng_field'] )
		&& isset( $this->regular_fields[ $geo_query['lat_field'] ] )
		&& isset( $this->regular_fields[ $geo_query['lng_field'] ] )
		&& in_array( $this->regular_fields[ $geo_query['lat_field'] ]['object_slug'], $post_type )
		&& in_array( $this->regular_fields[ $geo_query['lng_field'] ]['object_slug'], $post_type )
		&& $this->regular_fields[ $geo_query['lat_field'] ]['object_slug'] === $this->regular_fields[ $geo_query['lng_field'] ]['object_slug'];
	}

	/**
	 * Check if custom storage geo query is currently processed
	 *
	 * @param  array            $geo_query Geo query params
	 * @param  \WP_Query        $query     WP_Query instance
	 * @return array|false                 Array of geo query params; false if geo query should not be modified
	 */
	public function is_custom_storage_geo_query( $geo_query, $query ) {

		$post_type = $query->get( 'post_type' );

		if ( $post_type && ! is_array( $post_type ) ) {
			$post_type = [ $post_type ];
		}

		// Check by raw field
		if ( $this->check_raw_field( $geo_query, $post_type ) ) {
			global $wpdb;

			if ( false !== strpos( $geo_query['raw_field'], '+' ) ) {
				$field = str_replace( '+', '_', $geo_query['raw_field'] );

				$geo_query['lat_field'] = $field . '_lat';
				$geo_query['lng_field'] = $field . '_lng';

				$table_field = explode( '+', $geo_query['raw_field'] )[0] ?? '';

				$geo_query['geo_query_table'] = $wpdb->prefix . Custom_Tables::instance()->get_table_name(
					$this->regular_fields[ $table_field ]['object_slug'] ?? ''
				);
			} else {
				$geo_query['geo_query_table'] = $wpdb->prefix . Custom_Tables::instance()->get_table_name(
					$this->fields[ $geo_query['raw_field'] ]['object_slug'] ?? ''
				);
			}
			
			return $geo_query;
		}

		// Check by separate lat/lng fields
		if ( $this->check_separate_fields( $geo_query, $post_type ) ) {
			global $wpdb;

			$geo_query['geo_query_table'] = $wpdb->prefix . Custom_Tables::instance()->get_table_name(
				$this->regular_fields[ $geo_query['lat_field'] ]['object_slug']
			);

			return $geo_query;
		}
		return false;
	}

	/**
	 * Change prefix for map fields which stored in custom meta storage
	 *
	 * @param  string $prefix Field prefix
	 * @param  string $field  Field name
	 * @return string         Field prefix; field name otherwise field is in custom storage, md5 field name hash otherwise
	 */
	public function ensure_fields_prefix( $prefix, $field ) {

		if ( isset( $this->fields[ $field ] ) ) {
			$prefix = $this->fields[ $field ]['name'];
		}

		return $prefix;
	}

	/**
	 * Register additional fields for maps
	 *
	 * @param  array                                 $storage_data Storage data to register.
	 * @param  \Jet_Engine\CPT\Custom_Tables\Manager $manager      \Jet_Engine\CPT\Custom_Tables\Manager instance.
	 * @return array
	 */
	public function register_storage_fields( $storage_data, $manager ) {

		$fields = $this->add_service_columns(
			array(
				'as_columns' => $storage_data['fields'],
				'raw' => $storage_data['raw_fields'],
			),
			$storage_data['object_slug']
		);

		$storage_data['raw_fields'] = $fields['raw'];
		$storage_data['fields'] = $fields['as_columns'];

		if ( ! empty( $storage_data['raw_fields'] ) ) {
			foreach ( $storage_data['raw_fields'] as $field ) {

				if ( empty( $field['type'] ) ) {
					continue;
				}

				$field_name = $manager->sanitize_field_name( $field['name'] );

				if ( $field['type'] === $this->map_field->field_type ) {

					$storage_data['fields'][] = $field_name . '_hash';
					$storage_data['fields'][] = $field_name . '_lat';
					$storage_data['fields'][] = $field_name . '_lng';

					$this->fields[ $field['name'] ] = [
						'name'        => $field_name,
						'object_slug' => $storage_data['object_slug'],
					];

				} else {
					$this->regular_fields[ $field['name'] ] = [
						'name'        => $field_name,
						'object_slug' => $storage_data['object_slug'],
					];
				}
			}
		}

		return $storage_data;
	}

}
