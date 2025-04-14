<?php
namespace Elementor;

use Elementor\Group_Control_Border;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! trait_exists( '\Jet_Engine_Get_Data_Sources_Trait' ) ) {
	require_once jet_engine()->plugin_path( 'includes/traits/get-data-sources.php' );
}

class Jet_Listing_Dynamic_Image_Widget extends \Jet_Listing_Dynamic_Widget {

	use \Jet_Engine_Get_Data_Sources_Trait;

	public function get_name() {
		return 'jet-listing-dynamic-image';
	}

	public function get_title() {
		return __( 'Dynamic Image', 'jet-engine' );
	}

	public function get_icon() {
		return 'jet-engine-icon-dynamic-image';
	}

	public function get_categories() {
		return [ 'jet-listing-elements' ];
	}

	public function get_help_url() {
		return 'https://crocoblock.com/knowledge-base/articles/jetengine-dynamic-image-widget-overview/?utm_source=jetengine&utm_medium=dynamic-image&utm_campaign=need-help';
	}

	protected function register_controls() {

		$this->start_controls_section(
			'section_general',
			[
				'label' => __( 'Content', 'jet-engine' ),
			]
		);

		$this->add_control(
			'dynamic_image_source',
			[
				'label'   => __( 'Source', 'jet-engine' ),
				'type'    => 'jet-select2',
				'default' => 'post_thumbnail',
				'groups'  => $this->get_dynamic_sources_list( 'media' ),
			]
		);

		if ( jet_engine()->options_pages ) {

			$options_pages_select = jet_engine()->options_pages->get_options_for_select( 'media' );

			if ( ! empty( $options_pages_select ) ) {
				$this->add_control(
					'dynamic_field_option',
					[
						'label'     => __( 'Option', 'jet-engine' ),
						'type'      => 'jet-select2',
						'default'   => '',
						'groups'    => $options_pages_select,
						'condition' => [
							'dynamic_image_source' => 'options_page',
						],
					]
				);
			}

		}

		/**
		 * Add 3rd-party controls for sources
		 */
		do_action( 'jet-engine/listings/dynamic-image/source-controls', $this );

		$this->add_control(
			'dynamic_image_source_custom',
			[
				'label'       => __( 'Custom field/repeater key/component control', 'jet-engine' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
				'label_block' => true,
				'ai'          => [ 'active' => false ],
				'description' => __( 'Note: this field will override Source value', 'jet-engine' ),
			]
		);

		$this->add_control(
			'image_url_prefix',
			[
				'label'       => __( 'Image URL Prefix', 'jet-engine' ),
				'label_block' => true,
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
				'description' => __( 'Add prefix to the image URL. For example for the cases when source contains only part of the URL', 'jet-engine' ),
			]
		);

		$this->add_group_control(
			Group_Control_Image_Size::get_type(),
			[
				'name'    => 'dynamic_image', // Usage: `{name}_size` and `{name}_custom_dimension`, in this case `dynamic_image_size` and `dynamic_image_custom_dimension`.
				'default' => 'full',
				'fields_options' => [
					'size' => [
						'description' => __( 'Note: this option will work only if image stored as attachment ID', 'jet-engine' ),
					],
				],
				'condition' => [
					'dynamic_image_source!' => 'user_avatar',
				],
			]
		);

		$this->add_control(
			'dynamic_avatar_size',
			[
				'label'      => __( 'Image Size', 'jet-engine' ),
				'type'       => Controls_Manager::SLIDER,
				'default'    => [
					'size' => 50,
				],
				'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
				'range'      => [
					'px' => [
						'min' => 10,
						'max' => 500,
					],
				],
				'condition'   => [
					'dynamic_image_source' => 'user_avatar',
				],
				'description' => __( 'Note: this option will work only if image stored as attachment ID', 'jet-engine' ),
			]
		);

		$this->add_control(
			'custom_image_alt',
			[
				'label'       => __( 'Custom Image Alt', 'jet-engine' ),
				'label_block' => true,
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
				'dynamic'     => [
					'active' => 'yes',
				],
			]
		);

		$this->add_control(
			'add_image_caption',
			[
				'label'   => __( 'Add Image Caption', 'jet-engine' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => '',
			]
		);

		$this->add_control(
			'image_caption_position',
			[
				'label'   => __( 'Image Caption Position', 'jet-engine' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'after',
				'options' => [
					'after'  => __( 'After', 'jet-engine' ),
					'before' => __( 'Before', 'jet-engine' ),
				],
				'condition'   => [
					'add_image_caption' => 'yes',
				],
			]
		);

		$this->add_control(
			'image_caption',
			[
				'label'       => __( 'Image Caption Text', 'jet-engine' ),
				'label_block' => true,
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
				'dynamic'     => [
					'active' => 'yes',
				],
				'condition' => [
					'add_image_caption' => 'yes',
				],
			]
		);

		$this->add_control(
			'lazy_load_image',
			[
				'label'   => __( 'Lazy Load', 'jet-engine' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => wp_lazy_loading_enabled( 'img', 'wp_get_attachment_image' ) ? 'yes' : '',
			]
		);

		$this->add_control(
			'linked_image',
			[
				'label'        => __( 'Linked image', 'jet-engine' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'jet-engine' ),
				'label_off'    => __( 'No', 'jet-engine' ),
				'return_value' => 'yes',
				'default'      => '',
				'separator'    => 'before',
			]
		);

		$this->add_control(
			'image_link_source',
			[
				'label'     => __( 'Link Source', 'jet-engine' ),
				'type'      => 'jet-select2',
				'default'   => '_permalink',
				'groups'    => $this->get_dynamic_sources_list( 'plain' ),
				'condition' => [
					'linked_image' => 'yes',
				],
			]
		);

		$this->add_control(
			'lightbox',
			[
				'label'     => esc_html__( 'Lightbox', 'jet-engine' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'condition' => [
					'linked_image'      => 'yes',
					'image_link_source' => '_file',
				],
			]
		);

		if ( jet_engine()->options_pages ) {

			$options_pages_select = jet_engine()->options_pages->get_options_for_select( 'plain' );

			if ( ! empty( $options_pages_select ) ) {
				$this->add_control(
					'image_link_option',
					[
						'label'     => __( 'Option', 'jet-engine' ),
						'type'      => 'jet-select2',
						'default'   => '',
						'groups'    => $options_pages_select,
						'condition' => [
							'linked_image'      => 'yes',
							'image_link_source' => 'options_page',
						],
					]
				);
			}

		}

		/**
		 * Add 3rd-party controls for sources
		 */
		do_action( 'jet-engine/listings/dynamic-image/link-source-controls', $this );

		$this->add_control(
			'image_link_source_custom',
			[
				'label'       => __( 'Custom field/repeater key/component control', 'jet-engine' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
				'label_block' => true,
				'ai'          => [ 'active' => false ],
				'description' => __( 'Note: this field will override Meta Field value', 'jet-engine' ),
				'condition'   => [
					'linked_image' => 'yes',
				],
			]
		);

		$this->add_control(
			'link_url_prefix',
			[
				'label'       => __( 'Link URL Prefix', 'jet-engine' ),
				'label_block' => true,
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
				'description' => __( 'Add prefix to the URL, for example tel:, mailto: etc.', 'jet-engine' ),
				'condition'   => [
					'linked_image' => 'yes',
				],
			]
		);

		$this->add_control(
			'open_in_new',
			[
				'label'        => esc_html__( 'Open in new window', 'jet-engine' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'jet-engine' ),
				'label_off'    => esc_html__( 'No', 'jet-engine' ),
				'return_value' => 'yes',
				'default'      => '',
				'condition'    => [
					'linked_image' => 'yes',
				],
			]
		);

		$this->add_control(
			'rel_attr',
			[
				'label'   => __( 'Add "rel" attr', 'jet-engine' ),
				'type'    => Controls_Manager::SELECT,
				'default' => '',
				'options' => \Jet_Engine_Tools::get_rel_attr_options(),
				'condition'   => [
					'linked_image' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'image_alignment',
			[
				'label'   => __( 'Alignment', 'jet-engine' ),
				'type'    => Controls_Manager::CHOOSE,
				'default' => 'flex-start',
				'options' => [
					'flex-start' => [
						'title' => esc_html__( 'Start', 'jet-engine' ),
						'icon'  => ! is_rtl() ? 'eicon-h-align-left' : 'eicon-h-align-right',
					],
					'center' => [
						'title' => esc_html__( 'Center', 'jet-engine' ),
						'icon'  => 'eicon-h-align-center',
					],
					'flex-end' => [
						'title' => esc_html__( 'End', 'jet-engine' ),
						'icon'  => ! is_rtl() ? 'eicon-h-align-right' : 'eicon-h-align-left',
					],
				],
				'selectors'  => [
					$this->css_selector() => 'justify-content: {{VALUE}};',
					$this->css_selector( '__figure' ) => 'align-items: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'hide_if_empty',
			[
				'label'        => esc_html__( 'Hide if value is empty', 'jet-engine' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'jet-engine' ),
				'label_off'    => esc_html__( 'No', 'jet-engine' ),
				'return_value' => 'yes',
				'default'      => '',
				'separator'    => 'before',
			]
		);

		$this->add_control(
			'fallback_image',
			[
				'label'       => __( 'Fallback Image', 'jet-engine' ),
				'description' => __( 'This image will be shown if selected source field is empty', 'jet-engine' ),
				'type'        => Controls_Manager::MEDIA,
				'dynamic'     => [
					'active' => true,
				],
				'condition'   => [
					'hide_if_empty' => '',
				],
			]
		);

		$this->add_control(
			'object_context',
			[
				'label'     => __( 'Context', 'jet-engine' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => 'default_object',
				'options'   => jet_engine()->listings->allowed_context_list(),
				'separator' => 'before',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_image_style',
			[
				'label'      => __( 'Image', 'jet-engine' ),
				'tab'        => Controls_Manager::TAB_STYLE,
				'show_label' => false,
			]
		);

		$this->add_responsive_control(
			'image_width',
			[
				'label' => esc_html__( 'Width', 'jet-engine' ),
				'type' => Controls_Manager::SLIDER,
				'default' => [
					'unit' => '%',
				],
				'tablet_default' => [
					'unit' => '%',
				],
				'mobile_default' => [
					'unit' => '%',
				],
				'size_units' => jet_engine()->elementor_views->add_custom_size_unit( [ 'px', '%', 'em', 'rem', 'custom' ] ),
				'range' => [
					'%' => [
						'min' => 1,
						'max' => 100,
					],
					'px' => [
						'min' => 1,
						'max' => 1000,
					],
					'vw' => [
						'min' => 1,
						'max' => 100,
					],
				],
				'selectors' => [
					$this->css_selector( ' a' )   => 'width: {{SIZE}}{{UNIT}};',
					$this->css_selector( ' img' ) => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'image_max_width',
			[
				'label' => esc_html__( 'Max Width', 'jet-engine' ),
				'type' => Controls_Manager::SLIDER,
				'default' => [
					'unit' => '%',
				],
				'tablet_default' => [
					'unit' => '%',
				],
				'mobile_default' => [
					'unit' => '%',
				],
				'size_units' => jet_engine()->elementor_views->add_custom_size_unit( [ 'px', '%', 'em', 'rem', 'custom' ] ),
				'range' => [
					'%' => [
						'min' => 1,
						'max' => 100,
					],
					'px' => [
						'min' => 1,
						'max' => 1000,
					],
					'vw' => [
						'min' => 1,
						'max' => 100,
					],
				],
				'selectors' => [
					$this->css_selector( ' a' )   => 'max-width: {{SIZE}}{{UNIT}};',
					$this->css_selector( ' img' ) => 'max-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'image_height',
			[
				'label' => esc_html__( 'Height', 'jet-engine' ),
				'type' => Controls_Manager::SLIDER,
				'default' => [
					'unit' => 'px',
				],
				'tablet_default' => [
					'unit' => 'px',
				],
				'mobile_default' => [
					'unit' => 'px',
				],
				'size_units' => jet_engine()->elementor_views->add_custom_size_unit( [ 'px', '%', 'em', 'rem', 'vh', 'custom' ] ),
				'range' => [
					'px' => [
						'min' => 1,
						'max' => 500,
					],
					'vh' => [
						'min' => 1,
						'max' => 100,
					],
				],
				'selectors' => [
					$this->css_selector( ' img' ) => 'height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'image_object_fit',
			[
				'label' => esc_html__( 'Object Fit', 'jet-engine' ),
				'type' => Controls_Manager::SELECT,
				'options' => [
					''        => esc_html__( 'Default', 'jet-engine' ),
					'fill'    => esc_html__( 'Fill', 'jet-engine' ),
					'cover'   => esc_html__( 'Cover', 'jet-engine' ),
					'contain' => esc_html__( 'Contain', 'jet-engine' ),
				],
				'default' => '',
				'condition' => [
					'image_height[size]!' => '',
				],
				'selectors' => [
					$this->css_selector( ' img' ) => 'object-fit: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'           => 'image_border',
				'label'          => __( 'Border', 'jet-engine' ),
				'placeholder'    => '1px',
				'selector'       => $this->css_selector( ' img' ),
			]
		);

		$this->add_responsive_control(
			'image_border_radius',
			[
				'label'      => __( 'Border Radius', 'jet-engine' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => jet_engine()->elementor_views->add_custom_size_unit( [ 'px', '%', 'custom' ] ),
				'selectors'  => [
					$this->css_selector( ' img' ) => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_caption_style',
			[
				'label'      => __( 'Caption', 'jet-engine' ),
				'tab'        => Controls_Manager::TAB_STYLE,
				'show_label' => false,
			]
		);

		$this->add_control(
			'caption_color',
			array(
				'label' => __( 'Color', 'jet-engine' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => array(
					$this->css_selector( '__caption' ) => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'caption_typography',
				'selector'  => $this->css_selector( '__caption' ),
			)
		);

		$this->add_responsive_control(
			'caption_max_width',
			[
				'label' => esc_html__( 'Max Width', 'jet-engine' ),
				'type' => Controls_Manager::SLIDER,
				'default' => [
					'unit' => '%',
				],
				'separator' => 'before',
				'tablet_default' => [
					'unit' => '%',
				],
				'mobile_default' => [
					'unit' => '%',
				],
				'size_units' => jet_engine()->elementor_views->add_custom_size_unit( [ 'px', '%', 'em', 'rem', 'custom' ] ),
				'range' => [
					'%' => [
						'min' => 1,
						'max' => 100,
					],
					'px' => [
						'min' => 1,
						'max' => 1000,
					],
					'vw' => [
						'min' => 1,
						'max' => 100,
					],
				],
				'selectors' => [
					$this->css_selector( '__caption' )   => 'max-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'caption_alignment',
			[
				'label'   => __( 'Caption Alignment', 'jet-engine' ),
				'type'    => Controls_Manager::CHOOSE,
				'default' => 'flex-start',
				'options' => [
					'flex-start' => [
						'title' => esc_html__( 'Start', 'jet-engine' ),
						'icon'  => ! is_rtl() ? 'eicon-h-align-left' : 'eicon-h-align-right',
					],
					'center' => [
						'title' => esc_html__( 'Center', 'jet-engine' ),
						'icon'  => 'eicon-h-align-center',
					],
					'flex-end' => [
						'title' => esc_html__( 'End', 'jet-engine' ),
						'icon'  => ! is_rtl() ? 'eicon-h-align-right' : 'eicon-h-align-left',
					],
				],
				'selectors'  => [
					$this->css_selector( '__caption' ) => 'align-self: {{VALUE}};',
				],
				'condition' => [
					'caption_max_width[size]!' => '',
				],
			]
		);

		$this->add_responsive_control(
			'caption_text_alignment',
			array(
				'label'       => esc_html__( 'Caption Text Alignment', 'jet-engine' ),
				'type'        => Controls_Manager::CHOOSE,
				'default'     => 'left',
				'options'     => array(
					'left'    => array(
						'title' => esc_html__( 'Left', 'jet-engine' ),
						'icon'  => 'eicon-text-align-left',
					),
					'center' => array(
						'title' => esc_html__( 'Center', 'jet-engine' ),
						'icon'  => 'eicon-text-align-center',
					),
					'right' => array(
						'title' => esc_html__( 'Right', 'jet-engine' ),
						'icon'  => 'eicon-text-align-right',
					),
				),
				'selectors'  => array(
					$this->css_selector( '__caption' ) => 'text-align: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'caption_padding',
			array(
				'label'      => __( 'Padding', 'jet-engine' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => jet_engine()->elementor_views->add_custom_size_unit( array( 'px', '%', 'em', 'rem', 'custom' ) ),
				'selectors'  => array(
					$this->css_selector( '__caption' ) => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'caption_margin',
			array(
				'label'      => __( 'Margin', 'jet-engine' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => jet_engine()->elementor_views->add_custom_size_unit( array( 'px', '%', 'em', 'rem', 'custom' ) ),
				'selectors'  => array(
					$this->css_selector( '__caption' ) => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'           => 'caption_border',
				'label'          => __( 'Border', 'jet-engine' ),
				'placeholder'    => '1px',
				'selector'       => $this->css_selector( '__caption' ),
			)
		);

		$this->add_responsive_control(
			'caption_border_radius',
			array(
				'label'      => __( 'Border Radius', 'jet-engine' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => jet_engine()->elementor_views->add_custom_size_unit( array( 'px', '%' ) ),
				'selectors'  => array( 
					$this->css_selector( '__caption' ) => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

	}

	/**
	 * Returns CSS selector for nested element
	 *
	 * @param  [type] $el [description]
	 * @return [type]     [description]
	 */
	public function css_selector( $el = null ) {
		return sprintf( '{{WRAPPER}} .%1$s%2$s', $this->get_name(), $el );
	}

	/**
	 * Get meta fields for post type
	 *
	 * @return array
	 */
	public function get_dynamic_sources_list( $for = 'media' ) {

		$raw    = $this->get_dynamic_sources( $for );
		$result = [];

		foreach ( $raw as $group ) {
			$result[] = [
				'label'   => $group['label'],
				'options' => array_combine(
					array_map( function( $item ) {
						return $item['value'];
					}, $group['values'] ),
					array_map( function( $item ) {
						return $item['label'];
					}, $group['values'] )
				),
			];
		}

		return $result;

	}

	protected function render() {
		jet_engine()->listings->render_item( 'dynamic-image', $this->get_settings_for_display() );
	}

}
