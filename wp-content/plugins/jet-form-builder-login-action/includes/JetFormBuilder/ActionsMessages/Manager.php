<?php


namespace Jet_FB_Login\JetFormBuilder\ActionsMessages;

use Jet_Form_Builder\Form_Messages\Status_Info;

class Manager {

	public static function register() {
		add_filter(
			'jet-form-builder/form-messages/register',
			array( self::class, 'add_action_messages' )
		);

		add_action(
			'jet-form-builder/response-status/init',
			array( self::class, 'make_reset_message_as_success' )
		);
	}

	public static function add_action_messages( array $types ): array {
		array_push(
			$types,
			new UserLoginMessages(),
			new ResetPasswordMessages()
		);

		return $types;
	}

	public static function make_reset_message_as_success( Status_Info $info ) {
		if ( 'success_reset' !== $info->get_message() ) {
			return;
		}

		$info->set_is_success( true );
	}

}
