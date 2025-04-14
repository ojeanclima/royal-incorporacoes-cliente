<?php


namespace Jet_FB_Login\JetFormBuilder;

use Jet_FB_Login\JetFormBuilder\DbViews\ResetTokenView;
use Jet_FB_Login\JetFormBuilder\Models\ResetTokenModel;
use Jet_FB_Login\JetFormBuilder\RenderStates\OnResetPasswordState;
use Jet_Form_Builder\Blocks\Conditional_Block\Render_State;
use Jet_Form_Builder\Exceptions\Action_Exception;
use Jet_Form_Builder\Exceptions\Handler_Exception;
use Jet_Form_Builder\Exceptions\Query_Builder_Exception;

class ResetPasswordTools {

	/**
	 * @param $user_identifier
	 *
	 * @return \WP_User
	 * @throws ResetPasswordException
	 */
	public static function get_user( $user_identifier ): \WP_User {
		$user = apply_filters(
			'jet-form-builder/reset-password/find-user',
			null,
			$user_identifier
		);

		if ( $user instanceof \WP_User ) {
			return $user;
		}

		return self::get_user_by_fields( $user_identifier );
	}

	/**
	 * @param string $user_identifier
	 *
	 * @return \WP_User
	 * @throws ResetPasswordException
	 */
	public static function get_user_by_fields( string $user_identifier ): \WP_User {
		if ( is_email( $user_identifier ) ) {
			$user = get_user_by( 'email', $user_identifier );

			if ( empty( $user ) ) {
				throw new ResetPasswordException( 'not_recognised_email' );
			}

			return $user;
		}

		$user = get_user_by( 'login', $user_identifier );

		if ( empty( $user ) ) {
			throw new ResetPasswordException( 'not_recognised_login' );
		}

		return $user;
	}


	/**
	 * @param string $secret_key
	 * @param int $user_id
	 *
	 * @return ResetPassword
	 * @throws ResetPasswordException
	 */
	public static function get_reset( string $secret_key, int $user_id ): ResetPassword {
		self::clear();

		try {
			$reset_row = ResetTokenView::get_by_uid( $user_id );
		} catch ( Query_Builder_Exception $exception ) {
			throw new ResetPasswordException( 'not_found' );
		}

		$user  = get_user_by( 'ID', $reset_row['user_id'] );
		$reset = new ResetPassword( $user );

		$reset->set_mode( $reset_row['mode'] )
				->set_secret( $secret_key )
				->set_hash( $reset_row['hash'] );

		// throws an exceptions, if something wrong
		return $reset->validate();
	}

	public static function get_hasher(): \PasswordHash {
		global $wp_hasher;

		if ( ! empty( $wp_hasher ) ) {
			return $wp_hasher;
		}

		require_once ABSPATH . WPINC . '/class-phpass.php';
		$wp_hasher = new \PasswordHash( 8, true );

		return $wp_hasher;
	}

	public static function get_reset_state(): OnResetPasswordState {
		return Render_State::instance()
							->get_current()
							->get_by_id( OnResetPasswordState::ID )
							->current();
	}

	public static function clear() {
		global $wpdb;

		/** @var \DateTimeImmutable $datetime_limit */
		$datetime_limit = apply_filters(
			'jet-form-builder/reset-password/expiration-datetime',
			current_datetime()->modify( '-1 day' )
		);

		$table = ResetTokenModel::table();

		// phpcs:disable WordPress.DB
		$wpdb->query(
			$wpdb->prepare(
				"
DELETE FROM {$table} 
WHERE 1=1
	AND TIMESTAMP(`created_at`) <= TIMESTAMP(%s)
",
				$datetime_limit->format( 'Y-m-d H:i:s' )
			)
		);
		// phpcs:enable WordPress.DB
	}

}
