<?php

namespace JFB_PDF\Files;

use JFB_PDF\Vendor\Dompdf\Dompdf as VendorDompdf;
use JFB_PDF\Files\Interfaces\PDFInterface;
use JFB_PDF\Vendor\Dompdf\Options;

class DomPDF implements PDFInterface {

	/** @var VendorDompdf */
	private $file;

	public function create() {
		$options = new Options();
		$options->setDefaultFont( 'dejavu serif' );

		// for image processing
		$options->setIsRemoteEnabled( true );

		// instantiate and use the dompdf class
		$this->file = new VendorDompdf( $options );

		// set the paper size and orientation
		$this->file->setPaper( 'A4' );
	}

	public function load_html( string $html ) {
		$this->file->loadHtml( $html, 'UTF-8' );
	}

	public function render() {
		$this->file->render();
	}

	public function get_output() {
		return $this->file->output();
	}
}
