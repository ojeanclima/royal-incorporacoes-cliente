( function( $ ) {

	class JetEngineFrontendQueryEditor {
		queryPopup;
		queryIframe;
		closeButton;
		loadSpinner;		
	
		init() {
			this.queryPopup = document.querySelector( '.jet-engine-query-edit-modal' );
	
			if ( ! this.queryPopup ) {
				return;
			}
	
			this.queryIframe = this.queryPopup.querySelector( 'iframe' );
			this.closeButton = this.queryPopup.querySelector( '.jet-engine-query-edit-modal--close-button' );
			this.loadSpinner = this.queryPopup.querySelector( '.jet-engine-query-spinner' );
	
			this.queryPopup.addEventListener( 'close', this.resetIframe );
			this.queryIframe.addEventListener( 'load', this.readyIframe );
	
			this.closeButton.addEventListener( 'click', this.closeQueryPopup );
	
			this.widgetButtons();
	
			document.addEventListener( 'jet-smart-filters/inited', this.filtersCompatibility );
	
			$( window ).on( 'jet-popup/ajax/frontend-init/after', this.widgetButtons );
	
			$( document ).on( 'jet-engine/listing-grid/after-lazy-load', this.widgetButtons );

			$( document ).on( 'jet-engine-request-calendar-done', this.widgetButtons );
			$( document ).on( 'jet-engine-request-calendar-cached', this.widgetButtonsForced );
		}
	
		filtersCompatibility() {
			JetSmartFilters.events.subscribe( 'ajaxFilters/updated', function() {
				window.JetEngineFrontendQueryEditor.widgetButtons();
			} );
		}
	
		readyIframe() {
			window.JetEngineFrontendQueryEditor.loadSpinner.classList.add( 'inactive' );
		}
	
		closeQueryPopup() {
			window.JetEngineFrontendQueryEditor.queryPopup.close();
		}
	
		setIframe( url ) {
			window.JetEngineFrontendQueryEditor.queryIframe.src = url + '&mode=fullscreen';
		}
	
		resetIframe() {
			window.JetEngineFrontendQueryEditor.queryIframe.src = '';
		}
	
		widgetButtons() {
			const buttons = document.querySelectorAll( ':scope .jet-engine-frontend-query-editor-buttons>.edit-button:not(.initialized)' );
	
			for ( const button of buttons ) {
				button.addEventListener( 'click', window.JetEngineFrontendQueryEditor.widgetButtonsPopup );
				button.classList.add( 'initialized' );
			}
		}

		widgetButtonsForced( e, $scope ) {
			const buttons = $scope.find( '.jet-engine-frontend-query-editor-buttons>.edit-button' );

			for ( const button of buttons ) {
				button.addEventListener( 'click', window.JetEngineFrontendQueryEditor.widgetButtonsPopup );
				button.classList.add( 'initialized' );
			}
		}
	
		widgetButtonsPopup( e ) {
			e.preventDefault();
			e.stopImmediatePropagation();
	
			const url = this.dataset.queryLink;
			
			if ( ! url ) {
				return;
			}
	
			window.JetEngineFrontendQueryEditor.queryPopup.showModal();
			window.JetEngineFrontendQueryEditor.loadSpinner.classList.remove( 'inactive' );
			window.JetEngineFrontendQueryEditor.setIframe( url );
		}
	}

	$( window ).on( 'jet-engine/frontend/loaded', function() {
		window.JetEngineFrontendQueryEditor = new JetEngineFrontendQueryEditor();
		window.JetEngineFrontendQueryEditor.init();
		$( window ).trigger( 'jet-engine/frontend/query-editor/loaded' );
	} );

} )( jQuery );
