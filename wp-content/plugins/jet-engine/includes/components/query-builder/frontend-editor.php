<?php

namespace Jet_Engine\Query_Builder;

class Frontend_Editor {
	
	public function __construct() {
		add_action( 'admin_body_class', array( $this, 'tweak_body_classes' ) );
		$this->enqueue_assets();
	}

	public function is_editor() {
		if ( jet_engine()->bricks_views && jet_engine()->bricks_views->is_bricks_editor() ) {
			return true;
		}

		if ( jet_engine()->has_elementor() ) {
			$is_editor_mode = ! empty( $_GET['action'] ) && 'elementor' === $_GET['action'] && ! empty( $_GET['post'] );
			$is_editor_ajax = jet_engine()->elementor_views && jet_engine()->elementor_views->is_editor_ajax();

			if ( $is_editor_mode || $is_editor_ajax ) {
				return true;
			}
		}

		if ( jet_engine()->blocks_views ) {

			if ( defined( 'REST_REQUEST' ) && REST_REQUEST && ('edit' === $_GET['context'] ) ) {
				return true;
			}

			if ( ! function_exists( 'get_current_screen' ) ) {
				return false;
			}

			$current_screen = get_current_screen();

			if ( $current_screen && method_exists( $current_screen, 'is_block_editor' ) && $current_screen->is_block_editor() ) {
				return true;
			}
		}

		return false;
	}

	public function print_query_edit_modal() {
		?>
		
			<dialog class="jet-engine-query-edit-modal">
				<span class="jet-engine-query-edit-modal--close-button">&#10005;</span>
				<div class="jet-engine-query-spinner"></div>
				<iframe></iframe>
			</dialog>

		<?php
	}

	public function render_edit_buttons( $render_instance, $query_id = 0 ) {
		if ( $this->is_editor() ) {
			return;
		}

		if ( ! apply_filters(
				'jet-engine/query-builder/frontend-editor/user-has-access',
				current_user_can( 'manage_options' ),
				$render_instance,
				$query_id
			) ) {
			return;
		}

		if ( ! $query_id && is_object( $render_instance ) && method_exists( $render_instance, 'get_settings' ) ) {
			$settings   = $render_instance->get_settings();
			$listing_id = absint( $settings['lisitng_id'] ?? 0 );

			if ( $listing_id ) {
				$query_id = \Jet_Engine\Query_Builder\Manager::instance()->listings->get_query_id(
					$listing_id,
					$settings
				);
			}
		}

		$query_id = apply_filters( 'jet-engine/query-builder/frontend-editor/query-id', $query_id, $render_instance );

		$this->edit_buttons_template( $query_id );
	}

	public function edit_buttons_template( $query_id ) {
		if ( ! $query_id ) {
			return;
		}

		$query_link = admin_url( 'admin.php?page=jet-engine-query&query_action=edit&id=' . $query_id );

		?>

			<div class="jet-engine-frontend-query-editor-buttons">
				<span class="edit-button" data-query-link="<?php echo $query_link; ?>"><?php echo esc_html__( 'Edit query', 'jet-engine' ); ?></span>
			</div>
		
		<?php
	}

	public function tweak_body_classes( $classes ) {

		if ( empty( $_GET['mode'] ) || $_GET['mode'] !== 'fullscreen' ) {
			return $classes;
		}

		if ( is_array( $classes ) ) {
			$classes[] = 'jet-engine-query-builder-fullscreen';
		} else {
			$classes .= ' jet-engine-query-builder-fullscreen';
		}

		return $classes;
	}

	public function enqueue_assets() {

		if ( is_admin() || is_login() ) {
			return;
		}

		add_action( 'wp_footer', array( $this, 'print_query_edit_modal' ) );

		wp_enqueue_script(
			'jet-engine-frontend-query-edit',
			Manager::instance()->component_url( 'assets/js/frontend.js' ),
			array( 'jquery' ),
			jet_engine()->get_version(),
			true
		);

		wp_enqueue_style(
			'jet-engine-frontend-query-edit',
			Manager::instance()->component_url( 'assets/css/frontend.css' ),
			array(),
			jet_engine()->get_version()
		);
	}

}
