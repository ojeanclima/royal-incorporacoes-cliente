<?php

namespace JFB_PDF_Modules\Templates\Blocks;

use JFB_PDF\Vendor\Auryn\ConfigException;
use JFB_PDF\Vendor\Auryn\Injector;

class CoreColumn {

	public function init_hooks() {
		add_filter(
			'render_block_core/column',
			array( $this, 'apply_table_cell_style' ),
			10,
			2
		);
	}

	public function remove_hooks() {
		remove_filter(
			'render_block_core/column',
			array( $this, 'apply_table_cell_style' )
		);
	}

	public function apply_table_cell_style( string $block_content, array $block ): string {
		// Prep the processor for modifying the block output.
		$processor = new \WP_HTML_Tag_Processor( $block_content );

		// Having no tags implies there are no tags onto which to add class names.
		if ( ! $processor->next_tag() ) {
			return $block_content;
		}

		$width       = $block['attrs']['width'] ?? '';
		$width_style = $width ? sprintf( 'width: %s;', $width ) : '';
		$style       = trim( 'display: table-cell; ' . $width_style );

		// transform "flex-basis: 50%" into "flex-basis: 50%;" or empty string
		$base_style = rtrim( $processor->get_attribute( 'style' ), ';' );
		$base_style = $base_style ? ( $base_style . ';' ) : '';

		$processor->set_attribute(
			'style',
			trim( $base_style . ' ' . $style )
		);

		return $processor->get_updated_html();
	}

}
