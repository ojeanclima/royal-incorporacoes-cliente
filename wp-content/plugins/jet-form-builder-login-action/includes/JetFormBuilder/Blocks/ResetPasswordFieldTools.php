<?php


namespace Jet_FB_Login\JetFormBuilder\Blocks;

use Jet_FB_Login\Plugin;

class ResetPasswordFieldTools {

	public static function get_attrs_by_pref( string $prefix, array $attributes ): array {
		foreach ( $attributes as $name => $value ) {
			if ( ! str_starts_with( $name, $prefix ) ) {
				unset( $attributes[ $name ] );
				continue;
			}
			unset( $attributes[ $name ] );
			$name = str_replace( $prefix, '', $name );

			$attributes[ $name ] = $value;
		}

		return $attributes;
	}

	public static function get_blocks_dir( $block = '' ): string {
		return Plugin::instance()->plugin_dir(
			"includes/JetFormBuilder/Blocks/{$block}"
		);
	}

}
