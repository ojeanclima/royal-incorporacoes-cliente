<?php


namespace Jet_FB_Login\JetFormBuilder\Parsers;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Manager {

	/**
	 * Supported only >= 3.4.0 JetFormBuilder
	 *
	 * @return void
	 */
	public static function register() {
		if ( ! class_exists( '\JFB_Modules\Actions_V2\Module' ) ) {
			return;
		}
		add_filter( 'jet-form-builder/parsers-request/register', array( self::class, 'add_parsers' ) );
	}

	public static function add_parsers( array $parsers ): array {
		$parsers[] = new ResetPasswordParserNew();

		return $parsers;
	}

}
