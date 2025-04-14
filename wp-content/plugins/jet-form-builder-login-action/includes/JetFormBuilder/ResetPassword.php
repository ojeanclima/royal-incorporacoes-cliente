<?php


namespace Jet_FB_Login\JetFormBuilder;

use Jet_FB_Login\JetFormBuilder\Models\ResetTokenModel;
use Jet_Form_Builder\Classes\Security\Csrf_Tools;
use Jet_Form_Builder\Db_Queries\Exceptions\Sql_Exception;
use Jet_Form_Builder\Exceptions\Handler_Exception;

class ResetPassword {

	const ACTION    = 'jet_fb_reset_pswd';
	const MODE_URL  = 0;
	const MODE_CODE = 1;

	const MODES = [
		self::MODE_URL,
		self::MODE_CODE
	];

	protected $user;
	protected $secret_key;
	protected $hash;
	protected $mode = self::MODE_URL;
	protected $reset_link;

	/**
	 * ResetPassword constructor.
	 *
	 * @param \WP_User $user
	 */
	public function __construct( \WP_User $user ) {
		$this->user = $user;
	}

	/**
	 * @return string
	 */
	public function get_key(): string {
		if ( ! empty( $this->secret_key ) ) {
			return $this->secret_key;
		}

		$this->secret_key = $this->create_secret();

		return $this->secret_key;
	}

	/**
	 * @return string|null
	 */
	public function get_hash() {
		if ( ! empty( $this->hash ) ) {
			return $this->hash;
		}
		$key = $this->get_key();

		$this->hash = ResetPasswordTools::get_hasher()->HashPassword( $key );

		return $this->hash;
	}

	/**
	 * @return ResetPassword
	 * @throws ResetPasswordException
	 */
	public function validate(): ResetPassword {
		$key  = $this->get_key();
		$hash = $this->get_hash();

		if ( ! $key || ! $hash ) {
			throw new ResetPasswordException( 'empty_key' );
		}

		if ( ! ResetPasswordTools::get_hasher()->CheckPassword( $key, $hash ) ) {
			throw new ResetPasswordException( 'invalid_key' );
		}

		return $this;
	}

	/**
	 * @throws Sql_Exception
	 */
	public function clear() {
		( new ResetTokenModel() )->delete(
			array(
				'user_id' => $this->user->ID,
			)
		);
	}

	/**
	 * @return string
	 */
	public function get_link(): string {
		if ( ! $this->reset_link ) {
			$this->set_reset_link( get_permalink() );
		}

		return esc_url_raw(
			add_query_arg(
				array(
					self::ACTION => $this->get_key(),
					'uid'        => $this->user->ID,
				),
				$this->reset_link
			)
		);
	}

	/**
	 * @throws ResetPasswordException
	 */
	public function save_hash(): ResetPassword {
		$model = new ResetTokenModel();

		try {
			$model->insert(
				array(
					'form_id' => jet_fb_live()->form_id,
					'user_id' => $this->user->ID,
					'hash'    => $this->get_hash(),
					'mode'    => $this->mode,
				)
			);
		} catch ( Sql_Exception $exception ) {
			throw new ResetPasswordException( 'failed_save_hash', $exception->getMessage() );
		}

		return $this;
	}

	protected function create_secret() {
		switch ( $this->mode ) {
			case self::MODE_CODE:
				return wp_rand( 10000, 99999 );
			default:
				return Csrf_Tools::generate();
		}
	}

	/**
	 * @param $mode
	 *
	 * @return ResetPassword
	 * @throws ResetPasswordException
	 */
	public function set_mode( $mode ): ResetPassword {
		$mode = absint( $mode );

		if ( ! in_array( $mode, self::MODES, true ) ) {
			throw new ResetPasswordException( 'invalid_mode' );
		}

		$this->mode = $mode;

		return $this;
	}

	public function set_hash( string $hash ): ResetPassword {
		$this->hash = $hash;

		return $this;
	}

	public function set_secret( string $secret ): ResetPassword {
		$this->secret_key = $secret;

		return $this;
	}

	public function get_mode(): int {
		return $this->mode;
	}

	public function set_reset_link( string $link ): ResetPassword {
		$link = apply_filters(
			'jet-form-builder/reset-password/return-link',
			$link,
			$this
		);

		$this->reset_link = remove_query_arg(
			array( 'uid', self::ACTION ),
			$link
		);

		return $this;
	}

	/**
	 * @param string $password
	 */
	public function update_password( string $password ) {
		reset_password( $this->user, $password );
	}

	public function get_user(): \WP_User {
		return $this->user;
	}

}
