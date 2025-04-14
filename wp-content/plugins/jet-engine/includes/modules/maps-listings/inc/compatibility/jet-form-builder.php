<?php
namespace Jet_Engine\Modules\Maps_Listings\Compatibility;

use Jet_Engine\Modules\Maps_Listings\Map_Field;

class Jet_Form_Builder {

	/**
	 * @var array
	 */
	private $preloaded_posts = array();

	/**
	 * @var array
	 */
	private $preloaded_users = array();

	/**
	 * @var bool
	 */
	private $hooks_added = false;

	public function __construct() {
		add_action( 'jet-form-builder/action/after-post-insert', array( $this, 'update_post_service_map_fields' ), 10, 2 );
		add_action( 'jet-form-builder/action/after-post-update', array( $this, 'update_post_service_map_fields' ), 10, 2 );

		add_filter( 'insert_custom_user_meta', array( $this, 'update_user_service_map_fields' ) );

		add_filter( 'pre_update_option', array( $this, 'update_option_service_map_fields' ), 10, 2 );

		add_filter( 'jet-engine/custom-content-types/item-to-update', array( $this, 'update_cct_service_map_fields' ), 10, 3 );

		add_action( 'jet-form-builder/before-trigger-event', array( $this, 'add_hooks' ) );
		add_action( 'jet-form-builder/after-trigger-event', array( $this, 'run_preload' ) );
	}

	/**
	 * Add hooks to store user IDs to run preload later
	 *
	 * @param \Jet_Form_Builder\Actions\Events\Base_Event $event JetFormBuilder event instance
	 */
	public function add_hooks() {
		if ( $this->hooks_added ) {
			return;
		}

		//store post ID any time wp_insert_post() or wp_update_post() runs during form submit
		//this ensures that for posts, those created in custom hooks included, preload is triggered properly
		add_action( 'save_post', array( $this, 'store_post_id' ) );

		//store user ID any time user is created or updated during form submit
		//this ensures that for users, created / updated in custom hooks included, preload is triggered properly
		add_action( 'user_register', array( $this, 'store_user_id' ) );
		add_action( 'wp_update_user', array( $this, 'store_user_id' ) );

		$this->hooks_added = true;
	}

	/**
	 * Trigger preload groups for posts after form actions
	 * https://github.com/Crocoblock/issues-tracker/issues/12455
	 *
	 * @param \Jet_Form_Builder\Actions\Events\Base_Event $event JetFormBuilder event instance
	 */
	public function run_preload( $event ) {
		$this->preload_custom_posts();
		$this->preload_custom_users();
	}

	/**
	 * Trigger preload groups for posts after form actions
	 */
	public function preload_custom_posts() {
		foreach ( $this->preloaded_posts as $post_id => $done ) {
			if ( $done ) {
				continue;
			}

			do_action( 'jet-engine/maps-listing/preload/force/custom-posts', $post_id );
			$this->preloaded_posts[ $post_id ] = true;
		}
	}

	/**
	 * Trigger preload groups for users after form actions
	 */
	public function preload_custom_users() {
		foreach ( $this->preloaded_users as $user_id => $done ) {
			if ( $done ) {
				continue;
			}

			do_action( 'jet-engine/maps-listing/preload/force/custom-users', $user_id );
			$this->preloaded_users[ $user_id ] = true;
		}
	}

	/**
	 * Store post ID if post created / updated in the form
	 *
	 * @param int $post_id User ID
	 */
	public function store_post_id( $post_id ) {
		$this->preloaded_posts[ $post_id ] = false;
	}

	/**
	 * Store user ID if user created / updated in the form
	 *
	 * @param int $user_id User ID
	 */
	public function store_user_id( $user_id ) {
		$this->preloaded_users[ $user_id ] = false;
	}

	public function update_post_service_map_fields( $action, $handler ) {

		$service_values = $this->get_service_fields_values_from_request();

		if ( empty( $service_values ) ) {
			return;
		}

		$post_id = $handler->get_inserted_post_id( $action->_id );

		foreach ( $service_values as $field => $value ) {
			update_post_meta( $post_id, $field, $value );
		}
	}

	public function update_user_service_map_fields( $meta ) {

		if ( ! jet_fb_action_handler()->in_loop() ) {
			return $meta;
		}

		$action = jet_fb_action_handler()->get_current_action();

		if ( ! in_array( $action->get_id(), array( 'register_user', 'update_user' ) ) ) {
			return $meta;
		}

		$service_values = $this->get_service_fields_values_from_request();

		if ( empty( $service_values ) ) {
			return $meta;
		}

		$meta = array_merge( $meta, $service_values );

		return $meta;
	}

	public function update_option_service_map_fields( $value, $option ) {

		if ( ! jet_fb_action_handler()->in_loop() ) {
			return $value;
		}

		$action = jet_fb_action_handler()->get_current_action();

		if ( 'update_options' !== $action->get_id() ) {
			return $value;
		}

		$settings = $action->settings;

		if ( empty( $settings['options_page'] ) || $settings['options_page'] !== $option ) {
			return $value;
		}

		$service_values = $this->get_service_fields_values_from_request();

		if ( empty( $service_values ) ) {
			return $value;
		}

		$value = (array) $value;

		$value = array_merge( $value, $service_values );

		return $value;
	}

	public function update_cct_service_map_fields( $item, $fields, $cct ) {

		if ( ! jet_fb_action_handler()->in_loop() ) {
			return $item;
		}

		$action = jet_fb_action_handler()->get_current_action();

		if ( 'insert_custom_content_type' !== $action->get_id() ) {
			return $item;
		}

		$settings = $action->settings;
		$cct_slug = $cct->get_factory()->get_arg( 'slug' );

		if ( empty( $settings['type'] ) || $settings['type'] !== $cct_slug ) {
			return $item;
		}

		$service_values = $this->get_service_fields_values_from_request( false );

		$item = array_merge( $item, $service_values );

		return $item;
	}

	public function get_service_fields_values_from_request( $hash_prefix = true ) {

		$service_values = array();

		$action   = jet_fb_action_handler()->get_current_action();
		$settings = $action->settings;

		$fields_map_key = 'fields_map';

		if ( in_array( $action->get_id(), array( 'update_options', 'register_user' ) ) ) {
			$fields_map_key = 'meta_fields_map';
		}

		if ( empty( $settings[ $fields_map_key ] ) ) {
			return $service_values;
		}

		$request = jet_fb_action_handler()->request_data;

		foreach ( $settings[ $fields_map_key ] as $form_field => $post_field ) {
			$is_map_field = jet_fb_request_handler()->is_type( $form_field, 'map-field' );

			if ( ! $is_map_field ) {
				continue;
			}

			$prefix = $post_field;

			if ( $hash_prefix ) {
				$prefix = Map_Field::get_field_prefix( $prefix );
			}

			$service_values[ $prefix . '_hash' ] = ! empty( $request[ $form_field ] ) ? md5( $request[ $form_field ] ) : false;
			$service_values[ $prefix . '_lat' ]  = ! empty( $request[ $form_field . '_lat' ] ) ? $request[ $form_field . '_lat' ] : false;
			$service_values[ $prefix . '_lng' ]  = ! empty( $request[ $form_field . '_lng' ] ) ? $request[ $form_field . '_lng' ] : false;
		}

		return $service_values;
	}

}
