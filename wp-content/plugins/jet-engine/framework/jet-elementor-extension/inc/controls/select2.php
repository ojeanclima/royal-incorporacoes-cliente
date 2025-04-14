<?php

namespace Jet_Elementor_Extension;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Select2_Control extends \Elementor\Control_Select2 {

	public function get_type() {
		return 'jet-select2';
	}

	/**
	 * Render select2 control output in the editor.
	 *
	 * Used to generate the control HTML in the editor using Underscore JS
	 * template. The variables for the class are available using `data` JS
	 * object.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function content_template() {
		?>
		<div class="elementor-control-field">
			<# if ( data.label ) {#>
			<label for="<?php $this->print_control_uid(); ?>" class="elementor-control-title">{{{ data.label }}}</label>
			<# } #>
			<div class="elementor-control-input-wrapper elementor-control-unit-5">
				<# var multiple = ( data.multiple ) ? 'multiple' : ''; #>
				<select id="<?php $this->print_control_uid(); ?>" class="elementor-select2" type="select2" {{ multiple }} data-setting="{{ data.name }}">
					<#
						var printOptions = function( options ) {
							_.each( options, function( option_title, option_value ) {
								var value = data.controlValue;
								if ( typeof value == 'string' ) {
									var selected = ( option_value === value ) ? 'selected' : '';
								} else if ( null !== value ) {
									var value = _.values( value );
									var selected = ( -1 !== value.indexOf( option_value ) ) ? 'selected' : '';
								}
								#>
								<option {{ selected }} value="{{ _.escape( option_value ) }}">{{{ _.escape( option_title ) }}}</option>
							<# } );
						};

						if ( data.groups ) {
							for ( var groupIndex in data.groups ) {
								var groupArgs = data.groups[ groupIndex ];
								if ( groupArgs.options ) { #>
									<optgroup label="{{ groupArgs.label }}">
										<# printOptions( groupArgs.options ) #>
									</optgroup>
								<# } else if ( _.isString( groupArgs ) ) { #>
									<option value="{{ groupIndex }}">{{{ groupArgs }}}</option>
								<# }
							}
						} else {
							printOptions( data.options );
						}
					#>
				</select>
			</div>
		</div>
		<# if ( data.description ) { #>
			<div class="elementor-control-field-description">{{{ data.description }}}</div>
		<# } #>
		<?php
	}

}
