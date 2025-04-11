<?php

namespace JFB_PDF_Modules\FormRecord\Admin\MetaBoxes\Actions;

use Jet_Form_Builder\Admin\Table_Views\Actions\Link_Single_Action;

class ViewAttachment extends Link_Single_Action {

	public function get_slug(): string {
		return 'view';
	}

	public function get_label(): string {
		return __( 'View', 'jet-form-builder-pdf' );
	}

	public function show_in_header(): bool {
		return false;
	}

	public function show_in_row( array $record ): bool {
		return true;
	}

	/**
	 * @param array $record
	 *
	 * @return string
	 */
	public function get_href( array $record ): string {
		return get_the_guid( $record['ID'] ?? 0 );
	}

}
