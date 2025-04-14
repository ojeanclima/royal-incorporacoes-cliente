<?php
/**
 * Bricks Dynamic Data manager
 */
namespace Jet_Smart_Filters\Bricks_Views\Dynamic_Data;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die();
}

/**
 * Define Manager class
 */
class Manager {

	private $tags   = [];
	private $prefix = 'jsf_';

	public function __construct() {

		if ( is_admin() && ! bricks_is_ajax_call() && ! bricks_is_rest_call() ) {
			return;
		}

		$this->register_tags();

		add_filter( 'bricks/dynamic_tags_list', [ $this, 'add_tags_to_builder' ] );

		add_filter( 'bricks/frontend/render_data', [ $this, 'render' ], 10, 2 );
		add_filter( 'bricks/dynamic_data/render_content', [ $this, 'render' ], 10, 3 );
	}

	public function get_tag_classes_names() {

		return [
			'SEO_Rules_Title',
			'SEO_Rules_Description'
		];
	}

	public function get_group_name() {

		return __( 'Jet Smart Filters', 'jet-smart-filters' );
	}

	public function get_tags() {

		return $this->tags;
	}

	public function register_tags() {

		foreach ( $this->get_tag_classes_names() as $tag_class ) {
			$file     = str_replace( '_', '-', strtolower( $tag_class ) ) . '.php';
			$filepath = jet_smart_filters()->plugin_path( 'includes/bricks/dynamic-tags/tags/' . $file );

			if ( file_exists( $filepath ) ) {
				require $filepath;
			}

			$tag_class = '\Jet_Smart_Filters\Bricks_Views\Dynamic_Data\\' . $tag_class;

			if ( class_exists( $tag_class ) && ! array_key_exists( $tag_class, $this->tags ) ) {
				$tag = new $tag_class();

				if ( ! method_exists( $tag, 'get_name' ) || ! method_exists( $tag, 'get_title' ) || array_key_exists( $tag->get_name(), $this->tags ) ) {
					continue;
				}

				$this->tags[$tag->get_name()] = $tag;
			}
		}
	}

	/**
	 * Adds tags to the tags picker list (used in the builder)
	 */
	public function add_tags_to_builder( $tags ) {

		foreach ( $this->get_tags() as $tag ) {
			$tags[] = [
				'name'  => '{' . $this->prefix . $tag->get_name() . '}',
				'label' => $tag->get_title(),
				'group' => method_exists( $tag, 'get_group_name' )
					? $tag->get_group_name()
					: $this->get_group_name()
			];
		}

		return $tags;
	}

	/**
	 * Dynamic tag exists in $content: Replaces dynamic tag with requested data
	 */
	function render( $content, $post, $context = 'text' ) {

		foreach ( $this->get_tags() as $tag ) {
			$tag_key = '{' . $this->prefix . $tag->get_name() . '}';

			if ( strpos( $content, $tag_key ) ) {
				$content = str_replace( $tag_key, $tag->render(), $content );
			}
		}

		return $content;
	}
}
