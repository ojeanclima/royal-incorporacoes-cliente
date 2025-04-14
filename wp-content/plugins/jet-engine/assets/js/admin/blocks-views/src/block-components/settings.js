import SettingsApp from './settings-app';

( function() {

	// Strict mode for more secure JavaScript. Helps with debugging and prevents some silent errors.
	"use strict";

	// Grab the HTML element where the React app will mount.
	const el = document.getElementById( 'jet_engine_block_component_settings' );

	// Check if both the mounting point and the form exist
	if ( el ) {
		wp.element.render(
			<SettingsApp
				settings={ JSON.parse( el.dataset.settings ) }
				hook={ el.dataset.hook }
				nonce={ el.dataset.nonce }
				controlTypes={ JSON.parse( el.dataset.controlTypes ) }
				postID={ el.dataset.post }
			/>,
			el // The element where the React component should be rendered.
		);
	}

} )();