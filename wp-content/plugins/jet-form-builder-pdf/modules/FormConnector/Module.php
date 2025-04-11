<?php

namespace JFB_PDF_Modules\FormConnector;

use JFB_PDF\Vendor\Auryn\ConfigException;
use JFB_PDF\Vendor\Auryn\InjectionException;
use JFB_PDF\Vendor\Auryn\Injector;
use JFB_PDF_Modules\FormConnector\Templates\Editor;
use JFB_PDF_Modules\FormConnector\Templates\MacrosAdapter;
use JFB_PDF_Modules\FormConnector\Templates\PostType;
use JFB_PDF_Modules\FormConnector\Templates\ValidatorsAdapters\Between;
use JFB_PDF_Modules\FormConnector\Templates\ValidatorsAdapters\Compare;
use JFB_PDF_Modules\FormConnector\Templates\ValidatorsAdapters\Field;
use JFB_PDF_Modules\FormConnector\Templates\ValidatorsAdapters\InTheList;
use JFB_PDF_Modules\Templates\ConditionValidators\AdaptersManager;

class Module {

	/**
	 * @param Editor $editor
	 * @param PostType $post_type
	 * @param MacrosAdapter $macros
	 * @param AdaptersManager $adapter
	 *
	 * @throws ConfigException
	 */
	public function __construct(
		Editor $editor,
		PostType $post_type,
		MacrosAdapter $macros,
		AdaptersManager $adapter
	) {
		$editor->init_hooks();
		$post_type->init_hooks();
		$macros->init_hooks();

		$adapter->register( Field::class );
		$adapter->register( Compare::class );
		$adapter->register( Between::class );
		$adapter->register( InTheList::class );
	}

}
