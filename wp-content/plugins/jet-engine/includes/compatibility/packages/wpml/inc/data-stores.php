<?php

namespace Jet_Engine\Compatibility\Packages\Jet_Engine_WPML_Package\Data_Stores;

use \Jet_Engine\Modules\Data_Stores\Stores\Factory;

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
	 * @var \Jet_Engine\Compatibility\Packages\Jet_Engine_WPML_Package\Package
	 */
	private $package;

	private function __construct( $package = null ) {
		$this->package = $package;

		if ( ! jet_engine()->modules->is_module_active( 'data-stores' ) ) {
			return;
		}

		add_filter( 'jet-engine/data-stores/store/data', array( $this, 'set_translated_store' ), 10, 2 );

		//ensure all translated items share the same count
		add_filter( 'jet-engine/data-stores/pre-get-post-count', array( $this, 'get_post_count' ), 10, 3 );

		add_action( 'jet-engine/data-stores/post-count-increased', array( $this, 'update_translations_count' ), 10, 3 );
		add_action( 'jet-engine/data-stores/post-count-decreased', array( $this, 'update_translations_count' ), 10, 3 );

		add_action( 'jet-engine/data-stores/filtered-id', array( $this, 'get_original_post_id' ), 10, 3 );
	}

	/**
	 * @param int     $post_id Post ID
	 * @param string  $store   Store slug
	 * @param Factory $factory Factory instance
	 */
	public function get_original_post_id( $post_id, $store, $factory ) {
		if ( $factory->is_user_store() || $factory->get_arg( 'is_cct' ) ) {
			return $post_id;
		}

		switch ( $factory->get_type()->type_id() ) {
			case 'user_ip':
				$post_id = $this->package->get_original_post_id( $post_id );
				break;
		}

		return $post_id;
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

		$original_id = $this->package->get_original_post_id( $post_id );

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

		$original_id = $this->package->get_original_post_id( $post_id );

		if ( ( int ) $original_id !== ( int ) $post_id ) {
			update_post_meta( $original_id, $factory->get_count_posts_key() . $factory->get_slug(), $count );
		} else {
			$type = apply_filters( 'wpml_element_type', get_post_type( $post_id ) );
			$trid = apply_filters( 'wpml_element_trid', false, $post_id, $type );
			
			$translations = apply_filters( 'wpml_get_element_translations', array(), $trid, $type );

			foreach ( $translations as $translation ) {
				$id = $translation->element_id ?? 0;

				if ( ! $id || $id === $post_id ) {
					continue;
				}
				
				update_post_meta( $id, $factory->get_count_posts_key() . $factory->get_slug(), $count );
			}
		}
	}

	/**
	 * Get post count for User IP store
	 * 
	 * @param int                                            $post_id Post ID
	 * @param \Jet_Engine\Modules\Data_Stores\Stores\Factory $factory Data Store factory
	 * 
	 * @return int|false Original post count or false if no need to pre-get post count
	 */
	public function get_user_ip_post_count( $post_id, $factory ) {
		$original_id = $this->package->get_original_post_id( $post_id );

		if ( $original_id === $post_id || ! get_post( $original_id ) ) {
			return false;
		}

		return $factory->get_post_count( $original_id );
	}

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
				$item = apply_filters( 'wpml_object_id', $item, get_post_type( $item ), true );
			}

			return $item;
		}, $store );

		return $store;
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
