<?php

namespace Jet_Engine\Compatibility\Packages\Jet_Engine_WPML_Package\Profile_Builder;

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

	/**
	 * A reference to an instance of compatibility package.
	 *
	 * @access private
	 * @var    object
	 */
	private $package = null;

	private function __construct( $package = null ) {
		$this->package = $package;

		if ( ! jet_engine()->modules->is_module_active( 'profile-builder' ) ) {
			return;
		}

		add_filter( 'wpml_ls_language_url', array( $this, 'fix_language_switcher_on_pb' ) );
		add_filter( 'wpml_active_languages', array( $this, 'fix_language_list_on_pb' ) );
		add_filter( 'jet-engine/profile-builder/settings/post-state-id', array( $this, 'set_post_state_id' ), 10, 2 );
		add_filter( 'jet-engine/profile-builder/template-id', array( $this->package, 'set_translated_object' ) );
		add_filter( 'jet-engine/profile-builder/rewrite/page-translations', array( $this, 'get_page_translations' ), 10, 2 );
		
		add_filter( 'jet-engine/profile-builder/settings', array( $this, 'translate_page_titles' ) );
	}

	public function translate_page_titles( $settings ) {
		if ( is_array( $settings['account_page_structure'] ?? false ) ) {
			foreach ( $settings['account_page_structure'] as $i => $data ) {
				$settings['account_page_structure'][ $i ]['title'] = apply_filters('jet-engine/compatibility/translate-string', $data['title'] );
			}
		}

		if ( ! empty( $settings['enable_single_user_page'] ) && is_array( $settings['user_page_structure'] ?? false ) ) {
			foreach ( $settings['user_page_structure'] as $i => $data ) {
				$settings['user_page_structure'][ $i ]['title'] = apply_filters('jet-engine/compatibility/translate-string', $data['title'] );
			}
		}
		
		return $settings;
	}

	public function get_page_translations( $translations, $post_id ) {
		$type = apply_filters( 'wpml_element_type', get_post_type( $post_id ) );
		$trid = apply_filters( 'wpml_element_trid', false, $post_id, $type );
		  
		$translations = apply_filters( 'wpml_get_element_translations', array(), $trid, $type );

		if ( empty( $translations ) || ! is_array( $translations ) ) {
			return array( $post_id );
		}

		return array_column( $translations, 'element_id' );
	}

	public function fix_language_switcher_on_pb( $url ) {
		return $this->get_profile_builder_url( $url );
	}

	public function fix_language_list_on_pb( $languages ) {
		$module = \Jet_Engine\Modules\Profile_Builder\Module::instance();

		if ( ! $module->query->get_pagenow() ) {
			return $languages;
		}

		foreach ( $languages as $key => $lang ) {		
			$languages[ $key ]['url'] = $this->get_profile_builder_url( $languages[ $key ]['url'] );
		}
		
		return $languages;
	}

	public function set_post_state_id( $id, $post ) {
		return apply_filters( 'wpml_object_id', $id, get_post_type( $post ), true );
	}

	function get_profile_builder_url( $url ) {
		$module = \Jet_Engine\Modules\Profile_Builder\Module::instance();

		$page = $module->query->get_pagenow();

		if ( ! $page ) {
			return $url;
		}

		$subpage = $module->query->get_subpage();

		preg_match( '/\?.+$/', $url, $params );

		$url = preg_replace( '/\?.+$/', '', $url );

		if ( 'single_user_page' === $page ) {
			$url .= $module->query->get_queried_user_slug() . '/';
		}
		
		$url  = ! empty( $subpage ) ? $url . $subpage . '/' : $url;
		$url .= $params[0] ?? '';
		
		return $url;
	}

	/**
	 * Returns the instance.
	 *
	 * @access public
	 * @return object
	 */
	public static function instance( $package = null ) {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self( $package );
		}

		return self::$instance;

	}
	
}
