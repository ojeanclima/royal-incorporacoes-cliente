<?php
/**
 * WPML compatibility package
 */

namespace Jet_Engine\Compatibility\Packages;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Engine_WPML_Package' ) ) {

	class Jet_Engine_WPML_Package {

		public function __construct() {

			if ( ! class_exists( 'SitePress' ) ) {
				return;
			}

			$this->create_package_instance();
		}

		public function create_package_instance() {
			require jet_engine()->plugin_path( 'includes/compatibility/packages/wpml/inc/package.php' );
			\Jet_Engine\Compatibility\Packages\Jet_Engine_WPML_Package\Package::instance();
		}

	}

}

new Jet_Engine_WPML_Package();
