<?php


namespace JetLoginCore\JetEngine;


trait NotificationLocalize {

	public function visible_attributes_for_gateway_editor() {
		return array();
	}

	public function editor_labels() {
		return array();
	}

	public function editor_labels_help() {
		return array();
	}

	/**
	 * Register custom action data
	 *
	 * @return array [description]
	 */
	public function action_data() {
		return array();
	}

}