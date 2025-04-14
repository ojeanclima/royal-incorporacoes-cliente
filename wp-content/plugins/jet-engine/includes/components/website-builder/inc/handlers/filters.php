<?php
namespace Jet_Engine\Website_Builder\Handlers;

use \Jet_Engine\Website_Builder\Handler;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Filters extends Base {

	/**
	 * Get handler ID
	 *
	 * @return string
	 */
	public function get_id() {
		return Handler::FILTERS_ID;
	}

	/**
	 * Handle entities registration/creation
	 *
	 * @param  array $data Data to register.
	 * @return bool
	 */
	public function handle( array $data = [] ) {

		$result = true;

		if ( ! function_exists( 'jet_smart_filters' ) ) {

			$installed = false;

			try {
				$installed = $this->install_plugin();
			} catch ( \Exception $e ) {
				// Add errors processing
			}

			if ( ! $installed ) {
				return $result;
			}
		}

		/**
		 * Map model filter type to actual type of JSF
		 * @var array
		 */
		$types_map = [
			'checkbox' => 'checkboxes',
			'date'     => 'date-range',
		];

		foreach ( $data as $filter ) {

			$type = $filter['item']['filterType'];

			// Adjust filter type
			if ( isset( $types_map[ $type ] ) ) {
				$type = $types_map[ $type ];
			}

			$filter_data = [
				'post_status' => 'publish',
				'post_type'   => 'jet-smart-filters',
				'post_title'  => $filter['item']['title'],
				'meta_input'  => [
					'_filter_type' => $type,
					'_query_var'   => $filter['item']['name'],
				],
			];

			switch ( $filter['item']['filterType'] ) {
				case 'range':
					if ( ! empty( $filter['item']['is_cct'] ) ) {
						$filter_data['meta_input']['_source_callback'] = 'none';
					} elseif ( ! empty( $filter['item']['is_custom_storage'] )
						&& ! empty( $filter['item']['post_type'] )
					) {
						$cb = 'jet_engine_custom_storage_post_' . $filter['item']['post_type'];
						$filter_data['meta_input']['_source_callback'] = $cb;
					} else {
						$filter_data['meta_input']['_source_callback'] = 'jet_smart_filters_meta_values';
					}

					$filter_data['meta_input']['_range_inputs_enabled'] = true;
					break;

				case 'date-range':
					$filter_data['meta_input']['_date_source'] = 'meta_query';
					break;

				case 'select':
				case 'checkbox':
				case 'radio':
					$filter_data['meta_input'] = array_merge(
						$filter_data['meta_input'],
						$this->get_filter_data_source( $filter )
					);
					break;
			}

			if ( defined( 'JET_ENGINE_WEBSITE_BUILDER_DEBUG' ) && JET_ENGINE_WEBSITE_BUILDER_DEBUG ) {
				$filter_id = 1;
			} else {
				$filter_id = wp_insert_post( $filter_data );
			}

			if ( $filter_id ) {
				$this->log_entity( [
					'id'   => $filter_id,
					'name' => $filter['item']['title'],
				] );
			}
		}

		return $result;
	}

	/**
	 * Get data-source name for the filter
	 *
	 * @param  [type] $filter [description]
	 * @return [type]         [description]
	 */
	public function get_filter_data_source( $filter ) {

		$result = [
			'_data_source' => 'manual_input',
		];

		switch ( $filter['source'] ) {
			case 'meta_fields':
				if ( 'relation' === $filter['item']['type'] ) {

					$relation_handler = Handler::instance()->get_entity( Handler::RELATIONS_ID );
					$cct_handler = Handler::instance()->get_entity( Handler::CCT_ID );
					$log = $cct_handler->get_log();
					$is_query_source = false;

					if ( ! empty( $log ) ) {
						foreach ( $log as $cct ) {

							if (
								$cct['slug'] === $filter['item']['relatedPostType']
								&& ! empty( $cct['query_id'] )
							) {
								$is_query_source = true;
								$source = 'query_builder';
								$result['_query_builder_query'] = absint( $cct['query_id'] );
								$result['_query_builder_value_prop'] = '_ID';
								$result['_query_builder_label_prop'] = 'title';
							}
						}
					}

					if ( ! $is_query_source ) {

						$user_slugs = $relation_handler->reserved_user_slugs();

						if ( in_array( $filter['item']['relatedPostType'], $user_slugs ) ) {

							$query_id = $this->create_base_users_query();

							if ( $query_id ) {
								$source = 'query_builder';
								$result['_query_builder_query'] = $query_id;
								$result['_query_builder_value_prop'] = 'ID';
								$result['_query_builder_label_prop'] = 'display_name';
							}
						} else {

							$object_slug = $filter['item']['relatedPostType'];
							$model_objects = Handler::instance()->get_current_model_objects();
							$object = isset( $model_objects[ $object_slug ] ) ? $model_objects[ $object_slug ] : false;

							if ( ! empty( $object ) && 'taxonomy' === $object['type'] ) {
								$source = 'taxonomies';
								$result['_source_taxonomy'] = $object_slug;
							} else {
								$source = 'posts';
								$result['_source_post_type'] = $object_slug;
							}
						}
					}

					$result['_query_var'] = $this->get_relation_query_var( $filter );
				} elseif ( ! empty( $filter['item']['is_cct'] ) ) {
					$source = 'cct';
				} else {
					$source = 'custom_fields';
				}

				$result['_data_source'] = $source;
				$result['_source_custom_field'] = $filter['item']['name'];

				if ( in_array( $filter['item']['type'], [ 'select', 'checkbox', 'radio' ] ) ) {
					$result['_source_get_from_field_data'] = true;
					$result['_custom_field_source_plugin'] = 'jet_engine';
				}

				if ( 'checkbox' === $filter['item']['type'] ) {
					$result['_is_custom_checkbox'] = true;
				}

				break;

			case 'taxonomies':
			case 'wc_attributes':

				$taxonomy = $filter['item']['name'];

				if ( 'wc_attributes' === $taxonomy ) {
					$woo_handler = Handler::instance()->get_handle( Handler::WOO_ID );
					$taxonomy    = $woo_handler->get_attr_tax( $taxonomy );
				}

				$result['_data_source']     = 'taxonomies';
				$result['_source_taxonomy'] = $taxonomy;
				$result['_query_var']       = '';
				break;
		}

		return $result;
	}

	/**
	 * Get query variable for the relation this filter for
	 *
	 * @param  array  $filter [description]
	 * @return [type]         [description]
	 */
	public function get_relation_query_var( $filter = [] ) {

		$query_var = '';

		$registered_relations = Handler::instance()->get_entity( Handler::RELATIONS_ID );

		if ( $registered_relations ) {
			$log = $registered_relations->get_log();
			$children_mask = $filter['item']['relatedPostType'] . '>' . $filter['for'];
			$parent_mask = $filter['for'] . '>' . $filter['item']['relatedPostType'];

			foreach ( $log as $item ) {

				if ( $item['slug'] === $children_mask ) {
					$query_var = 'related_children*' . $item['id'];
				}

				if ( $item['slug'] === $parent_mask ) {
					$query_var = 'related_parents*' . $item['id'];
				}
			}
		}

		return $query_var;
	}

	/**
	 * Install JSF plugin
	 */
	public function install_plugin() {

		if ( ! class_exists( '\Jet_Dashboard\Utils' ) ) {
			throw new \Exception( 'Plugins manager class not found' );
		}

		$file    = 'jet-smart-filters/jet-smart-filters.php';
		$package = \Jet_Dashboard\Utils::package_url( $file );

		if ( ! $package ) {
			throw new \Exception( 'Please activate license which includes JetSmartFilters pliugin' );
		}

		include_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );

		$skin     = new \WP_Ajax_Upgrader_Skin();
		$upgrader = new \Plugin_Upgrader( $skin );
		$result   = $upgrader->install( $package );

		if ( is_wp_error( $result ) ) {
			throw new \Exception( $result->get_error_message() );
		} elseif ( is_wp_error( $skin->result ) ) {
			if ( 'folder_exists' !== $skin->result->get_error_code() ) {
				throw new \Exception( $skin->result->get_error_message() );
			}
		} elseif ( $skin->get_errors()->get_error_code() ) {
			throw new \Exception( $skin->get_error_messages() );
		} elseif ( is_null( $result ) ) {
			global $wp_filesystem;

			// Pass through the error from WP_Filesystem if one was raised.
			if ( $wp_filesystem instanceof \WP_Filesystem_Base && is_wp_error( $wp_filesystem->errors ) && $wp_filesystem->errors->get_error_code() ) {
				throw new \Exception( $skin->get_error_messages() );
			}
		}

		$result = activate_plugin( $file );

		if ( is_wp_error( $result ) ) {
			throw new \Exception( $result->get_error_messages() );
		}

		return true;
	}
}
