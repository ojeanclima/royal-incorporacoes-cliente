<?php
namespace Jet_Engine_Dynamic_Tables\Admin\Pages;

use Jet_Engine_Dynamic_Tables\Admin\Dashboard;
use Jet_Engine\Query_Builder\Manager as Query_Builder;

class Tables_List extends \Jet_Engine_CPT_Page_Base {

	public $is_default = true;

	/**
	 * Class constructor
	 */
	public function __construct( $manager ) {

		parent::__construct( $manager );

		add_action( 'jet-engine/' . $manager->instance_slug() . '/page/after-title', array( $this, 'add_new_btn' ) );
	}

	/**
	 * Add new  post type button
	 */
	public function add_new_btn( $page ) {

		if ( $page->get_slug() !== $this->get_slug() ) {
			return;
		}

		?>
		<a class="page-title-action" href="<?php echo $this->manager->get_page_link( 'add' ); ?>"><?php
			_e( 'Add New', 'jet-engine' );
		?></a>
		<?php

	}

	/**
	 * Page slug
	 *
	 * @return string
	 */
	public function get_slug() {
		return 'list';
	}

	/**
	 * Page name
	 *
	 * @return string
	 */
	public function get_name() {
		return esc_html__( 'Tables List', 'jet-engine' );
	}

	/**
	 * Register add controls
	 * @return [type] [description]
	 */
	public function page_specific_assets() {

		$module_data = jet_engine()->framework->get_included_module_data( 'cherry-x-vue-ui.php' );

		$ui = new \CX_Vue_UI( $module_data );

		$ui->enqueue_assets();

		wp_register_script(
			'jet-engine-table-delete-dialog',
			JET_ENGINE_DYNAMIC_TABLES_URL . 'assets/js/admin/delete-dialog.js',
			array( 'cx-vue-ui', 'wp-api-fetch', ),
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

		wp_enqueue_script(
			'jet-engine-tables-list',
			JET_ENGINE_DYNAMIC_TABLES_URL . 'assets/js/admin/list.js',
			array( 'cx-vue-ui', 'wp-api-fetch', 'jet-engine-table-delete-dialog' ),
			JET_ENGINE_DYNAMIC_TABLES_VERSION,
			true
		);

		wp_localize_script(
			'jet-engine-tables-list',
			'JetEngineTablesListConfig',
			$this->manager->get_admin_page_config ( array(
				'api_path'     => jet_engine()->api->get_route( 'get-items' ),
				'api_path_add' => jet_engine()->api->get_route( 'add-item' ),
				'edit_link'    => $this->manager->get_edit_item_link( '%id%' ),
				'queries'      => Query_Builder::instance()->get_queries_for_options(),
			) )
		);

		add_action( 'admin_footer', array( $this, 'add_page_template' ) );

	}

	/**
	 * Print add/edit page template
	 */
	public function add_page_template() {

		ob_start();
		include JET_ENGINE_DYNAMIC_TABLES_PATH . 'templates/list.php';
		$content = ob_get_clean();
		printf( '<script type="text/x-template" id="jet-tables-list">%s</script>', $content );

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
		<style type="text/css">
			.list-table-heading__cell,
			.list-table-item__cell {
				flex: 0 0 33.3333%;
				max-width: 33.3333%;
			}
		</style>
		<div id="jet_tables_list"></div>
		<?php

	}

}
