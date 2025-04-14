<?php

namespace Jet_FB_Login\JetFormBuilder\Actions;

use Jet_FB_Login\JetFormBuilder\ActionsMessages\UserLoginMessages;
use Jet_Form_Builder\Actions\Action_Handler;
use Jet_Form_Builder\Actions\Types\Base;
use Jet_Form_Builder\Exceptions\Action_Exception;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Define Base_Type class
 */
class Action extends Base {

	private $message_store;

	public function __construct() {
		parent::__construct();

		// shouldn't be cloned
		$this->message_store = new UserLoginMessages();
	}

	public function get_id() {
		return 'login';
	}

	public function get_name() {
		return __( 'User Login', 'jet-form-builder-login-action' );
	}

	/**
	 * @param array $request
	 * @param Action_Handler $handler
	 *
	 * @return void
	 * @throws Action_Exception
	 */
	public function do_action( array $request, Action_Handler $handler ) {
		$credentials   = $this->get_credentials();
		$secure_cookie = (bool) ( $this->settings['secure_cookie'] ?? true );

		$user = wp_signon( $credentials, $secure_cookie );

		if ( ! ( $user instanceof \WP_Error ) ) {
			wp_set_current_user( $user->ID );

			return;
		}

		if ( empty( $this->settings['use_custom_errors'] ) ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
			throw new Action_Exception( $user->get_error_message() );
		}

		$messages = $this->message_store->get_messages();

		if ( ! array_key_exists(
			UserLoginMessages::PREFIX . $user->get_error_code(),
			$messages
		) ) {
			throw new Action_Exception( 'failed' );
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
		throw new Action_Exception( UserLoginMessages::PREFIX . $user->get_error_code() );
	}

	public function get_credentials(): array {
		$fields        = array(
			'user_login'    => 'user_login_field',
			'user_password' => 'user_pass_field',
			'remember'      => 'remember_field',
		);
		$fields_values = array();

		foreach ( $fields as $name => $settings_name ) {
			$field = $this->settings[ $settings_name ] ?? '';

			if ( ! jet_fb_context()->has_field( $field ) ) {
				continue;
			}
			$fields_values[ $name ] = jet_fb_context()->get_value( $field );
		}

		return $fields_values;
	}
}
