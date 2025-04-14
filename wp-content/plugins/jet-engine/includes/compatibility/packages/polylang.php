<?php

use \Jet_Engine\Modules\Data_Stores\Stores\Factory;

/**
 * Polylang compatibility package
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Engine_Polylang_Package' ) ) {

	class Jet_Engine_Polylang_Package {

		public function __construct() {
			add_filter( 'jet-engine/listings/frontend/rendered-listing-id', array( $this, 'set_translated_object' ) );
			add_filter( 'jet-engine/forms/render/form-id',                  array( $this, 'set_translated_object' ) );

			// Translate Admin Labels
			add_filter( 'jet-engine/compatibility/translate-string', array( $this, 'translate_admin_labels' ) );

			// Disable `suppress_filters` in the `get_posts` args.
			add_filter( 'jet-engine/compatibility/get-posts/args', array( $this, 'disable_suppress_filters' ) );

			// Relations
			if ( jet_engine()->relations ) {
				$this->relations_hooks();
			}
			
			//Data Stores
			add_filter( 'jet-engine/data-stores/store/data', array( $this, 'set_translated_store' ), 10, 2 );

			add_action( 'jet-engine/data-stores/filtered-id', array( $this, 'get_original_data_store_post_id' ), 10, 3 );

			add_action( 'jet-engine/data-stores/post-count-increased', array( $this, 'update_translations_count' ), 10, 3 );
			add_action( 'jet-engine/data-stores/post-count-decreased', array( $this, 'update_translations_count' ), 10, 3 );

			//ensure all translated items share the same count
			add_filter( 'jet-engine/data-stores/pre-get-post-count', array( $this, 'get_post_count' ), 10, 3 );
		}

		/**
		 * @param int     $post_id Post ID
		 * @param string  $store   Store slug
		 * @param Factory $factory Factory instance
		 */
		public function get_original_data_store_post_id( $post_id, $store, $factory ) {
			if ( $factory->is_user_store() || $factory->get_arg( 'is_cct' ) ) {
				return $post_id;
			}
	
			switch ( $factory->get_type()->type_id() ) {
				case 'user_ip':
					$post_id = $this->get_original_post_id( $post_id );
					break;
			}
	
			return $post_id;
		}

		/**
		 * Get original post ID (translation in default language). Returns translation ID if no original found.
		 * 
		 * @param  int $translation_id Post ID
		 * @return int                 Original post ID if found, given post ID otherwise
		 */
		public function get_original_post_id( $translation_id ) {
			$default_language = pll_default_language();
			$original_id = pll_get_post( $translation_id, $default_language );

			if ( $original_id === $translation_id ) {
				return $translation_id;
			}

			return $original_id;
		}

		/**
		 * @param  int|false     $count   Pre-get post count, or false if count should not be pre-get
		 * @param  int           $post_id Store slug
		 * @param  Factory       $factory Factory instance
		 * @return int|false     Pre-get post count, or false if count should not be pre-get
		 */
		public function get_post_count( $count, $post_id, $factory ) {
			if ( $factory->is_user_store() || ! $factory->get_type() ) {
				return $count;
			}

			$original_id = $this->get_original_post_id( $post_id );

			if ( $original_id === $post_id || ! get_post( $original_id ) ) {
				return $count;
			}

			return $factory->get_post_count( $original_id );
		}

		/**
		 * Sync data store post count between translations
		 * 
		 * @param int     $post_id Store slug
		 * @param int     $count   Post count
		 * @param Factory $factory Factory instance
		 */
		public function update_translations_count( $post_id, $count, $factory ) {
			if ( $factory->is_user_store() || $factory->get_arg( 'is_cct' ) ) {
				return;
			}
	
			$original_id = $this->get_original_post_id( $post_id );
	
			if ( ( int ) $original_id !== ( int ) $post_id ) {
				update_post_meta( $original_id, $factory->get_count_posts_key() . $factory->get_slug(), $count );
			} else {
				$translations = pll_get_post_translations( $post_id );

				foreach ( $translations as $id ) {
					if ( ( int ) $post_id === ( int ) $id ) {
						continue;
					}

					update_post_meta( $id, $factory->get_count_posts_key() . $factory->get_slug(), $count );
				}
			}
		}

		/**
		 * Set translated object ID to show
		 *
		 * @param int|string $obj_id Object ID
		 *
		 * @return false|int|null
		 */
		public function set_translated_object( $obj_id ) {

			if ( function_exists( 'pll_get_post' ) ) {

				$translation_obj_id = pll_get_post( $obj_id );

				if ( null === $translation_obj_id ) {
					// the current language is not defined yet
					return $obj_id;
				} elseif ( false === $translation_obj_id ) {
					//no translation yet
					return $obj_id;
				} elseif ( $translation_obj_id > 0 ) {
					// return translated post id
					return $translation_obj_id;
				}
			}

			return $obj_id;
		}
		
		/**
		 * Set translated data store item IDs to show
		 *
		 * @param array   $store     Array of store items
		 * @param string  $store_id  Store ID
		 *
		 * @return array  Array of store items
		 */
		public function set_translated_store( $store, $store_id ) {

			if ( empty( $store ) ) {
				return $store;
			}

			$store_instance = \Jet_Engine\Modules\Data_Stores\Module::instance()->stores->get_store( $store_id );

			if ( $store_instance->is_user_store() || $store_instance->get_arg( 'is_cct' ) ) {
				return $store;
			}

			$store = array_map( function( $item ) {

				if ( ! is_array( $item ) ) {
					$item = $this->set_translated_object( $item );
				}

				return $item;
			}, $store );

			return $store;
		}	

		/**
		 * Translate Admin Labels
		 *
		 * @param  string $label
		 * @return string
		 */
		public function translate_admin_labels( $label ) {

			pll_register_string( 'jet-engine', $label, 'JetEngine', true );

			return pll__( $label );
		}

		public function disable_suppress_filters( $args = array() ) {
			$args['suppress_filters'] = false;
			return $args;
		}

		public function relations_hooks() {

			add_filter( 'jet-engine/relations/types/posts/get-items', array( $this, 'filtered_relations_posts_items' ), 10, 2 );
			add_action( 'jet-engine/relations/types/posts/on-create', array( $this, 'set_lang_to_new_object' ), 10, 3 );
			add_action( 'jet-engine/relations/types/terms/on-create', array( $this, 'set_lang_to_new_object' ), 10, 3 );

			$auto_sync_relations = apply_filters( 'jet-engine/compatibility/polylang/auto-sync-relations', true );

			if ( $auto_sync_relations && is_admin() ) {
				add_filter( 'wp_insert_post', array( $this, 'sync_relations_on_add_translation_post' ) );

				add_action( 'jet-engine/relation/update/after', array( $this, 'sync_relations_on_update' ), 10, 4 );
				add_action( 'jet-engine/relation/delete/after', array( $this, 'sync_relations_on_delete' ), 10, 4 );
			}
		}

		public function filtered_relations_posts_items( $items, $post_type ) {

			if ( ! pll_is_translated_post_type( $post_type ) ) {
				return $items;
			}

			$current_lang = pll_current_language();

			$items = array_filter( $items, function ( $item ) use ( $current_lang ) {
				$lang = pll_get_post_language( $item['value'] );
				return $current_lang === $lang;
			} );

			return $items;
		}

		public function set_lang_to_new_object( $object_id, $data, $object_type ) {

			if ( is_wp_error( $object_id ) ) {
				return;
			}

			if ( empty( $_REQUEST['relatedObjectType'] ) ) {
				return;
			}

			$refer_lang = false;

			if ( ! empty( $_REQUEST['pll_post_id'] ) ) {
				$refer_lang = pll_get_post_language( (int) $_REQUEST['pll_post_id'] );
			} elseif ( ! empty( $_REQUEST['pll_term_id'] ) ) {
				$refer_lang = pll_get_term_language( (int) $_REQUEST['pll_term_id'] );
			}

			if ( empty( $refer_lang ) ) {
				return;
			}

			switch ( $_REQUEST['relatedObjectType'] ) {
				case 'posts':

					if ( ! pll_is_translated_post_type( $object_type ) ) {
						return;
					}

					$obj_lang = pll_get_post_language( $object_id );

					if ( $obj_lang !== $refer_lang ) {
						pll_set_post_language( $object_id, $refer_lang );
					}

					break;

				case 'terms':

					if ( ! pll_is_translated_taxonomy( $object_type ) ) {
						return;
					}

					$obj_lang = pll_get_term_language( $object_id );

					if ( $obj_lang !== $refer_lang ) {
						pll_set_term_language( $object_id, $refer_lang );
					}

					break;
			}
		}

		public function sync_relations_on_add_translation_post( $post_id ) {

			if ( ! isset( $GLOBALS['pagenow'], $_GET['from_post'], $_GET['new_lang'] ) ) {
				return;
			}

			if ( 'post-new.php' !== $GLOBALS['pagenow'] ) {
				return;
			}

			if ( ! pll_is_translated_post_type( get_post_type( $post_id ) ) ) {
				return;
			}

			$from_post_id = (int) $_GET['from_post'];
			$lang         = PLL()->model->get_language( sanitize_key( $_GET['new_lang'] ) );

			$this->sync_relations_items( $from_post_id, $post_id, $lang->slug );
		}

		public function sync_relations_items( $original_id, $translated_id, $lang ) {

			$post_type = get_post_type( $original_id );
			$rel_type  = jet_engine()->relations->types_helper->type_name_by_parts( 'posts', $post_type );

			$active_relations = jet_engine()->relations->get_active_relations();

			$relations = array_filter( $active_relations, function( $relation ) use ( $rel_type ) {

				if ( $rel_type === $relation->get_args( 'parent_object' ) ) {
					return true;
				}

				if ( $rel_type === $relation->get_args( 'child_object' ) ) {
					return true;
				}

				return false;
			} );

			if ( empty( $relations ) ) {
				return;
			}

			foreach ( $relations as $rel_id => $relation ) {

				$is_parent   = $rel_type === $relation->get_args( 'parent_object' );
				$meta_fields = $relation->get_args( 'meta_fields' );

				if ( $is_parent ) {
					$rel_items = $relation->get_children( $original_id, 'ids' );
					$obj_data  = jet_engine()->relations->types_helper->type_parts_by_name( $relation->get_args( 'child_object' ) );
					$is_single = $relation->is_single_child();
				} else {
					$rel_items = $relation->get_parents( $original_id, 'ids' );
					$obj_data  = jet_engine()->relations->types_helper->type_parts_by_name( $relation->get_args( 'parent_object' ) );
					$is_single = $relation->is_single_parent();
				}

				$rel_items    = array_reverse( $rel_items );
				$obj_type     = $obj_data[0];
				$obj_sub_type = $obj_data[1];

				foreach ( $rel_items as $rel_item ) {

					switch ( $obj_type ) {
						case 'posts':
							$new_rel_item = pll_get_post( $rel_item, $lang );
							$new_rel_item = ! empty( $new_rel_item ) ? $new_rel_item : $rel_item;
							break;

						case 'terms':
							$new_rel_item = pll_get_term( $rel_item, $lang );
							$new_rel_item = ! empty( $new_rel_item ) ? $new_rel_item : $rel_item;
							break;

						default:
							$new_rel_item = $rel_item;
					}

					if ( $is_single && $new_rel_item == $rel_item ) {
						continue;
					}

					if ( $is_parent ) {
						$relation->update( $translated_id, $new_rel_item );

						if ( empty( $meta_fields ) ) {
							continue;
						}

						$meta     = $relation->get_all_meta( $original_id, $rel_item );
						$new_meta = $relation->get_all_meta( $translated_id, $new_rel_item );
						$new_meta = array_merge( $meta, $new_meta );

						if ( ! empty( $new_meta ) ) {
							$relation->update_all_meta( $new_meta, $translated_id, $new_rel_item );
						}

					} else {
						$relation->update( $new_rel_item, $translated_id );

						if ( empty( $meta_fields ) ) {
							continue;
						}

						$meta     = $relation->get_all_meta( $rel_item, $original_id );
						$new_meta = $relation->get_all_meta( $new_rel_item, $translated_id );
						$new_meta = array_merge( $meta, $new_meta );

						if ( ! empty( $new_meta ) ) {
							$relation->update_all_meta( $meta, $new_rel_item, $translated_id );
						}
					}
				}
			}
		}

		public function sync_relations_on_update( $parent_id, $child_id, $item_id, $relation ) {

			if ( empty( $item_id ) ) {
				return;
			}

			$parent_obj_data = jet_engine()->relations->types_helper->type_parts_by_name( $relation->get_args( 'parent_object' ) );
			$child_obj_data  = jet_engine()->relations->types_helper->type_parts_by_name( $relation->get_args( 'child_object' ) );

			$support_types = array( 'posts', 'terms' );

			if ( ! in_array( $parent_obj_data[0], $support_types ) || ! in_array( $child_obj_data[0], $support_types ) ) {
				return;
			}

			if ( ! $this->is_item_translated( $parent_obj_data[1], $parent_obj_data[0] ) ||
				 ! $this->is_item_translated( $child_obj_data[1], $child_obj_data[0] )
			) {
				return;
			}

			$parent_translations = $this->get_item_translations( $parent_id, $parent_obj_data[0] );
			$child_translations  = $this->get_item_translations( $child_id, $child_obj_data[0] );

			remove_action( 'jet-engine/relation/update/after', array( $this, 'sync_relations_on_update' ) );

			foreach ( $parent_translations as $lang => $translation_id ) {

				if ( $translation_id == $parent_id ) {
					continue;
				}

				if ( ! isset( $child_translations[ $lang ] ) ) {
					continue;
				}

				$child_trans_id = $child_translations[ $lang ];

				$relation->update( $translation_id, $child_trans_id );
			}

			add_action( 'jet-engine/relation/update/after', array( $this, 'sync_relations_on_update' ), 10, 4 );
		}

		public function sync_relations_on_delete( $parent_id, $child_id, $clear_meta, $relation ) {

			$parent_obj_data = jet_engine()->relations->types_helper->type_parts_by_name( $relation->get_args( 'parent_object' ) );
			$child_obj_data  = jet_engine()->relations->types_helper->type_parts_by_name( $relation->get_args( 'child_object' ) );

			$support_types = array( 'posts', 'terms' );

			if ( ! in_array( $parent_obj_data[0], $support_types ) || ! in_array( $child_obj_data[0], $support_types ) ) {
				return;
			}

			if ( ! $this->is_item_translated( $parent_obj_data[1], $parent_obj_data[0] ) ||
				 ! $this->is_item_translated( $child_obj_data[1], $child_obj_data[0] )
			) {
				return;
			}

			$parent_translations = $this->get_item_translations( $parent_id, $parent_obj_data[0] );
			$child_translations  = $this->get_item_translations( $child_id, $child_obj_data[0] );

			remove_action( 'jet-engine/relation/delete/after', array( $this, 'sync_relations_on_delete' ) );

			foreach ( $parent_translations as $lang => $translation_id ) {

				if ( $translation_id == $parent_id ) {
					continue;
				}

				if ( ! isset( $child_translations[ $lang ] ) ) {
					continue;
				}

				$rel_items      = $relation->get_children( $translation_id, 'ids' );
				$child_trans_id = $child_translations[ $lang ];

				if ( ! in_array( $child_trans_id, $rel_items ) ) {
					continue;
				}

				$relation->delete_rows( $translation_id, $child_trans_id );
			}

			add_action( 'jet-engine/relation/delete/after', array( $this, 'sync_relations_on_delete' ), 10, 4 );
		}

		public function is_item_translated( $type = null, $obj_type = null ) {

			switch ( $obj_type ) {
				case 'posts':
					$is_translated = pll_is_translated_post_type( $type );
					break;

				case 'terms':
					$is_translated = pll_is_translated_taxonomy( $type );
					break;

				default:
					$is_translated = false;
			}

			return $is_translated;
		}

		public function get_item_translations( $id, $obj_type = null ) {

			switch ( $obj_type ) {
				case 'posts':
					$translations = pll_get_post_translations( $id );
					break;

				case 'terms':
					$translations = pll_get_term_translations( $id );
					break;

				default:
					$translations = array();
			}

			return $translations;
		}

	}

}

new Jet_Engine_Polylang_Package();
