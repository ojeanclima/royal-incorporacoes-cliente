<?php
namespace Jet_Engine_Dynamic_Tables\Admin\Pages;

use Jet_Engine\Query_Builder\Manager as Query_Builder;
use Jet_Engine_Dynamic_Tables\Admin\Dashboard;

class Edit extends \Jet_Engine_CPT_Page_Base {

	/**
	 * Page slug
	 *
	 * @return string
	 */
	public function get_slug() {
		if ( $this->item_id() ) {
			return 'edit';
		} else {
			return 'add';
		}
	}

	/**
	 * Page name
	 *
	 * @return string
	 */
	public function get_name() {
		if ( $this->item_id() ) {
			return esc_html__( 'Edit Table', 'jet-engine' );
		} else {
			return esc_html__( 'Add New Table', 'jet-engine' );
		}
	}

	/**
	 * Returns currently requested items ID.
	 * If this funciton returns an empty result - this is add new item page
	 *
	 * @return [type] [description]
	 */
	public function item_id() {
		return isset( $_GET['id'] ) ? esc_attr( $_GET['id'] ) : false;
	}

	/**
	 * Allowed content types
	 *
	 * @return [type] [description]
	 */
	public function get_allowed_content_types() {
		return apply_filters( 'jet-engine/data-tables/content-types', array(
			array(
				'value' => 'raw',
				'label' => __( 'Raw Value', 'jet-engine' ),
			),
			array(
				'value' => 'template',
				'label' => __( 'Template', 'jet-engine' ),
			),
		) );
	}

	public function get_callbacks() {

		$result = array( array(
			'value' => '',
			'label' => __( 'Select callback...', 'jet-engine' ),
		) );

		foreach ( jet_engine()->listings->get_allowed_callbacks() as $value => $label ) {
			$result[] = array(
				'value' => $value,
				'label' => $label,
			);
		}

		return $result;

	}

	public function get_callbacks_args() {

		$result     = array();
		$disallowed = array( 'checklist_divider_color' );

		foreach ( jet_engine()->listings->get_callbacks_args() as $key => $args ) {

			if ( in_array( $key, $disallowed ) ) {
				continue;
			}

			$args['id'] = $key;

			switch ( $args['type'] ) {

				case 'select':

					$options = $args['options'];
					$args['options'] = array();

					foreach ( $options as $value => $label ) {
						$args['options'][] = array(
							'value' => $value,
							'label' => $label,
						);
					}

					$args['type'] = 'cx-vui-select';

					break;

				default:

					if ( ! in_array( $args['type'], array( 'switcher', 'textarea' ) ) ) {
						$args['type'] = 'cx-vui-input';
					} else {
						$args['type'] = 'cx-vui-' . $args['type'];
					}

					break;
			}

			$args['if'] = $args['condition']['filter_callback'];

			unset( $args['condition'] );

			$result[] = $args;
		}

		return $result;
	}

	/**
	 * Get meta fields for post type
	 *
	 * @return array
	 */
	public function get_meta_fields() {

		$res = array();

		if ( jet_engine()->meta_boxes ) {

			$raw = jet_engine()->meta_boxes->get_fields_for_select( 'plain', 'blocks' );

			foreach ( $raw as $group ) {
				$res[] = array(
					'label'   => $group['label'],
					'options' => $group['values'],
				);
			}

			return $res;

		} else {
			return $res;
		}

	}

	/**
	 * Register add controls
	 * @return [type] [description]
	 */
	public function page_specific_assets() {

		$module_data = jet_engine()->framework->get_included_module_data( 'cherry-x-vue-ui.php' );

		$ui = new \CX_Vue_UI( $module_data );

		$ui->enqueue_assets();

		wp_enqueue_script(
			'jet-engine-table-delete-dialog',
			JET_ENGINE_DYNAMIC_TABLES_URL . 'assets/js/admin/delete-dialog.js',
			array( 'cx-vue-ui', 'wp-api-fetch' ),
			JET_ENGINE_DYNAMIC_TABLES_VERSION,
			true
		);

		wp_localize_script(
			'jet-engine-table-delete-dialog',
			'JetEngineTableDeleteDialog',
			array(
				'api_path' => jet_engine()->api->get_route( 'delete-item' ),
				'redirect' => $this->manager->get_page_link( 'list' ),
				'instance' => Dashboard::instance()->instance_slug(),
			)
		);

		wp_enqueue_style(
			'jet-engine-table-admin',
			JET_ENGINE_DYNAMIC_TABLES_URL . 'assets/css/admin.css',
			array(),
			JET_ENGINE_DYNAMIC_TABLES_VERSION
		);

		wp_enqueue_script(
			'jet-engine-table-edit',
			JET_ENGINE_DYNAMIC_TABLES_URL . 'assets/js/admin/edit.js',
			array( 'cx-vue-ui', 'wp-api-fetch' ),
			JET_ENGINE_DYNAMIC_TABLES_VERSION,
			true
		);

		do_action( 'jet-engine/table-builder/editor/after-enqueue-scripts' );

		$id = $this->item_id();

		if ( $id ) {
			$button_label = __( 'Update Table', 'jet-engine' );
			$redirect     = false;
		} else {
			$button_label = __( 'Create Table', 'jet-engine' );
			$redirect     = $this->manager->get_edit_item_link( '%id%' );
		}

		wp_localize_script(
			'jet-engine-table-edit',
			'JetEngineTableConfig',
			$this->manager->get_admin_page_config( array(
				'api_path_edit'           => jet_engine()->api->get_route( $this->get_slug() . '-item' ),
				'api_path_search_preview' => jet_engine()->api->get_route( 'search-table-preview' ),
				'api_path_fetch_data'     => jet_engine()->api->get_route( 'table-fetch-data' ),
				'queries'                 => Query_Builder::instance()->get_queries_for_options( true ),
				'object_fields'           => jet_engine()->listings->data->get_object_fields( 'blocks', 'options' ),
				'meta_fields'             => $this->get_meta_fields(),
				'callbacks'               => $this->get_callbacks(),
				'callback_args'           => $this->get_callbacks_args(),
				'listing_templates'       => jet_engine()->listings->get_listings_for_options( 'blocks' ),
				'data_sources'            => array(
					'fetched' => __( 'Fetched Column', 'jet-engine' ),
					'object'  => __( 'Post/Term/User/Object Field', 'jet-engine' ),
					'meta'    => __( 'Meta Field', 'jet-engine' ),
				),
				'content_types'           => array_merge(
					array( array( 'value' => '', 'label' => __( 'Select column content type...', 'jet-engine' ) ) ),
					$this->get_allowed_content_types()
				),
				'item_id'                 => $id,
				'edit_button_label'       => $button_label,
				'redirect'                => $redirect,
				'help_links'              => array(
					array(
						'url'   => 'https://crocoblock.com/knowledge-base/articles/jetengine-tables-builder-overview/?utm_source=jetengine&utm_medium=table-builder&utm_campaign=need-help',
						'label' => __( 'Tables Builder Overview', 'jet-engine' ),
					),
				),
			) )
		);

		jet_engine()->frontend->register_listing_styles();
		jet_engine()->frontend->frontend_styles();

		add_action( 'admin_footer', array( $this, 'add_page_template' ) );

	}

	public function get_macros_for_editor() {

		$res = array();

		foreach ( jet_engine()->listings->macros->get_all() as $macros_id => $data ) {

			$macros_data = array(
				'id' => $macros_id,
			);

			if ( ! is_array( $data ) || empty( $data['label'] ) ) {
				$macros_data['name'] = $macros_id;
			} elseif ( ! empty( $data['label'] ) ) {
				$macros_data['name'] = $data['label'];
			}

			if ( is_array( $data ) && ! empty( $data['args'] ) ) {
				$macros_data['controls'] = $data['args'];
			}

			$res[] = $macros_data;

		}

		usort( $res, function( $a, $b ) {
			return strcmp( $a['name'], $b['name'] );
		} );

		return $res;

	}

	/**
	 * Print add/edit page template
	 */
	public function add_page_template() {

		ob_start();
		include JET_ENGINE_DYNAMIC_TABLES_PATH . 'templates/edit.php';
		$content = ob_get_clean();
		printf( '<script type="text/x-template" id="jet-table-form">%s</script>', $content );

		ob_start();
		include JET_ENGINE_DYNAMIC_TABLES_PATH . 'templates/columns.php';
		$content = ob_get_clean();
		printf( '<script type="text/x-template" id="jet-table-columns">%s</script>', $content );

		ob_start();
		include JET_ENGINE_DYNAMIC_TABLES_PATH . 'templates/switcher.php';
		$content = ob_get_clean();
		printf( '<script type="text/x-template" id="jet-table-switcher">%s</script>', $content );

		ob_start();
		include JET_ENGINE_DYNAMIC_TABLES_PATH . 'templates/delete-dialog.php';
		$content = ob_get_clean();
		printf( '<script type="text/x-template" id="jet-table-delete-dialog">%s</script>', $content );

	}

	/**
	 * Renderer callback
	 *
	 * @return void
	 */
	public function render_page() {
		?>
		<br>
		<div id="jet_table_form"></div>
		<?php
	}

}
