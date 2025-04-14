<?php


namespace Jet_FB_Login\JetFormBuilder;


use Jet_FB_Login\JetFormBuilder\RenderStates\OnInvalidResetTokenState;
use Jet_FB_Login\JetFormBuilder\RenderStates\OnResetPasswordState;

class RenderStatesManager {

	public static function register() {
		if ( ! Tools::is_3_version() ) {
			return;
		}
		add_filter( 'jet-form-builder/render-states', array( self::class, 'add_states' ) );
	}

	public static function add_states( array $states ): array {
		array_push(
			$states,
			new OnResetPasswordState(),
			new OnInvalidResetTokenState()
		);

		return $states;
	}

}