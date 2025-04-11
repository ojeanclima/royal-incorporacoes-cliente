<?php

namespace JFB_PDF_Modules\Upgrader\Upgraders;

use Jet_Form_Builder\Classes\Resources\Upload_Dir;
use JFB_PDF_Modules\GenerateFileAction\Includes\UploadDirAdapter;

class Upgrade102 extends Base {

	public function get_version(): string {
		return '1.0.2';
	}

	public function was_successfull(): bool {

		$base_dir   = Upload_Dir::upload_base();
		$wp_upload  = wp_upload_dir();
		$base_path  = trailingslashit( $wp_upload['basedir'] ) . trailingslashit( $base_dir );
		$pdf_path   = $base_path . 'pdf';
		$t_pdf_path = $base_path . 'temp/pdf';

		return ( is_dir( $pdf_path ) || is_dir( $t_pdf_path ) ) ? false : true;
	}

	public function get_manual_upgrade_message(): string {
		return 'The JetFormBuilder PDF addon requires an update to the saved PDF folder path. This is necessary to store your PDF files in a more secure way. Please click the button below to run the update process.<br/><b>Please note:</b> If this message keeps appearing, please go to the <i>`wp-content/uploads/jet-form-builder`</i> directory and manually rename the <i>`pdf`</i> subdirectory to <i>`' . UploadDirAdapter::unique_dir_name() . '`</i>. If you have any questions about the process - please contact <a href="https://support.crocoblock.com/support/home/" target="_blank">Crocoblock support</a>';
	}

	public function run(): bool {

		$base_dir   = Upload_Dir::upload_base();
		$wp_upload  = wp_upload_dir();
		$base_path  = trailingslashit( $wp_upload['basedir'] ) . trailingslashit( $base_dir );
		$pdf_path   = $base_path . 'pdf';
		$t_pdf_path = $base_path . 'temp/pdf';

		// Add noindex to .htaccess
		add_filter(
			'jet-form-builder/file-upload/htaccess-content',
			function ( $content ) {
				return '
# Disable directory browsing
Options -Indexes

# Hide the contents of directories
IndexIgnore *

<IfModule mod_headers.c>
	Header set X-Robots-Tag "noindex, nofollow"
</IfModule>';
			}
		);

		if ( ! file_exists( $base_path . '.htaccess' ) ) {
			Upload_Dir::create_htaccess( $base_path );
		}

		$current_file = $pdf_path . '/.htaccess';

		if ( file_exists( $current_file ) ) {
			$res = wp_delete_file( $current_file );
		}

		if ( is_dir( $pdf_path ) ) {
			Upload_Dir::create_htaccess( $pdf_path );
		}

		$current_file_tmp = $t_pdf_path . '/.htaccess';

		if ( file_exists( $current_file_tmp ) ) {
			wp_delete_file( $current_file_tmp );
		}

		if ( is_dir( $pdf_path ) ) {
			Upload_Dir::create_htaccess( $t_pdf_path );
		}

		// Rename dir to unique
		$new_dir_name = UploadDirAdapter::unique_dir_name();

		if ( is_dir( $pdf_path ) && ! is_dir( $base_path . $new_dir_name ) ) {
			rename( $pdf_path, $base_path . $new_dir_name );
		}

		if ( is_dir( $t_pdf_path ) && ! is_dir( $base_path . 'temp/' . $new_dir_name ) ) {
			rename( $t_pdf_path, $base_path . 'temp/' . $new_dir_name );
		}

		// Rename previously created files in DB
		global $wpdb;
		$fields_table = $wpdb->prefix . 'jet_fb_records_fields';
		$posts_table  = $wpdb->posts;

		$results = $wpdb->get_results(
			"
			SELECT * FROM $fields_table
			WHERE field_name LIKE 'generate_pdf_%_filepath' OR field_name LIKE 'generate_pdf_%_url';
		"
		);

		foreach ( $results as $row ) {

			$new_value = str_replace( '/pdf/', '/' . $new_dir_name . '/', $row->field_value );

			$wpdb->update(
				$fields_table,
				array(
					'field_value' => $new_value,
				),
				array(
					'id' => $row->id,
				)
			);
		}

		$posts_results = $wpdb->get_results(
			"
			SELECT * FROM $posts_table
			WHERE post_type = 'attachment'
			AND post_mime_type = 'application/pdf'
			AND guid LIKE '%jet-form-builder/pdf/%';
		"
		);

		if ( ! empty( $posts_results ) ) {
			foreach ( $posts_results as $post_object ) {

				$new_guid = str_replace( '/pdf/', '/' . $new_dir_name . '/', $post_object->guid );

				$wpdb->update(
					$posts_table,
					array(
						'guid' => $new_guid,
					),
					array(
						'ID' => $post_object->ID,
					)
				);
			}
		}

		return true;
	}

}
