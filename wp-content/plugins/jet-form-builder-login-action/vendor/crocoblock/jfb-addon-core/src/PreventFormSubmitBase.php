<?php


namespace JetLoginCore;


abstract class PreventFormSubmitBase {

	public static function register() {
		$instance = new static();

		add_action( $instance->action_init(), function () use ( $instance ) {
			if ( $instance->can_init() ) {
				$instance->manage_hooks();
			}
		} );
	}


	/**
	 * Can be overridden in JetLoginCore space
	 *
	 * @return boolean
	 */
	abstract public function can_init();

	/**
	 * Can be overridden in JetLoginCore space
	 *
	 * @return array
	 */
	abstract public function manage_hooks_data();

	/**
	 * Can be overridden in JetLoginCore space
	 *
	 * @return string
	 */
	abstract public function action_init();

	/**
	 * Can be overridden in client code space
	 *
	 * @param $handler
	 *
	 * @return void
	 */
	abstract public function prevent_process_ajax_form( $handler );

	/**
	 * Can be overridden in client code space
	 *
	 * @param $handler
	 *
	 * @return void
	 */
	abstract public function prevent_process_reload_form( $handler );

	public function manage_hooks() {
		$handler = $this->manage_hooks_data()[0];
		$action_name = $this->manage_hooks_data()[1];

		if ( wp_doing_ajax() ) {
			add_action(
				'wp_ajax_' . $action_name,
				array( $this, '_prevent_ajax_submit' ), -100
			);
			add_action(
				'wp_ajax_nopriv_' . $action_name,
				array( $this, '_prevent_ajax_submit' ), -100
			);

			return;
		} elseif ( isset( $_REQUEST[ $handler->hook_key ] ) && $handler->hook_val === $_REQUEST[ $handler->hook_key ] ) {
			add_action(
				'wp_loaded',
				array( $this, '_prevent_reload_submit' ), -100
			);
		}
	}

	public function _prevent_ajax_submit() {
		$handler = $this->manage_hooks_data()[0];

		$handler->is_ajax = true;
		$handler->setup_form();

		$this->prevent_process_ajax_form( $handler );
	}

	public function _prevent_reload_submit() {
		$handler = $this->manage_hooks_data()[0];

		$handler->setup_form();

		$this->prevent_process_reload_form( $handler );
	}

}