<?php

namespace JFB_PDF_Modules\PluginsPage;

use JFB_PDF_Modules\Templates\PostType\PostType;

class Module {

	public function __construct() {
		add_filter(
			'plugin_action_links_' . JFB_PDF_PLUGIN_BASE,
			array( $this, 'plugin_action_links' )
		);
	}

	public function plugin_action_links( array $actions ): array {
		return array_merge(
			array(
				'endpoints' => sprintf(
					'<a href="%1$s"><b>%2$s</b></a>',
					admin_url( PostType::MENU_SLUG ),
					__( 'Templates', 'jet-form-builder-formless-action-endpoints' )
				),
			),
			$actions
		);
	}


}
