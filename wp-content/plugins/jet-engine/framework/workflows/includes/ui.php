<?php
/**
 * Workflows UI
 */
namespace Croblock\Workflows;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class UI {

	public $workflows = false;

	public static $assests_enqueued = false;
	public static $invalidated = false;

	public function __construct( $workflows ) {
		$this->workflows = $workflows;
		$this->maybe_refresh_workflows_list();

	}

	public function invalidate_trigger() {
		return $this->workflows->prefix() . '-workflows';
	}

	public function maybe_refresh_workflows_list() {

		if ( empty( $_GET[ $this->invalidate_trigger() ] ) || 'refresh' !== $_GET[ $this->invalidate_trigger() ] ) {
			return;
		}

		if ( self::$invalidated ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( empty( $_GET['_nonce'] ) || ! wp_verify_nonce( $_GET['_nonce'], 'crocoblock-workflows-ui' ) ) {
			return;
		}

		$this->workflows->storage()->invalidate_cache();
		self::$invalidated = true;

	}

	public function render_page() {

		$namespaces = $this->workflows->storage()->get_namespaces();

		echo '<div class="crocoblock-workflows-page">';

		echo '<div class="crocoblock-workflows-page__title">';
		
			printf( 
				'<h1 class="crocoblock-workflows-page__title">%s</h1>',
				__( 'Available Interactive Tutorials', 'jet-engine' )
			);

			printf( 
				'<a href="%1$s">%2$s</a>',
				add_query_arg( [
					$this->invalidate_trigger() => 'refresh',
					'_nonce' => wp_create_nonce( 'crocoblock-workflows-ui' ),
				] ),
				__( 'Check for new tutorials', 'jet-engine' )
			);

		echo '</div>';

		printf( 
			'<p class="crocoblock-workflows-page__text">%s</p>',
			__( 'These interactive tutorials will help you complete the most common tasks related to Crocoblock plugins easily.', 'jet-engine' )
		);

		foreach ( $namespaces as $namespace => $label ) {

			$workflows = $this->workflows->storage()->get_workflows( $namespace );

			if ( empty( $workflows ) ) {
				continue;
			}

			printf( '<h3>%1$s</h3>', $label );

			echo '<div class="crocoblock-workflows-list">';

			foreach ( $workflows as $workflow ) {
				$this->workflow_template( $workflow );
			}

			echo '</div>';

		}

		echo '</div>';

		$this->assets();

	}

	public function assets() {

		if ( self::$assests_enqueued ) {
			return;
		}

		self::$assests_enqueued = true;

		wp_enqueue_script(
			'crocoblock-workflows',
			$this->workflows->url . '/assets/js/workflows.js',
			[ 'jquery' ],
			$this->workflows->version,
			true
		);

		wp_localize_script( 'crocoblock-workflows', 'CrocoblockWorkflowsData', [
			'stateNonce'        => $this->workflows->state()->nonce(),
			'dependenciesNonce' => $this->workflows->dependencies()->nonce(),
			'prefix'            => $this->workflows->prefix(),
			'activeWorkflow'    => $this->workflows->state()->get_prepared_active_workflow(),
		] );

		wp_enqueue_style( 
			'crocoblock-workflows',
			$this->workflows->url . '/assets/css/workflows.css',
			[],
			$this->workflows->version
		);
	}

	public function workflow_template( $workflow ) {

		$workflow = $this->workflows->dependencies()->add_checked_dependencies( $workflow );
		
		$est_time = [
			'min' => 0,
			'max' => 0,
		];

		foreach ( $workflow['steps'] as $step ) {
			$est_time['min'] += isset( $step['minTime'] ) ? $step['minTime'] : 1;
			$est_time['max'] += isset( $step['maxTime'] ) ? $step['maxTime'] : 1;
		}

		$button_label = __( 'Start Tutorial', 'jet-engine' );
		$is_active    = $this->workflows->state()->get_active_workflow( $workflow['id'] );

		if ( $is_active ) {
			$button_label = __( 'Resume Tutorial', 'jet-engine' );
			$workflow['step'] = $is_active['step'];
		}

		$encoded_workflow = htmlspecialchars( json_encode( $workflow ) );

		?>
		<div class="crocoblock-workflow-item">
			<div class="crocoblock-workflow-item__title"><?php echo $workflow['workflow']; ?></div>
			<div class="crocoblock-workflow-item__desc"><?php echo $workflow['description']; ?></div>
			<div class="crocoblock-workflow-item__meta">
				<svg xmlns="http://www.w3.org/2000/svg" height="20" viewBox="0 -960 960 960" width="20"><path d="M231.476-375.386q-43.551 0-74.128-30.486-30.577-30.486-30.577-74.037 0-43.552 30.487-74.129 30.486-30.576 74.037-30.576 43.552 0 74.128 30.486Q336-523.642 336-480.091q0 43.552-30.486 74.129-30.486 30.576-74.038 30.576Zm-.117-51.998q22.35 0 37.496-15.12 15.146-15.119 15.146-37.469t-15.119-37.496q-15.119-15.147-37.469-15.147t-37.497 15.12q-15.146 15.119-15.146 37.469t15.119 37.496q15.12 15.147 37.47 15.147Zm248.732 51.998q-43.552 0-74.129-30.486-30.576-30.486-30.576-74.037 0-43.552 30.486-74.129 30.486-30.576 74.037-30.576 43.552 0 74.129 30.486 30.576 30.486 30.576 74.037 0 43.552-30.486 74.129-30.486 30.576-74.037 30.576Zm-.118-51.998q22.35 0 37.496-15.12 15.147-15.119 15.147-37.469t-15.12-37.496q-15.119-15.147-37.469-15.147t-37.496 15.12q-15.147 15.119-15.147 37.469t15.12 37.496q15.119 15.147 37.469 15.147Zm248.732 51.998q-43.552 0-74.128-30.486Q624-436.358 624-479.909q0-43.552 30.486-74.129 30.486-30.576 74.038-30.576 43.551 0 74.128 30.486 30.577 30.486 30.577 74.037 0 43.552-30.487 74.129-30.486 30.576-74.037 30.576Z"/></svg>
				Steps: <?php echo count( $workflow['steps'] ); ?>
			</div>
			<div class="crocoblock-workflow-item__meta">
				<svg xmlns="http://www.w3.org/2000/svg" height="20" viewBox="0 -960 960 960" width="20"><path d="M367.693-826.001v-51.998h224.614v51.998H367.693Zm86.308 434.308h51.998v-224.614h-51.998v224.614ZM480-116.001q-66.154 0-123.419-24.777-57.266-24.776-100.146-67.657-42.881-42.881-67.657-100.146Q164.001-365.846 164.001-432t24.777-123.419q24.776-57.266 67.657-100.146 42.88-42.881 100.146-67.657 57.265-24.777 122.979-24.777 56.357 0 108.591 19.885 52.233 19.885 95.925 54.885l45.846-44.846 36.153 36.153-44.846 45.846q35 42.692 54.885 95.153 19.885 52.462 19.885 109.067 0 66.01-24.777 123.275-24.776 57.265-67.657 100.146-42.88 42.881-100.146 67.657Q546.154-116.001 480-116.001ZM480-168q110 0 187-77t77-187q0-110-77-187t-187-77q-110 0-187 77t-77 187q0 110 77 187t187 77Zm0-264Z"/></svg>
				Est. time: <?php echo implode( '-', $est_time ); ?> mins.</div>
				<div class="crocoblock-workflow-item__actions">
					<button type="button" class="crocoblock-workflow-item__btn" data-workflow="<?php echo $encoded_workflow; ?>"><?php echo $button_label; ?></button>
					<?php
						if ( ! empty( $workflow['fullTutorial'] ) ) {
							printf( '<a href="%s" class="crocoblock-workflow-item__link">Read full tutorial <svg xmlns="http://www.w3.org/2000/svg" height="20" viewBox="0 -960 960 960" width="20"><path d="M228.309-164.001q-27.008 0-45.658-18.65-18.65-18.65-18.65-45.658v-503.382q0-27.008 18.65-45.658 18.65-18.65 45.658-18.65h236.305V-744H228.309q-4.616 0-8.463 3.846-3.846 3.847-3.846 8.463v503.382q0 4.616 3.846 8.463 3.847 3.846 8.463 3.846h503.382q4.616 0 8.463-3.846 3.846-3.847 3.846-8.463v-236.305h51.999v236.305q0 27.008-18.65 45.658-18.65 18.65-45.658 18.65H228.309Zm159.46-186.615-37.153-37.153L706.847-744H576v-51.999h219.999V-576H744v-130.847L387.769-350.616Z"/></svg></a>', esc_url( $workflow['fullTutorial'] ) );
						}
					?>
				</div>
		</div>
		<?php
	}

}
