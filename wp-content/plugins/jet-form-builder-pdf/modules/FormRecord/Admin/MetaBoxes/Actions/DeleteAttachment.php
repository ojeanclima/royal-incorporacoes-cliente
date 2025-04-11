<?php


namespace JFB_PDF_Modules\FormRecord\Admin\MetaBoxes\Actions;

use Jet_Form_Builder\Admin\Table_Views\Actions\Api_Single_Action;
use Jet_Form_Builder\Admin\Table_Views\Actions\View_Single_Action;
use JFB_PDF_Modules\RestAPI\Endpoints\DeleteAttachment as RestEndpoint;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class DeleteAttachment extends View_Single_Action {

	public function get_slug(): string {
		return 'delete';
	}

	public function get_label(): string {
		return __( 'Delete', 'jet-form-builder-pdf' );
	}

	public function get_payload( array $record ): array {
		return array(
			'id' => $record['ID'] ?? 0,
		);
	}

	public function get_type(): string {
		return 'danger';
	}

	public function show_in_header(): bool {
		return true;
	}

	public function show_in_row( array $record ): bool {
		return true;
	}

	public function to_array( array $record ): array {
		$attrs = parent::to_array( $record );

		$attrs['payload'] = $this->get_payload( $record );

		return $attrs;
	}
}
