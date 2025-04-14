<?php
namespace Jet_Engine\Modules\Custom_Content_Types;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( '\Jet_Engine\Modules\Custom_Content_Types\Pages\Edit_Item_Page' ) ) {
	require_once Module::instance()->module_path( 'pages/edit-content-type-item.php' );
}

/**
 * Define Quick_Edit page class
 */
class Quick_Edit extends Pages\Edit_Item_Page {

	/**
	 * Current page data
	 *
	 * @var null
	 */
	public $page = null;

	/**
	 * Current page slug
	 *
	 * @var null
	 */
	public $slug = null;

	/**
	 * Prepared fields array
	 *
	 * @var null
	 */
	public $prepared_fields = null;

	/**
	 * Holder for is page or not is page now prop
	 *
	 * @var null
	 */
	public $is_page_now = null;

	/**
	 * Inerface builder instance
	 *
	 * @var null
	 */
	public $builder = null;

	/**
	 * Factory holder
	 *
	 * @var null
	 */
	private $factory = null;

	/**
	 * Constructor for the class
	 */
	public function __construct( $page, $factory ) {
		$this->page     = $page;
		$this->meta_box = $page['fields'];
		$this->factory  = $factory;
	}

	/**
	 * Initialize page builder
	 *
	 * @return [type] [description]
	 */
	public function init_builder() {

		$builder_data = jet_engine()->framework->get_included_module_data( 'cherry-x-interface-builder.php' );

		$this->builder = new \CX_Interface_Builder(
			array(
				'path' => $builder_data['path'],
				'url'  => $builder_data['url'],
			)
		);

		$this->setup_page_fields();

		$fields = $this->get_prepared_fields();

		$this->builder->register_settings(
			array(
				'settings_top' => array(
					'type'  => 'settings',
					'class' => 'fields-count-' . count( $fields ),
				),
				'settings_bottom' => array(
					'type' => 'settings',
				),
			)
		);

		// Register the Service Fields
		$service_fields = array(
			'cct_status' => array(
				'type'    => 'select',
				'parent'  => 'settings_bottom',
				'id'      => 'cct_status',
				'name'    => 'cct_status',
				'title'   => __( 'Status', 'jet-engine' ),
				'options' => $this->factory->get_statuses(),
			),
			'cct_created' => array(
				'type'         => 'text',
				'input_type'   => 'datetime-local',
				'autocomplete' => 'off',
				'parent'       => 'settings_bottom',
				'id'           => 'cct_created',
				'name'         => 'cct_created',
				'title'        => __( 'Published', 'jet-engine' ),
				'extra_attr'   => array(
					'step' => '1',
				),
			),
		);

		$this->builder->register_control( array_merge( $fields, $service_fields ) );

	}

	public function render_fields() {
		$this->init_builder();
		$html = $this->builder->render( false );

		$replace_map_attrs = array(
			'data-required=' => 'required=',
			'data-min='      => 'min=',
			'data-max='      => 'max=',
			'data-step='     => 'step=',
		);

		$html = str_replace( array_keys( $replace_map_attrs ), array_values( $replace_map_attrs ), $html );

		echo $html;
	}
}
