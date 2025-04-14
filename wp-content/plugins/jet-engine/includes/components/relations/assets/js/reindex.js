(function( $ ) {

	'use strict';

	$( '.cpt-header' ).find( '.wp-header-end' ).before( '<div style="margin: -33px 0 10px 0; display: flex; justify-content: flex-end; gap: 10px; align-items: center;" class="jet-engine-reindex-relation-container"><span style="display: none;" class="jet-engine-reindex-relation-processing">' + window.JetEngineRelationsReindex.processing + '</span><span style="display: none;" class="jet-engine-reindex-relation-done">' + window.JetEngineRelationsReindex.done + '</span><a class="jet-engine-reindex-relation" style="display: flex; align-items: center; gap: 5px; text-decoration-style: dashed;" href="#"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M18 15.422v.983c0 .771-1.862 1.396-4 1.396s-4-.625-4-1.396v-.983c.968.695 2.801.902 4 .902 1.202 0 3.035-.208 4-.902zm-4-1.363c-1.202 0-3.035-.209-4-.902v.973c0 .771 1.862 1.396 4 1.396s4-.625 4-1.396v-.973c-.968.695-2.801.902-4 .902zm0-5.86c-2.138 0-4 .625-4 1.396 0 .77 1.862 1.395 4 1.395s4-.625 4-1.395c0-.771-1.862-1.396-4-1.396zm0 3.591c-1.202 0-3.035-.209-4-.902v.977c0 .77 1.862 1.395 4 1.395s4-.625 4-1.395v-.977c-.968.695-2.801.902-4 .902zm-.5-9.79c-5.288 0-9.649 3.914-10.377 9h-3.123l4 5.917 4-5.917h-2.847c.711-3.972 4.174-7 8.347-7 4.687 0 8.5 3.813 8.5 8.5s-3.813 8.5-8.5 8.5c-3.015 0-5.662-1.583-7.171-3.957l-1.2 1.775c1.916 2.536 4.948 4.182 8.371 4.182 5.797 0 10.5-4.702 10.5-10.5s-4.703-10.5-10.5-10.5z" fill="currentColor"/></svg>' + window.JetEngineRelationsReindex.label + '</a></div>' );

	$( document ).on( 'click', '.jet-engine-reindex-relation', function( event ) {

		event.preventDefault();

		const $button = $( this );
		const $container = $button.closest( '.jet-engine-reindex-relation-container' );
		const $processing = $container.find( '.jet-engine-reindex-relation-processing' );
		const $done = $container.find( '.jet-engine-reindex-relation-done' );

		let relationID = false;

		$processing.show();
		$done.hide();

		if ( window.JetEngineRelationsReindex.relation_id ) {
			relationID = window.JetEngineRelationsReindex.relation_id;
		}

		$button.css({
			pointerEvents: 'none',
			opacity: '0.7',
		});

		$.ajax({
			url: window.ajaxurl,
			type: 'POST',
			dataType: 'json',
			data: {
				action: window.JetEngineRelationsReindex.action,
				_nonce: window.JetEngineRelationsReindex._nonce,
				relation: relationID
			},
		} ).always( function() {
			$button.css({
				pointerEvents: 'auto',
				opacity: '1',
			});

			$processing.hide();

		}).done( function() {

			$done.show();

			setTimeout( () => {
				$done.hide();
			}, 1500 );

		}).fail( function() {
			console.log("error");
		} );
		

	} );

})( jQuery );
