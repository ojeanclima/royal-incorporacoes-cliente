<?php
namespace Jet_Engine\Website_Builder;


// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Handler {

	const POST_TYPES_ID = 'post_types';
	const WOO_ID        = 'woo';
	const TAXONOMIES_ID = 'taxonomies';
	const CCT_ID        = 'cct';
	const RELATIONS_ID  = 'relations';
	const FILTERS_ID    = 'filters';
	const TAGS_ID       = 'tags';

	protected $model = [];
	protected $handlers = [];
	protected $skins = [];
	protected $registered_entities = [];
	protected $model_objects = [];
	protected $log_data = [];

	/**
	 * A reference to an instance of this class.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    object
	 */
	private static $instance = null;

	/**
	 * Create a new model by given data
	 *
	 * @param  array  $model [description]
	 * @return [type]        [description]
	 */
	public function create_model( array $model = [] ) {

		$this->model = $model;

		$components = $this->prepare_model_components();

		foreach( $components as $handler_id => $data_to_register ) {

			$handler = $this->get_handler( $handler_id );

			if ( $handler ) {
				$result = $handler->handle( $data_to_register );
				$this->registered_entities[ $handler_id ] = $handler;
			}
		}

		return $this;
	}

	/**
	 * Remap CPT slugs to avoid not allowed naming
	 *
	 * @param  string $cpt_slug Initial slug
	 * @return string
	 */
	public function cpt_remap( $cpt_slug = '' ) {
		$map = [
			'post' => 'posts',
			'page' => 'pages',
			'attachment' => 'attachments',
			'revision' => 'revisions',
			'nav_menu_item' => 'nav_menu_items',
			'action' => 'actions',
			'author' => 'authors',
			'order' => 'orders',
			'theme' => 'custom-theme',
			'themes' => 'custom-themes',
		];

		return isset( $map[ $cpt_slug ] ) ? $map[ $cpt_slug ] : $cpt_slug;
	}

	/**
	 * Get skin object by its ID
	 *
	 * @param  [type] $skin_id [description]
	 * @return [type]          [description]
	 */
	public function get_skin( $skin_id ) {

		if ( empty( $this->skins ) ) {
			$this->setup_skins();
		}

		return isset( $this->skins[ $skin_id ] ) ? $this->skins[ $skin_id ] : false;

	}

	/**
	 * Setup all skins
	 *
	 * @return void
	 */
	public function setup_skins() {

		require_once Manager::instance()->component_path( 'handlers-skins/base.php' );
		require_once Manager::instance()->component_path( 'handlers-skins/post-types.php' );
		require_once Manager::instance()->component_path( 'handlers-skins/woo.php' );
		require_once Manager::instance()->component_path( 'handlers-skins/taxonomies.php' );
		require_once Manager::instance()->component_path( 'handlers-skins/cct.php' );
		require_once Manager::instance()->component_path( 'handlers-skins/relations.php' );
		require_once Manager::instance()->component_path( 'handlers-skins/filters.php' );
		require_once Manager::instance()->component_path( 'handlers-skins/tags.php' );

		$this->setup_skin( new Handlers_Skins\Post_Types() );
		$this->setup_skin( new Handlers_Skins\Woo() );
		$this->setup_skin( new Handlers_Skins\Taxonomies() );
		$this->setup_skin( new Handlers_Skins\CCT() );
		$this->setup_skin( new Handlers_Skins\Relations() );
		$this->setup_skin( new Handlers_Skins\Filters() );
		$this->setup_skin( new Handlers_Skins\Tags() );

		do_action( 'jet-engine/ai-website-builder/handler/resgiter-skins', $this );

	}

	/**
	 * Store skin instance into $this->skins[] by skin_id
	 *
	 * @param  [type] $skin [description]
	 * @return [type]          [description]
	 */
	public function setup_skin( $skin ) {
		$this->skins[ $skin->get_id() ] = $skin;
	}

	/**
	 * Returns current state of $model_objects property.
	 * Assumed as objects which will be created for currently processed model
	 *
	 * @return array
	 */
	public function get_current_model_objects() {
		return $this->model_objects;
	}

	/**
	 * Get handler instance for given handler ID
	 *
	 * @param  [type] $handler_id [description]
	 * @return [type]             [description]
	 */
	public function get_handler( $handler_id ) {

		if ( empty( $this->handlers ) ) {
			$this->setup_handlers();
		}

		return isset( $this->handlers[ $handler_id ] ) ? $this->handlers[ $handler_id ] : false;
	}

	/**
	 * Setup hadlers list
	 *
	 * @return array
	 */
	public function setup_handlers() {

		require_once Manager::instance()->component_path( 'handlers/base.php' );
		require_once Manager::instance()->component_path( 'handlers/post-types.php' );
		require_once Manager::instance()->component_path( 'handlers/woo.php' );
		require_once Manager::instance()->component_path( 'handlers/taxonomies.php' );
		require_once Manager::instance()->component_path( 'handlers/cct.php' );
		require_once Manager::instance()->component_path( 'handlers/relations.php' );
		require_once Manager::instance()->component_path( 'handlers/filters.php' );
		require_once Manager::instance()->component_path( 'handlers/tags.php' );

		$this->setup_handler( new Handlers\Post_Types() );
		$this->setup_handler( new Handlers\Woo() );
		$this->setup_handler( new Handlers\Taxonomies() );
		$this->setup_handler( new Handlers\CCT() );
		$this->setup_handler( new Handlers\Relations() );
		$this->setup_handler( new Handlers\Filters() );
		$this->setup_handler( new Handlers\Tags() );

		do_action( 'jet-engine/ai-website-builder/handler/resgiter-handlers', $this );

	}

	/**
	 * Store handler instance into $this->handlers[] by handle_id
	 *
	 * @param  [type] $handler [description]
	 * @return [type]          [description]
	 */
	public function setup_handler( $handler ) {
		$this->handlers[ $handler->get_id() ] = $handler;
	}

	/**
	 * Return full model desciption
	 *
	 * @return array
	 */
	public function get_model() {
		return $this->model;
	}

	/**
	 * Get list of model components
	 *
	 * @return array
	 */
	public function prepare_model_components() {

		$components = [
			self::POST_TYPES_ID => [],
			self::WOO_ID        => [],
			self::TAXONOMIES_ID => [],
			self::CCT_ID        => [],
			self::RELATIONS_ID  => [],
			self::FILTERS_ID    => [],
			self::TAGS_ID       => [],
		];

		$model = $this->get_model();

		if ( ! empty( $model['postTypes'] ) ) {

			foreach ( $model['postTypes'] as $post_type_slug => $post_type ) {

				$post_type_slug = $this->cpt_remap( $post_type_slug );
				$custom_storage = ! empty( $post_type['customStorage'] ) ? filter_var( $post_type['customStorage'], FILTER_VALIDATE_BOOLEAN ) : false;
				$is_cct = ! empty( $post_type['isCCT'] ) ? $post_type['isCCT'] : false;
				$is_cct = filter_var( $is_cct, FILTER_VALIDATE_BOOLEAN );

				if ( ! empty( $post_type['metaFields'] ) ) {
					$post_type['metaFields'] = $this->sanitize_meta_fields(
						$post_type['metaFields'],
						[
							'post_type'         => $post_type_slug,
							'is_custom_storage' => $custom_storage,
							'is_cct'            => $is_cct,
						]
					);
				}

				if ( ! empty( $post_type['isWoo'] ) ) {

					$components[ self::WOO_ID ] = $post_type;

					if ( ! empty( $post_type['taxonomies'] ) ) {

						$builtin_taxonomies = $this->get_handler( self::WOO_ID )->built_in_taxonomies();

						foreach ( $post_type['taxonomies'] as $i => $tax ) {
							if ( isset( $builtin_taxonomies[ $tax['name'] ] ) ) {
								$tax['name'] = $builtin_taxonomies[ $tax['name'] ];
								$post_type['taxonomies'][ $i ] = $tax;
							}
						}

						$components[ self::FILTERS_ID ] = array_merge(
							$components[ self::FILTERS_ID ],
							$this->extract_filters( $post_type['taxonomies'], 'taxonomies', 'product' )
						);
					}

					if ( ! empty( $post_type['metaFields'] ) ) {
						$components[ self::FILTERS_ID ] = array_merge(
							$components[ self::FILTERS_ID ],
							$this->extract_woo_filters( $post_type['metaFields'] )
						);
					}

					// Nothing more to do with this CPT
					continue;
				}

				$post_type['slug'] = $post_type_slug;

				if ( ! empty( $post_type['taxonomies'] ) ) {

					foreach ( $post_type['taxonomies'] as $taxonomy ) {

						$taxonomy['post_type']      = $post_type_slug;
						$components[ self::TAXONOMIES_ID ][] = $taxonomy;

						$this->model_objects[ $taxonomy['name'] ] = [
							'title' => $taxonomy['title'],
							'type'  => 'taxonomy',
						];
					}

					$components[ self::FILTERS_ID ] = array_merge(
						$components[ self::FILTERS_ID ],
						$this->extract_filters( $post_type['taxonomies'], 'taxonomies', $post_type_slug )
					);
				}

				if ( ! empty( $post_type['metaFields'] ) ) {
					$components[ self::FILTERS_ID ] = array_merge(
						$components[ self::FILTERS_ID ],
						$this->extract_filters( $post_type['metaFields'], 'meta_fields', $post_type_slug )
					);
				}

				$object = [
					'title' => $post_type['title'],
				];

				if ( $is_cct ) {
					$components[ self::CCT_ID ][] = $post_type;
					$object['type']      = 'cct';
				} else {
					$components[ self::POST_TYPES_ID ][] = $post_type;
					$object['type']             = 'post_type';
				}

				$this->model_objects[ $post_type['slug'] ] = $object;
			}
		}

		if ( ! empty( $model['relations'] ) ) {

			$relations = [];

			foreach ( $model['relations'] as $relation ) {
				$relation['from'] = $this->cpt_remap( $relation['from'] );
				$relation['to']   = $this->cpt_remap( $relation['to'] );

				$relations[] = $relation;
			}

			$components[ self::RELATIONS_ID ] = [
				'relations' => $relations,
				'objects'   => $this->model_objects,
			];
		}

		if ( ! empty( $model['tags'] ) ) {
			$components[ self::TAGS_ID ] = $model['tags'];
		}

		return apply_filters( 'jet-engine/ai-website-builder/handler/components-to-register', $components );
	}

	/**
	 * Sanitize names of CPTs in given fields list and add custom storage trigger
	 *
	 * @param  array $fields Raw fields list.
	 * @param  array $args   Additional arguments.
	 * @return array
	 */
	public function sanitize_meta_fields( $fields = [], $args = [] ) {

		$post_type         = ! empty( $args['post_type'] ) ? $args['post_type'] : '';
		$is_custom_storage = ! empty( $args['is_custom_storage'] ) ? $args['is_custom_storage'] : false;
		$is_cct            = ! empty( $args['is_cct'] ) ? $args['is_cct'] : false;

		foreach ( $fields as $i => $field ) {

			$field['is_custom_storage'] = $is_custom_storage;
			$field['is_cct']            = $is_cct;
			$field['post_type']         = $this->cpt_remap( $post_type );

			if ( isset( $field['relatedPostType'] ) ) {
				$field['relatedPostType']   = $this->cpt_remap( $field['relatedPostType'] );
				$field['name']              = $this->cpt_remap( $field['name'] );
			}

			$fields[ $i ] = $field;
		}

		return $fields;
	}

	/**
	 * Get model installation results HTML.
	 *
	 * @param  array $model Model HTML to get HTML for.
	 * @return string
	 */
	public function get_results_html( $model = [] ) {

		$result = '';

		if ( empty( $model ) ) {
			$model = $this->get_model_log();
		}

		foreach ( $model as $entity_type => $log ) {

			$skin = $this->get_skin( $entity_type );

			if ( $skin ) {
				$result .= $skin->get_skin_content( $log );
			}
		}

		return $result;
	}

	/**
	 * Returns log of just created model
	 *
	 * @return array
	 */
	public function get_model_log() {

		$result = [];

		foreach ( $this->registered_entities as $entity_type => $handler ) {
			$result[ $handler->get_id() ] = $handler->get_log();
		}

		$result['log_data'] = $this->log_data;

		return $result;
	}

	/**
	 * Log some additional data for handled model
	 * @param  string $key  [description]
	 * @param  string $data [description]
	 * @return void
	 */
	public function log_data( $key = '', $data = '' ) {
		$this->log_data[ $key ] = wp_kses_post( $data );
	}

	/**
	 * Gets registered entity data
	 *
	 * @param string $handler_id Handler ID to get regitered entity for.
	 */
	public function get_entity( $handler_id ) {
		return isset( $this->registered_entities[ $handler_id ] ) ? $this->registered_entities[ $handler_id ] : false;
	}

	/**
	 * Extract available filters from list of items
	 *
	 * @param  array  $items  Input items.
	 * @param  string $source Source name.
	 * @param  string $for    Slug of object is filter for (CPT/CCT).
	 * @return array
	 */
	public function extract_filters( $items = [], $source = '', $for = '' ) {

		$filters = [];

		if ( is_array( $items ) && ! empty( $source ) ) {
			foreach ( $items as $item ) {
				if ( ! empty( $item['filterType'] ) ) {
					$filters[] = [
						'source' => $source,
						'for'    => $for,
						'item'   => $item,
					];
				}
			}
		}

		return $filters;
	}

	/**
	 * Extract woocommerce filters from product meta fields
	 * @return array
	 */
	public function extract_woo_filters( $fields = [] ) {

		$meta_fields = [];
		$attrs       = [];

		$reserved_fields = $this->get_handler( self::WOO_ID )->reserved_fields();

		foreach ( $fields as $field ) {
			if ( isset( $reserved_fields[ $field['name'] ] ) ) {
				$field['name'] = $reserved_fields[ $field['name'] ];
				$meta_fields[] = $field;
			} else {
				// For now - forse select filter for WC attributes
				$field['filterType'] = ! empty( $field['filterType'] ) ? 'select' : false;
				$attrs[] = $field;
			}
		}

		return array_merge(
			$this->extract_filters( $meta_fields, 'meta_fields', 'product' ),
			$this->extract_filters( $attrs, 'wc_attributes', 'product' )
		);
	}

	/**
	 * Returns the instance.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return Jet_Engine
	 */
	public static function instance() {
		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}
}
