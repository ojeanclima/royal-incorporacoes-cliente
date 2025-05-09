<?php


namespace JFB_Compatibility\Jet_Engine;

use Jet_Engine\Query_Builder\Queries\Base_Query;
use Jet_Form_Builder\Actions\Methods\Object_Properties_Collection;
use Jet_Form_Builder\Exceptions\Repository_Exception;
use JFB_Compatibility\Jet_Engine\Blocks\Check_Mark\Block_Asset;
use JFB_Compatibility\Jet_Engine\Compatibility\Bricks\Bricks;
use JFB_Compatibility\Jet_Engine\Compatibility\Elementor\Elementor;
use JFB_Modules\Option_Field\Blocks\Checkbox;
use JFB_Modules\Option_Field\Blocks\Radio;
use Jet_Form_Builder\Classes\Builder_Helper;
use JFB_Compatibility\Jet_Engine\Actions\Update_Options;
use JFB_Compatibility\Jet_Engine\Generators\Get_From_Field;
use JFB_Compatibility\Jet_Engine\Generators\Get_From_Je_Query;
use JFB_Compatibility\Jet_Engine\Methods\Post_Modification\Post_Je_Relation_Property;
use JFB_Compatibility\Jet_Engine\Option_Query\Inner_Module;
use JFB_Compatibility\Jet_Engine\Preset_Sources\Preset_Source_Options_Page;
use JFB_Compatibility\Jet_Engine\Preset_Sources\Preset_User;
use JFB_Components\Compatibility\Base_Compat_Dir_Trait;
use JFB_Components\Compatibility\Base_Compat_Handle_Trait;
use JFB_Components\Compatibility\Base_Compat_Url_Trait;
use JFB_Components\Module\Base_Module_After_Install_It;
use JFB_Components\Module\Base_Module_Dir_It;
use JFB_Components\Module\Base_Module_Handle_It;
use JFB_Components\Module\Base_Module_It;
use JFB_Components\Module\Base_Module_Url_It;
use Jet_Form_Builder\Blocks\Module;
use Jet_Engine\Modules\Custom_Content_Types;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Jet_Engine implements
	Base_Module_It,
	Base_Module_Handle_It,
	Base_Module_Dir_It,
	Base_Module_Url_It,
	Base_Module_After_Install_It {

	use Base_Compat_Handle_Trait;
	use Base_Compat_Url_Trait;
	use Base_Compat_Dir_Trait;

	private $has_custom_template;

	/**
	 * @var Inner_Module|null
	 */
	private $option_query;
	private $option_type = 'checkbox';

	/**
	 * @var Elementor
	 */
	private $elementor;

	/**
	 * @var Bricks
	 */
	private $bricks;

	public function rep_item_id() {
		return 'jet-engine';
	}

	public function condition(): bool {
		return function_exists( 'jet_engine' );
	}

	public function on_install() {
		$this->option_query = new Inner_Module();
		$this->elementor    = new Elementor();
		$this->bricks       = new Bricks();
	}

	public function on_uninstall() {
		$this->option_query = null;
		$this->elementor    = null;
		$this->bricks       = null;
	}

	public function init_hooks() {
		add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ) );
		add_action( 'jet_plugins/frontend/register_scripts', array( $this, 'register_scripts' ) );
		add_action( 'jet-form-builder/editor-assets/before', array( $this, 'enqueue_admin_assets' ) );
		add_action( 'jet-form-builder/actions/register', array( $this, 'add_actions' ) );

		add_filter(
			'jet-form-builder/preset/source-types',
			array( $this, 'add_sources' )
		);
		add_filter(
			'jet-form-builder/forms/options-generators',
			array( $this, 'add_generators' )
		);
		add_filter(
			'jet-form-builder/custom-template-object',
			array( $this, 'get_custom_object_for_template' ),
			10,
			3
		);
		add_action(
			'jet-form-builder/generators/get_from_query/setup',
			array( $this, 'add_cct_type_arg_to_block_args' ),
			10,
			2
		);
		add_filter(
			'jet-form-builder/blocks/items',
			array( $this, 'add_blocks' ),
			0
		);
		add_action(
			'init',
			array( $this, 'register_listing_related_blocks' )
		);
		add_filter(
			'jet-form-builder/render/checkbox-field/option',
			array( $this, 'on_render_field_checkbox' ),
			10,
			4
		);
		add_filter(
			'jet-form-builder/render/radio-field/option',
			array( $this, 'on_render_field_radio' ),
			10,
			4
		);
		add_filter(
			'render_block_jet-forms/checkbox-field',
			array( $this, 'reset_post_data_after_render_options' )
		);
		add_filter(
			'render_block_jet-forms/radio-field',
			array( $this, 'reset_post_data_after_render_options' )
		);

		if ( jet_engine()->relations ) {
			add_filter(
				'jet-form-builder/post-modifier/object-properties',
				array( $this, 'add_post_properties' )
			);
		}

		$this->option_query->init_hooks();

		if ( jet_form_builder()->has_compat( 'elementor' ) ) {
			$this->elementor->init_hooks();
		}

		if ( jet_form_builder()->has_compat( 'bricks' ) ) {
			$this->bricks->init_hooks();
		}

		/**
		 * @see https://github.com/Crocoblock/issues-tracker/issues/12555
		 */
		add_action( 'jet-engine/rest-api/init-endpoints', array( $this, 'rewrite_map_location_data_endpoint' ), 99 );
	}

	public function remove_hooks() {
		remove_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ) );
		remove_action( 'jet_plugins/frontend/register_scripts', array( $this, 'register_scripts' ) );

		remove_filter(
			'jet-form-builder/preset/source-types',
			array( $this, 'add_sources' )
		);
		remove_filter(
			'jet-form-builder/forms/options-generators',
			array( $this, 'add_generators' )
		);
		remove_filter(
			'jet-form-builder/custom-template-object',
			array( $this, 'get_custom_object_for_template' )
		);
		remove_action(
			'jet-form-builder/generators/get_from_query/setup',
			array( $this, 'add_cct_type_arg_to_block_args' )
		);
		remove_filter(
			'jet-form-builder/blocks/items',
			array( $this, 'add_blocks' ),
			0
		);
		remove_filter(
			'jet-form-builder/render/checkbox-field/option',
			array( $this, 'on_render_field_option' )
		);
		remove_filter(
			'jet-form-builder/render/radio-field/option',
			array( $this, 'on_render_field_option' )
		);
		remove_filter(
			'render_block_jet-forms/checkbox-field',
			array( $this, 'reset_post_data_after_render_options' )
		);
		remove_filter(
			'render_block_jet-forms/radio-field',
			array( $this, 'reset_post_data_after_render_options' )
		);

		remove_action(
			'jet-form-builder/actions/register',
			array( $this, 'add_actions' )
		);

		if ( jet_engine()->relations ) {
			remove_filter(
				'jet-form-builder/post-modifier/object-properties',
				array( $this, 'add_post_properties' )
			);
		}

		$this->option_query->remove_hooks();

		/**
		 * @see https://github.com/Crocoblock/issues-tracker/issues/12555
		 */
		remove_action( 'jet-engine/rest-api/init-endpoints', array( $this, 'rewrite_map_location_data_endpoint' ), 99 );
	}

	/**
	 * If map fields endpoint are regsitered - rewrite location data endpoint permission_callback.
	 * This is required to make it public to use with map field search autocomplete.
	 *
	 * @see https://github.com/Crocoblock/issues-tracker/issues/12555
	 */
	public function rewrite_map_location_data_endpoint( $api_manager ) {
		if ( class_exists( '\Jet_Engine\Modules\Maps_Listings\Get_Map_Location_Data' ) ) {
			require_once $this->get_dir( 'map-field/get-map-location-data-endpoint.php' );
			$api_manager->register_endpoint( new Map_Field\Get_Map_Location_Data_Endpoint() );
		}
	}

	public function register_scripts() {
		$script_asset = require_once $this->get_dir( 'assets/build/frontend/listing.options.asset.php' );

		if ( true === $script_asset ) {
			return;
		}

		array_push(
			$script_asset['dependencies'],
			Module::MAIN_SCRIPT_HANDLE
		);

		wp_register_script(
			Module::LISTING_OPTIONS_HANDLE,
			$this->get_url( 'assets/build/frontend/listing.options.js' ),
			$script_asset['dependencies'],
			$script_asset['version'],
			true
		);
	}

	public function enqueue_admin_assets() {
		$script_asset = require_once $this->get_dir( 'assets/build/editor.asset.php' );
		$handle       = $this->get_handle( 'editor' );

		wp_enqueue_script(
			$handle,
			$this->get_url( 'assets/build/editor.js' ),
			$script_asset['dependencies'],
			$script_asset['version'],
			true
		);
	}

	public function add_sources( array $sources ): array {
		array_push(
			$sources,
			new Preset_Source_Options_Page(),
			new Preset_User()
		);

		return $sources;
	}

	public function add_generators( array $generators ): array {
		array_push(
			$generators,
			new Get_From_Field(),
			new Get_From_Je_Query()
		);

		return $generators;
	}

	public function add_blocks( array $blocks ): array {
		array_push(
			$blocks,
			new Blocks\Map_Field\Block_Type()
		);

		return $blocks;
	}

	public function register_listing_related_blocks() {
		register_block_type(
			$this->get_dir( 'blocks/check-mark' )
		);

		( new Block_Asset() )->init_hooks();
	}

	public function add_actions( \Jet_Form_Builder\Actions\Manager $manager ) {
		$manager->register_action_type( new Update_Options() );
	}

	public function add_post_properties(
		Object_Properties_Collection $collection
	): Object_Properties_Collection {
		return $collection->add( new Post_Je_Relation_Property() );
	}

	public function on_render_field_checkbox( string $item, $value, $option, $render ): string {
		$this->option_type = 'checkbox';

		return $this->on_render_field_option( $item, $value, $option, $render );
	}

	public function on_render_field_radio( string $item, $value, $option, $render ): string {
		$this->option_type = 'radio';

		return $this->on_render_field_option( $item, $value, $option, $render );
	}

	/**
	 * @param string $item
	 * @param $value
	 * @param $option
	 * @param Checkbox\Block_Render|Radio\Block_Render $render
	 *
	 * @return string
	 */
	public function on_render_field_option( string $item, $value, $option, $render ): string {
		$template = '';

		if ( ! empty( $render->args['custom_item_template'] ) ) {
			$template = ( new Builder_Helper() )->get_custom_template(
				$option['object_id'] ?? $value,
				$render->args
			);
		}

		if ( $template ) {
			$this->has_custom_template = true;
		}

		return ( $template . $item );
	}

	public function reset_post_data_after_render_options( string $content ): string {
		if ( $this->has_custom_template ) {
			wp_reset_postdata();
		}

		$this->has_custom_template = null;

		return $content;
	}

	public function add_cct_type_arg_to_block_args( Get_From_Je_Query $generator, Base_Query $query ) {
		if ( 'custom-content-type' !== $query->query_type ) {
			return;
		}
		$generator->get_block()->block_attrs['je_generator_content_type'] = (
			$query->final_query['content_type'] ?? ''
		);
	}

	public function get_custom_object_for_template( $data_object, $object_id, $args ) {
		switch ( $args['je_generator_query_type'] ?? false ) {
			case 'users':
				return get_user_by( 'ID', $object_id );
			case 'terms':
				return get_term( $object_id );
			case 'posts':
				return get_post( $object_id );
			case 'custom-content-type':
				/** @var Custom_Content_Types\Factory $cct_factory */
				$cct_factory = \Jet_Engine\Modules\Custom_Content_Types\Module::instance()->manager->get_content_types(
					$args['je_generator_content_type']
				);

				if ( ! is_object( $cct_factory ) ) {
					return false;
				}

				return (object) $cct_factory->get_db()->get_item( $object_id );
			default:
				return $data_object;
		}
	}

	/**
	 * Only for the internal usage.
	 *
	 * @return string
	 */
	public function get_option_type(): string {
		return $this->option_type;
	}

}
