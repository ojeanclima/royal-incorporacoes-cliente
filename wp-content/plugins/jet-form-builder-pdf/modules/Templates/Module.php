<?php


namespace JFB_PDF_Modules\Templates;

use JFB_PDF\Vendor\Auryn\ConfigException;
use JFB_PDF\Vendor\Auryn\Injector;
use JFB_PDF_Modules\Templates\Blocks\Manager;
use JFB_PDF_Modules\Templates\ConditionValidators\AdaptersManager;
use JFB_PDF_Modules\Templates\ConditionValidators\Factory;
use JFB_PDF_Modules\Templates\PostType\PostType;
use JFB_PDF_Modules\Templates\ConditionValidators;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die();
}

class Module {

	public function __construct(
		PostType $post_type,
		Blocks\Manager $block_manager,
		Editor $editor
	) {
		$post_type->init_hooks();
		$block_manager->init_hooks();
		$editor->init_hooks();
	}


}
