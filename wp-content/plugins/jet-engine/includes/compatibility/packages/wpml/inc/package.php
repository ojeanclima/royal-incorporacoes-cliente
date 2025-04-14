<?php

namespace Jet_Engine\Compatibility\Packages\Jet_Engine_WPML_Package;

use stdClass;

class Package {

	/**
	 * A reference to an instance of this class.
	 *
	 * @access private
	 * @var    object
	 */
	private static $instance = null;

	/**
	 * Constructor for the class
	 */
	public function __construct() {
		add_filter( 'wpml_elementor_widgets_to_translate',     array( $this, 'add_translatable_nodes' ) );
		add_filter( 'jet-engine/forms/render/form-id',         array( $this, 'set_translated_object' ) );
		add_filter( 'jet-engine/compatibility/translate/post', array( $this, 'set_translated_object' ) );
		add_filter( 'jet-engine/compatibility/translate/term', array( $this, 'set_translated_object' ), 10, 2 );

		// Translate CPT Name
		if ( jet_engine()->cpt ) {
			$cpt_items = jet_engine()->cpt->get_items();

			if ( ! empty( $cpt_items ) ) {
				foreach ( $cpt_items as $post_type ) {
					add_filter( "post_type_labels_{$post_type['slug']}", array( $this, 'translate_cpt_name' ) );
				}
			}
		}

		// Translate Admin Labels
		add_filter( 'jet-engine/compatibility/translate-string', array( $this, 'translate_admin_labels' ) );

		// Disable `suppress_filters` in the `get_posts` args.
		add_filter( 'jet-engine/compatibility/get-posts/args', array( $this, 'disable_suppress_filters' ) );

		// Fixed the translated tax query on archive page at ajax( pagination, load more, lazy load ).
		// See: https://github.com/Crocoblock/issues-tracker/issues/2055
		if ( wpml_is_ajax() && class_exists( 'WPML_Display_As_Translated_Tax_Query' ) ) {
			global $sitepress, $wpml_term_translations;

			$translated_tax_query = new \WPML_Display_As_Translated_Tax_Query( $sitepress, $wpml_term_translations );
			$translated_tax_query->add_hooks();
		}

		$this->init_package_components();
	}

	/**
	 * Init package components
	 *
	 * @return void
	 */
	public function init_package_components() {
		require_once $this->package_path( 'listings.php' );
		Listings\Manager::instance( $this );

		require_once $this->package_path( 'components.php' );
		Components\Manager::instance();

		require_once $this->package_path( 'meta-boxes.php' );
		Meta_Boxes\Manager::instance( $this );

		require_once $this->package_path( 'relations.php' );
		Relations\Manager::instance( $this );

		require_once $this->package_path( 'profile-builder.php' );
		Profile_Builder\Manager::instance( $this );

		require_once $this->package_path( 'data-stores.php' );
		Data_Stores\Manager::instance( $this );
	}

	/**
	 * Return path inside package.
	 *
	 * @param string $relative_path
	 *
	 * @return string
	 */
	public function package_path( $relative_path = '' ) {
		return jet_engine()->plugin_path( 'includes/compatibility/packages/wpml/inc/' . $relative_path );
	}

	/**
	 * Return url inside package.
	 *
	 * @param string $relative_path
	 *
	 * @return string
	 */
	public function package_url( $relative_path = '' ) {
		return jet_engine()->plugin_url( 'includes/compatibility/packages/wpml/inc/' . $relative_path );
	}

	/**
	 * Set translated object ID to show
	 *
	 * @param int    $obj_id   Object ID.
	 * @param string $obj_type Object type: post type or taxonomy slug.
	 *
	 * @return int
	 */
	public function set_translated_object( $obj_id = null, $obj_type = null ) {

		global $sitepress;

		if ( empty( $obj_type ) ) {
			$obj_type = get_post_type( $obj_id );
		}

		$new_id = $sitepress->get_object_id( $obj_id, $obj_type );

		if ( $new_id ) {
			return $new_id;
		}

		return $obj_id;
	}

	/**
	 * Add translation strings
	 */
	public function add_translatable_nodes( $nodes ) {

		$nodes['jet-listing-grid'] = array(
			'conditions' => array(
				'widgetType' => 'jet-listing-grid'
			),
			'fields'     => array(
				array(
					'field'       => 'not_found_message',
					'type'        => esc_html__( 'Listing Grid: Not found message', 'jet-engine' ),
					'editor_type' => 'LINE',
				),
			),
		);

		$nodes['jet-listing-dynamic-field'] = array(
			'conditions' => array(
				'widgetType' => 'jet-listing-dynamic-field'
			),
			'fields'     => array(
				array(
					'field'       => 'date_format',
					'type'        => esc_html__( 'Field: Date format (if used)', 'jet-engine' ),
					'editor_type' => 'LINE',
				),
				array(
					'field'       => 'num_dec_point',
					'type'        => esc_html__( 'Field: Separator for the decimal point (if used)', 'jet-engine' ),
					'editor_type' => 'LINE',
				),
				array(
					'field'       => 'num_thousands_sep',
					'type'        => esc_html__( 'Field: Thousands separator (if used)', 'jet-engine' ),
					'editor_type' => 'LINE',
				),
				array(
					'field'       => 'dynamic_field_format',
					'type'        => esc_html__( 'Field: Field format (if used)', 'jet-engine' ),
					'editor_type' => 'AREA',
				),
			),
		);

		$nodes['jet-listing-dynamic-link'] = array(
			'conditions' => array(
				'widgetType' => 'jet-listing-dynamic-link'
			),
			'fields'     => array(
				array(
					'field'       => 'link_label',
					'type'        => esc_html__( 'Link: Label (if used)', 'jet-engine' ),
					'editor_type' => 'LINE',
				),
				array(
					'field'       => 'added_to_store_text',
					'type'        => esc_html__( 'Link: Added to store text (if used)', 'jet-engine' ),
					'editor_type' => 'LINE',
				),
			),
		);

		$nodes['jet-listing-dynamic-meta'] = array(
			'conditions' => array(
				'widgetType' => 'jet-listing-dynamic-meta'
			),
			'fields'     => array(
				array(
					'field'       => 'prefix',
					'type'        => esc_html__( 'Meta: Prefix (if used)', 'jet-engine' ),
					'editor_type' => 'LINE',
				),
				array(
					'field'       => 'suffix',
					'type'        => esc_html__( 'Meta: Suffix (if used)', 'jet-engine' ),
					'editor_type' => 'LINE',
				),
				array(
					'field'       => 'zero_comments_format',
					'type'        => esc_html__( 'Meta: Zero Comments Format (if used)', 'jet-engine' ),
					'editor_type' => 'LINE',
				),
				array(
					'field'       => 'one_comment_format',
					'type'        => esc_html__( 'Meta: One Comments Format (if used)', 'jet-engine' ),
					'editor_type' => 'LINE',
				),
				array(
					'field'       => 'more_comments_format',
					'type'        => esc_html__( 'Meta: More Comments Format (if used)', 'jet-engine' ),
					'editor_type' => 'LINE',
				),
				array(
					'field'       => 'date_format',
					'type'        => esc_html__( 'Meta: Date Format (if used)', 'jet-engine' ),
					'editor_type' => 'LINE',
				),
			),
		);

		$nodes['jet-listing-dynamic-terms'] = array(
			'conditions' => array(
				'widgetType' => 'jet-listing-dynamic-terms'
			),
			'fields'     => array(
				array(
					'field'       => 'terms_prefix',
					'type'        => esc_html__( 'Terms: Prefix (if used)', 'jet-engine' ),
					'editor_type' => 'LINE',
				),
				array(
					'field'       => 'terms_suffix',
					'type'        => esc_html__( 'Terms: Suffix (if used)', 'jet-engine' ),
					'editor_type' => 'LINE',
				),
			),
		);

		$nodes['jet-listing-dynamic-repeater'] = array(
			'conditions' => array(
				'widgetType' => 'jet-listing-dynamic-repeater'
			),
			'fields'     => array(
				array(
					'field'       => 'dynamic_field_format',
					'type'        => esc_html__( 'Repeater: Field format (if used)', 'jet-engine' ),
					'editor_type' => 'AREA',
				),
			),
		);

		return $nodes;

	}

	/**
	 * Translate CPT Name
	 *
	 * @param  object $labels
	 * @return object
	 */
	public function translate_cpt_name( $labels ) {
		do_action( 'wpml_register_single_string', 'Jet Engine CPT Labels', "Jet Engine CPT Name ({$labels->name})", $labels->name );
		$labels->name = apply_filters( 'wpml_translate_single_string', $labels->name, 'Jet Engine CPT Labels', "Jet Engine CPT Name ({$labels->name})" );

		return $labels;
	}

	/**
	 * Translate Admin Labels
	 *
	 * @param  string $label
	 * @return string
	 */
	public function translate_admin_labels( $label ) {

		global $sitepress;

		$wpml_default_lang = apply_filters( 'wpml_default_language', null );

		$lang = method_exists( $sitepress, 'get_current_language' ) ? $sitepress->get_current_language() : null;

		$name = "Admin Label - {$label}";

		if ( 160 < strlen( $name ) ) {
			$name = jet_engine_trim_string( $name, 100, '' ) . '... - ' . md5( $label );
		}

		if ( $lang === $wpml_default_lang ) {
			do_action( 'wpml_register_single_string', 'Jet Engine Admin Labels', $name, $label );
		}

		$label = apply_filters( 'wpml_translate_single_string', $label, 'Jet Engine Admin Labels', $name, $lang );

		return $label;
	}

	/**
	 * Get original post ID (translation in default language). Returns translation ID if no original found.
	 * 
	 * @param  int $translation_id Post ID
	 * @return int                 Original post ID if found, given post ID otherwise
	 */
	public function get_original_post_id( $translation_id ) {
		global $sitepress;
		$original_id = apply_filters(
			'wpml_object_id',
			$translation_id,
			get_post_type( $translation_id ),
			true,
			$sitepress->get_default_language()
		);
		return $original_id;
	}

	public function disable_suppress_filters( $args = array() ) {
		$args['suppress_filters'] = false;
		return $args;
	}

	public function get_current_language() {
		return apply_filters( 'wpml_current_language', null );
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
