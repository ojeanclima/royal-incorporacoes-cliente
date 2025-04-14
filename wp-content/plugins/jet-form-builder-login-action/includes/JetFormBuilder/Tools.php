<?php


namespace Jet_FB_Login\JetFormBuilder;


class Tools {

	public static function is_3_version(): bool {
		return class_exists( '\Jet_Form_Builder\Blocks\Validation' );
	}

}