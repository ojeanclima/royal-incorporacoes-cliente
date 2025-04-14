<?php


namespace JetLoginCore\JetFormBuilder;


use JetLoginCore\PreventFormSubmitBase;

abstract class PreventFormSubmit extends PreventFormSubmitBase {

	public function can_init() {
		return function_exists( 'jet_form_builder' );
	}

	public function action_init() {
		return 'after_setup_theme';
	}

	public function manage_hooks_data() {
		return array(
			jet_form_builder()->form_handler,
			jet_form_builder()->form_handler->hook_key,
		);
	}

}