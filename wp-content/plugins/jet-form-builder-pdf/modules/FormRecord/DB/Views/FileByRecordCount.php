<?php


namespace JFB_PDF_Modules\FormRecord\DB\Views;

use Jet_Form_Builder\Db_Queries\Views\View_Base_Count_Trait;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * @method static FileByRecordCount findOne( $columns )
 *
 * Class PaymentsBySubscriptionCount
 * @package Jet_FB_Paypal\QueryViews
 */
class FileByRecordCount extends FileByRecord {

	use View_Base_Count_Trait;
}
