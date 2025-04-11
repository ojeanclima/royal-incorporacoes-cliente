<?php

namespace JFB_PDF_Modules\Templates\Blocks\Interfaces;

use JFB_PDF_Modules\Templates\Module;

interface BlockInterface {

	public function get_block_json(): string;

	public function render( array $attrs, $content = null, $wp_block = null ): string;

}
