window.JetPlugins.hooks.addAction(
	'jetEngine.shortcodeGenerator.controls',
	'jetEngineDynamicVisibility',
	( addControl, shortcodeGenerator ) => {
		const conditionJetEngineCondition = { 'shortcode_types': 'jet_engine_condition' };
		const {
			visibility_condition_type: visibilityConditionType,
			condition_controls: conditionControls,
		} = window.JetEngineDashboardConfig.shortode_generator;

		addControl( 'jedv_type', {
			label: shortcodeGenerator.labels.visibility_condition_type.label,
			description: shortcodeGenerator.labels.visibility_condition_type.description,
			type: 'select',
			default: 'show',
			options: visibilityConditionType,
			condition: {
				...conditionJetEngineCondition,
				tag_type: 'enclosed'
			},
		} );

		for ( const name in conditionControls ) {
			const {
				condition,
				type,
				label,
				description = '',
				groups,
				options,
				multiple,
				default: defaultValue = '',
			} = conditionControls[ name ];

			const mergedCondition = { ...conditionJetEngineCondition, ...(condition || {}) };

			const data = {
				type,
				label,
				description,
				default: defaultValue,
				condition: mergedCondition,
			}

			switch (type) {
				case 'select':
					if ( options ) {
						data.options = options;
					}

					if ( groups ) {
						data.groups = groups;
					}

					break;

				case 'select2':
					data.multiple = multiple;
					data.options = options;

					break;

				case 'raw_html':
					continue;
			}

			addControl( name, data );
		}
	}
);
