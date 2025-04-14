<?php
/**
 * Timber view class
 */
namespace Jet_Engine\Website_Builder;


// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Page {

	/**
	 * Register website builder page
	 *
	 * @return [type] [description]
	 */
	public function register() {

		add_submenu_page(
			jet_engine()->admin_page,
			__( 'AI Website Builder', 'jet-engine' ),
			'<span><svg width="14" height="14" style="margin: 0 2px -1px 0" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M7.5 3.6L10 5L8.6 2.5L10 0L7.5 1.4L5 0L6.4 2.5L5 5L7.5 3.6ZM19.5 13.4L17 12L18.4 14.5L17 17L19.5 15.6L22 17L20.6 14.5L22 12L19.5 13.4ZM22 0L19.5 1.4L17 0L18.4 2.5L17 5L19.5 3.6L22 5L20.6 2.5L22 0ZM14.37 5.29C13.98 4.9 13.35 4.9 12.96 5.29L1.29 16.96C0.899998 17.35 0.899998 17.98 1.29 18.37L3.63 20.71C4.02 21.1 4.65 21.1 5.04 20.71L16.7 9.05C17.09 8.66 17.09 8.03 16.7 7.64L14.37 5.29ZM13.34 10.78L11.22 8.66L13.66 6.22L15.78 8.34L13.34 10.78Z" fill="currentColor"></path></svg> ' . __( 'Website Builder', 'jet-engine' ) . '</span>',
			'manage_options',
			Manager::instance()->slug(),
			[ $this, 'render' ]
		);

	}

	/**
	 * Render menu page
	 *
	 * @return void
	 */
	public function render() {

		$this->enqueue_assets();

		?>
		<div class="wrap">
			<h1><?php _e( 'AI Website Structure Builder', 'jet-engine' ) ?></h1>
			<p><?php
				_e( 'Build <b>the basic structure</b> of your website with the help of AI. Quickly set up custom post types, taxonomies, meta fields, and create queries and listings.', 'jet-engine' )
			?></p>
			<div id="jet-ai-website-builder"></div>
		</div>
		<?php

	}

	/**
	 * Enqueue page assets
	 *
	 * @return [type] [description]
	 */
	public function enqueue_assets() {

		require_once Manager::instance()->component_path( 'models-store.php' );

		$module_data = jet_engine()->framework->get_included_module_data( 'cherry-x-vue-ui.php' );
		$ui          = new \CX_Vue_UI( $module_data );

		$ui->enqueue_assets();

		wp_enqueue_style(
			'jet-engine-website-builder',
			Manager::instance()->component_url( 'css/builder.css' ),
			[],
			jet_engine()->get_version()
		);

		wp_enqueue_script(
			'jet-engine-website-builder',
			Manager::instance()->component_url( 'js/builder.js' ),
			[ 'cx-vue-ui' ],
			jet_engine()->get_version(),
			true
		);

		$jsf_license = \Jet_Dashboard\Utils::get_plugin_license_key( 'jet-smart-filters/jet-smart-filters.php' );

		wp_localize_script( 'jet-engine-website-builder', 'JetEngineWebsiteBuilderData', [
			'has_license'     => ( false !== jet_engine()->ai->get_matched_license() ? true : false ),
			'has_jsf_license' => ( false !== $jsf_license ) ? true : false,
			'limit'           => jet_engine()->ai->get_ai_limit(),
			'is_allowed'      => jet_engine()->ai->is_ai_allowed( 'website' ),
			'nonce'           => wp_create_nonce( Manager::instance()->hook_name() ),
			'action'          => Manager::instance()->hook_name(),
			'base_url'        => add_query_arg( [
				'page' => Manager::instance()->slug(),
			], esc_url( admin_url( 'admin.php' ) ) ),
			'models'          => array_reverse( Models_Store::instance()->get_models() ),
			'subpages'        => [
				'model'  => 'models/%id%',
				'models' => 'models',
			]
		] );

		add_action( 'admin_footer', [ $this, 'print_templates' ] );

	}

	/**
	 * Prine Vue.js templates
	 *
	 * @return [type] [description]
	 */
	public function print_templates() {

		ob_start();
		include Manager::instance()->component_path( 'templates/main.php' );
		$content = ob_get_clean();

		printf( '<script type="text/x-template" id="jet-ai-website-builder-main">%s</script>', $content );

		ob_start();
		include Manager::instance()->component_path( 'templates/section.php' );
		$content = ob_get_clean();

		printf( '<script type="text/x-template" id="jet-ai-website-builder-section">%s</script>', $content );

		ob_start();
		include Manager::instance()->component_path( 'templates/models.php' );
		$content = ob_get_clean();

		printf( '<script type="text/x-template" id="jet-ai-website-builder-models">%s</script>', $content );

	}

}
