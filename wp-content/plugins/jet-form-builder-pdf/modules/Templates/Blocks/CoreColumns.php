<?php

namespace JFB_PDF_Modules\Templates\Blocks;

class CoreColumns {

	public function init_hooks() {
		add_filter(
			'render_block_core/columns',
			array( $this, 'apply_table_style' )
		);
	}

	public function remove_hooks() {
		remove_filter(
			'render_block_core/columns',
			array( $this, 'apply_table_style' )
		);
	}

	public function apply_table_style( string $block_content ): string {
		// Prep the processor for modifying the block output.
		$processor = new \WP_HTML_Tag_Processor( $block_content );

		// Having no tags implies there are no tags onto which to add class names.
		if ( ! $processor->next_tag() ) {
			return $block_content;
		}

		// transform "flex-basis: 50%" into "flex-basis: 50%;" or empty string
		$base_style = rtrim( $processor->get_attribute( 'style' ), ';' );
		$base_style = $base_style ? ( $base_style . ';' ) : '';

		$processor->set_attribute(
			'style',
			trim( $base_style . ' display: table; width: 100%;' )
		);

		return $processor->get_updated_html();
	}

}
