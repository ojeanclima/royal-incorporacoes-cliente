<?php
/**
 * Workflows State manager
 */
namespace Croblock\Workflows;

class State {

	/**
	 * A reference to an instance of this class.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    Module
	 */
	private static $instance = null;

	/**
	 * @var Conditions\Manager
	 */
	public $workflows = null;

	private $nonce_key = 'crocoblock-workflows-state';

	/**
	 * Constructor for the class
	 */
	public function __construct() {
		add_action( 'wp_ajax_crocoblock_workflow_state_change', [ $this, 'process_ajax_state' ] );
	}

	/**
	 * Return instance oif state manager with provided workflows parent
	 * @param  [type] $workflows [description]
	 * @return [type]            [description]
	 */
	public function with_context( $workflows ) {
		$this->workflows = $workflows;
		return $this;
	}

	/**
	 * Ajax callback to process state update
	 * @return [type] [description]
	 */
	public function process_ajax_state() {
		
		$nonce = ! empty( $_REQUEST['nonce'] ) ? $_REQUEST['nonce'] : false;

		if ( ! $nonce || ! wp_verify_nonce( $nonce, $this->nonce_key ) ) {
			wp_send_json_error( __( 'The page is expired. Please reload page and try again', 'jet-engine' ) );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Access denied', 'jet-engine' ) );
		}

		$data = ! empty( $_REQUEST['data'] ) ? $_REQUEST['data'] : [];
		$action = $data['action'] ?? false;
		$workflow_id = ! empty( $_REQUEST['workflow'] ) ? $_REQUEST['workflow'] : false;

		if ( ! $workflow_id || ! $action ) {
			wp_send_json_error( __( 'Incomplete request', 'jet-engine' ) );
		}

		switch ( $action ) {
			case 'to_step':
				$step = $data['step'] ?? false;
				if ( false !== $step ) {
					$this->update_state( $workflow_id, 'step', $step );
				}
				break;
			
			case 'pause':
				$this->update_state( $workflow_id, 'status', 'pause' );
				break;

			case 'resume':
				$this->pause_all();
				$this->update_state( $workflow_id, 'status', 'in-progress' );
				break;

			case 'start':
				$this->pause_all();
				$this->update_state( $workflow_id, 'status', 'in-progress' );
				$this->update_state( $workflow_id, 'step', 0, false );
				break;

			case 'complete':
				$this->update_state( $workflow_id, 'status', 'complete' );
				$this->update_state( $workflow_id, 'step', 0 );
				break;
		}

		wp_send_json_success();

	}

	/**
	 * Pause all currently active workflow before starting/resuming a new one
	 * @return [type] [description]
	 */
	public function pause_all() {

		$data = $this->get_state();

		foreach ( $data as $workflow_id => $workflow_state ) {
			$data[ $workflow_id ]['status'] = 'pause';
		}

		update_user_meta( get_current_user_id(), 'crocoblock_workflows_state', $data );

	}

	/**
	 * Check if current user has workflows with the status 'in-progress'
	 * 
	 * @return boolean [description]
	 */
	public function get_active_workflow( $workflow_id = false ) {

		$data = $this->get_state();

		if ( empty( $data ) ) {
			return false;
		}

		if ( $workflow_id ) {
			return ( isset( $data[ $workflow_id ] ) 
				&& ! empty( $data[ $workflow_id ]['status'] ) 
				&& in_array( $data[ $workflow_id ]['status'], [ 'pause', 'in-progress' ] ) )
					? $data[ $workflow_id ] 
					: false;
		}

		foreach ( array_reverse( $data ) as $w_id => $w_state ) {
			if ( ! empty( $w_state['status'] ) && 'in-progress' === $w_state['status'] ) {
				return $w_state;
			}
		}

		return false;

	}

	public function get_prepared_active_workflow() {
	
		$active_workflow        = $this->get_active_workflow();
		$merged_active_workflow = false;

		if ( $active_workflow ) {
			
			$all_workflows = $this->workflows->storage()->get_workflows();

			foreach ( $all_workflows as $workflow ) {
				if ( $workflow['id'] == $active_workflow['id'] ) {
					$merged_active_workflow = array_merge( $active_workflow, $workflow );
					break;
				}
			}

			if ( $merged_active_workflow && ! empty( $merged_active_workflow['steps'] ) ) {
				
				$merged_active_workflow = $this->workflows->dependencies()->add_checked_dependencies(
					$merged_active_workflow
				);

			}

			// If has active workflows, but not merge it - is broken workflows, so clean it
			if ( ! $merged_active_workflow ) {
				delete_user_meta( get_current_user_id(), 'crocoblock_workflows_state' );
			}

		}

		return $merged_active_workflow;

	}

	/**
	 * Get all state data for current user
	 * 
	 * @return [type] [description]
	 */
	public function get_state() {
		
		$data = get_user_meta( get_current_user_id(), 'crocoblock_workflows_state', true );

		if ( ! $data ) {
			$data = [];
		}

		return $data;
	}

	/**
	 * Update state for given workflow
	 * 
	 * @param  [type] $workflow_id [description]
	 * @param  [type] $property    [description]
	 * @param  [type] $value       [description]
	 * @return [type]              [description]
	 */
	public function update_state( $workflow_id, $property, $value, $force_rewrite = true ) {
		
		$data = $this->get_state();

		if ( ! isset( $data[ $workflow_id ] ) ) {
			$data[ $workflow_id ] = [ 'id' => $workflow_id ];
		}

		if ( $force_rewrite ) {
			$data[ $workflow_id ][ $property ] = $value;
		} elseif ( ! isset( $data[ $workflow_id ][ $property ] ) ) {
			$data[ $workflow_id ][ $property ] = $value;
		}

		update_user_meta( get_current_user_id(), 'crocoblock_workflows_state', $data );

	}

	/**
	 * Returns a nonce for states
	 * @return [type] [description]
	 */
	public function nonce() {
		return wp_create_nonce( $this->nonce_key );
	}

	/**
	 * Returns the instance.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return Module
	 */
	public static function instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

}