<?php


namespace JetLoginCore;


class BaseFormFieldsManager {

	protected $_fields = array();

	public function fields() {
		return [];
	}

	/**
	 * @return array
	 */
	public function get_fields() {
		if ( ! $this->_fields ) {
			$this->_fields = $this->fields();
		}

		return $this->_fields;
	}

}