<?php

namespace JFB_PDF_Modules\GenerateFileAction\Includes;

use Jet_Form_Builder\Classes\Resources\Upload_Dir;

class UploadDirAdapter {

	private $temp = true;

	public function init_hooks() {
		add_action(
			'jet-form-builder/before-do-action/generate_pdf',
			array( $this, 'change_is_temp' )
		);
		add_action(
			'jet-form-builder-pdf/render-body-bottom',
			array( $this, 'apply_upload_dir' )
		);
		add_action(
			'jet-form-builder-pdf/uploaded',
			array( $this, 'remove_apply_upload_dir' )
		);
	}

	public function apply_upload_dir() {
		add_filter(
			'jet-form-builder/file-upload/dir',
			array( $this, 'file_upload_dir' )
		);
		add_filter( 'upload_dir', array( Upload_Dir::class, 'apply_upload_dir' ) );
	}

	public function remove_apply_upload_dir() {
		remove_filter(
			'jet-form-builder/file-upload/dir',
			array( $this, 'file_upload_dir' )
		);
		remove_filter( 'upload_dir', array( Upload_Dir::class, 'apply_upload_dir' ) );
	}

	public function file_upload_dir( string $dir ): string {
		$unique_dir = self::unique_dir_name();
		return sprintf( $this->is_temp() ? '%1$s/temp/%2$s' : '%1$s/%2$s', $dir, $unique_dir );
	}

	public function change_is_temp( Action $action ) {
		if ( empty( $action->settings['save'] ) ) {
			$this->set_temp( true );

			return;
		}
		$this->set_temp( false );
	}

	public static function unique_dir_name() {

		$dir_name = get_option( 'jfb_pdf_unique_name' );

		if ( ! $dir_name ) {
			$dir_name = md5( rand( 100000000, 999999999 ) );
			update_option( 'jfb_pdf_unique_name', $dir_name, false );
		}

		return $dir_name;
	}

	/**
	 * @param bool $temp
	 */
	public function set_temp( bool $temp ) {
		$this->temp = $temp;
	}

	/**
	 * @return bool
	 */
	public function is_temp(): bool {
		return $this->temp;
	}

}
