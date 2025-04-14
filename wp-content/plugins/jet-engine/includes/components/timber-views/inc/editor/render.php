<?php
/**
 * Timber editor render class
 */
namespace Jet_Engine\Timber_Views\Editor;

use Jet_Engine\Timber_Views\Package;
use Timber\Loader as Timber_Loader;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Render {

	private $editor_trigger = 'jet_engine_timber_editor';
	private $preview;
	private $save;

	public $twig;

	public function __construct() {

		require_once Package::instance()->package_path( 'editor/preview.php' );
		require_once Package::instance()->package_path( 'editor/save.php' );

		$this->preview = new Preview();
		$this->save    = new Save();

		if ( $this->is_editor() ) {
			add_filter( 'replace_editor', '__return_true' );
			add_action( 'post_action_' . $this->editor_trigger, [ $this, 'render_editor' ] );
		}
	}

	public function get_editor_trigger() {
		return $this->editor_trigger;
	}

	public function render_editor( $post_id ) {

		global $title, $post, $self, $parent_file, $submenu_file;

		if ( ! $title ) {
			$title = esc_html__( 'Edit listing item/component template', 'jet-engine' );
		}

		$parent_file  = 'jet-engine';
		$submenu_file = 'edit.php?post_type=jet-engine';
		
		$post = get_post( $post_id );
		$self = 'post.php';

		$dummy_loader = new Timber_Loader();
		$this->twig = $dummy_loader->get_twig();

		require_once ABSPATH . 'wp-admin/admin-header.php';
		$this->editor_assets();
		?>
		<div class="wrap">
			<h1><?php echo $title; ?></h1>
			<div id="<?php echo $this->editor_trigger; ?>"></div>
		</div>
		<?php
		require_once ABSPATH . 'wp-admin/admin-footer.php';
		exit();

	}

	public function get_css_variables() {

		$css_vars = [];

		return apply_filters( 'jet-engine/twig-views/editor/css-variables', $css_vars );
	}

	public function editor_assets() {

		$module_data = jet_engine()->framework->get_included_module_data( 'cherry-x-vue-ui.php' );
		$ui          = new \CX_Vue_UI( $module_data );

		$ui->enqueue_assets();
		$emmet_settings = [
			'extraKeys' => [
				'Tab'   => 'emmetExpandAbbreviation',
				'Esc'   => 'emmetResetAbbreviation',
				'Enter' => 'emmetInsertLineBreak',
			]
		];

		$html_settings = wp_enqueue_code_editor( [
			'type'       => 'text/html',
			'codemirror' => $emmet_settings
		] );

		$css_settings = wp_enqueue_code_editor( [
			'type'       => 'text/css',
			'codemirror' => $emmet_settings
		] );

		jet_engine()->register_jet_plugins_js();

		wp_enqueue_script(
			'jet-engine-timber-editor-helpers', 
			Package::instance()->package_url( 'assets/js/helpers.js' ),
			[ 'jquery', 'cx-vue-ui' ],
			jet_engine()->get_version(),
			true
		);

		do_action( 'jet-engine/twig-views/editor/before-enqueue-assets' );

		wp_enqueue_script(
			'jquery-slick',
			jet_engine()->plugin_url( 'assets/lib/slick/slick.min.js' ),
			array( 'jquery' ),
			'1.8.1',
			true
		);
		
		jet_engine()->frontend->ensure_lib( 'imagesloaded' );

		wp_enqueue_script(
			'jet-engine-frontend',
			jet_engine()->plugin_url( 'assets/js/frontend.js' ),
			array( 'jquery', 'jet-plugins' ),
			jet_engine()->get_version(),
			true
		);

		wp_enqueue_media();

		wp_enqueue_script(
			'jet-engine-codemirror-emmet',
			Package::instance()->package_url( 'assets/js/codemirror-emmet.js' ),
			[],
			jet_engine()->get_version(),
			true
		);

		wp_enqueue_script(
			'jet-engine-timber-editor', 
			Package::instance()->package_url( 'assets/js/editor.js' ),
			[ 'jquery', 'cx-vue-ui' ],
			jet_engine()->get_version(),
			true
		);

		wp_enqueue_style(
			'jet-engine-timber-editor', 
			Package::instance()->package_url( 'assets/css/editor.css' ),
			[],
			jet_engine()->get_version(),
		);

		wp_enqueue_style( 'jet-engine-frontend' );

		global $post;

		$listing = jet_engine()->listings->get_new_doc( [], $post->ID );

		require_once Package::instance()->package_path( 'editor/presets.php' );
		$presets = new Presets( $this->twig );

		$component_control_types = \Jet_Engine_Tools::prepare_list_for_js(
			jet_engine()->listings->components->get_supported_control_types(), ARRAY_A
		);

		wp_localize_script( 'jet-engine-timber-editor', 'JetEngineTimberEditor', [
			'post_title'              => $post->post_title,
			'ID'                      => $post->ID,
			'settings'                => $listing->get_settings(),
			'listing_css'             => $listing->get_listing_css(),
			'listing_html'            => $listing->get_listing_html(),
			'entry_type'              => $listing->get_meta( '_entry_type' ),
			'component_control_types' => $component_control_types,
			'html_settings'           => $html_settings,
			'css_settings'            => $css_settings,
			'preview_settings'        => $listing->get_meta( '_twig_preview_settings' ),
			'css_variables'           => $this->get_css_variables(),
			'nonce'                   => $this->preview->nonce(),
			'functions'               => Package::instance()->registry->functions->get_functions_for_js(),
			'filters'                 => Package::instance()->registry->filters->get_filters_for_js(),
			'presets'                 => $presets->get_presets_with_preview( $listing->get_settings(), $post->ID ),
		] );

		printf(
			'<script type="text/x-template" id="%1$s_template">%2$s</script>',
			$this->editor_trigger,
			$this->editor_main_template()
		);

		printf(
			'<script type="text/x-template" id="%1$s_settings_template">%2$s</script>',
			$this->editor_trigger,
			$this->editor_settings_template()
		);

		printf(
			'<script type="text/x-template" id="%1$s_dynamic_data_template">%2$s</script>',
			$this->editor_trigger,
			$this->editor_dynamic_data_template()
		);

	}

	public function editor_dynamic_data_template() {
		ob_start();
		include Package::instance()->package_path( 'templates/editor/dynamic-data.php' );
		return ob_get_clean();
	}

	public function editor_settings_template() {

		ob_start();

		$data    = [];
		$sources = jet_engine()->listings->post_type->get_listing_item_sources();
		$views   = jet_engine()->listings->post_type->get_listing_views();

		include jet_engine()->get_template( 'admin/listing-settings-form.php' );
		$form = ob_get_clean();

		$form = preg_replace( '/name="(.*?)"/', '$0 v-model="settings.$1"', $form );

		ob_start();
		include Package::instance()->package_path( 'templates/editor/settings.php' );
		return ob_get_clean();
	}

	public function editor_main_template() {
		ob_start();
		include Package::instance()->package_path( 'templates/editor/main.php' );
		return ob_get_clean();
	}

	public function is_editor() {
		return ( ! empty( $_GET['action'] ) && $this->editor_trigger === $_GET['action'] ) ? true : false;
	}

}
