<?php
/**
 * Website builder main class
 */
namespace Jet_Engine\Website_Builder;


// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Manager {

	/**
	 * A reference to an instance of this class.
	 *
	 * @access private
	 * @var    object
	 */
	private static $instance = null;

	public function __construct() {

		// There is nothing to do on the front.
		if ( ! is_admin() ) {
			return;
		}

		add_action( 'admin_menu', [ $this, 'register_menu_page' ], 99 );
		add_action( 'wp_ajax_' . $this->hook_name(), [ $this, 'handle_request' ] );

	}

	public function slug() {
		return 'website-builder';
	}

	public function hook_name() {
		return 'jet-engine-' . $this->slug();
	}

	public function component_path( $relative_path = '' ) {
		return jet_engine()->plugin_path( 'includes/components/website-builder/inc/' . $relative_path );
	}

	public function component_url( $relative_url = '' ) {
		return jet_engine()->plugin_url( 'includes/components/website-builder/assets/' . $relative_url );
	}

	public function handle_request() {

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'You have no permissions to do this', 'jet-engine' ) );
		}

		if ( empty( $_REQUEST['nonce'] ) || ! wp_verify_nonce( $_REQUEST['nonce'], $this->hook_name() ) ) {
			wp_send_json_error( __( 'The page is expired. Please reload it and try again.', 'jet-engine' ) );
		}

		$buider_action = ! empty( $_REQUEST['builder_action'] ) ? $_REQUEST['builder_action'] : false;

		require_once $this->component_path( 'models-store.php' );
		require_once $this->component_path( 'handler.php' );
		require_once $this->component_path( 'api.php' );

		switch ( $buider_action ) {
			case 'prepare_model':

				$topic         = ! empty( $_REQUEST['topic'] ) ? strip_tags( $_REQUEST['topic'] ) : false;
				$functionality = ! empty( $_REQUEST['functionality'] ) ? strip_tags( $_REQUEST['functionality'] ) : false;

				if ( ! $topic || ! $functionality ) {
					wp_send_json_error(
						__( 'Required parameters are missing, please fill the form and try again.', 'jet-engine' )
					);
				}

				$api   = new API();
				$model = $api->set_prompts( [
					'topic'         => $topic,
					'functionality' => $functionality,
				] )->get_model();

				wp_send_json_success( $model );
				// Kept just in case, with normal flow should be aborted on wp_send_json_success.
				break;

			case 'create_model':

				$model = ! empty( $_REQUEST['model'] ) ? $_REQUEST['model'] : false;

				try {

					if ( ! $model ) {
						wp_send_json_error( 'Can`t create an empty model.' );
					}

					$result        = Handler::instance()->create_model( $model );
					$model_uid     = false;
					$topic         = ! empty( $_REQUEST['topic'] ) ? $_REQUEST['topic'] : '';
					$functionality = ! empty( $_REQUEST['functionality'] ) ? $_REQUEST['functionality'] : '';

					Handler::instance()->log_data( 'topic', $topic );
					Handler::instance()->log_data( 'functionality', $functionality );

					if ( $result ) {
						$model_uid = Models_Store::instance()->add_model( Handler::instance()->get_model_log() );
					}

					wp_send_json_success( [ 'uid' => $model_uid ] );
				} catch ( \Exception $e ) {
					wp_send_json_error( $e->getMessage() );
				}

				// Kept just in case, with normal flow should be aborted on wp_send_json_success.
				break;

			case 'get_model':

				$model_uid = ! empty( $_REQUEST['uid'] ) ? absint( $_REQUEST['uid'] ) : false;
				$model     = false;

				if ( $model_uid ) {
					$model = Models_Store::instance()->get_model( $model_uid );
				}

				if ( ! $model_uid || ! $model ) {
					wp_send_json_error( __( 'Model not found', 'jet-engine' ) );
				}

				wp_send_json_success( [
					'html'     => Handler::instance()->get_results_html( $model ),
					'name'     => isset( $model['name'] ) ? $model['name'] : '',
					'log_data' => isset( $model['log_data'] ) ? $model['log_data'] : [],
				] );
				// Kept just in case, with normal flow should be aborted on wp_send_json_success.
				break;

			case 'update_model':

				$model = ! empty( $_REQUEST['model'] ) ? $_REQUEST['model'] : [];
				$model['uid'] = isset( $model['uid'] ) ? absint( $model['uid'] ) : false;

				if ( $model['uid'] && ! empty( $model['name'] ) ) {
					$model['name'] = sanitize_text_field( $model['name'] );
					Models_Store::instance()->add_model( $model );
				}

				wp_send_json_success();
				// Kept just in case, with normal flow should be aborted on wp_send_json_success.
				break;

			case 'delete_model':

				$model_uid = ! empty( $_REQUEST['uid'] ) ? absint( $_REQUEST['uid'] ) : false;

				if ( ! $model_uid ) {
					wp_send_json_error( __( 'Model not found', 'jet-engine' ) );
				}

				if ( $model_uid ) {
					Models_Store::instance()->delete_model( $model_uid );
				}

				wp_send_json_success();
				// Kept just in case, with normal flow should be aborted on wp_send_json_success.
				break;

			default:
				wp_send_json_error( __( 'Allowed builder action doesn`t found in the request.', 'jet-engine' ) );
				// Kept just in case, with normal flow should be aborted on wp_send_json_error.
				break;
		}

	}

	public function register_menu_page() {

		require_once $this->component_path( 'page.php' );
		$page = new Page();

		$page->register();

	}

	/**
	 * Returns the instance.
	 *
	 * @access public
	 * @return object
	 */
	public static function instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;

	}

}

Manager::instance();
