'use strict';

class CroblockWorkflow {

	constructor( workflow, resetPosition ) {

		resetPosition = resetPosition || false;

		this.setup( workflow, resetPosition );
		this.render();

	}

	setup( workflow, resetPosition ) {

		this.workflow = workflow;
		this.workflow.step = parseInt( this.workflow.step, 10 ) || 0;
		this.rendered = false;
		this.isActive = true;

		if ( resetPosition ) {
			window.localStorage.removeItem( 'cw-top' );
			window.localStorage.removeItem( 'cw-right' );
			window.localStorage.removeItem( 'cw-width' );
			window.localStorage.removeItem( 'cw-height' );
		}
	}

	getIcon( icon ) {

		let iconPath = false;

		switch ( icon ) {
			case 'check':
				iconPath = '<path d="m428.231-349.847 224.922-223.922L616-610.922 428.231-424.153l-85-84L306.078-471l122.153 121.153Zm51.836 233.846q-74.836 0-141.204-28.42-66.369-28.42-116.182-78.21-49.814-49.791-78.247-116.129-28.433-66.337-28.433-141.173 0-75.836 28.42-141.704 28.42-65.869 78.21-115.682 49.791-49.814 116.129-78.247 66.337-28.433 141.173-28.433 75.836 0 141.704 28.42 65.869 28.42 115.682 78.21 49.814 49.791 78.247 115.629 28.433 65.837 28.433 141.673 0 74.836-28.42 141.204-28.42 66.369-78.21 116.182-49.791 49.814-115.629 78.247-65.837 28.433-141.673 28.433ZM480-168q130 0 221-91t91-221q0-130-91-221t-221-91q-130 0-221 91t-91 221q0 130 91 221t221 91Zm0-312Z"/>';
				break;

			case 'close':
				iconPath = '<path d="M291-253.847 253.847-291l189-189-189-189L291-706.153l189 189 189-189L706.153-669l-189 189 189 189L669-253.847l-189-189-189 189Z"/>';
				break;

			case 'drag':
				iconPath = '<path d="M359.788-207.386q-23.441 0-39.922-16.693-16.48-16.692-16.48-40.133 0-23.441 16.693-39.922 16.692-16.48 40.133-16.48 23.441 0 39.922 16.693 16.48 16.692 16.48 40.133 0 23.441-16.693 39.922-16.692 16.48-40.133 16.48Zm240 0q-23.441 0-39.922-16.693-16.48-16.692-16.48-40.133 0-23.441 16.693-39.922 16.692-16.48 40.133-16.48 23.441 0 39.922 16.693 16.48 16.692 16.48 40.133 0 23.441-16.693 39.922-16.692 16.48-40.133 16.48Zm-240-216q-23.441 0-39.922-16.693-16.48-16.692-16.48-40.133 0-23.441 16.693-39.922 16.692-16.48 40.133-16.48 23.441 0 39.922 16.693 16.48 16.692 16.48 40.133 0 23.441-16.693 39.922-16.692 16.48-40.133 16.48Zm240 0q-23.441 0-39.922-16.693-16.48-16.692-16.48-40.133 0-23.441 16.693-39.922 16.692-16.48 40.133-16.48 23.441 0 39.922 16.693 16.48 16.692 16.48 40.133 0 23.441-16.693 39.922-16.692 16.48-40.133 16.48Zm-240-216q-23.441 0-39.922-16.693-16.48-16.692-16.48-40.133 0-23.441 16.693-39.922 16.692-16.48 40.133-16.48 23.441 0 39.922 16.693 16.48 16.692 16.48 40.133 0 23.441-16.693 39.922-16.692 16.48-40.133 16.48Zm240 0q-23.441 0-39.922-16.693-16.48-16.692-16.48-40.133 0-23.441 16.693-39.922 16.692-16.48 40.133-16.48 23.441 0 39.922 16.693 16.48 16.692 16.48 40.133 0 23.441-16.693 39.922-16.692 16.48-40.133 16.48Z"/>';
				break;

			case 'link':
				iconPath = '<path d="M228.309-164.001q-27.008 0-45.658-18.65-18.65-18.65-18.65-45.658v-503.382q0-27.008 18.65-45.658 18.65-18.65 45.658-18.65h236.305V-744H228.309q-4.616 0-8.463 3.846-3.846 3.847-3.846 8.463v503.382q0 4.616 3.846 8.463 3.847 3.846 8.463 3.846h503.382q4.616 0 8.463-3.846 3.846-3.847 3.846-8.463v-236.305h51.999v236.305q0 27.008-18.65 45.658-18.65 18.65-45.658 18.65H228.309Zm159.46-186.615-37.153-37.153L706.847-744H576v-51.999h219.999V-576H744v-130.847L387.769-350.616Z"/>';
				break;

			case 'next':
				iconPath = '<path d="M648.078-454.001H212.001v-51.998h436.077L443.232-710.846 480-747.999 747.999-480 480-212.001l-36.768-37.153 204.846-204.847Z"/>';
				break;

			case 'prev':
				iconPath = '<path d="m311.922-454.001 204.846 204.847L480-212.001 212.001-480 480-747.999l36.768 37.153-204.846 204.847h436.077v51.998H311.922Z"/>';
				break;

			case 'play':
				iconPath = '<path d="M356.001-252.156v-455.688L707.074-480 356.001-252.156ZM409-481Zm-1 133 204.769-132L408-612v264Z"/>';
				break;

			case 'pause':
				iconPath = '<path d="M538.001-212.001v-535.998h209.998v535.998H538.001Zm-326 0v-535.998h209.998v535.998H212.001ZM589.999-264H696v-432H589.999v432ZM264-264h106.001v-432H264v432Zm0-432v432-432Zm325.999 0v432-432Z"/>';
				break;

		}

		if ( iconPath ) {
			return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960">' + iconPath + '</svg>';
		}

	}

	render() {

		if ( this.rendered ) {
			return;
		}

		this.rendered = true;

		this.createContainers();
		this.setupDrag();
		this.setupResize();
		this.setupWorkflow( ( this.workflow.step ) );

		this.adjustContainerHeight();

	}

	adjustContainerHeight() {

		let minHeight = this.dataContainer.getBoundingClientRect().height;
		let currentHeight = parseInt( this.mainContainer.style.height, 10 );

		if ( currentHeight < ( minHeight + 40 ) ) {
			this.mainContainer.style.height = ( minHeight + 40 ) + 'px';
		}

		this.resizableTrigger.style.top = ( parseInt( this.mainContainer.style.height, 10 ) - 14 ) + 'px';

	}

	resume() {

		this.changeState( {
			action: 'resume'
		} );

		this.render();
	}

	createContainers() {

		this.mainContainer = document.createElement( 'div' );
		this.dataContainer = document.createElement( 'div' );
		this.draggableTrigger = document.createElement( 'div' );
		this.resizableTrigger = document.createElement( 'div' );
		this.mainContainer.classList.add( 'crocoblock-workflow' );
		this.draggableTrigger.classList.add( 'crocoblock-workflow__drag' );
		this.draggableTrigger.innerHTML = this.getIcon( 'drag' );
		this.resizableTrigger.classList.add( 'crocoblock-workflow__resize' );
		this.dataContainer.classList.add( 'crocoblock-workflow-data' );
		this.mainContainer.append( this.draggableTrigger );
		this.mainContainer.append( this.dataContainer );
		this.mainContainer.append( this.resizableTrigger );

		let top = window.localStorage.getItem( 'cw-top' ) || false;
		let right = window.localStorage.getItem( 'cw-right' ) || false;
		let width = window.localStorage.getItem( 'cw-width' ) || false;
		let height = window.localStorage.getItem( 'cw-height' ) || false;

		if ( top ) {
			this.mainContainer.style.top = top;
		}

		if ( right ) {
			this.mainContainer.style.right = right;
		}

		if ( width ) {
			this.mainContainer.style.width = width;
		}

		if ( height ) {
			this.mainContainer.style.height = height;
		}

		document.body.append( this.mainContainer );

		// add close button
		this.draggableTrigger.append( this.createButton(
			this.getIcon( 'close' ),
			'crocoblock-workflow__close',
			{},
			( e ) => {
				this.destroy();
			}
		) );
	}

	dragOver( event ) {
		
		let offset;
			
		try {
			offset = event.dataTransfer.getData( "text/plain" ).split( ',' );
		} catch(e) {
			console.log( 'Can`t access event.dataTransfer object' );
		}

		let leftPos = ( event.clientX + parseInt( offset[0], 10 ) );
		let topPos  = ( event.clientY + parseInt( offset[1], 10 ) );

		if ( 0 > leftPos ) {
			leftPos = 0;
		}

		if ( leftPos > window.innerWidth - this.mainContainer.offsetWidth ) {
			leftPos = window.innerWidth - this.mainContainer.offsetWidth;
		}

		if ( topPos > window.innerHeight - this.mainContainer.offsetHeight ) {
			topPos = window.innerHeight - this.mainContainer.offsetHeight;
		}

		this.mainContainer.style.right = window.innerWidth - ( leftPos + this.mainContainer.offsetWidth ) + 'px';
		this.mainContainer.style.top = topPos + 'px';

		window.localStorage.setItem( 'cw-right', this.mainContainer.style.right );
		window.localStorage.setItem( 'cw-top', this.mainContainer.style.top );

		event.preventDefault();
		return false;
	}

	setupDrag() {
		
		this.mainContainer.setAttribute( 'draggable', true );

		this.mainContainer.addEventListener( 'dragstart', ( event ) => {
			
			let style = window.getComputedStyle( event.target, null );
			let offsetData = ( parseInt( style.getPropertyValue( "left" ), 10 ) - event.clientX ) + ',' + ( parseInt( style.getPropertyValue( "top" ), 10 ) - event.clientY );
			
			event.dataTransfer.setData( "text/plain", offsetData );
		} );

		document.body.addEventListener( 'dragover', ( event ) => { 
			this.dragOver( event );
		} );

		document.body.addEventListener( 'drop', ( event ) => {
			this.dragOver( event );
		} );
	}

	setupResize() {
		this.posResizeX = 0;
		this.posResizeY = 0;
		this.resizableTrigger.onmousedown = ( e ) => { this.resizeMouseDown( e ) };
	}

	resizeMouseDown( e ) {

		e = e || window.event;
		e.preventDefault();

		this.posResizeX = e.clientX;
		this.posResizeY = e.clientY;

		document.onmouseup = ( e ) => { this.stopElementEvents( e ) };
		document.onmousemove = ( e ) => { this.elementResize( e ) };

	}

	elementResize( e ) {

		e = e || window.event;
		e.preventDefault();
		
		// calculate the new cursor position:
		let newWidth = ( this.mainContainer.getBoundingClientRect().width + ( this.posResizeX - e.clientX ) ) + "px";
		let newHeight = ( this.mainContainer.getBoundingClientRect().height - ( this.posResizeY - e.clientY ) ) + "px";

		this.mainContainer.style.width = newWidth;
		this.mainContainer.style.height = newHeight;

		this.posResizeX = e.clientX;
		this.posResizeY = e.clientY;

		window.localStorage.setItem( 'cw-width', newWidth );
		window.localStorage.setItem( 'cw-height', newHeight );

		this.resizableTrigger.style.top = ( parseInt( newHeight, 10 ) - 14 ) + 'px';

	}

	stopElementEvents() {
		// stop moving when mouse button is released:
		document.onmouseup = null;
		document.onmousemove = null;
	}

	goToStep( step ) {
		this.clearData( this.dataContainer );
		this.setupWorkflow( step );

		this.workflow.step = step;
		
		this.changeState( {
			action: 'to_step',
			step: step,
		} );

		this.adjustContainerHeight();

	}

	completeWorkflow() {

		this.changeState( {
			action: 'complete'
		} );

		this.destroy();

	}

	changeState( data ) {
		
		jQuery.ajax({
			url: window.ajaxurl,
			type: 'POST',
			dataType: 'json',
			data: {
				action: 'crocoblock_workflow_state_change',
				nonce: window.CrocoblockWorkflowsData.stateNonce,
				workflow: this.workflow.id,
				data: data,
			},
		}).done( ( response ) => {
			if ( ! response.success ) {
				alert( response.data );
			}
		} );
	}

	clearData( el ) {
		for ( var i = el.children.length; i--; ) {
			el.removeChild( el.children[ i ] );
		}
	}

	pause() {

		if ( this.isActive ) {
			
			this.changeState( {
				action: 'pause'
			} );

			this.isActive = false;
			this.pauseButton.innerHTML = this.getIcon( 'play' );
			this.addTooltip( this.pauseButton, 'Resume tutorial' );

		} else {
			this.changeState( {
				action: 'resume'
			} );

			this.isActive = true;
			this.pauseButton.innerHTML = this.getIcon( 'pause' );
			this.addTooltip( this.pauseButton, 'Pause current tutorial (can be continued from the Tutorials page)' );

		}
		
	}

	start() {
		if ( 'in-progress' !== this.workflow.status ) {
			this.changeState( {
				action: 'start'
			} );
			this.workflow.status = 'in-progress';
		}
	}

	destroy() {
		this.clearData( this.mainContainer );
		this.mainContainer.remove();
	}

	createStepper( complete, total ) {

		let completed = '';

		for ( var i = 0; i < total; i++ ) {
			
			let classes = 'crocoblock-workflow__stepper-item';

			if ( i < complete ) {
				classes += ' crocoblock-workflow__stepper-item-active';
			}

			completed += '<div class="' + classes + '"></div>';

		}

		const stepper = document.createElement( 'div' );
		stepper.classList.add( 'crocoblock-workflow__stepper' );
		stepper.innerHTML = completed;
		this.dataContainer.append( stepper );
	}

	setupWorkflow( step ) {

		const stepData = this.workflow.steps[ step ];

		this.createEl( this.workflow.workflow, 'title' );
		this.createEl( 'Step: ' + ( step + 1 ) + ' of ' + this.workflow.steps.length, 'steps' );
		this.createStepper( step + 1, this.workflow.steps.length );
		this.createEl( stepData.name, 'name' );

		if ( stepData.dependency && ! stepData.dependency.isCompleted ) {
			this.createEl( () => {
				return this.createButton(
					stepData.dependency.label,
					'crocoblock-workflow__complete-dependency',
					{},
					( e, el ) => {
						jQuery.ajax({
							url: window.ajaxurl,
							type: 'POST',
							dataType: 'json',
							data: {
								action: 'crocoblock_workflow_process_dependency',
								nonce: window.CrocoblockWorkflowsData.dependenciesNonce,
								data: stepData.dependency,
							},
						}).done( ( response ) => {
							if ( ! response.success ) {
								alert( response.data );
							} else {
								el.replaceWith( this.getDependencyCompletedEl( stepData.dependency.label ) );
								this.actionLink.classList.remove( 'crocoblock-workflow__go-to-page-disabled' );
							}
						} );
						
					}
				);
			}, 'row' );
		}

		if ( stepData.dependency && stepData.dependency.isCompleted ) {
			this.createEl( () => {
				return this.getDependencyCompletedEl( stepData.dependency.label )
			}, 'text' );
		}

		this.createEl( stepData.help, 'description' );

		if ( stepData.stepURL ) {
			this.createEl( () => {
				
				this.actionLink = document.createElement( 'a' );
				this.actionLink.setAttribute( 'href', stepData.stepURL.url );
				this.actionLink.classList.add( 'crocoblock-workflow__go-to-page' );

				if ( stepData.dependency && ! stepData.dependency.isCompleted ) {
					this.actionLink.classList.add( 'crocoblock-workflow__go-to-page-disabled' );
				}

				this.actionLink.innerHTML = stepData.stepURL.label;

				return this.actionLink;
			}, 'row' );
		}

		if ( stepData.tutorial ) {
			this.createEl( () => {
				
				this.tutorialLink = document.createElement( 'a' );
				this.tutorialLink.setAttribute( 'target', '_blank' );
				this.tutorialLink.setAttribute( 'href', stepData.tutorial );
				this.tutorialLink.classList.add( 'crocoblock-workflow__link' );
				this.tutorialLink.innerHTML = 'More detailed info ' + this.getIcon( 'link' );

				return this.tutorialLink;
			}, 'row' );
		}

		let minStep = 0;
		let maxStep = this.workflow.steps.length - 1;

		this.createEl( () => {

			let actions = [];

			// Prev step
			actions.push( this.createButton(
				this.getIcon( 'prev' ),
				'crocoblock-workflow__prev-step',
				{ disabled: minStep === step },
				( e ) => {
					this.goToStep( step - 1 );
				},
				'Go to previous step'
			) );

			// Pause
			this.pauseButton = this.createButton(
				this.getIcon( 'pause' ),
				'crocoblock-workflow__pause',
				{},
				( e ) => {
					this.pause();
				},
				'Pause current tutorial (can be continued from the Tutorials page)'
			);

			actions.push( this.pauseButton );

			if ( maxStep === step ) {
				// Complete
				actions.push( this.createButton(
					this.getIcon( 'check' ),
					'crocoblock-workflow__next-step',
					{},
					( e ) => {
						this.completeWorkflow();
					},
					'Complete tutorial (can be started again from the Tutorials page)'
				) );
			} else {
				// Next step
				actions.push( this.createButton(
					this.getIcon( 'next' ),
					'crocoblock-workflow__next-step',
					{ disabled: maxStep === step },
					( e ) => {
						this.goToStep( step + 1 );
					},
					'Go to next step'
				) );
			}

			return actions;

		}, 'actions' );
	}

	getDependencyCompletedEl( label ) {
		const el = document.createElement( 'div' );
		el.classList.add( 'crocoblock-workflow__dependency-completed' );
		el.innerHTML = this.getIcon( 'check' ) + label;
		return el;
	}

	addTooltip( el, tooltip ) {
		const tooltipEl = document.createElement( 'div' );
		tooltipEl.classList.add( 'crocoblock-workflow__tooltip' );
		tooltipEl.innerHTML = tooltip;
		el.append( tooltipEl );
	}

	createButton( label, className, attrs, onClick, tooltip ) {
		
		const button = document.createElement( 'button' );
		
		button.setAttribute( 'type', 'button' );
		button.innerHTML = label;
		button.classList.add( 'crocoblock-workflow__btn' );
		button.classList.add( className );

		if ( tooltip ) {
			this.addTooltip( button, tooltip );
		}

		if ( attrs ) {
			for ( const attr in attrs ) {
				if ( false !== attrs[ attr ] ) {
					button.setAttribute( attr, attrs[ attr ] );
				}
			}
		}

		if ( onClick && ! button.getAttribute( 'disabled' ) ) {
			button.addEventListener( 'click', ( e ) => {
				onClick( e, button );
			} );
		}

		return button;
	}

	createEl( html, prop ) {
		
		const el = document.createElement( 'div' );
		
		if ( html && 'function' === typeof html ) {
			let nodes = html();

			if ( nodes.length ) {
				el.append( ...nodes );
			} else {
				el.append( nodes );
			}

		} else {
			el.innerHTML = html;
		}

		el.classList.add( 'crocoblock-workflow-data__' + prop );
		this.dataContainer.append( el );
	}

}

var crocoblockCurrentWorkflow;

if ( window.CrocoblockWorkflowsData.activeWorkflow ) {
	crocoblockCurrentWorkflow = new CroblockWorkflow( window.CrocoblockWorkflowsData.activeWorkflow );
}

const crocoblockWorkflowsTrigger = document.querySelectorAll( '.crocoblock-workflow-item__btn' );

if ( crocoblockWorkflowsTrigger ) {
	crocoblockWorkflowsTrigger.forEach( ( workflowTrigger ) => {
		workflowTrigger.addEventListener( 'click', ( event ) => {

			if ( confirm( 'Are you sure? If you have any active workflows, the will be paused' ) ) {

				const workflowData = JSON.parse( workflowTrigger.dataset.workflow );

				if ( crocoblockCurrentWorkflow 
					&& workflowData.id != crocoblockCurrentWorkflow.workflow.id ) {
					crocoblockCurrentWorkflow.destroy();
				}

				if ( workflowTrigger.workflowInstance ) {

					crocoblockCurrentWorkflow = workflowTrigger.workflowInstance;
					crocoblockCurrentWorkflow.setup( workflowData, true );
					crocoblockCurrentWorkflow.resume();

				} else {

					if ( ! crocoblockCurrentWorkflow 
						|| workflowData.id != crocoblockCurrentWorkflow.workflow.id
					) {
						const buttonWorkflow = new CroblockWorkflow( workflowData );
						crocoblockCurrentWorkflow = buttonWorkflow;
						crocoblockCurrentWorkflow.start();
						workflowTrigger.workflowInstance = buttonWorkflow;
					} else {
						crocoblockCurrentWorkflow.setup( workflowData, true );
						crocoblockCurrentWorkflow.resume();
						workflowTrigger.workflowInstance = crocoblockCurrentWorkflow;
					}

				}

			}
		} );
	} );
}
