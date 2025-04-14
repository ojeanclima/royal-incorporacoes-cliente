<?php

namespace LoginWP\Core\Admin\Installer;

/**
 * Skin for on-the-fly addon installations.
 *
 * Extend PluginSilentUpgraderSkin and clean up the class.
 */
class LoginWP_Install_Skin extends PluginSilentUpgraderSkin {

	/**
	 * Instead of outputting HTML for errors, json_encode the errors and send them
	 * back to the Ajax script for processing.
	 *
	 * @since 1.0.0
	 *
	 * @param array $errors Array of errors with the install process.
	 */
	public function error( $errors ) {

		if ( ! empty( $errors ) ) {
			wp_send_json_error( $errors );
		}
	}
}
