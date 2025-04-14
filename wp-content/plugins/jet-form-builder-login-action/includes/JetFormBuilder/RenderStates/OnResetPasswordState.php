<?php


namespace Jet_FB_Login\JetFormBuilder\RenderStates;

use Jet_FB_Login\JetFormBuilder\ResetPassword;
use Jet_FB_Login\JetFormBuilder\ResetPasswordException;
use Jet_FB_Login\JetFormBuilder\ResetPasswordTools;
use Jet_Form_Builder\Blocks\Conditional_Block\Render_States\Base_Render_State;
use Jet_Form_Builder\Blocks\Conditional_Block\Render_States\Render_State_Replace_Exception;
use Jet_Form_Builder\Classes\Http\Http_Tools;

class OnResetPasswordState extends Base_Render_State {

	const ID = 'RESET.PASSWORD';

	protected $reset;

	/**
	 * @return string
	 */
	public function get_id(): string {
		return self::ID;
	}

	/**
	 * @return string
	 */
	public function get_title(): string {
		return __( 'On reset user password', 'jet-form-builder-login-action' );
	}

	/**
	 * @return bool
	 * @throws Render_State_Replace_Exception
	 */
	public function is_supported(): bool {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$query = Http_Tools::get_query();
		$key   = sanitize_key( $query[ ResetPassword::ACTION ] ?? '' );

		if ( empty( $key ) ) {
			return false;
		};

		$uid = absint( $query['uid'] ?? 0 );

		try {
			$this->reset = ResetPasswordTools::get_reset( $key, $uid );
		} catch ( ResetPasswordException $exception ) {
			throw ( new Render_State_Replace_Exception() )->with(
				new OnInvalidResetTokenState()
			);
		}

		return true;
	}

	public function get_reset(): ResetPassword {
		return $this->reset;
	}
}
