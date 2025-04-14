<?php
/**
 * Elementor views manager
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! trait_exists( 'Jet_Engine_Get_Data_Sources_Trait' ) ) {
	require_once jet_engine()->plugin_path( 'includes/traits/get-data-sources.php' );
}

if ( ! class_exists( 'Jet_Engine_Blocks_Views_Editor' ) ) {

	/**
	 * Define Jet_Engine_Blocks_Views_Editor class
	 */
	class Jet_Engine_Blocks_Views_Editor {

		use Jet_Engine_Get_Data_Sources_Trait;

		public function __construct( $silent = false ) {

			if ( $silent ) {
				return;
			}

			add_action( 'enqueue_block_editor_assets', array( $this, 'blocks_assets' ), -1 );

			require_once jet_engine()->plugin_path( 'includes/components/blocks-views/editor-meta-boxes.php' );
			new Jet_Engine_Blocks_Views_Editor_Meta_Boxes();

		}

		/**
		 * Get meta fields for post type
		 *
		 * @return array
		 */
		public function get_meta_fields() {

			if ( jet_engine()->meta_boxes ) {
				return jet_engine()->meta_boxes->get_fields_for_select( 'plain', 'blocks' );
			} else {
				return array();
			}

		}

		/**
		 * Get meta fields for post type
		 *
		 * @return array
		 */
		public function get_repeater_fields() {

			if ( jet_engine()->meta_boxes ) {
				$groups = jet_engine()->meta_boxes->get_fields_for_select( 'repeater', 'blocks' );
			} else {
				$groups = array();
			}

			if ( jet_engine()->options_pages ) {
				$groups[] = array(
					'label'  => __( 'Other', 'jet-engine' ),
					'values' => array(
						array(
							'value' => 'options_page',
							'label' => __( 'Options' ),
						),
					),
				);
			}

			$extra_fields = apply_filters( 'jet-engine/listings/dynamic-repeater/fields', array() );

			if ( ! empty( $extra_fields ) ) {

				foreach ( $extra_fields as $key => $data ) {

					if ( ! is_array( $data ) ) {

						$groups[] = array(
							'label'  => $data,
							'values' => array(
								array(
									'value' => $key,
									'label' => $data,
								),
							),
						);

						continue;
					}

					$values = array();

					if ( ! empty( $data['options'] ) ) {
						foreach ( $data['options'] as $val => $label ) {
							$values[] = array(
								'value' => $val,
								'label' => $label,
							);
						}
					}

					$groups[] = array(
						'label'  => $data['label'],
						'values' => $values,
					);
				}
			}

			return $groups;

		}

		/**
		 * Get registered options fields
		 *
		 * @return array
		 */
		public function get_options_fields( $type = 'plain' ) {
			if ( jet_engine()->options_pages ) {
				return jet_engine()->options_pages->get_options_for_select( $type, 'blocks' );
			} else {
				return array();
			}
		}

		/**
		 * Returns filter callbacks list
		 *
		 * @return [type] [description]
		 */
		public function get_filter_callbacks() {

			$callbacks = jet_engine()->listings->get_allowed_callbacks();
			$result    = array( array(
				'value' => '',
				'label' => '--',
			) );

			foreach ( $callbacks as $function => $label ) {
				$result[] = array(
					'value' => $function,
					'label' => $label,
				);
			}

			return $result;

		}

		public function get_filter_callbacks_args() {

			$result     = array();
			$disallowed = array( 'checklist_divider_color' );

			foreach ( jet_engine()->listings->get_callbacks_args() as $key => $args ) {

				if ( in_array( $key, $disallowed ) ) {
					continue;
				}

				$args['prop'] = $key;

				if ( ! empty( $args['description'] ) ) {
					$args['description'] = wp_kses_post( $args['description'] );
				}

				if ( 'select' === $args['type'] ) {

					$options = $args['options'];
					$args['options'] = array();

					foreach ( $options as $value => $label ) {
						$args['options'][] = array(
							'value' => $value,
							'label' => $label,
						);
					}
				}

				// Convert `slider` control to `number` control.
				if ( 'slider' === $args['type'] ) {
					$args['type'] = 'number';

					if ( ! empty( $args['range'] ) ) {

						$first_unit = $this->get_first_key( $args['range'] );

						foreach ( array( 'min', 'max', 'step' ) as $range_arg ) {
							if ( isset( $args['range'][ $first_unit ][ $range_arg ] ) ) {
								$args[ $range_arg ] = $args['range'][ $first_unit ][ $range_arg ];
							}
						}

						unset( $args['range'] );
					}
				}

				$args['condition'] = $args['condition']['filter_callback'];

				$result[] = $args;
			}

			return $result;
		}

		public function get_first_key( $array = array() ) {

			if ( function_exists( 'array_key_first' ) ) {
				return array_key_first( $array );
			} else {
				$keys = array_keys( $array );
				return $keys[0];
			}

		}

		/**
		 * Returns all taxonomies list for options
		 *
		 * @return [type] [description]
		 */
		public function get_taxonomies_for_options() {

			$result     = array();
			$taxonomies = get_taxonomies( array( 'public' => true ), 'objects' );

			foreach ( $taxonomies as $taxonomy ) {

				if ( empty( $taxonomy->object_type ) || ! is_array( $taxonomy->object_type ) ) {
					continue;
				}

				foreach ( $taxonomy->object_type as $object ) {
					if ( empty( $result[ $object ] ) ) {
						$post_type = get_post_type_object( $object );

						if ( ! $post_type ) {
							continue;
						}

						$result[ $object ] = array(
							'label'  => $post_type->labels->name,
							'values' => array(),
						);
					}

					$result[ $object ]['values'][] = array(
						'value' => $taxonomy->name,
						'label' => $taxonomy->labels->name,
					);

				};
			}

			return array_values( $result );

		}

		/**
		 * Register plugin sidebar
		 *
		 * @return [type] [description]
		 */
		public function blocks_assets() {

			do_action( 'jet-engine/blocks-views/editor-script/before' );

			wp_enqueue_script(
				'jet-engine-blocks-views',
				jet_engine()->plugin_url( 'assets/js/admin/blocks-views/blocks.js' ),
				array( 'wp-components', 'wp-element', 'wp-blocks', 'wp-block-editor', 'lodash' ),
				jet_engine()->get_version(),
				true
			);

			wp_enqueue_style(
				'jet-engine-blocks-views',
				jet_engine()->plugin_url( 'assets/css/admin/blocks-views.css' ),
				array(),
				jet_engine()->get_version()
			);

			do_action( 'jet-engine/blocks-views/editor-script/after' );

			wp_localize_script(
				'jet-engine-blocks-views',
				'JetEngineListingData',
				$this->get_block_editor_config()
			);
		}

		/**
		 * Get block editor config array
		 *
		 * @return array
		 */
		public function get_block_editor_config() {

			global $post;

			$settings = array();
			$post_id  = false;

			if ( $post ) {
				$settings = get_post_meta( $post->ID, '_elementor_page_settings', true );
				$post_id  = $post->ID;
			}

			if ( empty( $settings ) ) {
				$settings = array();
			}

			$source     = ! empty( $settings['listing_source'] ) ? $settings['listing_source'] : 'posts';
			$post_type  = ! empty( $settings['listing_post_type'] ) ? $settings['listing_post_type'] : 'post';
			$tax        = ! empty( $settings['listing_tax'] ) ? $settings['listing_tax'] : 'category';
			$rep_source = ! empty( $settings['repeater_source'] ) ? esc_attr( $settings['repeater_source'] ) : '';
			$rep_field  = ! empty( $settings['repeater_field'] ) ? esc_attr( $settings['repeater_field'] ) : '';
			$rep_option = ! empty( $settings['repeater_option'] ) ? esc_attr( $settings['repeater_option'] ) : '';

			jet_engine()->listings->data->set_listing( jet_engine()->listings->get_new_doc( array(
				'listing_source'    => $source,
				'listing_post_type' => $post_type,
				'listing_tax'       => $tax,
				'repeater_source'   => $rep_source,
				'repeater_field'    => $rep_field,
				'repeater_option'   => $rep_option,
				'is_main'           => true,
			), $post_id ) );

			$current_object_id = $this->get_current_object();
			$field_sources     = jet_engine()->listings->data->get_field_sources();
			$sources           = array();

			foreach ( $field_sources as $value => $label ) {
				$sources[] = array(
					'value' => $value,
					'label' => $label,
				);
			}

			$link_sources = $this->get_dynamic_sources( 'plain' );
			$link_sources = apply_filters( 'jet-engine/blocks-views/dynamic-link-sources', $link_sources );

			$media_sources = $this->get_dynamic_sources( 'media' );
			$media_sources = apply_filters( 'jet-engine/blocks-views/dynamic-media-sources', $media_sources );

			/**
			 * Format:
			 * array(
			 *  	'block-type-name' => array(
			 *  		array(
			 * 				'prop' => 'prop-name-to-set',
			 * 				'label' => 'control-label',
			 * 				'condition' => array(
			 * 					'prop' => array( 'value' ),
			 * 				)
			 * 			)
			 *  	)
			 *  )
			 */
			$custom_controls = apply_filters( 'jet-engine/blocks-views/custom-blocks-controls', array() );
			$custom_panles   = array();

			$config = apply_filters( 'jet-engine/blocks-views/editor-data', array(
				'adminLinks'            => array(
					'shortcodes_generator' => admin_url( 'admin.php?page=jet-engine#shortcode_generator' ),
					'macros_generator'     => admin_url( 'admin.php?page=jet-engine#macros_generator' ),
				),
				'isJetEnginePostType'   => 'jet-engine' === get_post_type(),
				'settings'              => $settings,
				'object_id'             => $current_object_id,
				'fieldSources'          => $sources,
				'imageSizes'            => jet_engine()->listings->get_image_sizes( 'blocks' ),
				'metaFields'            => $this->get_meta_fields(),
				'repeaterFields'        => $this->get_repeater_fields(),
				'mediaFields'           => $media_sources,
				'linkFields'            => $link_sources,
				'optionsFields'         => $this->get_options_fields( 'plain' ),
				'mediaOptionsFields'    => $this->get_options_fields( 'media' ),
				'userRoles'             => Jet_Engine_Tools::get_user_roles_for_js(),
				'repeaterOptionsFields' => $this->get_options_fields( 'repeater' ),
				'filterCallbacks'       => $this->get_filter_callbacks(),
				'filterCallbacksArgs'   => $this->get_filter_callbacks_args(),
				'taxonomies'            => $this->get_taxonomies_for_options(),
				'queriesList'           => \Jet_Engine\Query_Builder\Manager::instance()->get_queries_for_options( true ),
				'objectFields'          => jet_engine()->listings->data->get_object_fields( 'blocks' ),
				'postTypes'             => Jet_Engine_Tools::get_post_types_for_js(),
				'legacy'                => array(
					'is_disabled' => jet_engine()->listings->legacy->is_disabled(),
					'message'     => jet_engine()->listings->legacy->get_notice(),
				),
				'glossariesList'        => jet_engine()->glossaries->get_glossaries_for_js(),
				'atts'                  => array(
					'dynamicField'    => jet_engine()->blocks_views->block_types->get_block_atts( 'dynamic-field' ),
					'dynamicLink'     => jet_engine()->blocks_views->block_types->get_block_atts( 'dynamic-link' ),
					'dynamicImage'    => jet_engine()->blocks_views->block_types->get_block_atts( 'dynamic-image' ),
					'dynamicRepeater' => jet_engine()->blocks_views->block_types->get_block_atts( 'dynamic-repeater' ),
					'dynamicTerms'    => jet_engine()->blocks_views->block_types->get_block_atts( 'dynamic-terms' ),
					'listingGrid'     => jet_engine()->blocks_views->block_types->get_block_atts( 'listing-grid' ),
				),
				'customPanles'          => $custom_panles,
				'customControls'        => $custom_controls,
				'injections'            => apply_filters( 'jet-engine/blocks-views/listing-injections-config', array(
					'enabled' => false,
				) ),
				'relationsTypes'        => array(
					array(
						'value' => 'grandparents',
						'label' => __( 'Grandparent Posts', 'jet-engine' ),
					),
					array(
						'value' => 'grandchildren',
						'label' => __( 'Grandchildren Posts', 'jet-engine' ),
					),
				),
				'listingOptions'   => jet_engine()->listings->get_listings_for_options( 'blocks' ),
				'hideOptions'      => jet_engine()->listings->get_widget_hide_options( 'blocks' ),
				'activeModules'    => jet_engine()->modules->get_active_modules(),
				'blocksWithIdAttr' => jet_engine()->blocks_views->block_types->get_blocks_with_id_attr(),
				'preventWrap'      => \Jet_Engine\Modules\Performance\Module::instance()->is_tweak_active( 'optimized_dom' ),
			) );

			return apply_filters( 'jet-engine/blocks-views/editor/config', $config );
		}

		/**
		 * Returns information about current object
		 *
		 * @param  [type] $source [description]
		 * @return [type]         [description]
		 */
		public function get_current_object() {

			if ( 'jet-engine' !== get_post_type() ) {
				return get_the_ID();
			}

			$source    = jet_engine()->listings->data->get_listing_source();
			$object_id = null;

			switch ( $source ) {

				case 'posts':
				case 'repeater':

					$post_type = jet_engine()->listings->data->get_listing_post_type();

					$posts = get_posts( array(
						'post_type'        => $post_type,
						'numberposts'      => 1,
						'orderby'          => 'date',
						'order'            => 'DESC',
						'suppress_filters' => false,
					) );

					if ( ! empty( $posts ) ) {
						$post = $posts[0];
						jet_engine()->listings->data->set_current_object( $post );
						$object_id = $post->ID;
					}

					break;

				case 'terms':

					$tax   = jet_engine()->listings->data->get_listing_tax();
					$terms = get_terms( array(
						'taxonomy'   => $tax,
						'hide_empty' => false,
					) );

					if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
						$term = $terms[0];
						jet_engine()->listings->data->set_current_object( $term );
						$object_id = $term->term_id;
					}

					break;

				case 'users':

					$object_id = get_current_user_id();
					jet_engine()->listings->data->set_current_object( wp_get_current_user() );

					break;

				default:

					$object_id = apply_filters(
						'jet-engine/blocks-views/editor/config/object/' . $source,
						false,
						$this
					);

					break;

			}

			return $object_id;

		}

	}

}
