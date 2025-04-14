<?php
namespace Jet_Engine\Website_Builder;


// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Models_Store {

	protected $store_name = 'jet_engine_website_models';

	/**
	 * A reference to an instance of this class.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    object
	 */
	private static $instance = null;

	/**
	 * Get stored models
	 *
	 * @return [type] [description]
	 */
	public function get_models() {

		$models = get_option( $this->store_name, [] );

		if ( ! is_array( $models ) ) {
			$models = [];
		}

		return $models;
	}

	/**
	 * Add new model
	 */
	public function add_model( array $model = [] ) {

		$models = $this->get_models();

		if ( ! empty( $model['log_data'] ) ) {
			foreach ( $model['log_data'] as $key => $value ) {
				$model['log_data'][ $key ] = wp_unslash( $value );
			}
		}

		if ( ! isset( $model['uid'] ) ) {

			$model['uid'] = $this->get_uid();
			$model['name'] = ! empty( $model['name'] ) ? $model['name'] : 'Model #' . $model['uid'];

			$models[] = $model;
		} else {
			foreach ( $models as $i => $saved_model ) {
				if ( $saved_model['uid'] === $model['uid'] ) {
					$models[ $i ] = array_merge( $saved_model, $model );
				}
			}
		}

		update_option( $this->store_name, $models, false );

		return $model['uid'];
	}

	/**
	 * Get model data by ID
	 *
	 * @param  int|integer $model_uid [description]
	 * @return bool|array
	 */
	public function get_model( int $model_uid = 0 ) {

		$models = $this->get_models();

		if ( ! empty( $models ) ) {
			foreach ( $models as $model ) {
				if ( $model_uid === $model['uid'] ) {

					if ( ! empty( $model['log_data'] ) ) {
						foreach ( $model['log_data'] as $key => $value ) {
							$model['log_data'][ $key ] = wp_unslash( $value );
						}
					}

					return $model;
				}
			}
		}

		return false;
	}

	/**
	 * Delete model
	 */
	public function delete_model( int $model_uid = 0 ) {

		if ( ! $model_uid ) {
			return;
		}

		$offset = -1;
		$models = $this->get_models();

		if ( ! empty( $models ) ) {
			foreach ( $models as $i => $model ) {
				if ( $model['uid'] === $model_uid ) {
					unset( $models[ $i ] );
					break;
				}
			}
		}

		update_option( $this->store_name, $models, false );
	}

	/**
	 * Return UID for the model
	 *
	 * @return int
	 */
	public function get_uid() {
		return rand(10000, 99999);
	}

	/**
	 * Returns the instance.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return Jet_Engine
	 */
	public static function instance() {
		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

}
