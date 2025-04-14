<?php
namespace Jet_Engine\Website_Builder\Handlers_Skins;

use \Jet_Engine\Website_Builder\Handler;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Tags extends Base {

	/**
	 * Get skin ID
	 *
	 * @return string
	 */
	public function get_id() {
		return Handler::TAGS_ID;
	}

	/**
	 * Get skin title
	 *
	 * @return string
	 */
	public function get_title() {
		return esc_html__( 'What You May Need Next', 'jet-engine' );
	}

	/**
	 * Render single results row
	 *
	 * @param  array  $row Results data row.
	 * @return string
	 */
	public function skin_data_row( $row = [] ) {
		return '';
	}

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

		$result .= '<div class="jet-ai-builder-tuts__list">';
		foreach ( $data as $tag ) {

			$tag_tuts = $this->get_tuts_by_tag( $tag['tag'] );

			foreach ( $tag_tuts as $tut ) {
				$result .= sprintf(
					'<div class="jet-ai-builder-tuts__item"><a href="%1$s" target="_blank" title="%2$s">%2$s</a></div>',
					$tut['url'],
					$tut['label']
				);
			}
		}
		$result .= '</div>';

		$result .= $this->close_skin_wrapper();

		return $result;
	}

	public function get_tuts_by_tag( $tag = '' ) {

		$tuts_base = [
			'profile' => [
				[
					'url'   => 'https://crocoblock.com/knowledge-base/jetengine/creating-user-account-page/',
					'label' => 'Creating User Account Page',
				],
				[
					'url'   => 'https://crocoblock.com/knowledge-base/jetengine/how-to-set-up-public-single-user-page/',
					'label' => 'Set Up a Public Single User Page',
				],
				[
					'url'   => 'https://crocoblock.com/knowledge-base/jetengine/jetengine-how-to-update-wordpress-users-via-front-end-form-submission-option/',
					'label' => 'Update Users’ Accounts via Front End Form',
				],
			],
			'front-end submission' => [
				[
					'url'   => 'https://jetformbuilder.com/features/wordpress-front-end-post-submission-form/',
					'label' => 'Front-End Post Submission Form',
				],
				[
					'url'   => 'https://crocoblock.com/knowledge-base/jetengine/how-to-insert-update-cct-via-form/',
					'label' => 'Insert and Edit CCT via Front-End Form',
				],
			],
			'front-end relations' => [
				[
					'url'   => 'https://jetformbuilder.com/features/jetformbuilder-connecting-wordpress-related-items-with-post-submit-actions/',
					'label' => 'Connect Related Items via Front-End Form',
				],
			],
			'archive-template' => [
				[
					'url'   => 'https://crocoblock.com/knowledge-base/jetthemecore/how-to-create-an-archive-page-template-to-display-the-custom-post-type-archive/',
					'label' => 'Create Template to Display Custom Post Type Archive',
				],
			],
			'maps listings' => [
				[
					'url'   => 'https://crocoblock.com/knowledge-base/features/map-listing-overview/',
					'label' => 'Map Listing Overview',
				],
				[
					'url'   => 'https://crocoblock.com/knowledge-base/jetengine/how-to-filter-listings-based-on-geolocation/',
					'label' => 'Filter Listings Based on Geolocation',
				],
			],
			'calendar listings' => [
				[
					'url'   => 'https://crocoblock.com/knowledge-base/features/dynamic-listing-calendar-widget-overview/',
					'label' => 'Dynamic Listing Calendar Widget Overview',
				],
				[
					'url'   => 'https://crocoblock.com/knowledge-base/jetengine/how-to-display-recurring-events-in-the-dynamic-calendar/',
					'label' => 'Display Recurring Events in the Dynamic Calendar',
				],
			],
			'data tables' => [
				[
					'url'   => 'https://crocoblock.com/knowledge-base/features/jetengine-tables-builder-overview/',
					'label' => 'Dynamic Tables Builder Overview',
				],
			],
			'favorites, bookmarks, wishlists' => [
				[
					'url'   => 'https://crocoblock.com/knowledge-base/jetengine/how-to-add-likes-and-display-likes-counter-in-posts/',
					'label' => 'Add Likes and Display Likes Counter',
				],
				[
					'url'   => 'https://crocoblock.com/knowledge-base/jetengine/jetengine-data-stores-module-overview/',
					'label' => 'Create Favorites Page Using Data Stores',
				],
			],
		];

		return isset( $tuts_base[ $tag ] ) ? $tuts_base[ $tag ] : [];
	}
}
