<?php
namespace Jet_Engine\Macros;

/**
 * Return timestamp by string.
 */
class Str_To_Time extends \Jet_Engine_Base_Macros {

	/**
	 * @inheritDoc
	 */
	public function macros_tag() {
		return 'str_to_time';
	}

	/**
	 * @inheritDoc
	 */
	public function macros_name() {
		return esc_html__( 'String to timestamp', 'jet-engine' );
	}

	/**
	 * @inheritDoc
	 */
	public function macros_args() {
		return array(
			'str' => array(
				'label'   => __( 'String to convert', 'jet-engine' ),
				'type'    => 'text',
				'default' => '',
			),
			'adjust' => array(
				'label'   => __( 'Adjust', 'jet-engine' ),
				'type'    => 'select',
				'options' => array(
					'no'     => __( 'No', 'jet-engine' ),
					'server' => __( 'Adjust to server timezone', 'jet-engine' ),
				),
			),
		);
	}

	/**
	 * @inheritDoc
	 */
	public function macros_callback( $args = array() ) {

		$string  = ! empty( $args['str'] ) ? $args['str'] : false;
		$adjust = ! empty( $args['adjust'] ) ? $args['adjust'] : 'no';

		switch ( $adjust ) {
			case 'server':
				$offset = ( float ) get_option( 'gmt_offset', 0 ) * 3600;
				break;
			default:
				$offset = 0;
		}

		return strtotime( $string, strtotime( 'now' ) + $offset );
	}
}