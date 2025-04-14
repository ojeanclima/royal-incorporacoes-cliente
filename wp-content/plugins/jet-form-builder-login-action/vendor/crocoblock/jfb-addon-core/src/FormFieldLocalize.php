<?php


namespace JetLoginCore;


trait FormFieldLocalize {

	/**
	 * @return string
	 */
	abstract public function handler_for_localize();

	public function maybe_localize_block_data() {
		$prepared = array();
		$methods  = array(
			array(
				'name' => 'editor_labels',
				'prop' => '__labels',
			),
			array(
				'name' => 'editor_help',
				'prop' => '__help',
			),
			array(
				'name' => 'editor_data',
			),
		);

		foreach ( $this->get_fields() as $type ) {
			$methods = $this->get_exist_methods( $type, $methods );
			if ( empty( $methods ) ) {
				continue;
			}
			$prepared[ $type->get_name() ] = $this->prepare_localize_notification( $type, $methods );
		}

		if ( empty( $prepared ) ) {
			return;
		}

		wp_add_inline_script( $this->handler_for_localize(), "
			window.JetFormBuilderFields = window.JetFormBuilderFields || {};
			window.JetFormBuilderFields = { ...window.JetFormBuilderFields, ..." . wp_json_encode( $prepared ) . " }; 
		" );
	}

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