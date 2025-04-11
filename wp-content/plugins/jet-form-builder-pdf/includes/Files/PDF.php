<?php

namespace JFB_PDF\Files;

use JFB_PDF\Files\Interfaces\PDFInterface;

final class PDF {

	private $proxy;
	private $html      = '';
	private $file_name = 'form-record';

	const MIME_TYPE = 'application/pdf';

	public function __construct( PDFInterface $proxy ) {
		$this->proxy = $proxy;
	}

	public function upload(): array {
		$file = wp_upload_bits(
			sprintf( '%s.pdf', $this->get_file_name() ),
			null,
			$this->get_proxy()->get_output()
		);

		do_action_ref_array( 'jet-form-builder-pdf/uploaded', $file );

		return $file;
	}

	/**
	 * Open <html> and <head> tags
	 *
	 * @return void
	 */
	public function start_html() {
		$this->add_html( '<html lang="en-US"><head><meta charset="UTF-8">' );

		do_action( 'jet-form-builder-pdf/render-head-top', $this );

		$this->add_styles();
	}

	/**
	 * Close <head> and opens <body>
	 *
	 * @return void
	 */
	public function start_body() {
		do_action( 'jet-form-builder-pdf/render-head-bottom', $this );

		$this->add_html(
			'
</head>
<body>
	<div class="is-layout-constrained">
'
		);

		do_action( 'jet-form-builder-pdf/render-body-top', $this );
	}

	/**
	 * Closes <body> and <html>.
	 * Process proxy->render
	 *
	 * @return void
	 */
	public function render() {
		do_action( 'jet-form-builder-pdf/render-body-bottom', $this );

		$this->inject_styles();

		$this->add_html(
			'
	</div>
</body>
</html>'
		);

		$html = $this->processHTMLWithCSSVariables( $this->get_html() );

		$this->get_proxy()->load_html( $html );

		$this->set_html( '' );

		$this->get_proxy()->render();
	}

	/**
	 * Processes HTML content to replace CSS variables defined in :root with their actual values.
	 *
	 * This method extracts CSS variables from the :root selector, replaces all occurrences
	 * of var(--variable-name) in the provided HTML content with their corresponding values,
	 * and finally removes the :root block from the content.
	 *
	 * @param string $content The raw HTML content containing CSS variables.
	 * @return string Processed HTML with variables replaced by their actual values.
	 */
	public function processHTMLWithCSSVariables( $html ) {
		preg_match( '/:root\s*{([^}]*)}/', $html, $matches );

		if ( ! isset( $matches[1] ) ) {
			return $html;
		}

		$rootStyles = $matches[1];

		preg_match_all( '/--([\w-]+)\s*:\s*([^;]+)/', $rootStyles, $varMatches );

		if ( empty( $varMatches[1] ) ) {
			return $html;
		}

		$variables = array_combine( $varMatches[1], $varMatches[2] );

		$processedCss = preg_replace_callback(
			'/var\(--([\w-]+)\)/',
			function ( $matches ) use ( $variables ) {
				return $variables[ $matches[1] ] ?? $matches[0];
			},
			$html
		);

		$processedCss = preg_replace( '/:root\s*{[^}]*}/', '', $processedCss );

		return $processedCss;
	}

	private function add_styles() {
		add_filter( 'should_load_block_editor_scripts_and_styles', '__return_true' );

		wp_common_block_scripts_and_styles();
		wp_enqueue_global_styles();

		wp_add_inline_style(
			'global-styles',
			'
body .is-layout-constrained > * {
    margin-bottom: 1rem;
}
.wp-block-columns.has-background {
	padding: 1.25em 2.375em;
}
body {
	word-break: break-word;
}
		'
		);
		wp_add_inline_style(
			'wp-block-library',
			'
figure {
	margin: 0 0 1em;
}
				'
		);
	}

	private function inject_styles() {
		ob_start();

		global $wp_styles;

		$wp_styles->done  = array();
		$wp_styles->queue = array_unique( array_merge( $wp_styles->queue, array( 'wp-block-library', 'global-styles' ) ) );

		wp_styles()->do_items();
		wp_styles()->do_footer_items();

		$this->add_html( ob_get_clean() );
	}

	/**
	 * @return PDFInterface
	 */
	public function get_proxy(): PDFInterface {
		return $this->proxy;
	}

	public function add_html( string $html ) {
		$this->html .= $html;
	}


	/**
	 * @param string $html
	 */
	public function set_html( string $html ) {
		$this->html = $html;
	}

	/**
	 * @return string
	 */
	public function get_html(): string {
		return $this->html;
	}

	/**
	 * @param string $file_name
	 */
	public function set_file_name( string $file_name ) {
		$this->file_name = sanitize_file_name( $file_name );
	}

	/**
	 * @return string
	 */
	public function get_file_name(): string {
		return $this->file_name;
	}


}
