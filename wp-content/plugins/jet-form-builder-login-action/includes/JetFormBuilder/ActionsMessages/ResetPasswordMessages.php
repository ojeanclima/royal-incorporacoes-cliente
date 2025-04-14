<?php


namespace Jet_FB_Login\JetFormBuilder\ActionsMessages;

use Jet_FB_Login\JetFormBuilder\Actions\ResetAction;
use Jet_Form_Builder\Actions\Types\Base;
use Jet_Form_Builder\Form_Messages\Actions\Base_Action_Messages;

class ResetPasswordMessages extends Base_Action_Messages {

	public function is_supported( Base $action ): bool {
		return is_a( $action, ResetAction::class );
	}

	protected function messages(): array {
		$messages = array(
			'password_mismatch' => array(
				'label' => __( 'Passwords mismatch', 'jet-form-builder' ),
				'value' => 'Passwords don\'t match.',
			),
			'sanitize_user'     => array(
				'label' => __( 'Incorrect login/email', 'jet-form-builder' ),
				'value' => 'Email address or login does not exist',
			),
		);

		if ( function_exists( 'jet_fb_context' ) ) {
			$messages['success_reset'] = array(
				'label' => __( 'Custom success message', 'jet-form-builder' ),
				'value' => 'Password changed successfully',
			);
		}

		return $messages;
	}
}
