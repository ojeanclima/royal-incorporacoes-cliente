<?php


namespace JetLoginCore\Exceptions;


class BaseHandlerException extends \Exception {

	private $additional_data;
	private $type;

	public function __construct( $message = '', $type = '',...$additional_data ) {
		parent::__construct( $message, 0, null );

		$this->type = $type;
		$this->additional_data = $additional_data;
	}

	public function getAdditional() {
		return $this->additional_data;
	}

	public function set_code( $code ) {
		$this->code = $code;

		return $this;
	}

	public function type() {
		return $this->type;
	}

}