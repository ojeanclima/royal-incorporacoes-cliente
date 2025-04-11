<?php

namespace JFB_PDF_Modules\GenerateFileAction\Includes;

use Jet_Form_Builder\Actions\Action_Handler;
use Jet_Form_Builder\Actions\Types\Base;
use Jet_Form_Builder\Classes\Resources\Uploaded_File;
use Jet_Form_Builder\Db_Queries\Exceptions\Sql_Exception;
use Jet_Form_Builder\Exceptions\Action_Exception;
use JFB_Modules\Form_Record\Action_Types\Save_Record;
use JFB_PDF\Files\PDF;
use JFB_PDF\Vendor\Auryn\InjectionException;
use JFB_PDF_Modules\FormRecord\DB\Models\FilesToRecordsModel;

class Action extends Base {

	const FILE_PATH = 'filepath';
	const FILE_URL  = 'url';
	const FILE_ID   = 'id';

	public function get_id() {
		return 'generate_pdf';
	}

	public function get_name() {
		return __( 'Generate PDF', 'jet-form-builder-pdf' );
	}

	/**
	 * @param array $request
	 * @param Action_Handler $handler
	 *
	 * @return void
	 * @throws Action_Exception
	 * @throws InjectionException
	 */
	public function do_action( array $request, Action_Handler $handler ) {
		$template = get_post( $this->settings['template'] ?? 0 );

		/**
		 * In JetFormBuilder (3.3.2) we cannot easily scale replacements
		 * for \JFB_Modules\Rich_Content\Module::rich.
		 * Therefore, we temporarily use this method.
		 */
		$base_name = str_replace(
			'%_template_name%',
			$template->post_title,
			( $this->settings['fileName'] ?? '' )
		);
		$file_name = \JFB_Modules\Rich_Content\Module::rich( $base_name );
		$file_name = $file_name ?: $template->post_title ?: '';

		/** @var PDF $pdf */
		$pdf = jet_fb_pdf_injector()->make( PDF::class );
		// create instance of Vendor class
		$pdf->get_proxy()->create();
		// open html & head tags. add styles into the head
		$pdf->start_html();
		// close head & open body tags
		$pdf->start_body();
		// we should render block only after opening body tag
		$pdf->add_html( do_blocks( $template->post_content ) );
		$pdf->render();
		$pdf->set_file_name( $file_name );
		$file = $pdf->upload();

		if ( ! empty( $file['error'] ) ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
			throw new Action_Exception( 'pdf_file_create', $file );
		}

		$uploaded_file = new Uploaded_File();
		$uploaded_file->set_from_array( $file );

		$computed_field_name = $this->get_computed_field( self::FILE_PATH );

		jet_fb_context()->set_field_type( 'generated-pdf', $computed_field_name );
		jet_fb_context()->update_request( $uploaded_file->get_file(), $computed_field_name );
		jet_fb_context()->update_file( $uploaded_file, $computed_field_name );

		if ( empty( $this->settings['save'] ) ) {
			add_action(
				'jet-form-builder/form-handler/after-send',
				array( $this, 'delete_generated_file' )
			);

			return;
		}

		Save_Record::add_hidden();

		$attachment = wp_insert_attachment(
			array(
				'guid'           => $uploaded_file->get_url(),
				'post_mime_type' => PDF::MIME_TYPE,
				'post_title'     => sanitize_title( basename( $uploaded_file->get_file(), '.pdf' ) ),
				'post_content'   => '',
				'post_status'    => 'publish',
			),
			$file['file'],
			0,
			true
		);

		if ( is_wp_error( $attachment ) ) {
			throw new Action_Exception( 'pdf_file_save', esc_html( $attachment->get_error_message() ) );
		}

		jet_fb_context()->update_request(
			$attachment,
			$this->get_computed_field( self::FILE_ID )
		);

		jet_fb_context()->update_request(
			$uploaded_file->get_url(),
			$this->get_computed_field( self::FILE_URL )
		);

		add_action(
			'jet-form-builder/form-handler/after-send',
			array( $this, 'attach_to_the_form_record' )
		);
	}

	/**
	 * @return void
	 * @throws Sql_Exception
	 */
	public function attach_to_the_form_record() {
		$attachment_id = jet_fb_context()->get_value(
			$this->get_computed_field( self::FILE_ID )
		);
		$record_id     = jet_fb_action_handler()->get_context( 'save_record', 'id' );

		( new FilesToRecordsModel() )->insert(
			array(
				'attachment_id' => $attachment_id,
				'record_id'     => $record_id,
			)
		);
	}

	public function delete_generated_file() {
		wp_delete_file(
			jet_fb_context()->get_value(
				$this->get_computed_field( self::FILE_PATH )
			)
		);
	}

	protected function get_computed_field( string $name ): string {
		return sprintf( '%1$s_%2$d_%3$s', $this->get_id(), $this->_id, $name );
	}
}
