<?php
namespace Jet_Engine\Modules\Maps_Listings\Compatibility;

class Borlabs_Cookie_v3 {

	public function __construct() {
		add_action( 'jet-engine/maps-listing/settings/after-controls', array( $this, 'add_settings_fields' ), 99 );
	}

	public function add_settings_fields() {

		$url = admin_url( 'admin.php?page=borlabs-cookie-library&borlabs-filter=compatibility-patch' );
		?>
		<div class="validatation-result validatation-result--error">
			<p>Since <b>Borlabs Cookie</b> added an official package for JetEngine maps, it's mandatory to use this addon, so we removed the integration from JetEngine side. You can install an official package from  <a href="<?php echo $url; ?>">Borlabs Cookie > Library page</a>.</p>
			<p>If you have any questions about Borlabs Cookie integration, please contact <a href="https://borlabs.io/support/">Borlabs Cookie plugin support</a>.</p>
		</div>
		<?php
	}

}
