<?php

namespace JFB_PDF_Modules\FormRecord\Admin\MetaBoxes\Actions;

use Jet_Form_Builder\Admin\Table_Views\Actions\Link_Single_Action;

class EditAttachment extends Link_Single_Action {

	public function get_slug(): string {
		return 'edit';
	}

	public function get_label(): string {
		return __( 'Edit', 'jet-form-builder-pdf' );
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

		if ( ! is_array( $record ) || empty( $record['ID'] ) ) {
			return '';
		}

		$href = get_edit_post_link( $record['ID'], false );

		return $href ? $href : '';
	}

}
