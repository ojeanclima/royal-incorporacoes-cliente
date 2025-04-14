<?php


namespace Jet_FB_Login\JetFormBuilder\Parsers;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

use Jet_FB_Login\JetFormBuilder\Actions\ResetAction;
use Jet_Form_Builder\Exceptions\Parse_Exception;
use Jet_Form_Builder\Request\Field_Data_Parser;

/**
 * @deprecated 2.0.1
 *
 * Class ResetPasswordParser
 * @package Jet_FB_Login\JetFormBuilder\Parsers
 */
class ResetPasswordParser extends Field_Data_Parser {

	public function type() {
		return 'reset-password';
	}

	public function parse_value( $value ) {
		$request  = $this->context->get_request();
		$response = array();

		$user_field             = $this->settings['user_name'] ?? 'user_login_or_email';
		$password_field         = $this->settings['password_name'] ?? 'new_password';
		$confirm_password_field = $this->settings['confirm_password_name'] ?? 'confirm_new_password';

		foreach ( array( $user_field, $password_field, $confirm_password_field ) as $field ) {
			$response[ $field ] = $request[ $field ] ?? '';
		}

		if ( empty( $response ) ) {
			return $response;
		}

		$settings = array(
			'user_field'             => $user_field,
			'password_field'         => $password_field,
			'confirm_password_field' => $confirm_password_field,
		);

		$action = ResetAction::add_hidden();

		if ( $action ) {
			$action->settings = $settings;
		}

		return $response;
	}

	/**
	 * @throws Parse_Exception
	 */
	public function get_response() {
		throw new Parse_Exception( 'Throw reset-password computed fields', $this->value );
	}

}