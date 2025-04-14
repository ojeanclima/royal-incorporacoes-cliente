<?php
/**
 * Conditional tags editor and apply implementation
 */
namespace Jet_Engine\Timber_Views;

use Twig\TwigFunction;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Conditional_Tags {

	public $function_name = 'jet_engine_show_if';

	/**
	 * - Register UI
	 * - Register new twig function
	 * - call conditions checker inside this function
	 */
	public function __construct() {
		add_action( 'jet-engine/twig-views/editor/before-enqueue-assets', [ $this, 'conditions_assets' ] );
		add_action( 'jet-engine/twig-views/editor/custom-actions', [ $this, 'register_conditional_tags_action' ] );
		add_filter( 'timber/twig', [ $this, 'add_functions' ] );
	}

	public function add_functions( $twig ) {

		$twig->addFunction( new TwigFunction(
			$this->function_name,
			[ $this, 'check_condition' ]
		) );

		remove_filter( 'timber/twig', [ $this, 'add_functions' ] );

		return $twig;
	}

	public function check_condition( $args ) {

		if ( ! class_exists( '\Jet_Engine\Modules\Dynamic_Visibility\Condition_Checker' ) ) {
			return false;
		}

		$checker = new \Jet_Engine\Modules\Dynamic_Visibility\Condition_Checker();
		$result  = $checker->check_cond( [
			'jedv_enabled' => true,
		], [ 
			'jedv_conditions' => [ $args ]
		] );

		return $result;

	}

	/**
	 * Enqueue conditions-specific assets
	 * 
	 * @return [type] [description]
	 */
	public function conditions_assets() {

		wp_enqueue_script(
			'jet-engine-timber-editor-conditions', 
			Package::instance()->package_url( 'assets/js/conditions-editor.js' ),
			[ 'jquery', 'cx-vue-ui' ],
			jet_engine()->get_version(),
			true
		);

		wp_localize_script( 
			'jet-engine-timber-editor-conditions', 
			'JetEngineDynamicVisibilityData',
			[
				'controls'        => $this->get_prepared_controls(),
				'function_name'   => $this->function_name,
				'is_enabled'      => jet_engine()->modules->is_module_active( 'dynamic-visibility' ),
				'disabled_notice' => sprintf( 
					__( 'To use enhanced conditions, you need to activate <a href="%s" target="_blank">Dynamic Visibility</a> module', 'jet-engine' ),
					jet_engine()->dashboard->dashboard_url( 'modules' ),
				),
				'macros_notice'   => sprintf( 
					__( 'You can use <a href="%s" target="_blank">macros</a> on any text field', 'jet-engine' ),
					jet_engine()->dashboard->dashboard_url( 'macros_generator' ),
				),
				'twig_notice'     => __( 'More details about the conditional logic in the official <a href="https://twig.symfony.com/doc/2.x/tags/if.html" target="_blank">Twig documentation</a>', 'jet-engine' ),
				'macros_list'  => $this->get_macros_for_editor(),
				'context_list' => jet_engine()->listings->allowed_context_list( 'blocks' ),
			]
		);
	}

	public function get_macros_for_editor() {
		return jet_engine()->listings->macros->get_macros_for_js();
	}

	public function register_conditional_tags_action() {
		?>
		<jet-engine-timber-editor-conditions 
			@insert="insertDynamicData"
		></jet-engine-timber-editor-conditions>
		<?php
	}

	public function get_prepared_controls() {

		if ( ! jet_engine()->modules->is_module_active( 'dynamic-visibility' ) ) {
			return [];
		}

		$controls = \Jet_Engine_Tools::prepare_controls_for_js(
			\Jet_Engine\Modules\Dynamic_Visibility\Module::instance()->get_condition_controls()
		);

		$prepared_controls = [];

		foreach ( $controls as $control ) {
			$prepared_controls[ $control['name'] ] = $this->maybe_adjust_control( $control );
		}

		return $prepared_controls;

	}

	public function maybe_adjust_control( $control ) {
		if ( empty( $control['name'] ) ) {
			return $control;
		}

		switch ( $control['name'] ) {
			case 'jedv_field':
				$control['description'] = esc_html__( 'Enter meta field name or use macro to get value directly.', 'jet-engine' );
				break;
		}

		return $control;
	}

}
