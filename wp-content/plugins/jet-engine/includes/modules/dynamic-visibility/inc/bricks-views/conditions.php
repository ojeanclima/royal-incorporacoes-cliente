<?php

namespace Jet_Engine\Modules\Dynamic_Visibility\Bricks_Views;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Conditions {
	public function __construct() {
		if ( ! $this->has_bricks() ) {
			return;
		}

		add_filter( 'bricks/conditions/groups', [ $this, 'add_condition_group' ] );
		add_filter( 'bricks/conditions/options', [ $this, 'add_condition' ] );
		add_filter( 'bricks/conditions/result', [ $this, 'run_condition' ], 10, 3 );
	}

	public function add_condition_group( $groups ) {
		// Ensure your group name is unique (best to prefix it)
		$groups[] = [
			'name'  => 'jet_engine_group',
			'label' => esc_html__( 'Jet Engine', 'jet-engine' ),
		];

		return $groups;
	}

	public function add_condition( $options ) {
		$shortcode_description = sprintf(
			__( 'Use JetEngine Condition shortcode. You can create it in a visual way with JetEngine / %s.', 'jet-engine' ),
			'<a href="' . admin_url( 'admin.php?page=jet-engine#shortcode_generator' ) . '" target="_blank">Shortcode Generator</a>'
		);

		// Ensure key is unique, and that group exists
		$options[] = [
			'key'     => 'jedv_condition',
			'label'   => esc_html__( 'Dynamic Visibility', 'jet-engine' ),
			'group'   => 'jet_engine_group',
			'compare' => [
				'type'        => 'select',
				'options'     => [
					'==' => esc_html__( 'Condition is met', 'jet-engine' ),
					'!=' => esc_html__( 'Condition is NOT met', 'jet-engine' ),
				],
				'placeholder' => esc_html__( 'is', 'jet-engine' ),
			],
			'value'   => [
				'type'        => 'text',
				'placeholder' => 'Enter a JetEngine Condition shortcode',
				'description' => $shortcode_description,
			],
		];

		return $options;
	}

	public function run_condition( $result, $condition_key, $condition ) {
		// If $condition_key is not 'my_post_type', we return the $render as it is
		if ( $condition_key !== 'jedv_condition' ) {
			return $result;
		}

		// In my example, if compare is empty, we set it to '==' as default
		$compare    = $condition['compare'] ?? '==';
		$user_value = $condition['value'] ?? false;

		if ( ! preg_match( '/^\[[^\]]+\]/', $user_value, $matches ) ) {
			return $result;
		}

		$shortcode = $matches[0];

		if ( ! shortcode_exists( strtok( trim( $shortcode, '[]' ), ' ' ) ) ) {
			return $result;
		}

		$shortcode_result = filter_var( do_shortcode( $shortcode ), FILTER_VALIDATE_BOOLEAN );

		$condition_met = false;

		switch ( $compare ) {
			case '==': // "is met"
				$condition_met = true === $shortcode_result;
				break;
			case '!=': // "is not met"
				$condition_met = false === $shortcode_result;
				break;
		}

		return $condition_met;
	}

	public function has_bricks() {
		return ( defined( 'BRICKS_VERSION' ) && \Jet_Engine\Modules\Performance\Module::instance()->is_tweak_active( 'enable_bricks_views' ) );
	}
}