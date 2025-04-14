<?php


namespace JetLoginCore\JetEngine;

use JetLoginCore\Exceptions\BaseHandlerException;
use JetLoginCore\SmartNotificationActionTrait;

abstract class SmartBaseNotification extends BaseNotification {

	use SmartNotificationActionTrait;

	public function provider_slug() {
		return 'jef';
	}

	public function setRequest( $key, $value ) {
		$this->getInstance()->data[ $key ]               = $value;
		$this->getInstance()->handler->form_data[ $key ] = $value;

		return $this;
	}

	public function hasGateway() {
		return $this->getInstance()->handler->has_gateway();
	}

	public function getFormId() {
		return $this->getInstance()->form;
	}

	public function isAjax() {
		return $this->getInstance()->handler->is_ajax;
	}

	public function getSettingsWithGlobal() {
		$settings = $this->getSettings();

		if ( empty( $settings ) ) {
			throw new BaseHandlerException( 'failed' );
		}

		return $this->getInstance()->get_settings_with_global(
			$settings,
			$this->getGlobalOptionName()
		);
	}

	public function addResponse( $response_arr ) {
		$this->getInstance()->handler->add_response_data( $response_arr );
	}

	/**
	 * @inheritDoc
	 */
	public function do_action( array $settings, $notifications ) {
		try {
			$this->_requestData = $notifications->data;
			$this->_instance    = $notifications;
			$this->_settings    = isset( $settings[ $this->get_id() ] )
				? $settings[ $this->get_id() ]
				: array();

			$this->run_action();

			$notifications->log[] = true;

		} catch ( BaseHandlerException $exception ) {
			return $notifications->set_specific_status( $exception->getMessage() );
		}
	}
}
