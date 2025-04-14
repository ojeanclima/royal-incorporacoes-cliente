<?php
/**
 * Elementor views manager
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Define Jet_Engine_Blocks_Views_Editor_Meta_Boxes class
 */
class Jet_Engine_Blocks_Views_Editor_Meta_Boxes {

	public $hook = 'jet_engine_blocks_component_save';

	public function __construct() {

		add_action( 'add_meta_boxes', array( $this, 'add_css_meta_box' ) );
		add_action( 'save_post', array( $this, 'save_meta' ) );

		add_action( 'wp_ajax_' . $this->hook, [ $this, 'save_component_settings' ] );

	}

	public function save_component_settings() {

		$post_id = ! empty( $_REQUEST['post_id'] ) ? absint( $_REQUEST['post_id'] ) : false;

		if ( ! $post_id || ! current_user_can( 'edit_post', $post_id ) ) {
			wp_send_json_error( __( 'You can`t edit this component', 'jet-engine' ) );
		}

		$nonce = ! empty( $_REQUEST['nonce'] ) ? $_REQUEST['nonce'] : false;

		if ( ! $nonce || ! wp_verify_nonce( $nonce, $this->hook ) ) {
			wp_send_json_error( __( 'The page is expired. Please reload it and try again.', 'jet-engine' ) );
		}

		$settings = ! empty( $_REQUEST['settings'] ) ? $_REQUEST['settings'] : [];
		$controls = ( ! empty( $settings['controls'] ) && is_array( $settings['controls'] ) ) ? $settings['controls'] : [];
		$styles = ( ! empty( $settings['styles'] ) && is_array( $settings['styles'] ) ) ? $settings['styles'] : [];

		$component = jet_engine()->listings->components->get( $post_id, 'id' );

		if ( ! $component ) {
			wp_send_json_error( __( 'Component doesn`t exists.', 'jet-engine' ) );
		}

		$component->set_props( $controls );
		$component->set_styles( $styles );

		wp_send_json_success();

	}

	public function save_meta( $post_id ) {

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( isset( $_POST['_jet_engine_listing_css'] ) ) {
			$css = esc_attr( $_POST['_jet_engine_listing_css'] );
			update_post_meta( $post_id, '_jet_engine_listing_css', $css );
		}

		$settings_keys = array(
			'jet_engine_listing_source',
			'jet_engine_listing_post_type',
			'jet_engine_listing_tax',

			'jet_engine_listing_repeater_source',
			'jet_engine_listing_repeater_field',
			'jet_engine_listing_repeater_option',

			'jet_engine_listing_link',
			'jet_engine_listing_link_source',
			'jet_engine_listing_link_object_prop',
			'jet_engine_listing_link_custom_url',
			'jet_engine_listing_link_add_query_args',
			'jet_engine_listing_link_query_args',
			'jet_engine_listing_link_url_anchor',
			'jet_engine_listing_link_option',
			'jet_engine_listing_link_open_in_new',
			'jet_engine_listing_link_rel_attr',
			'jet_engine_listing_link_aria_label',
			'jet_engine_listing_link_prefix',
		);

		$keep_newlines = array(
			'jet_engine_listing_link_query_args' => true,
		);

		$settings_to_store    = array();
		$el_settings_to_store = array();

		foreach ( $settings_keys as $key ) {
			if ( isset( $_POST[ $key ] ) ) {
				$store_key = str_ireplace( 'jet_engine_listing_', '', $key );

				if ( in_array( $store_key, array( 'source', 'post_type', 'tax' ) ) ) {
					$settings_to_store[ $store_key ] = esc_attr( $_POST[ $key ] );
					$el_settings_to_store[ 'listing_' . $store_key ] = esc_attr( $_POST[ $key ] );
				} elseif ( false !== strpos( $store_key, 'repeater_' ) ) {
					// repeater settings store only to `_elementor_page_settings` without `listing_` prefix
					$el_settings_to_store[ $store_key ] = esc_attr( $_POST[ $key ] );
				} else {
					// link settings
					if ( isset( $keep_newlines[ $key ] ) ) {
						$store = sanitize_textarea_field( $_POST[ $key ] );
					} else {
						$store = sanitize_text_field( $_POST[ $key ] );
					}
					
					$el_settings_to_store[ 'listing_' . $store_key ] = $store;
				}
			}
		}

		if ( ! empty( $settings_to_store ) ) {

			$listing_settings = get_post_meta( $post_id, '_listing_data', true );
			$elementor_page_settings = get_post_meta( $post_id, '_elementor_page_settings', true );

			if ( empty( $listing_settings ) ) {
				$listing_settings = array();
			}

			if ( empty( $elementor_page_settings ) ) {
				$elementor_page_settings = array();
			}

			$listing_settings        = array_merge( $listing_settings, $settings_to_store );
			$elementor_page_settings = array_merge( $elementor_page_settings, $el_settings_to_store );

			update_post_meta( $post_id, '_listing_data', $listing_settings );
			update_post_meta( $post_id, '_elementor_page_settings', $elementor_page_settings );

		}

		do_action( 'jet-engine/blocks/editor/save-settings', $post_id );

	}

	/**
	 * Add listing item CSS metabox
	 */
	public function add_css_meta_box() {

		global $post;

		if ( ! $post ) {
			return;
		}

		if ( jet_engine()->listings->components->is_component( $post->ID ) ) {
			$settings_label = __( 'Component Settings', 'jet-engine' );
			$settings_cb = array( $this, 'render_component_settings_box' );
			$css_label = __( 'Component CSS', 'jet-engine' );
		} else {
			$settings_label = __( 'Listing Settings', 'jet-engine' );
			$settings_cb = array( $this, 'render_settings_box' );
			$css_label = __( 'Listing CSS', 'jet-engine' );
		}

		add_meta_box(
			'jet_engine_lisitng_settings',
			$settings_label,
			$settings_cb,
			jet_engine()->listings->post_type->slug(),
			'side',
			'high'
		);

		add_meta_box(
			'jet_engine_lisitng_css',
			$css_label,
			array( $this, 'render_css_box' ),
			jet_engine()->listings->post_type->slug(),
			'side',
			'high'
		);

	}

	/**
	 * Render a placeholder for component settings meta box
	 * 
	 * @return [type] [description]
	 */
	public function render_component_settings_box() {

		global $post;

		if ( ! $post ) {
			return;
		}

		$component = jet_engine()->listings->components->get( $post->ID, 'id' );

		if ( ! $component ) {
			return;
		}

		printf(
			'<div id="jet_engine_block_component_settings" data-settings="%1$s" data-hook="%2$s" data-nonce="%3$s" data-control-types="%4$s" data-post="%5$s"></div>',
			htmlspecialchars( wp_json_encode( [
				'controls' => $component->get_props(),
				'styles'   => $component->get_styles(),
			] ) ),
			$this->hook,
			wp_create_nonce( $this->hook ),
			htmlspecialchars( wp_json_encode( Jet_Engine_Tools::prepare_list_for_js(
				jet_engine()->listings->components->get_supported_control_types(), ARRAY_A
			) ) ),
			$post->ID
		);
	}

	/**
	 * Render box settings HTML
	 *
	 * @return [type] [description]
	 */
	public function render_settings_box( $post ) {

		$settings      = get_post_meta( $post->ID, '_listing_data', true );
		$page_settings = get_post_meta( $post->ID, '_elementor_page_settings', true );

		if ( empty( $settings ) ) {
			$settings = array();
		}

		$source = ! empty( $settings['source'] ) ? $settings['source'] : 'posts';

		$controls = array(
			'jet_engine_listing_source' => array(
				'label'   => __( 'Listing Source', 'jet-engine' ),
				'options' => jet_engine()->listings->post_type->get_listing_item_sources(),
				'value'   => $source,
			),
			'jet_engine_listing_post_type' => array(
				'label'     => __( 'Listing Post Type', 'jet-engine' ),
				'options'   => jet_engine()->listings->get_post_types_for_options(),
				'value'     => ! empty( $settings['post_type'] ) ? $settings['post_type'] : 'post',
				'condition' => array(
					'jet_engine_listing_source' => array( 'posts', 'repeater' ),
				),
			),
			'jet_engine_listing_tax' => array(
				'label'     => __( 'Listing Taxonomy', 'jet-engine' ),
				'options'   => jet_engine()->listings->get_taxonomies_for_options(),
				'value'     => ! empty( $settings['tax'] ) ? $settings['tax'] : 'category',
				'condition' => array(
					'jet_engine_listing_source' => array( 'terms' ),
				),
			),
			'jet_engine_listing_repeater_source' => array(
				'label'     => __( 'Repeater source', 'jet-engine' ),
				'options'   => jet_engine()->listings->repeater_sources(),
				'value'     => ! empty( $page_settings['repeater_source'] ) ? $page_settings['repeater_source'] : 'jet_engine',
				'condition' => array(
					'jet_engine_listing_source' => array( 'repeater' ),
				),
			),
			'jet_engine_listing_repeater_field' => array(
				'label'       => __( 'Repeater field', 'jet-engine' ),
				'description' => __( 'If JetEngine, or ACF, or etc selected as source.', 'jet-engine' ),
				'value'       => ! empty( $page_settings['repeater_field'] ) ? $page_settings['repeater_field'] : '',
				'condition'   => array(
					'jet_engine_listing_source' => array( 'repeater' ),
					'jet_engine_listing_repeater_source!' => 'jet_engine_options',
				),
			),
			'jet_engine_listing_repeater_option' => array(
				'label'       => __( 'Repeater option', 'jet-engine' ),
				'description' => __( 'If <b>JetEngine Options Page</b> selected as source.', 'jet-engine' ),
				'groups'      => jet_engine()->options_pages->get_options_for_select( 'repeater' ),
				'value'       => ! empty( $page_settings['repeater_option'] ) ? $page_settings['repeater_option'] : '',
				'condition'   => array(
					'jet_engine_listing_source' => array( 'repeater' ),
					'jet_engine_listing_repeater_source' => 'jet_engine_options',
				),
			),
		);

		$controls = apply_filters( 'jet-engine/blocks/editor/controls/settings', $controls, $settings, $post );

		$link_controls = array(
			'jet_engine_listing_link' => array(
				'label'   => __( 'Make listing item clickable', 'jet-engine' ),
				'value'   => ! empty( $page_settings['listing_link'] ) ? $page_settings['listing_link'] : '',
				'options' => array(
					''    => __( 'No', 'jet-engine' ),
					'yes' => __( 'Yes', 'jet-engine' ),
				),
			),
			'jet_engine_listing_link_source' => array(
				'label'     => __( 'Link source', 'jet-engine' ),
				'value'     => ! empty( $page_settings['listing_link_source'] ) ? $page_settings['listing_link_source'] : '',
				'groups'    => jet_engine()->listings->get_listing_link_sources(),
				'condition' => array(
					'jet_engine_listing_link' => 'yes',
				),
			),
			'jet_engine_listing_link_object_prop' => array(
				'label'     => __( 'Object property', 'jet-engine' ),
				'value'     => ! empty( $page_settings['listing_link_object_prop'] ) ? $page_settings['listing_link_object_prop'] : '',
				'groups'    => jet_engine()->listings->data->get_object_fields(),
				'condition' => array(
					'jet_engine_listing_link' => 'yes',
					'jet_engine_listing_link_source' => 'object_prop',
				),
			),
			'jet_engine_listing_link_custom_url' => array(
				'label'       => __( 'Custom URL', 'jet-engine' ),
				'value'       => ! empty( $page_settings['listing_link_custom_url'] ) ? $page_settings['listing_link_custom_url'] : '',
				'description' => __( 'Shortcodes and JetEngine macros supported. Overrides Link source.', 'jet-engine' ),
				'condition'   => array(
					'jet_engine_listing_link' => 'yes',
				),
			),
			'jet_engine_listing_link_open_in_new' => array(
				'label'   => __( 'Open in new window', 'jet-engine' ),
				'value'   => ! empty( $page_settings['listing_link_open_in_new'] ) ? $page_settings['listing_link_open_in_new'] : '',
				'options' => array(
					''    => __( 'No', 'jet-engine' ),
					'yes' => __( 'Yes', 'jet-engine' ),
				),
				'condition' => array(
					'jet_engine_listing_link' => 'yes',
				),
			),
			'jet_engine_listing_link_rel_attr' => array(
				'label'     => __( 'Add "rel" attr', 'jet-engine' ),
				'value'     => ! empty( $page_settings['listing_link_rel_attr'] ) ? $page_settings['listing_link_rel_attr'] : '',
				'options'   => \Jet_Engine_Tools::get_rel_attr_options(),
				'condition' => array(
					'jet_engine_listing_link' => 'yes',
				),
			),
			'jet_engine_listing_link_aria_label' => array(
				'label'       => __( 'Aria label attr / Link text', 'jet-engine' ),
				'description' => __( 'Use <b>Shortcode Generator</b> or <b>Macros Generator</b> to pass a dynamic value', 'jet-engine' ),
				'value'       => ! empty( $page_settings['listing_link_aria_label'] ) ? $page_settings['listing_link_aria_label'] : '',
				'condition'   => array(
					'jet_engine_listing_link' => 'yes',
				),
			),
			'jet_engine_listing_link_prefix' => array(
				'label'     => __( 'Link prefix', 'jet-engine' ),
				'value'     => ! empty( $page_settings['listing_link_prefix'] ) ? $page_settings['listing_link_prefix'] : '',
				'condition' => array(
					'jet_engine_listing_link' => 'yes',
				),
			),
			'jet_engine_listing_link_add_query_args' => array(
				'label'       => esc_html__( 'Add Query Arguments', 'jet-engine' ),
				'options' => array(
					''    => __( 'No', 'jet-engine' ),
					'yes' => __( 'Yes', 'jet-engine' ),
				),
				'value'       => ! empty( $page_settings['listing_link_add_query_args'] ) ? $page_settings['listing_link_add_query_args'] : '',
				'condition'   => array(
					'jet_engine_listing_link' => 'yes',
				),
			),
			'jet_engine_listing_link_query_args' => array(
				'label'       => __( 'Query Arguments', 'jet-engine' ),
				'label_block' => true,
				'input_type'  => 'textarea',
				'value'       => ! empty( $page_settings['listing_link_query_args'] ) ? $page_settings['listing_link_query_args'] : '_post_id=%current_id%',
				'description' => __( 'One argument per line. Separate key and value with "="', 'jet-engine' ),
				'condition'   => array(
					'jet_engine_listing_link'                => 'yes',
					'jet_engine_listing_link_add_query_args' => 'yes',
				),
			),
			'jet_engine_listing_link_url_anchor' => array(
				'label'       => __( 'URL Anchor', 'jet-engine' ),
				'label_block' => true,
				'value'       => ! empty( $page_settings['listing_link_url_anchor'] ) ? $page_settings['listing_link_url_anchor'] : '',
				'description' => __( 'Add anchor to the URL. Without #.', 'jet-engine' ),
				'condition'   => array(
					'jet_engine_listing_link' => 'yes',
				),
			),
		);

		if ( jet_engine()->options_pages ) {
			$options_pages_select = jet_engine()->options_pages->get_options_for_select( 'plain' );

			if ( ! empty( $options_pages_select ) ) {

				$options_link_controls = array(
					'jet_engine_listing_link_option' => array(
						'label'     => __( 'Option', 'jet-engine' ),
						'groups'    => $options_pages_select,
						'value'     => ! empty( $page_settings['listing_link_option'] ) ? $page_settings['listing_link_option'] : '',
						'condition' => array(
							'jet_engine_listing_link'        => 'yes',
							'jet_engine_listing_link_source' => 'options_page',
						),
					),
				);

				$link_controls = \Jet_Engine_Tools::array_insert_after( $link_controls, 'jet_engine_listing_link_source', $options_link_controls );
			}
		}

		$link_controls = apply_filters( 'jet-engine/blocks/editor/controls/link-settings', $link_controls, $page_settings, $post );
		$all_controls  = array_merge( $controls, $link_controls );

		$conditions = array();

		echo '<style>
			.jet-engine-base-control select,
			.jet-engine-base-control input {
				box-sizing: border-box;
				margin: 0;
			}
			.jet-engine-base-control select {
				width: 100%;
			}
			.jet-engine-base-control .components-base-control__field {
				margin: 0 0 10px;
			}
			.jet-engine-base-control .components-base-control__label {
				display: block;
				font-weight: bold;
				padding: 0 0 5px;
			}
			.jet-engine-base-control .components-base-control__help {
				font-size: 12px;
				font-style: normal;
				color: #757575;
				margin: 5px 0 0;
			}
			.jet-engine-condition-setting {
				display: none;
			}
			.jet-engine-condition-setting-show {
				display: block;
			}
		</style>';
		echo '<div class="components-base-control jet-engine-base-control">';

			foreach ( $all_controls as $control_name => $control_args ) {

				$field_classes = array(
					'components-base-control__field',
				);

				// for backward compatibility
				if ( ! empty( $control_args['source'] ) ) {
					$control_args['condition'] = array(
						'jet_engine_listing_source' => $control_args['source'],
					);
				}

				if ( ! empty( $control_args['condition'] ) ) {
					$conditions[ $control_name ] = $control_args['condition'];

					$field_classes[] = 'jet-engine-condition-setting';

					$is_visible = true;

					foreach ( $control_args['condition'] as $cond_field => $cond_value ) {

						$is_negative = false !== strpos( $cond_field, '!' );

						if ( $is_negative ) {
							$cond_field = str_replace( '!', '', $cond_field );
						}

						$current_value = $all_controls[ $cond_field ]['value'];

						if ( is_array( $cond_value ) ) {
							$check = in_array( $current_value, $cond_value );
						} else {
							$check = $current_value == $cond_value;
						}

						if ( $is_negative ) {
							$check = ! $check;
						}

						if ( ! $check ) {
							$is_visible = false;
							break;
						}
					}

					if ( $is_visible ) {
						$field_classes[] = 'jet-engine-condition-setting-show';
					}
				}

				echo '<div class="' . join( ' ', $field_classes ) . '">';
					echo '<label class="components-base-control__label" for="' . $control_name . '">';
						echo $control_args['label'];
					echo '</label>';

					if ( ! empty( $control_args['groups'] ) || ! empty( $control_args['options'] ) ) {
						echo '<select id="' . $control_name . '" name="' . $control_name . '" class="components-select-control__input">';

							if ( ! empty( $control_args['groups'] ) ) {

								foreach ( $control_args['groups'] as $group_key => $group ) {

									if ( empty( $group ) ) {
										continue;
									}

									if ( ! empty( $group['options'] ) ) {
										echo '<optgroup label="' . $group['label'] . '">';

										foreach ( $group['options'] as $option_key => $option_label ) {
											printf( '<option value="%1$s"%3$s>%2$s</option>',
												$option_key,
												$option_label,
												selected( $control_args['value'], $option_key, false )
											);
										}

										echo '</optgroup>';

									} elseif ( is_string( $group ) ) {
										printf( '<option value="%1$s"%3$s>%2$s</option>',
											$group_key,
											$group,
											selected( $control_args['value'], $group_key, false )
										);
									}
								}

							} else {
								foreach ( $control_args['options'] as $option_key => $option_label ) {
									printf( '<option value="%1$s"%3$s>%2$s</option>',
										$option_key,
										$option_label,
										selected( $control_args['value'], $option_key, false )
									);
								}
							}

						echo '</select>';
					} else {
						$input_type = ! empty( $control_args['input_type'] ) ? $control_args['input_type'] : 'text';
						if ( $input_type === 'textarea' ) {
							printf( '<textarea id="%1$s" name="%1$s" rows="5" class="components-textarea-control__input">%2$s</textarea>',
								$control_name,
								esc_attr( $control_args['value'] )
							);
						} else {
							printf( '<input type="%1$s" id="%2$s" name="%2$s" class="components-text-control__input" value="%3$s">',
								esc_attr( $input_type ),
								$control_name,
								esc_attr( $control_args['value'] )
							);
						}
						
					}

					if ( ! empty( $control_args['description'] ) ) {
						echo '<p class="components-base-control__help">' . $control_args['description'] . '</p>';
					}

				echo '</div>';
			}

			do_action( 'jet-engine/blocks/editor/settings-meta-box', $post );

			echo '<p>';
				_e( 'You need to reload page after saving to apply new settings', 'jet-engine' );
			echo '</p>';
		echo '</div>';

		echo "<script>
				var JetEngineListingConditions = " . json_encode( $conditions ) . ";
				
				jQuery( '[name^=\"jet_engine_listing_\"]' ).on( 'change', function( e ) {
					var fieldName = jQuery( e.currentTarget ).attr('name');
					
					for ( var field in JetEngineListingConditions ) {
						
						if ( field === fieldName ) {
							continue;
						}
						
						var conditions = JetEngineListingConditions[ field ];
						
						if ( -1 === Object.keys( conditions ).indexOf( fieldName ) && -1 === Object.keys( conditions ).indexOf( fieldName + '!' ) ) {
							continue;
						}
						
						var isVisible = true,
							fieldWrapper = jQuery( '[name=\"' + field + '\"]' ).closest( '.jet-engine-condition-setting' );

						for ( var conditionField in conditions ) {
								
							var isNegative = -1 !== conditionField.indexOf( '!' ),
								conditionFieldName = conditionField;
							
							if ( isNegative ) {
								conditionFieldName = conditionField.replace( '!', '' );
							}
							
							var currentValue   = jQuery( '[name=\"' + conditionFieldName + '\"]' ).val(),
								conditionValue = conditions[ conditionField ];
							
							if ( Array.isArray( conditionValue ) ) {
								isVisible = -1 !== conditionValue.indexOf( currentValue )
							} else {
								isVisible = conditionValue == currentValue;
							}
							
							if ( isNegative ) {
								isVisible = !isVisible;
							}
							
							if ( !isVisible ) {
								break;
							}
						}
						
						if ( isVisible )  {
							fieldWrapper.addClass( 'jet-engine-condition-setting-show' );
						} else {
							fieldWrapper.removeClass( 'jet-engine-condition-setting-show' );
						}
					}
				} );
			</script>";
	}

	/**
	 * Render CSS metabox
	 *
	 * @return [type] [description]
	 */
	public function render_css_box( $post ) {

		$css = get_post_meta( $post->ID, '_jet_engine_listing_css', true );

		if ( ! $css ) {
			$css = '';
		}

		?>
		<div class="jet-engine-listing-css">
			<p><?php
				_e( 'When targeting your specific element, add <code>selector</code> before the tags and classes you want to exclusively target, i.e: <code>selector a { color: red;}</code>', 'jet-engine' );
			?></p>
			<textarea class="components-textarea-control__input jet_engine_listing_css" name="_jet_engine_listing_css" rows="16" style="width:100%"><?php
				echo $css;
			?></textarea>
			<div id="jet_component_css_vars">
				
			</div>
		</div>
		<?php

	}

}
