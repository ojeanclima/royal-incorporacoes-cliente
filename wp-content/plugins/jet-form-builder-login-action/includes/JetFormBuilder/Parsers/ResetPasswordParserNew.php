<?php


namespace Jet_FB_Login\JetFormBuilder\Parsers;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

use Jet_FB_Login\JetFormBuilder\Actions\ResetAction;
use JFB_Modules\Block_Parsers\Field_Data_Parser;
use JFB_Modules\Block_Parsers\Fields\Default_Parser;
use JFB_Modules\Block_Parsers\Interfaces\Exclude_Self_Parser;
use JFB_Modules\Block_Parsers\Interfaces\Multiple_Parsers;

/**
 * @since 2.0.1
 *
 * Class ResetPasswordParserNew
 * @package Jet_FB_Login\JetFormBuilder\Parsers
 */
class ResetPasswordParserNew extends Field_Data_Parser implements Exclude_Self_Parser, Multiple_Parsers {

	public function type() {
		return 'reset-password';
	}

	public function generate_parsers(): \Generator {
		$user_field = new Default_Parser();
		$user_field->set_type( 'text-field' );
		$user_field->set_context( $this->get_context() );

		$password_field = clone $user_field;
		$password_field->make_secure();

		$confirm_password_field = clone $password_field;

		$user_field->set_name( $this->settings['user_name'] ?? 'user_login_or_email' );
		$password_field->set_name( $this->settings['password_name'] ?? 'new_password' );
		$confirm_password_field->set_name( $this->settings['confirm_password_name'] ?? 'confirm_new_password' );

		$user_field->set_setting( 'label', $this->settings['user_label'] ?? 'User login/email' );
		$password_field->set_setting( 'label', $this->settings['password_label'] ?? 'Input new password' );
		$confirm_password_field->set_setting( 'label', $this->settings['confirm_password_label'] ?? 'Confirm new password' );

		yield $user_field;
		yield $password_field;
		yield $confirm_password_field;

		if ( ! did_action( 'jet-form-builder/request' ) ) {
			return;
		}

		$action = ResetAction::add_hidden();

		if ( $action ) {
			$action->settings = array(
				'user_field'             => $user_field->get_name(),
				'password_field'         => $password_field->get_name(),
				'confirm_password_field' => $confirm_password_field->get_name(),
			);
		}
	}
}
