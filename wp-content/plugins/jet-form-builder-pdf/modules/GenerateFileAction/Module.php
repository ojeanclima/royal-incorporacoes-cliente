<?php


namespace JFB_PDF_Modules\GenerateFileAction;

use Jet_Form_Builder\Actions\Manager;
use JFB_PDF\Plugin;
use JFB_PDF\Vendor\Auryn\InjectionException;
use JFB_PDF_Modules\Components;
use JFB_PDF_Modules\GenerateFileAction\Includes\Action;
use JFB_PDF_Modules\GenerateFileAction\Includes\ActionMessages;
use JFB_PDF_Modules\GenerateFileAction\Includes\UploadDirAdapter;
use JFB_PDF_Modules\Templates\PostType\PostType;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die();
}

class Module {

	private $plugin;

	public function __construct( Plugin $plugin, UploadDirAdapter $adapter ) {
		$this->plugin = $plugin;

		$this->init_hooks();
		$adapter->init_hooks();
	}

	public function init_hooks() {
		add_action( 'jet-form-builder/actions/register', array( $this, 'actions_register' ) );
		add_action( 'jet-form-builder/editor-assets/before', array( $this, 'editor_assets_before' ) );
		add_filter( 'jet-form-builder/form-messages/register', array( $this, 'add_action_messages' ) );
	}

	/**
	 * @param Manager $manager
	 *
	 * @return void
	 * @throws InjectionException
	 */
	public function actions_register( Manager $manager ) {
		/** @var Action $action */
		$action = $this->plugin->get_injector()->make( Action::class );

		$manager->register_action_type( $action );
	}

	/**
	 * @param array $types
	 *
	 * @return array
	 * @throws InjectionException
	 */
	public function add_action_messages( array $types ): array {
		$types[] = $this->plugin->get_injector()->make( ActionMessages::class );

		return $types;
	}

	public function editor_assets_before() {
		$script_url   = $this->build_url( 'editor.js' );
		$script_asset = require_once $this->build_path( 'editor.asset.php' );

		array_push(
			$script_asset['dependencies'],
			Components\Module::HANDLE
		);

		wp_enqueue_script(
			Plugin::SLUG . '-action',
			$script_url,
			$script_asset['dependencies'],
			$script_asset['version'],
			true
		);

		wp_localize_script(
			Plugin::SLUG . '-action',
			'JFBPDFGenerateFileConfig',
			array(
				'addNewUrl' => add_query_arg(
					array(
						'post_type' => PostType::SLUG,
					),
					esc_url_raw( admin_url( 'post-new.php' ) )
				),
				'editUrl'   => add_query_arg(
					array(
						'action' => 'edit',
					),
					esc_url_raw( admin_url( 'post.php' ) )
				),
			)
		);
	}

	protected function build_url( string $url = '' ): string {
		return JFB_PDF_URL . 'modules/GenerateFileAction/assets/build/' . $url;
	}

	protected function build_path( string $path = '' ): string {
		return JFB_PDF_PATH . '/modules/GenerateFileAction/assets/build/' . $path;
	}
}
