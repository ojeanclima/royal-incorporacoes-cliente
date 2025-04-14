<?php
namespace Jet_Engine\Modules\Data_Stores\Stores;

use Jet_Engine\Modules\Data_Stores\Module;

class User_Ip_Schedules {

	private $manager = null;
	private $stores  = false;
	private $event_prefix = 'jet_data_store_user_ip_clear_';

	public function __construct( Manager $manager ) {

		$this->manager = $manager;

		if ( ! $this->has_user_ip_stores() ) {
			return;
		}

		add_action( 'init', array( $this, 'register_schedules' ), 99 );
	}

	public function has_user_ip_stores() {
		$store = $this->get_user_ip_stores();
		return ! empty( $store );
	}

	public function get_user_ip_stores() {

		if ( false === $this->stores ) {
			foreach ( $this->manager->get_stores() as $store ) {

				if ( 'user_ip' !== $store->get_arg( 'type' ) ) {
					continue;
				}

				if ( ! is_array( $this->stores ) ) {
					$this->stores = array();
				}

				$this->stores[] = $store;
			}
		}

		return $this->stores;
	}

	public function register_schedules() {

		foreach ( $this->get_user_ip_stores() as $store ) {

			if ( $this->auto_clear_enabled( $store ) ) {
				$this->schedule_event( $store );
				add_action( $this->event_hook( $store ), function () use ( $store ) {
					$this->clear_store( $store );
				} );
			} else {
				$this->unschedule_event( $store );
			}

		}
	}

	public function auto_clear_enabled( $store ) {
		return apply_filters( 'jet-engine/data-stores/user-ip/schedules/auto-clear-store', false, $store->get_arg( 'slug' ), $store );
	}

	public function schedule_event( $store ) {

		if ( ! $this->next_schedule( $store ) ) {
			wp_schedule_event(
				$this->event_timestamp( $store ),
				$this->event_interval( $store ),
				$this->event_hook( $store )
			);
		}
	}

	public function unschedule_event( $store ) {
		$timestamp = $this->next_schedule( $store );

		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, $this->event_hook( $store ) );
		}
	}

	public function event_hook( $store ) {
		return $this->event_prefix . $store->get_arg( 'slug' );
	}

	public function event_timestamp( $store ) {
		return apply_filters( 'jet-engine/data-stores/user-ip/schedules/event-timestamp', time(), $store->get_arg( 'slug' ), $store );
	}

	public function event_interval( $store ) {
		return apply_filters( 'jet-engine/data-stores/user-ip/schedules/event-interval', 'daily', $store->get_arg( 'slug' ), $store );
	}

	public function next_schedule( $store ) {
		return wp_next_scheduled( $this->event_hook( $store ) );
	}

	public function clear_expiration( $store ) {
		return apply_filters( 'jet-engine/data-stores/user-ip/schedules/clear-expiration', YEAR_IN_SECONDS, $store->get_arg( 'slug' ), $store );
	}

	public function clear_store( $store ) {

		$store_type = $store->get_type();

		if ( ! method_exists( $store_type, 'clear_store' ) ) {
			return;
		}

		$expiration = $this->clear_expiration( $store );

		$store_type->clear_store( $store->get_arg( 'slug' ), ( time() - absint( $expiration ) ) );
	}

}
