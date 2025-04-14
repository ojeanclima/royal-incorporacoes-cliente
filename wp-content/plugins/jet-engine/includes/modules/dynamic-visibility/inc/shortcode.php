<?php
namespace Jet_Engine\Modules\Dynamic_Visibility;

class Shortcode {

	protected $shortdcode_tag = 'jet_engine_condition';

	public function __construct() {
		add_shortcode( $this->shortdcode_tag, [ $this, 'do_shortcode' ] );
		add_filter( 'jet-engine/shortcodes/generator-shortcode-types', [ $this, 'register_shortcode_for_generator' ] );
		add_filter( 'jet-engine/shortcodes/generator-config', [ $this, 'register_generator_config' ] );
		add_action( 'jet-engine/dashboard/assets', [ $this, 'generator_assets' ] );
	}

	/**
	 * Enqueue shrotcode genrator-related assets
	 *
	 * @return void
	 */
	public function generator_assets() {
		wp_enqueue_script(
			'jet-engine-dynamic-visibility-shortcode-generator',
			jet_engine()->plugin_url( 'includes/modules/dynamic-visibility/inc/assets/js/shortcode-generator.js' ),
			array( 'jet-plugins' ),
			jet_engine()->get_version(),
			true
		);
	}

	/**
	 * Register a JetEngine Condition to be available in SHortcode Generator.
	 *
	 * @param  array $shortcode_types Initial shortcode types.
	 * @return array
	 */
	public function register_shortcode_for_generator( $shortcode_types ) {
		$shortcode_types[ $this->shortdcode_tag ] = 'JetEngine Condition';
		return $shortcode_types;
	}

	/**
	 * Add JetEngine Condition configs to shortcode generator config.
	 *
	 * @param  array $config Intial config.
	 * @return array
	 */
	public function register_generator_config( $config ) {

		$config['visibility_condition_type'] = [
			[
				'value' => 'show',
				'label' => 'Show element if condition met',
			],
			[
				'value' => 'hide',
				'label' => 'Hide element if condition met',
			],
		];

		$config['condition_controls'] = $this->get_prepared_controls();

		$config['labels']['visibility_condition_type'] = [
			'label' => __( 'Visibility Condition Type', 'jet-engine' ),
			'description' => __( 'How the condition result will be processed', 'jet-engine' ),
		];

		return $config;
	}

	/**
	 * Get conditinal-specific controls.
	 *
	 * @return array
	 */
	public function get_prepared_controls() {

		if ( ! jet_engine()->modules->is_module_active( 'dynamic-visibility' ) ) {
			return [];
		}

		$controls = \Jet_Engine_Tools::prepare_controls_for_js(
			Module::instance()->get_condition_controls()
		);

		$prepared_controls = [];

		foreach ( $controls as $control ) {
			$prepared_controls[ $control['name'] ] = $control;
		}

		return $prepared_controls;
	}

	/**
	 * Handle shortcode.
	 *
	 * @param array $atts Attributes passed to the shortcode.
	 * @param string|null $content Content inside the shortcode.
	 *
	 * @return string|bool          Rendered content or boolean result.
	 */
	public function do_shortcode( $atts = [], $content = null ) {
		$checker = new Condition_Checker();

		// This block handles cases like multiselect, where values are separated by commas
		if ( isset( $atts['_parse_attrs'] ) ) {
			$parse_atts = explode( ',', $atts['_parse_attrs'] );

			foreach ( $parse_atts as $attr ) {
				if ( isset( $atts[ $attr ] ) && is_string( $atts[ $attr ] ) ) {
					$atts[ $attr ] = explode( ',', $atts[ $attr ] );
				}
			}
		}

		// Settings
		$settings = [ 'jedv_enabled' => true ];
		if ( ! empty( $content ) ) {
			$settings['jedv_type'] = $atts['jedv_type'] ?? 'show';
		}

		// Check condition
		$condition = $checker->check_cond( $settings, [
			'jedv_conditions' => [ $atts ]
		] );

		// Return result
		if ( ! empty( $content ) ) {
			if ( $condition ) {
				$safe_content = wp_kses_post( $content ?? '' );
				$result       = do_shortcode( $safe_content );
			} else {
				$result = '';
			}
		} else {
			$result = $condition;
		}

		return $result;
	}
}
