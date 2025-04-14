<?php


namespace JetLoginCore\Common;


class MetaQuery {


	/**
	 *
	 * @param array $params
	 *
	 * @return array
	 */
	public static function get_json_meta( array $params ) {
		$params = array_merge( array(
			'id'  => 0,
			'key' => ''
		), $params );

		$post_id = $params['id'];
		$key     = $params['key'];

		if ( ! $post_id ) {
			$post_id = get_the_ID();
		}
		$meta = get_post_meta( $post_id, $key, true );

		if ( $meta ) {
			/** For compatibility with php 7.0.x */
			if ( defined( 'JSON_INVALID_UTF8_IGNORE' ) ) {
				return json_decode( $meta, true, 512, JSON_INVALID_UTF8_IGNORE );
			}

			return json_decode( $meta, true );
		}

		return array();
	}

	public static function set_json_meta( array $params ) {
		$params = array_merge( array(
			'id'    => 0,
			'key'   => '',
			'value' => array()
		), $params );

		$post_id = $params['id'];
		$key     = $params['key'];
		$value   = $params['value'];

		if ( ! $post_id ) {
			$post_id = get_the_ID();
		}

		return update_post_meta( $post_id, $key, json_encode( $value, JSON_UNESCAPED_UNICODE ) );
	}

}