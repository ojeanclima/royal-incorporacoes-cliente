<?php

namespace Jet_Engine\Bricks_Views\Dynamic_Data;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Providers {
	/**
	 * Holds the providers
	 *
	 * @var array
	 */
	private $providers_keys = [];

	/**
	 * Holds the providers instances
	 *
	 * @var array
	 */
	private static $providers = [];

	/**
	 * Holds the tags instances
	 *
	 * @var array
	 */
	private $tags = [];

	private $allowed_prefixes = [ 'je_', 'jet_' ];

	public function __construct( $providers ) {
		$this->providers_keys = $providers;
	}

	public static function register( $providers = [] ) {
		$instance = new self( $providers );

		// Register providers (priority 10000 due to CMB2 priority)
		add_action( 'init', [ $instance, 'register_providers' ], 10000 );

		// Register tags on init after register_providers (@since 1.9.8)
		add_action( 'init', [ $instance, 'register_tags' ], 10001 );

		// Register providers during WP REST API call (priority 7 to run before register_tags() on WP REST API)
		add_action( 'rest_api_init', [ $instance, 'register_providers' ], 7 );

		// Hook 'init' doesn't run on REST API calls so we need this to register the tags when rendering elements (needed for Posts element) or fetching dynamic data content
		add_action( 'rest_api_init', [ $instance, 'register_tags' ], 8 );

		// Register tags before wp_enqueue_scripts (but not before wp to get the post custom fields)
		// Priority = 8 to run before Setup::init_control_options
		// Not in use (@since 1.9.8), Register on 'init' hook above (#86bw2ytax)
		// add_action( 'wp', [ $instance, 'register_tags' ], 8 );

		// Hook "wp" doesn't run on AJAX/REST API calls so we need this to register the tags when rendering elements (needed for Posts element) or fetching dynamic data content
		// Not in use (@since 1.9.8), Register on 'init' hook above (#86bw2ytax)
		// add_action( 'admin_init', [ $instance, 'register_tags' ], 8 );

		add_filter( 'bricks/dynamic_tags_list', [ $instance, 'add_tags_to_builder' ] );

		// Render dynamic data in builder too (when template preview post ID is set)
		add_filter( 'bricks/frontend/render_data', [ $instance, 'render' ], 10, 2 );

		add_filter( 'bricks/dynamic_data/render_content', [ $instance, 'render' ], 10, 3 );

		add_filter( 'bricks/dynamic_data/render_tag', [ $instance, 'get_tag_value' ], 10, 3 );
	}

	public function register_providers() {
		/**
		 * Don't register providers in WP admin
		 *
		 * Interferes with default ACF logic of displaying ACF fields in the backend.
		 *
		 * @see #86byqukg8, #86bx5f831
		 *
		 * @since 1.9.9
		 */
		if ( is_admin() && ! bricks_is_ajax_call() && ! bricks_is_rest_call() ) {
			return;
		}

		foreach ( $this->providers_keys as $provider => $namespace ) {
			$classname = $namespace . '\Provider_' . str_replace( ' ', '_', ucwords( str_replace( '-', ' ', $provider ) ) );
			self::$providers[ $provider ] = new $classname( str_replace( '-', '_', $provider ) );
		}
	}

	public function register_tags() {
		foreach ( self::$providers as $key => $provider ) {
			$this->tags = array_merge( $this->tags, $provider->get_tags() );

		}
	}

	public function get_tags() {
		return $this->tags;
	}

	/**
	 * Adds tags to the tags picker list (used in the builder)
	 *
	 * @param array $tags
	 * @return array
	 */
	public function add_tags_to_builder( $tags ) {
		$list = $this->get_tags();

		foreach ( $list as $tag ) {
			if ( isset( $tag['deprecated'] ) ) {
				continue;
			}

			$tags[] = [
				'name'  => $tag['name'],
				'label' => $tag['label'],
				'group' => $tag['group']
			];
		}

		return $tags;
	}

	/**
	 * Dynamic tag exists in $content: Replaces dynamic tag with requested data
	 *
	 * @param string  $content
	 * @param WP_Post $post
	 */
	public function render( $content, $post, $context = 'text' ) {
		/**
		 * \w: Matches any word character (alphanumeric & underscore).
		 * Equivalent to [A-Za-z0-9_]
		 * "À-ÖØ-öø-ÿ" Add the accented characters
		 * "-" Needed because some post types handles are like "my-post-type"
		 * ":" Needed for extra arguments to dynamic data tags (e.g. post_excerpt:20 or wp_user_meta:my_meta_key)
		 * "|" and "," needed for the post terms like {post_terms_post_tag:sep} where sep could be a pipe or comma
		 * "(", ")" and "'" for the function arguments of the dynamic tag {echo}
		 * "@" to support email addresses as arguments of the dynamic tag {echo} #3kazphp
		 *
		 * @since 1.9.4: "u" modifier: Pattern strings are treated as UTF-8 to support Cyrillic, Arabic, etc.
		 * @since 1.10: "$", "+", "%", "#", "!", "=", "<", ">", "&", "~", "[", "]", ";" as arguments of the dynamic tag {echo}
		 * @since 1.10.2: "?" as arguments of the dynamic tag {echo}
		 *
		 * @see https://regexr.com/
		 */
		$prefixes = [];

		foreach( $this->allowed_prefixes as $prefix ) {
			$prefixes[] = $prefix . '[\wÀ-ÖØ-öø-ÿ\-\s\.\/:\(\)\'@|,$%#!+=<>&~\[\];?]+';
		}

		$prefixes = implode( '|', $prefixes );
		$pattern  = '/{(' . $prefixes . ')}/u';

		// Get a list of tags to exclude from the Dynamic Data logic
		$exclude_tags = apply_filters( 'bricks/dynamic_data/exclude_tags', [] );

		/**
		 * STEP: Determine how many times we need to run the DD parser
		 *
		 * Previously we ran the parser by counting the number of open curly braces in the content. (@since 1.8)
		 * But this is not reliable because the content could contain curly braces in the code elements or any shortcodes.
		 * Causing the website to load extremely slow.
		 *
		 * @since 1.8.2 (#862jyyryg)
		 */
		// Get all registered tags except the excluded ones.
		// Example: [0 => "post_title", 1 => "woo_product_price", 2 => "echo"]
		$registered_tags = array_filter(
			array_keys( $this->get_tags() ),
			function( $tag ) use ( $exclude_tags ) {
				return ! in_array( $tag, $exclude_tags );
			}
		);

		$dd_tags_in_content = [];
		$dd_tags_found      = [];

		// Find all dynamic data tags in the content
		preg_match_all( $pattern, $content, $dd_tags_in_content );

		$dd_tags_in_content = ! empty( $dd_tags_in_content[1] ) ? $dd_tags_in_content[1] : [];

		if ( ! empty( $dd_tags_in_content ) ) {
			/**
			 * $dd_tags_in_content only matches the pattern, but some codes from Code element could match the pattern too.
			 * Example: function test() { return 'Hello World'; } will match the pattern, but it's not a dynamic data tag.
			 *
			 * Find all dynamic data tags in the content which starts with dynamic data tag from $registered_tags
			 * Cannot use array_in or array_intersect because $registered_tags only contains the tag name, somemore tags could have filters like {echo:my_function( 'Hello World' )
			 *
			 * Example: $registered_tags    = [0 => "post_title", 1 => "woo_product_price", 2 => "echo"]
			 * Example: $dd_tags_in_content = [0 => "post_title", 1 => "woo_product_price:value", 2 => "echo:my_function('Hello World')"]
			 */
			$dd_tags_found = array_filter(
				$dd_tags_in_content,
				function( $tag ) use ( $registered_tags ) {
					foreach ( $registered_tags as $all_tag ) {
						if ( strpos( $tag, $all_tag ) === 0 ) {
							return true;
						}
					}
					return false;
				}
			);
		}

		// Get the count of found dynamic data tags
		$dd_tag_count = count( $dd_tags_found );

		// STEP: Run the parser based on the count of found dynamic data tags
		for ( $i = 0; $i < $dd_tag_count; $i++ ) {
			preg_match_all( $pattern, $content, $matches );

			if ( empty( $matches[0] ) ) {
				return $content;
			}

			foreach ( $matches[1] as $key => $match ) {
				$tag = $matches[0][ $key ];

				if ( in_array( $match, $exclude_tags ) ) {
					continue;
				}

				$value = $this->get_tag_value( $match, $post, $context );

				// Value is a WP_Error: Set value to false to avoid error in builder (#862k4cyc8)
				if ( is_a( $value, 'WP_Error' ) ) {
					$value = false;
				}

				$content = str_replace( $tag, $value, $content );
			}
		}

		return $content;
	}

	/**
	 * Get the value of a dynamic data tag
	 *
	 * @param string  $tag (without {})
	 * @param WP_Post $post
	 * @return void
	 */
	public function get_tag_value( $tag, $post, $context = 'text' ) {
		if ( ! is_string( $tag ) || ! $this->has_allowed_prefix( $tag ) ) {
			return $tag;
		}

		$tag = str_replace( [ '{', '}' ], '', $tag );

		// Parse the tag and extract arguments
		$parser = new \Bricks\Integrations\Dynamic_Data\Dynamic_Data_Parser();
		$parsed = $parser->parse( $tag );

		$tag          = $parsed['tag'] ?? $tag;
		$args         = $parsed['args'];
		$original_tag = $parsed['original_tag'];

		$tags = $this->get_tags();

		if ( ! array_key_exists( $tag, $tags ) ) {
			/**
			 * If true, Bricks replaces not existing DD tags with an empty string
			 *
			 * true caused unwanted replacement of inline <script> & <style> tag data.
			 *
			 * Set to false @since 1.4 to render all non-matching DD tags (#2ufh0uf)
			 *
			 * https://academy.bricksbuilder.io/article/filter-bricks-dynamic_data-replace_nonexistent_tags/
			 */
			$replace_tag = apply_filters( 'bricks/dynamic_data/replace_nonexistent_tags', false );

			return $replace_tag ? '' : '{' . $original_tag . '}';
		}

		$provider = str_replace( '_', '-', $tags[ $tag ]['provider'] );

		return self::$providers[ $provider ]->get_tag_value( $tag, $post, $args, $context );
	}

	public function has_allowed_prefix( $tag ) {
		foreach ( $this->allowed_prefixes as $prefix ) {
			if ( false !== strpos( $tag, $prefix ) ) {
				return true;
			}
		}

		return false;
	}
}