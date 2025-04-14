<?php
namespace Jet_Engine_Dynamic_Tables\Render;

class Preview {

	public function __construct() {
		add_action( 'wp_ajax_jet_engine_data_table_preview', array( $this, 'render_preview' ) );
	}

	public function render_preview() {

		$columns  = ! empty( $_POST['meta_fields'] ) ? $_POST['meta_fields'] : array();
		$settings = ! empty( $_POST['general_settings'] ) ? $_POST['general_settings'] : array();
		$renderer = jet_engine()->listings->get_render_instance( 'dynamic-table', array(
			'table_id'   => 99999,
			'inline_css' => $this->get_preview_styles(),
		) );

		$renderer->setup_table( null, $columns, $settings );

		$query = $renderer->get_table_object()->get_query();

		if ( $query ) {
			$query->setup_query();
			$query->set_filtered_prop( 'limit', 5 );
		}

		$renderer->render();
		die();

	}

	public function get_preview_styles() {
		ob_start();
		?>
		{{WRAPPER}} {
			width: 100%;
			background: #fff;
			box-shadow: 0 2px 6px rgba( 35, 40, 45, .07 );
			margin: 15px 0 0;
			border-collapse: collapse;
		}
		{{WRAPPER}} td,
		{{WRAPPER}} th {
			padding: 10px;
			border: 1px solid #ccc;
		}
		<?php
		return ob_get_clean();
	}
}
