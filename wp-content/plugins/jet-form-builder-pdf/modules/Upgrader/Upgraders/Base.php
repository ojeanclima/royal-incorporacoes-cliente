<?php

namespace JFB_PDF_Modules\Upgrader\Upgraders;

abstract class Base {

	abstract public function get_version(): string;

	abstract public function run(): bool;

	abstract public function was_successfull(): bool;

	abstract public function get_manual_upgrade_message(): string;

	public function is_upgrade_required() {

		$current_version = get_option( 'jfb-pdf-version', false );

		if ( ! $current_version ) {
			return true;
		}

		return version_compare( $this->get_version(), $current_version, '>' );
	}

	public function done() {
		update_option( 'jfb-pdf-version', $this->get_version(), false );
	}

}
