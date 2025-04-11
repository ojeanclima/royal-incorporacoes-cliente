<?php

namespace JFB_PDF_Modules\FormRecord;

use Jet_Form_Builder\Admin\Single_Pages\Meta_Containers\Base_Meta_Container;
use Jet_Form_Builder\Db_Queries\Exceptions\Sql_Exception;
use Jet_Form_Builder\Exceptions\Query_Builder_Exception;
use JFB_PDF\Plugin;
use JFB_PDF_Modules\FormRecord\Admin\MetaBoxes\GenerateFileBox;
use JFB_PDF_Modules\FormRecord\DB\Models\FilesToRecordsModel;
use JFB_PDF_Modules\FormRecord\DB\Views\FileByRecord;
use JFB_PDF_Modules\GenerateFileAction\Includes\Action;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die();
}


class Module {

	private $box;
	private $model;

	public function __construct( GenerateFileBox $box, FilesToRecordsModel $model ) {
		$this->box   = $box;
		$this->model = $model;

		$this->init_hooks();
	}

	public function init_hooks() {
		add_filter(
			'jet-form-builder/page-containers/jfb-records-single',
			array( $this, 'add_box_to_single_record' )
		);
		add_action(
			'jet-fb/admin-pages/before-assets/jfb-records-single',
			array( $this, 'enqueue_assets_for_record_single' )
		);
		add_action(
			'jet-form-builder/db/records/after-insert',
			array( $this, 'connect_record_or_delete_file' )
		);
		add_action(
			'delete_attachment',
			array( $this, 'delete_relative_rows' )
		);
	}

	/**
	 * @param Base_Meta_Container[] $containers
	 *
	 * @return array
	 */
	public function add_box_to_single_record( array $containers ): array {
		$containers[1]->add_meta_box( $this->box );

		return $containers;
	}

	public function enqueue_assets_for_record_single() {
		$script_url   = $this->get_url( 'assets/build/index.js' );
		$script_asset = require_once $this->get_path( 'assets/build/index.asset.php' );

		wp_enqueue_script(
			Plugin::SLUG . '-fr-attachments',
			$script_url,
			$script_asset['dependencies'],
			$script_asset['version'],
			true
		);
	}

	/**
	 * @param $record_id
	 *
	 * @throws Sql_Exception
	 */
	public function connect_record_or_delete_file( $record_id ) {
		$file_id = jet_fb_context()->get_value( Action::FILE_ID );

		if ( ! $file_id ) {
			return;
		}

		$this->model->insert(
			array(
				'file_id'   => $file_id,
				'record_id' => $record_id,
			)
		);
	}

	public function delete_relative_rows( $attachment_id ) {
		try {
			FileByRecord::delete(
				array(
					'attachment_id' => $attachment_id,
				)
			);
		} catch ( Query_Builder_Exception $exception ) {
			// do nothing
		}
	}

	public function get_url( string $url = '' ): string {
		return JFB_PDF_URL . 'modules/FormRecord/' . $url;
	}

	public function get_path( string $path = '' ): string {
		return JFB_PDF_PATH . 'modules/FormRecord/' . $path;
	}
}
