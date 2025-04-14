<?php


namespace Jet_FB_Login\JetFormBuilder\ActionsMessages;

use Jet_FB_Login\JetFormBuilder\Actions\Action;
use Jet_Form_Builder\Actions\Types\Base;
use Jet_Form_Builder\Form_Messages\Actions\Base_Action_Messages;

class UserLoginMessages extends Base_Action_Messages {

	const PREFIX = 'ul_';

	public function is_supported( Base $action ): bool {
		return is_a( $action, Action::class );
	}

	protected function messages(): array {
		return array(
			/** @see \wp_authenticate_username_password */
			self::PREFIX . 'invalid_username'   => array(
				'label' => __( 'Incorrect username', 'jet-form-builder' ),
				'value' => 'The username is not registered on this site. If you are unsure of your username, try your email address instead',
			),
			/** @see \wp_authenticate_email_password */
			self::PREFIX . 'invalid_email'      => array(
				'label' => __( 'Incorrect email', 'jet-form-builder' ),
				'value' => 'Unknown email address. Check again or try your username.',
			),
			// both
			self::PREFIX . 'empty_username'     => array(
				'label' => __( 'Empty username', 'jet-form-builder' ),
				'value' => 'The username field is empty',
			),
			self::PREFIX . 'empty_password'     => array(
				'label' => __( 'Empty password', 'jet-form-builder' ),
				'value' => 'The password field is empty',
			),
			self::PREFIX . 'incorrect_password' => array(
				'label' => __( 'Incorrect password', 'jet-form-builder' ),
				'value' => 'The password you entered is incorrect',
			),
		);
	}
}
