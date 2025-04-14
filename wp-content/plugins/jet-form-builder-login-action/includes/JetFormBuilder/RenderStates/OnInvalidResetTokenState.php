<?php


namespace Jet_FB_Login\JetFormBuilder\RenderStates;


use Jet_Form_Builder\Blocks\Conditional_Block\Render_States\Base_Render_State;

class OnInvalidResetTokenState extends Base_Render_State {

	const ID = 'INVALID.RESET.TOKEN';

	public function get_id(): string {
		return self::ID;
	}

	public function get_title(): string {
		return __( 'On invalid token for reset user password', 'jet-form-builder-login-action' );
	}

	public function is_supported(): bool {
		return true;
	}

	/**
	 * This state can be only computed from RESET.PASSWORD state
	 *
	 * @return bool
	 */
	public function is_supported_on_current(): bool {
		return false;
	}


}