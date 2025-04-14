<?php

namespace Jet_Engine\Compatibility\Packages\Jet_Engine_WPML_Package\Relations;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Manager {

	/**
	 * A reference to an instance of this class.
	 *
	 * @access private
	 * @var    object
	 */
	private static $instance = null;

	/**
	 * A reference to an instance of compatibility package.
	 *
	 * @access private
	 * @var    object
	 */
	private $package = null;

	private function __construct( $package = null ) {
		$this->package = $package;

		if ( ! jet_engine()->relations ) {
			return;
		}
		
		$this->relation_hooks();
	}

	public function relation_hooks() {

		add_filter( 'jet-engine/relations/get_related_posts', array( $this, 'set_translated_related_posts' ) );

		add_filter( 'jet-engine/relations/types/posts/get-items', array( $this, 'filtered_relations_posts_items' ), 10, 2 );
		add_filter( 'jet-engine/relations/raw-args',              array( $this, 'translate_relations_labels' ) );

		$auto_sync_relations = apply_filters( 'jet-engine/compatibility/wpml/auto-sync-relations', true );

		if ( $auto_sync_relations ) {

			if ( is_admin() ) {
				add_action( 'icl_make_duplicate', array( $this, 'sync_relations_on_make_duplicate' ), 10, 4 );
			}

			if ( is_admin() || wpml_is_rest_request() ) {
				add_action( 'icl_pro_translation_completed', array( $this, 'sync_relations_on_translation_completed' ), 10, 3 );
			}

			add_action( 'jet-engine/relation/update/after', array( $this, 'sync_relations_on_update' ), 10, 4 );
			add_action( 'jet-engine/relation/delete/after', array( $this, 'sync_relations_on_delete' ), 10, 4 );

		}
	}

	public function filtered_relations_posts_items( $items, $post_type ) {

		if ( ! is_post_type_translated( $post_type ) ) {
			return $items;
		}

		global $sitepress;

		$current_lang = $sitepress->get_current_language();

		$items = array_filter( $items, function ( $item ) use ( $sitepress, $post_type, $current_lang ) {
			$lang = $sitepress->get_language_for_element( $item['value'], 'post_' . $post_type );
			return $current_lang === $lang;
		} );

		return $items;
	}

	public function translate_relations_labels( $args ) {

		if ( empty( $args['labels'] ) ) {
			return $args;
		}

		global $sitepress;

		$relation_name = ! empty( $args['labels']['name'] ) ? $args['labels']['name'] : esc_html__( 'Relation Label', 'jet-engine' );
		$lang          = method_exists( $sitepress, 'get_current_language' ) ? $sitepress->get_current_language() : null;

		foreach ( $args['labels'] as $key => $label ) {

			if ( 'name' === $key ) {
				continue;
			}

			if ( empty( $label ) ) {
				continue;
			}

			do_action( 'wpml_register_single_string', 'Jet Engine Relations Labels', $relation_name . ' - ' . $label, $label );
			$args['labels'][ $key ] = apply_filters( 'wpml_translate_single_string', $label, 'Jet Engine Relations Labels', $relation_name . ' - ' . $label, $lang );
		}

		return $args;
	}

	public function sync_relations_on_make_duplicate( $original_id, $lang, $post_array, $translated_id ) {
		$this->sync_relations_items( $original_id, $translated_id, $lang );
	}

	public function sync_relations_on_translation_completed( $translated_id, $fields, $job ) {
		$original_id = ! empty( $job->original_doc_id ) ? $job->original_doc_id : false;
		$lang        = ! empty( $job->language_code ) ? $job->language_code : null;

		if ( empty( $original_id ) ) {
			return;
		}

		$this->sync_relations_items( $original_id, $translated_id, $lang );
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

				if ( in_array( $obj_type, array( 'posts', 'terms' ) ) ) {
					$new_rel_item = apply_filters( 'wpml_object_id', $rel_item, $obj_sub_type, true, $lang );
				} else {
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

		$parent_translations = $this->get_item_translations( $parent_id, $parent_obj_data[1] );
		$child_translations  = $this->get_item_translations( $child_id, $child_obj_data[1] );

		remove_action( 'jet-engine/relation/update/after', array( $this, 'sync_relations_on_update' ) );

		foreach ( $parent_translations as $lang => $translation ) {

			if ( $translation->element_id == $parent_id ) {
				continue;
			}

			if ( ! isset( $child_translations[ $lang ] ) ) {
				continue;
			}

			$child_trans_id = $child_translations[ $lang ]->element_id;

			$relation->update( $translation->element_id, $child_trans_id );
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

		$parent_translations = $this->get_item_translations( $parent_id, $parent_obj_data[1] );
		$child_translations  = $this->get_item_translations( $child_id, $child_obj_data[1] );

		remove_action( 'jet-engine/relation/delete/after', array( $this, 'sync_relations_on_delete' ) );

		foreach ( $parent_translations as $lang => $translation ) {

			if ( $translation->element_id == $parent_id ) {
				continue;
			}

			if ( ! isset( $child_translations[ $lang ] ) ) {
				continue;
			}

			$rel_items      = $relation->get_children( $translation->element_id, 'ids' );
			$child_trans_id = $child_translations[ $lang ]->element_id;

			if ( ! in_array( $child_trans_id, $rel_items ) ) {
				continue;
			}

			$relation->delete_rows( $translation->element_id, $child_trans_id );
		}

		add_action( 'jet-engine/relation/delete/after', array( $this, 'sync_relations_on_delete' ), 10, 4 );
	}

	public function is_item_translated( $type = null, $obj_type = 'posts' ) {

		switch ( $obj_type ) {
			case 'posts':
				$is_translated = is_post_type_translated( $type );
				break;

			case 'terms':
				$is_translated = is_taxonomy_translated( $type );
				break;

			default:
				$is_translated = false;
		}

		return $is_translated;
	}

	public function get_item_translations( $id, $type ) {
		$elem_type = apply_filters( 'wpml_element_type', $type );
		$trid      = apply_filters( 'wpml_element_trid', false, $id, $elem_type );

		return apply_filters( 'wpml_get_element_translations', array(), $trid, $elem_type );
	}

	/**
	 * Set translated related posts
	 *
	 * @param  mixed $ids
	 * @return mixed
	 */
	public function set_translated_related_posts( $ids ) {

		if ( is_array( $ids ) ) {
			foreach ( $ids as $id ) {
				$ids[ $id ] = apply_filters( 'wpml_object_id', $id, get_post_type( $id ), true );
			}
		} else {
			$ids = apply_filters( 'wpml_object_id', $ids, get_post_type( $ids ), true );
		}

		return $ids;
	}

	/**
	 * Returns the instance.
	 *
	 * @access public
	 * @return object
	 */
	public static function instance( $package = null ) {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self( $package );
		}

		return self::$instance;

	}
	
}
