<?php


namespace JFB_PDF;

use JFB_PDF\Files\DomPDF;
use JFB_PDF\Files\Interfaces\PDFInterface;
use JFB_PDF\Vendor\Auryn\Injector;
use JFB_PDF_Modules\GenerateFileAction;
use JFB_PDF_Modules\Templates;
use JFB_PDF_Modules\FormRecord;
use JFB_PDF_Modules\RestAPI;
use JFB_PDF_Modules\PDFTemplates;
use JFB_PDF_Modules\Components;
use JFB_PDF_Modules\FormConnector;
use JFB_PDF_Modules\Templates\ConditionValidators;
use JFB_PDF_Modules\PluginsPage;
use JFB_PDF_Modules\WooCommerce;
use JFB_PDF_Modules\Upgrader;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die();
}

final class Plugin {

	private $injector;

	const SLUG = 'jet-form-builder-pdf';

	public function __construct( Injector $injector ) {
		$this->injector = $injector;
	}

	private function declare_modules(): \Generator {
		yield Components\Module::class;
		yield Templates\Module::class;
		yield GenerateFileAction\Module::class;
		yield FormRecord\Module::class;
		yield RestAPI\Module::class;
		yield PDFTemplates\Module::class;
		yield FormConnector\Module::class;
		yield PluginsPage\Module::class;
		yield WooCommerce\Module::class;
		yield Upgrader\Module::class;
	}

	/**
	 * @throws Vendor\Auryn\InjectionException|Vendor\Auryn\ConfigException
	 */
	public function setup() {
		$this->injector->alias( PDFInterface::class, DomPDF::class );

		$this->injector->share( ConditionValidators\Manager::class );
		$this->injector->share( ConditionValidators\Factory::class );
		$this->injector->share( ConditionValidators\AdaptersManager::class );
		$this->injector->share( GenerateFileAction\Includes\UploadDirAdapter::class );

		foreach ( $this->declare_modules() as $module_class ) {
			$this->injector->share( $module_class )->make( $module_class );
		}

		\JFB_License_Manager::instance();
	}

	/**
	 * @return Injector
	 */
	public function get_injector(): Injector {
		return $this->injector;
	}

}
