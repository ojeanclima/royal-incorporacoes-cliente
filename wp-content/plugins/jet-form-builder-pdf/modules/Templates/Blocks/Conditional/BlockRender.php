<?php

namespace JFB_PDF_Modules\Templates\Blocks\Conditional;

use JFB_PDF_Modules\Templates\Blocks\Interfaces\BlockInterface;

class BlockRender implements BlockInterface {

	const NAME = 'jet-bl-tmpl/conditional';

	public function render( array $attrs, $content = null, $wp_block = null ): string {
		return sprintf(
			'<div %1$s>%2$s</div>',
			get_block_wrapper_attributes(),
			$content
		);
	}

	public function get_block_json(): string {
		return JFB_PDF_PATH . 'modules/Templates/Shared/Blocks/Conditional/block.json';
	}
}
