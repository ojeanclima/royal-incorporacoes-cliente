<?php


namespace JetLoginCore;


use JetLoginCore\Exceptions\BaseHandlerException;

trait SmartNotificationActionTrait {

	protected $_requestData;
	protected $_instance;
	protected $_settings;

	abstract public function run_action();

	abstract public function provider_slug();

	abstract public function setRequest( $key, $value );

	abstract public function hasGateway();

	abstract public function getFormId();

	abstract public function isAjax();

	abstract public function addResponse( $response_arr );

	public function getInstance() {
		return $this->_instance;
	}

	public function getSettingsWithGlobal() {
		throw new BaseHandlerException( 'failed', __METHOD__ );
	}

	public function getGlobalOptionName() {
		return $this->get_id();
	}

	public function parseDynamicException( $type, $message ): string {
		return $message;
	}

	public function getRequest( $key = '', $ifNotExist = false ) {
		if ( ! $key ) {
			return $this->_requestData;
		}
		return isset( $this->_requestData[ $key ] ) ? $this->_requestData[ $key ] : $ifNotExist;
	}

	public function issetRequest( $key ) {
		return isset( $this->_requestData[ $key ] );
	}

	public function getSettings( $key = '', $ifNotExist = false ) {
		if ( ! $key ) {
			return $this->_settings;
		}
		return isset( $this->_settings[ $key ] ) ? $this->_settings[ $key ] : $ifNotExist;
	}

	/**
	 * @param $message
	 * @param mixed ...$additional
	 *
	 * @throws BaseHandlerException
	 */
	public function dynamicError( $message, ...$additional ) {
		throw new BaseHandlerException( $message, 'error', ...$additional );
	}

	/**
	 * @param $message
	 * @param mixed ...$additional
	 *
	 * @throws BaseHandlerException
	 */
	public function dynamicSuccess( $message, ...$additional ) {
		throw new BaseHandlerException( $message, 'success', ...$additional );
	}

	/**
	 * @param $status
	 * @param mixed ...$additional
	 *
	 * @throws BaseHandlerException
	 */
	public function error( $status, ...$additional ) {
		throw new BaseHandlerException( $status, '', ...$additional );
	}

	public function debug( ...$additional ) {
		new BaseHandlerException( 'debug', '', ...$additional );
	}

	protected function applyFilters( $suffix, ...$params ) {
		return apply_filters(
			"jet-form-builder/action/{$this->get_id()}/{$suffix}",
			...$params
		);
	}

}