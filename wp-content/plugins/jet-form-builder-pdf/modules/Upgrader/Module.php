<?php

namespace JFB_PDF_Modules\Upgrader;

final class Module {

	public function __construct() {

		add_action( 'admin_init', array( $this, 'run_upgrades' ) );
	}

	public function run_upgrades() {

		// Allow upgrades only for admin-level users
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$upgrades = array(
			new Upgraders\Upgrade102(),
		);

		foreach ( $upgrades as $upgrade ) {

			if ( $this->is_upgrade_required( $upgrade ) ) {

				$upgraded = $upgrade->run();

				if ( $upgraded ) {
					$upgrade->done();
				}
			} elseif ( ! $upgrade->was_successfull() ) {

				add_action(
					'admin_notices',
					function () use ( $upgrade ) {

						$message = $upgrade->get_manual_upgrade_message();

						printf(
							'<div class="notice notice-info"><p>%1$s</p><p><a href="%2$s" class="button">Run Upgrade</a></p></div>',
							wp_kses_post( $message ),
							esc_url(
								add_query_arg(
									array(
										'force_upgrade' => get_class( $upgrade ),
										'nonce'         => wp_create_nonce( 'jfb-pdf-upgrader' ),
									)
								)
							)
						);
					}
				);
			}
		}
	}

	public function is_upgrade_required( $upgrade ) {

		if ( ! empty( $_REQUEST['force_upgrade'] )
			&& stripslashes( get_class( $upgrade ) ) === $_REQUEST['force_upgrade']
		) {
			// additionaly check nonce in this case
			if ( ! empty( $_REQUEST['nonce'] )
				&& wp_verify_nonce( $_REQUEST['nonce'], 'jfb-pdf-upgrader' )
			) {
				return true;
			} else {
				return false;
			}
		}

		return $upgrade->is_upgrade_required();
	}

}
