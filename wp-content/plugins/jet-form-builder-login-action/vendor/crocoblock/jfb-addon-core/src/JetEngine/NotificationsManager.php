<?php


namespace JetLoginCore\JetEngine;

abstract class NotificationsManager {

	use WithInit;

	public $notifications = array();

	/**
	 * @return array
	 */
	abstract public function register_notification();

	public function plugin_version_compare() {
		return '2.8.1';
	}

	public function on_plugin_init() {
		add_action( 'init', array( $this, 'setup_notifications' ) );

		add_filter(
			'jet-engine/forms/booking/notification-types',
			array( $this, 'register_notifications' )
		);
		add_action(
			'jet-engine/forms/editor/before-assets',
			array( $this, 'register_assets_before' )
		);

		add_action(
			'jet-engine/forms/editor/assets',
			array( $this, 'maybe_localize_notifications' ), 0
		);

		add_action(
			'jet-engine/forms/editor/assets',
			array( $this, 'register_assets' )
		);
	}

	public function register_assets() {
	}

	public function register_assets_before() {
	}

	public function setup_notifications() {
		$types = $this->register_notification();

		foreach ( $types as $type ) {
			if ( ! $type->dependence() ) {
				continue;
			}
			
			$this->notifications[ $type->get_id() ] = $type;

			add_action(
				'jet-engine/forms/booking/notifications/fields-after',
				array( $type, 'notification_fields' )
			);

			add_action(
				'jet-engine/forms/booking/notification/' . $type->get_id(),
				array( $type, 'do_action' ), 10, 2
			);
		}
	}


	/**
	 * Register new notification type
	 *
	 * @param $notifications
	 *
	 * @return mixed [type] [description]
	 */
	public function register_notifications( $notifications ) {
		foreach ( $this->notifications as $type ) {
			$notifications[ $type->get_id() ] = $type->get_name();
		}

		return $notifications;
	}

	public function maybe_localize_notifications() {
		$prepared = array();
		$methods  = array(
			array(
				'name' => 'get_id',
				'prop' => 'id'
			),
			array(
				'name' => 'editor_labels',
				'prop' => '__labels',
			),
			array(
				'name' => 'editor_labels_help',
				'prop' => '__help_messages',
			),
			array(
				'name' => 'visible_attributes_for_gateway_editor',
				'prop' => '__gateway_attrs',
			),
			array(
				'name' => 'action_data',
			),
		);

		foreach ( $this->notifications as $type ) {
			$methods = $this->get_exist_methods( $type, $methods );
			if ( empty( $methods ) ) {
				continue;
			}
			$prepared[] = $this->prepare_localize_notification( $type, $methods );
		}

		if ( empty( $prepared ) ) {
			return;
		}

		wp_add_inline_script( 'jet-engine-forms', "
			window.jetFormActionTypes = window.jetFormActionTypes || [];
			window.jetFormActionTypes.push( ..." . wp_json_encode( $prepared ) . " ) 
		" );
	}

	/**
	 * @param NotificationLocalize & SmartBaseNotification $notification
	 *
	 * @param $methods
	 *
	 * @return array
	 */
	public function prepare_localize_notification( $notification, $methods ) {
		$response = array();

		foreach ( $methods as $method ) {
			if ( empty( $method['prop'] ) ) {
				$response = array_merge(
					$response,
					call_user_func( array( $notification, $method['name'] ) )
				);

				continue;
			}

			$response[ $method['prop'] ] = call_user_func( array( $notification, $method['name'] ) );
		}

		return $response;
	}

	public function get_exist_methods( $object_or_class_name, $method_names ) {
		return array_filter( $method_names, function ( $method ) use ( $object_or_class_name ) {
			return method_exists( $object_or_class_name, $method['name'] );
		} );
	}

}