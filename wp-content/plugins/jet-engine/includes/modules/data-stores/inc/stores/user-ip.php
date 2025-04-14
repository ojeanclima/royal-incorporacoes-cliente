<?php
namespace Jet_Engine\Modules\Data_Stores\Stores;

use Jet_Engine\Modules\Data_Stores\Module;

class User_Ip_Store extends Base_Store {

	public $db = null;

	/**
	 * Store type ID
	 */
	public function type_id() {
		return 'user_ip';
	}

	/**
	 * Store type name
	 */
	public function type_name() {
		return __( 'User IP', 'jet-engine' );
	}

	/**
	 * Store-specific initialization actions
	 */
	public function on_init() {

		if ( ! class_exists( '\Jet_Engine\Modules\Data_Stores\DB' ) ) {
			require_once Module::instance()->module_path( 'db.php' );
		}

		$schema = array(
			'store_id'   => 'TEXT',
			'store_item' => 'TEXT',
			'user_ip'    => 'TEXT',
		);

		$this->db = new \Jet_Engine\Modules\Data_Stores\DB( 'user_ip', $schema );

		if ( ! $this->db->is_table_exists() ) {
			$this->db->create_table();
		}

	}

	public function get_user_ip_hash() {

		$server_ip_keys = array(
			'HTTP_CLIENT_IP',
			'HTTP_X_FORWARDED_FOR',
			'HTTP_X_FORWARDED',
			'HTTP_X_CLUSTER_CLIENT_IP',
			'HTTP_FORWARDED_FOR',
			'HTTP_FORWARDED',
			'REMOTE_ADDR',
		);

		// Fallback local ip.
		$user_ip = '127.0.0.1';

		foreach ( $server_ip_keys as $key ) {

			if ( empty( $_SERVER[ $key ] ) ) {
				continue;
			}

			if ( ! filter_var( $_SERVER[ $key ], FILTER_VALIDATE_IP ) ) {
				continue;
			}

			$address_chain = explode( ',', $_SERVER[ $key ] );
			$user_ip       = trim( $address_chain[0] );
			break;
		}

		$user_ip = sanitize_text_field( $user_ip );

		return md5( $user_ip );
	}

	/**
	 * Get post IDs from store (unfiltered)
	 */
	public function get_unfiltered( $store_id ) {

		$val = $this->db->query( array(
			'store_id' => $store_id,
			'user_ip'  => $this->get_user_ip_hash(),
		) );

		if ( ! empty( $val ) ) {
			$store = wp_list_pluck( $val, 'store_item' );
		} else {
			$store = array();
		}

		return $store;
	}

	/**
	 * Get post IDs from store
	 */
	public function get( $store_id ) {
		return apply_filters( 'jet-engine/data-stores/store/data', $this->get_unfiltered( $store_id ), $store_id );
	}

	/**
	 * Add to store callback
	 */
	public function add_to_store( $store_id, $post_id ) {

		$store = $this->get_unfiltered( $store_id );
		$count = count( $store );

		if ( ! in_array( $post_id, $store ) ) {
			$inserted = $this->db->insert(
				array(
					'store_id'   => $store_id,
					'store_item' => $this->sanitize_store_item( $post_id ),
					'user_ip'    => $this->get_user_ip_hash(),
				)
			);

			if ( $inserted ) {
				$count++;
			}
		}

		return $count;
	}

	/**
	 * Add to store callback
	 */
	public function remove( $store_id, $post_id ) {

		$store = $this->get_unfiltered( $store_id );

		if ( false !== ( $index = array_search( $post_id, $store ) ) ) {

			$this->db->delete(
				array(
					'store_id'   => $store_id,
					'store_item' => $post_id,
					'user_ip'    => $this->get_user_ip_hash(),
				)
			);

			unset( $store[ $index ] );
		}

		$count = count( $store );

		return $count;
	}

	/**
	 * Clear store.
	 *
	 * @param $store_id
	 * @param $expiration
	 */
	public function clear_store( $store_id = null, $expiration = null ) {

		if ( empty( $store_id ) ) {
			return;
		}

		$args = array(
			'store_id' => $store_id,
		);

		if ( $expiration ) {
			$args[] = array(
				'field'    => 'created',
				'operator' => '<',
				'value'    => $expiration,
			);
		}

		$this->db->delete( $args );
	}

}
