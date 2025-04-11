<?php

namespace JFB_PDF\Files\Interfaces;

interface PDFInterface {

	public function create();

	public function load_html( string $html );

	public function get_output();

	public function render();

}
