<?php

namespace JFB_PDF_Modules\PDFTemplates;

use JFB_PDF_Modules\Templates\Blocks;

class Module {

	public function __construct(
		Blocks\CoreColumns $columns,
		Blocks\CoreColumn $column
	) {
		// for columns
		add_action(
			'jet-form-builder-pdf/render-body-top',
			array( $columns, 'init_hooks' )
		);
		add_action(
			'jet-form-builder-pdf/render-body-bottom',
			array( $columns, 'remove_hooks' )
		);

		// for single column
		add_action(
			'jet-form-builder-pdf/render-body-top',
			array( $column, 'init_hooks' )
		);
		add_action(
			'jet-form-builder-pdf/render-body-bottom',
			array( $column, 'remove_hooks' )
		);
	}

}
