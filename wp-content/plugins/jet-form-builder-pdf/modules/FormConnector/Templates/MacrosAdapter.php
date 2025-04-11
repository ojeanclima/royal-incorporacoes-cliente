<?php

namespace JFB_PDF_Modules\FormConnector\Templates;

use Jet_Form_Builder\Classes\Resources\Media_Block_Value;
use Jet_Form_Builder\Classes\Resources\Uploaded_Collection;
use Jet_Form_Builder\Classes\Resources\Uploaded_File;
use Jet_Form_Builder\Classes\Tools;
use Jet_Form_Builder\Request\Request_Tools;
use JFB_Modules\Rich_Content;

class MacrosAdapter {

	const SUPPORTED_BLOCKS = array(
		'core/paragraph',
		'core/heading',
		'core/table',
		'core/html',
	);

	public function init_hooks() {
		add_filter(
			'render_block',
			array( $this, 'apply_macros' ),
			10,
			2
		);
		add_filter(
			'render_block_core/image',
			array( $this, 'apply_image' )
		);
	}

	public function apply_macros( string $content, array $parsed_block ): string {
		if (
			! in_array( $parsed_block['blockName'] ?? '', self::SUPPORTED_BLOCKS, true ) ||
			! $content
		) {
			return $content;
		}

		return Rich_Content\Module::rich( $content );
	}

	public function apply_image( string $content ): string {
		$html = new \WP_HTML_Tag_Processor( $content );
		$html->next_tag( 'img' );
		$image_url = $html->get_attribute( 'src' );

		if ( ! $image_url ) {
			return $content;
		}

		$url_query = wp_parse_url( $image_url, PHP_URL_QUERY );

		$output = array();

		if ( $url_query ) {
			foreach ( explode( '&', $url_query ) as $pair ) {
				$parts = explode( '=', $pair, 2 );
				$key   = urldecode( $parts[0] );
				$value = isset( $parts[1] ) ? urldecode( $parts[1] ) : null;
				$output[ $key ] = $value;
			}
		}

		$media_field = sanitize_key( Tools::to_string( $output['jfb_media'] ?? '' ) );

		if ( ! $media_field ) {
			return $content;
		}

		$file = $this->get_file( $media_field );

		if ( ! $file ) {
			return $content;
		}

		$url = $this->get_file_url( $file );

		if ( ! $url ) {
			return $content;
		}

		$html->set_attribute( 'src', $url );

		return $html->get_updated_html();
	}

	private function get_file_url( Media_Block_Value $file ): string {
		$urls = explode( ',', $file->get_attachment_url() );

		if ( ! empty( $urls[0] ) ) {
			return $urls[0];
		}

		$ids = explode( ',', $file->get_attachment_id() );

		if ( empty( $ids[0] ) ) {
			return '';
		}

		return get_the_guid( (int) $ids[0] );
	}

	/**
	 * Move this method inside JetFormBuilder
	 *
	 * @param string $field_name
	 *
	 * @return false|Media_Block_Value
	 */
	private function get_file( string $field_name ) {
		$file = jet_fb_context()->get_file( $field_name );

		if ( false !== $file ) {
			return $file;
		}

		$file_data = jet_fb_context()->get_value( $field_name );

		// parse value in json format (both)
		if ( is_string( $file_data ) && ! is_numeric( $file_data ) ) {
			$decoded = Tools::decode_json( $file_data );

			if ( ! is_null( $decoded ) ) {
				$file_data = $decoded;
			}
		}

		if ( is_int( $file_data ) ) {
			$file_data = array( $file_data );
		}

		if ( is_string( $file_data ) ) {
			$file_data = explode( ',', $file_data );
		}

		if ( ! is_array( $file_data ) ) {
			return false;
		}

		if ( empty( $file_data[0] ) ) {
			return $this->create_uploaded_file( $file_data );
		}

		$collection = array();

		foreach ( $file_data as $item ) {
			$collection[] = $this->create_uploaded_file( $item );
		}

		return new Uploaded_Collection( $collection );
	}

	/**
	 * @param string|int|array $file_data
	 *
	 * @return Uploaded_File|false
	 */
	public static function create_uploaded_file( $file_data ) {
		if ( is_numeric( $file_data ) ) {
			$uploaded = new Uploaded_File();

			return $uploaded->set_attachment_id( $file_data );
		}

		if ( ! empty( $file_data['id'] ) && is_numeric( $file_data['id'] ) ) {
			$uploaded = new Uploaded_File();
			$uploaded->set_attachment_id( $file_data['id'] );
			$uploaded->set_url( $file_data['url'] );

			return $uploaded;
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions
		if ( ! is_string( $file_data ) || false === parse_url( $file_data ) ) {
			return false;
		}

		$attachment_id = attachment_url_to_postid( $file_data );

		return $attachment_id
			? self::create_uploaded_file(
				array(
					'id'  => $attachment_id,
					'url' => $file_data,
				)
			)
			: false;
	}

}
