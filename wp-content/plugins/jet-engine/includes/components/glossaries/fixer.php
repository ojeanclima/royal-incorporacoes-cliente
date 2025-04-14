<?php
namespace Jet_Engine\Glossaries;

/**
 * Glossary fixer class
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Define Fixer class
 */
class Fixer {

	private $args = array(
		'glossary_id' => '',
		'glossary'    => array(),
	);

	protected $nonce = '_jet-engine-glossary-%s-fix-nonce';

	protected $updated = false;

	public function __construct( $glossary ) {

		if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( empty( $glossary['id'] ) ) {
			return;
		}

		$this->args['glossary'] = $glossary;

		$this->args['glossary_id'] = $this->args['glossary']['id'];

		add_action( 'admin_notices', array( $this, 'init_notices' ) );
		add_action( 'admin_init',    array( $this, 'do_update' ) );

	}

	private function is_glossary_broken() {
		$glossary = $this->args['glossary'];

		$array_values = array(
			'labels'      => true,
			'args'        => true,
			'fields'      => true,
			'meta_fields' => true,
		);

		foreach ( $glossary as $key => $value ) {
			if ( isset( $array_values[$key] ) && ! is_array( maybe_unserialize( $value ) ) ) {
				return true;
			}
		}

		return false;
	}

	public function ensure_glossary_structure( $item = array() ) {
		$array_values = array(
			'labels'      => true,
			'args'        => true,
			'fields'      => true,
			'meta_fields' => true,
		);

		foreach ( $item as $key => $value ) {
			if ( isset( $array_values[$key] ) && ! is_array( maybe_unserialize( $value ) ) ) {
				$is_broken = true;

				$item[ $key ] = preg_replace_callback(
					'/s:(\d+?):"(.+?)"/',
					function( $matches ) {
						return sprintf( 's:%s:"%s"', strlen( $matches[2] ), $matches[2] );
					},
					$item[ $key ]
				);

				if ( ! is_array( maybe_unserialize( $item[ $key ] ) ) ) {
					$item[ $key ] = array();
				}
			}
		}

		$item = array_map( 'maybe_unserialize', $item );
		$item['meta_fields'] = $item['fields'];
		unset( $item['fields'] );
		
		if ( isset( $item['args']['source'] ) && $item['args']['source'] === 'file' ) {
			$id = attachment_url_to_postid( $item['args']['source_file']['url'] );
			
			if ( ! get_attached_file( $item['args']['source_file']['id'] ) || $id !== $item['args']['source_file']['id'] ) {
				if ( get_attached_file( $id ) ) {
					$item['args']['source_file']['id'] = $id;
					$item['args']['source_file']['name'] = get_the_title( $id );
				} else {
					$item['args']['source_file']['name'] = 'FILE MISSING';
				}
			}
		}

		jet_engine()->glossaries->data->update_item_in_db( $item );

		$this->updated = true;

		return $item;
	}

	public function do_update() {

		if ( ! $this->is_current_update() ) {
			return;
		}

		$item = $this->args['glossary'];

		$this->ensure_glossary_structure( $item );

	}

	private function is_current_update() {

		if ( empty( $_GET['je_glossary_fix'] ) || empty( $_GET['_nonce'] ) ) {
			return false;
		}

		if ( $_GET['je_glossary_fix'] !== $this->args['glossary_id'] ) {
			return false;
		}

		$nonce_action = sprintf( $this->nonce, esc_attr( $this->args['glossary_id'] ) );

		if ( ! wp_verify_nonce( $_GET['_nonce'], $nonce_action ) ) {
			return false;
		}
		
		return true;
	}

	public function init_notices() {
		if ( ! $this->updated && $this->is_glossary_broken() ) {
			$this->show_failure();
		} else {
			$this->show_updated_notice();
		}
	}

	private function show_failure() {

		echo '<div class="notice notice-error">';
			echo '<p>';
				$this->notice_title();
				printf(
					'Glossary ID: %s is broken. Check %s or press the button to try fixing it.',
					$this->args['glossary_id'],
					sprintf( '<a href="%s">glossary settings</a>', admin_url( 'admin.php?page=jet-engine#glossaries' ) )
				);
			echo '</p>';
			echo '<p>';
				$this->notice_submit();
			echo '</p>';
		echo '</div>';
	}

	private function show_updated_notice() {
		echo '<div class="notice notice-success is-dismissible">';
			echo '<p>';
				$this->notice_title();
				printf( 'Glossary %s successfully restored.', $this->args['glossary_id'] );
			echo '</p>';
		echo '</div>';
	}

	private function notice_submit() {

		$format = '<a href="%1s" class="button button-primary">%2$s</a>';
		$label  = __( 'Fix', 'jet-engine' );
		$url    = add_query_arg(
			array(
				'je_glossary_fix' => $this->args['glossary_id'],
				'_nonce'          => $this->create_nonce(),
			),
			esc_url( admin_url( 'index.php' ) )
		);

		printf( $format, $url, $label );

	}

	private function create_nonce() {
		return wp_create_nonce( sprintf( $this->nonce, $this->args['glossary_id'] ) );
	}

	private function notice_title() {
		printf( '<strong>%1$s</strong> &#8211; ', 'JetEngine Glossary Failure' );
	}

}
