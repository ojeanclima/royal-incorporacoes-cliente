<?php
namespace Jet_Engine\Website_Builder\Handlers_Skins;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

abstract class Base {

	/**
	 * Get skin ID
	 *
	 * @return string
	 */
	abstract public function get_id();

	/**
	 * Get skin title
	 *
	 * @return string
	 */
	abstract public function get_title();

	/**
	 * Render single results row
	 *
	 * @param  array  $row Results data row.
	 * @return string
	 */
	abstract public function skin_data_row( $row = [] );

	/**
	 * Public function get skin content with information about created data
	 *
	 * @param  array  $data [description]
	 * @return [type]       [description]
	 */
	public function get_skin_content( $data = [] ) {

		$result = '';

		if ( empty( $data ) ) {
			return $result;
		}

		$result .= $this->open_skin_wrapper();
		$result .= $this->title_html();
		$result .= $this->open_data_wrapper();

		foreach ( $data as $row ) {
			$result .= sprintf(
				'<div class="jet-ai-builder-res__dl-i">%1$s</div>',
				$this->skin_data_row( $row )
			);
		}

		$result .= $this->close_data_wrapper();

		$tuts = $this->get_tuts();

		if ( ! empty( $tuts ) ) {

			$result .= sprintf(
				'<div class="jet-ai-builder-tuts__title">%s</div>',
				esc_html__( 'Related Documentation', 'jet-engine' )
			);

			$result .= '<div class="jet-ai-builder-tuts__list">';
			foreach ( $tuts as $tut ) {
				$result .= sprintf(
					'<div class="jet-ai-builder-tuts__item"><a href="%1$s" target="_blank" title="%2$s">%2$s</a></div>',
					$tut['url'],
					$tut['label']
				);
			}
			$result .= '</div>';

		}

		$result .= $this->close_skin_wrapper();

		return $result;
	}

	/**
	 * Html string with skin title
	 *
	 * @return [type] [description]
	 */
	public function title_html() {
		return sprintf( '<h4 class="jet-ai-builder-res__title cx-vui-subtitle">%s</h4>', $this->get_title() );
	}

	/**
	 * Open HTML wrapper with skin results
	 *
	 * @return [type] [description]
	 */
	public function open_skin_wrapper() {
		return '<div class="jet-ai-builder-res__skin">';
	}

	/**
	 * Close HTML wrapper with skin results
	 *
	 * @return [type] [description]
	 */
	public function close_skin_wrapper() {
		return '</div>';
	}

	/**
	 * Open HTML wrapper with skin results
	 *
	 * @return [type] [description]
	 */
	public function open_data_wrapper() {
		return '<div class="jet-ai-builder-res__dl">';
	}

	/**
	 * Close HTML wrapper with skin results
	 *
	 * @return [type] [description]
	 */
	public function close_data_wrapper() {
		return '</div>';
	}

	/**
	 * Renders given content with row title wrapper
	 *
	 * @param  string $content Content to print.
	 * @return string
	 */
	public function data_row_title( $content = '', $action = '' ) {

		$edit_icon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><g><path d="M13.89 3.39l2.71 2.72c.46.46.42 1.24.03 1.64l-8.01 8.02-5.56 1.16 1.16-5.58s7.6-7.63 7.99-8.03c.39-.39 1.22-.39 1.68.07zm-2.73 2.79l-5.59 5.61 1.11 1.11 5.54-5.65zm-2.97 8.23l5.58-5.6-1.07-1.08-5.59 5.6z"/></g></svg>';

		$action = str_replace( '">', '">' . $edit_icon, $action );

		return sprintf( '<div class="jet-ai-builder-res__dl-i-title">%1$s %2$s</div>', $content, $action );
	}

	/**
	 * Renders given content with row content wrapper
	 *
	 * @param  string $content Content to print.
	 * @return string
	 */
	public function data_row_content( $content = '' ) {
		return sprintf( '<div class="jet-ai-builder-res__dl-i-content">%s</div>', $content );
	}

	/**
	 * Renders given content with row item wrapper
	 *
	 * @param  string $content Content to print.
	 * @return string
	 */
	public function data_row_item( $content = '' ) {
		return sprintf( '<div class="jet-ai-builder-res__dl-i-item">%s</div>', $content );
	}

	/**
	 * Get basic tutorials related to the entity
	 *
	 * @return array
	 */
	public function get_tuts() {
		return [];
	}
}
