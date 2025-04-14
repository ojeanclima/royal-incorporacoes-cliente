<?php
namespace Jet_Engine\Listings\Components;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Define state manager class
 */
class State {

	protected $state = [];

	protected $parents = [];

	/**
	 * Set current state
	 * 
	 * @param array $state [description]
	 */
	public function set( $state = [] ) {

		if ( ! empty( $this->state ) ) {
			$this->parents[] = $this->state;
		}

		if ( ! empty( $state['heading_text'] ) ) {
			$state['heading_text_copy'] = $state['heading_text'];
		}

		$this->state = $state;
	}

	/**
	 * Empty current state
	 * or reset to latest parent
	 * @return [type] [description]
	 */
	public function reset() {

		$initial_state = [];

		if ( ! empty( $this->parents ) ) {
			$latest_parent = count( $this->parents ) - 1;
			if ( isset( $this->parents[ $latest_parent ] ) ) {
				$initial_state = $this->parents[ $latest_parent ];
				unset( $this->parents[ $latest_parent ] );
			}
		}

		$this->state = $initial_state;

	}

	/**
	 * Get current component state
	 * 
	 * @return [type] [description]
	 */
	public function get( $field = null, $default = '' ) {

		if ( ! $field ) {
			return $this->state;
		}

		$field_value = isset( $this->state[ $field ] ) ? $this->state[ $field ] : $default;

		return apply_filters( 'jet-engine/listings/components/state/get', $field_value, $field, $this );

	}

}
