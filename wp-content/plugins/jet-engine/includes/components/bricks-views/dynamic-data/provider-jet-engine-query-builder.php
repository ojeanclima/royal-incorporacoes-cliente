<?php
namespace Jet_Engine\Bricks_Views\Dynamic_Data;

use Bricks\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Provider_Jet_Engine_Query_Builder extends \Bricks\Integrations\Dynamic_Data\Providers\Base {
	public function register_tags() {
		$fields = [
			[
				'slug' => 'je_query_results_count',
				'title' => 'Query results count - add Query ID after :',
			],
			[
				'slug' => 'je_query_results_count_filter',
				'title' => 'Query results count (Filter) - add Query ID after :',
			],
		];

		foreach ( $fields as $field ) {
			$this->register_tag( $field );
		}
	}

	public function register_tag( $field ) {
		$name = $field['slug'];
		$tag = [
			'name'     => '{' . $name . '}',
			'label'    => $field['title'],
			'group'    => 'Jet Engine Dynamic Data',
			'field'    => 'text',
			'provider' => $this->name
		];

		$this->tags[ $name ] = $tag;
	}

	public function get_tag_value( $tag, $post, $args, $context ) {
		if ( empty( $args ) ) {
			if ( bricks_is_frontend() ) {
				return '';
			}

			return Helpers::get_element_placeholder( [
				'title'       => esc_html__( 'Use Query builder item ID and pass it as an argument for the dynamic token.', 'jet-engine' ),
				'description' => esc_html__( 'Go to: JetEngine > Query builder.', 'jet-engine' ),
				'icon-class'  => 'ti-alert',
			], 'info' );
		}

		$post    = jet_engine()->listings->data->get_current_object();
		$post_id = $post->ID ?? $post->_ID ?? '';

		$args[0] = 'query_id_'  . $args[0];

		// STEP: Check for filter args
		$filters = $this->get_filters_from_args( $args );

		// STEP: Get the value
		$value = '';

		$render = isset( $this->tags[ $tag ]['render'] ) ? $this->tags[ $tag ]['render'] : $tag;

		$query_id = $filters['meta_key'] ?? false;
		$query_id = str_replace( 'query_id_', '', $query_id );
		$query_id = sanitize_text_field( $query_id );

		$query = \Jet_Engine\Query_Builder\Manager::instance()->get_query_by_id(
			str_replace( 'query_id_', '', $query_id )
		);

		if ( ! $query ) {
			return '';
		}

		switch ( $render ) {
			case 'je_query_results_count':
			case 'je_query_results_count_filter':
				$value = $query->get_items_total_count();

				/**
				 * {query_results_count_filter} - wrap the value with a span for AJAX update when using query filter feature
				 * element ID is a must so we know which count to update after AJAX
				 *
				 * @since 1.9.6
				 */
				if ( $tag === 'je_query_results_count_filter' && $query_id ) {
					$filters['skip_sanitize'] = true;
					$value                    = sprintf(
						'<span data-je-qr-count="%1$s">%2$s</span>',
						esc_attr( $query_id ),
						$value
					);
				}
		}

		// STEP: Apply context (text, link, image, media)
		$value = $this->format_value_for_context( $value, $tag, $post_id, $filters, $context );

		return $value;
	}
}
