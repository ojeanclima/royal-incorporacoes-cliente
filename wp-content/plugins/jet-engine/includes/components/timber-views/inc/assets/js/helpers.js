window.controlsHelper = {
	methods: {
		getPreparedControls( instance ) {

			const controls = [];

			for ( const controlID in instance ) {

				let control     = instance[ controlID ];
				let optionsList = [];
				let type        = control.type;
				let label       = control.label;
				let defaultVal  = control.default;
				let groupsList  = [];
				let condition   = control.condition || {};
				let multiple    = false;

				switch ( control.type ) {

					case 'switcher':
						type = 'cx-vui-switcher';
						break;

					case 'text':
					case 'number':
						type = 'cx-vui-input';
						break;

					case 'textarea':
						type = 'cx-vui-textarea';
						break;

					case 'select':
					case 'select2':

						if ( 'select' === control.type ) {
							type = 'cx-vui-select';
						} else {
							type = 'cx-vui-f-select';
							multiple = true;
						}

						if ( control.groups ) {

							for ( var i = 0; i < control.groups.length; i++) {

								let group = control.groups[ i ];

								if ( group.values ) {
									groupsList.push( {
										label: group.label,
										options: group.values,
									} );
								} else {
									groupsList.push( {
										label: group.label,
										options: group.options,
									} );
								}
								

							}
						}

						/* else {
							for ( const optionValue in control.options ) {
								optionsList.push( {
									value: optionValue,
									label: control.options[ optionValue ],
								} );
							}
						}*/

						break;
				}

				controls.push( {
					type: type,
					name: controlID,
					label: label,
					description: control.description,
					default: defaultVal,
					optionsList: control.options,
					groupsList: groupsList,
					condition: condition,
					multiple: multiple,
				} );

			}

			return controls;
		},
		checkCondition: function( condition, settings ) {

			let checkResult = true;

			condition = condition || {};

			for ( let [ fieldName, check ] of Object.entries( condition ) ) {
				
				let isNegative = false;
				
				if ( fieldName.includes( '!' ) ) {
					fieldName = fieldName.replace( '!', '' );
					isNegative = true;
				}

				if ( check && check.length ) {
					if ( isNegative ) {
						if ( check.includes( settings[ fieldName ] ) ) {
							checkResult = false;
						}
					} else {
						if ( ! settings[ fieldName ] || ! check.includes( settings[ fieldName ] ) ) {
							checkResult = false;
						}
					}
					
				} else {
					if ( isNegative ) {
						if ( check == settings[ fieldName ] ) {
							checkResult = false;
						}
					} else {
						if ( check != settings[ fieldName ] ) {
							checkResult = false;
						}
					}
				}
			}

			return checkResult;

		}
	}
}

window.popupHelper = {
	data() {
		return {
			showPopup: false,
		};
	},
	methods: {
		closePopup() {
			if ( this.showPopup ) {
				this.switchPopup();
			}
		},
		switchPopup() {
			if ( this.showPopup ) {
				this.showPopup = false;
				this.onPopupClose();
			} else {
				this.onPopupShow();
				this.showPopup = true;
			}
		},
		onPopupClose() {
		},
		onPopupShow() {
		}
	}
}