<?php


namespace Jet_FB_Login\JetFormBuilder\Actions;

use Jet_FB_Login\JetFormBuilder\RenderStates\OnResetPasswordState;
use Jet_FB_Login\JetFormBuilder\ResetPassword;
use Jet_FB_Login\JetFormBuilder\ResetPasswordException;
use Jet_FB_Login\JetFormBuilder\ResetPasswordTools;
use Jet_FB_Login\Plugin;
use Jet_Form_Builder\Actions\Action_Handler;
use Jet_Form_Builder\Actions\Actions_Tools;
use Jet_Form_Builder\Actions\Types\Base;
use Jet_Form_Builder\Blocks\Conditional_Block\Render_State;
use Jet_Form_Builder\Db_Queries\Exceptions\Sql_Exception;
use Jet_Form_Builder\Exceptions\Action_Exception;
use Jet_Form_Builder\Form_Handler;

class ResetAction extends Base {

	const LINK    = '_reset_pass_link';
	const UID     = '_reset_pass_user_id';
	const U_EMAIL = '_reset_pass_user_email';

	public function get_id() {
		return 'reset_password';
	}

	public function get_name() {
		return __( 'Reset Password', 'jet-form-builder-login-action' );
	}

	/**
	 * @param array $request
	 * @param Action_Handler $handler
	 *
	 * @return void
	 * @throws Action_Exception
	 */
	public function do_action( array $request, Action_Handler $handler ) {
		if ( is_user_logged_in() ) {
			throw new Action_Exception( 'failed' );
		}
		$collection = Render_State::instance()->get_current();

		if ( $collection->in_array( OnResetPasswordState::class ) ) {
			$this->reset_password();
		} else {
			$this->generate_reset_link();
		}
	}

	/**
	 * @throws Action_Exception
	 */
	protected function generate_reset_link() {
		try {
			$user = $this->get_user();
		} catch ( ResetPasswordException $exception ) {
			throw new Action_Exception( 'sanitize_user' );
		}
		$reset = new ResetPassword( $user );

		try {
			$reset->save_hash();
		} catch ( ResetPasswordException $exception ) {
			throw new Action_Exception(
				'failed',
				$exception->getMessage(),
				...$exception->get_additional()
			);
		}

		$this->add_context_once( array( $reset, $user ) );

		jet_fb_action_handler()->add_request(
			array(
				self::LINK    => $reset->get_link(),
				self::UID     => $user->ID,
				self::U_EMAIL => $user->user_email,
			)
		);

		if ( function_exists( 'jet_fb_context' ) ) {
			jet_fb_context()->set_field_type( 'text-field', self::LINK );
		}

		// don't save secret key in record
		jet_fb_request_handler()->exclude( self::LINK );

		if ( ! empty( $this->settings['email'] ) ) {
			return;
		}

		$generator = Actions_Tools::get_flow(
			Plugin::instance()->plugin_dir( 'includes/JetFormBuilder/Actions/send.email.flow.json' )
		);

		foreach ( $generator as list( $action, $props ) ) {
			jet_fb_action_handler()->add( $action, $props );
		}
	}

	/**
	 * @throws Action_Exception
	 */
	protected function reset_password() {
		$reset = ResetPasswordTools::get_reset_state()->get_reset();

		$password_field         = $this->settings['password_field'] ?? false;
		$confirm_password_field = $this->settings['confirm_password_field'] ?? false;

		$password = jet_fb_action_handler()->request_data[ $password_field ] ?? '';
		$confirm  = jet_fb_action_handler()->request_data[ $confirm_password_field ] ?? '';

		if ( ! $password || $password !== $confirm ) {
			throw new Action_Exception( 'password_mismatch', 'invalid_passwords' );
		}
		$reset->update_password( $password );

		try {
			$reset->clear();
		} catch ( Sql_Exception $exception ) {
			throw new Action_Exception( $exception->getMessage(), ...$exception->get_additional() );
		}

		jet_fb_action_handler()->add_request(
			array(
				self::UID     => $reset->get_user()->ID,
				self::U_EMAIL => $reset->get_user()->user_email,
			)
		);

		if ( empty( $this->settings['custom_success'] ) || ! function_exists( 'jet_fb_context' ) ) {
			return;
		}

		add_action(
			'jet-form-builder/form-handler/after-send',
			array( $this, 'on_reset_form_after_send' ),
			10,
			2
		);
	}

	public function on_reset_form_after_send( Form_Handler $handler, $is_success ) {
		if ( ! $is_success ) {
			return;
		}
		$handler->set_response_args(
			array(
				'status' => 'success_reset',
			)
		);
	}

	/**
	 * @return \WP_User
	 * @throws ResetPasswordException
	 */
	protected function get_user(): \WP_User {
		$user_field = $this->settings['user_field'] ?? false;

		// get user login or email
		$user_identifier = jet_fb_action_handler()->request_data[ $user_field ] ?? '';

		return ResetPasswordTools::get_user( $user_identifier );
	}

	public function editor_labels() {
		return array(
			'user_field'             => __( 'User field', 'jet-form-builder' ),
			'password_field'         => __( 'Password field', 'jet-form-builder' ),
			'confirm_password_field' => __( 'Confirm password field', 'jet-form-builder' ),
		);
	}

	public function editor_labels_help() {
		return array(
			'user_field' => __(
				'This field can contain either the username or user\'s email address.',
				'jet-form-builder'
			),
		);
	}

	public function action_data() {
		return array(
			'action_label' => $this->get_name(),
		);
	}
}
