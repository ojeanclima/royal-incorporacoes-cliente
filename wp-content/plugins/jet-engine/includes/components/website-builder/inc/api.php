<?php
/**
 * Timber view class
 */
namespace Jet_Engine\Website_Builder;


// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class API {

	protected $prompts = [];

	/**
	 * Setup promprts for the API
	 *
	 * @param array $prompts [description]
	 */
	public function set_prompts( $prompts = [] ) {
		$this->prompts = $prompts;
		return $this;
	}

	public function get_model() {

		$prompt_string = sprintf(
			'I need to create such website: %1$s. I am planning to add the next functionality to this website: %2$s',
			esc_attr( $this->prompts['topic'] ),
			esc_attr( $this->prompts['functionality'] )
		);

		/**
		 * Uncomment to make test requests without actual API call.
		 * $this->test_model() - plain model
		 * $this->test_model2() - Woo model
		 */
		//wp_send_json_success( $this->test_model() );
		$response = jet_engine()->ai->remote_get_completion( urlencode( $prompt_string ), 'website' );

		if ( $response && isset( $response['completion'] ) ) {

			$response['completion'] = ltrim( $response['completion'], '```json' );
			$response['completion'] = rtrim( $response['completion'], '```' );
			$response['completion'] = json_decode( $response['completion'], true );

			wp_send_json_success( $response );
		} else {
			wp_send_json_error( 'Empty response' );
		}

	}

	public function test_model2() {
		return [ 'completion' => json_decode( '{
	"postTypes": {
		"product": {
			"title": "Product",
			"withFront": true,
			"supports": ["title", "editor", "thumbnail"],
			"taxonomies": [
				{
					"name": "product_category",
					"title": "Product Category",
					"sampleData": ["T-Shirts", "Jeans", "Jackets", "Dresses", "Shoes"],
					"filterType": "select"
				},
				{
					"name": "product_tag",
					"title": "Product Tag",
					"sampleData": ["Summer", "Winter", "Casual", "Formal", "Sports"],
					"filterType": "select"
				}
			],
			"metaFields": [
				{
					"name": "price",
					"title": "Price",
					"type": "text",
					"filterType": "range"
				},
				{
					"name": "size",
					"title": "Size",
					"type": "select",
					"options": ["XS", "S", "M", "L", "XL"],
					"filterType": "select"
				},
				{
					"name": "color",
					"title": "Color",
					"type": "select",
					"options": ["Red", "Blue", "Green", "Black", "White"],
					"filterType": "select"
				},
				{
					"name": "material",
					"title": "Material",
					"type": "select",
					"options": ["cotton", "wool", "leather", "denim", "polyester"],
					"filterType": "select"
				}
			]
		}
	},
	"tags": [
		"archive-template",
		"front-end submission"
	]
}' ), 'limit' => 150, 'usage' => 8 ];
	}

	public function test_model() {
		return [ 'completion' => json_decode( '{
	"postTypes": {
		"destinations": {
			"title": "Destinations",
			"withFront": true,
			"supports": [
				"title",
				"editor",
				"thumbnail"
			],
			"metaFields": [],
			"taxonomies": [
				{
					"name": "destination_category",
					"title": "Destination Category",
					"sampleData": [
						"europe",
						"asia",
						"america",
						"africa",
						"oceania"
					]
				},
				{
					"name": "destination_type",
					"title": "Destination Type",
					"sampleData": [
						"europe",
						"asia",
						"america",
						"africa",
						"oceania"
					]
				}
			]
		},
		"tours": {
			"title": "Tours",
			"withFront": true,
			"supports": [
				"title",
				"editor",
				"thumbnail"
			],
			"metaFields": [
				{
					"name": "price",
					"title": "Price",
					"type": "text",
					"filterType": "range"
				},
				{
					"name": "type",
					"title": "Type",
					"type": "text",
					"filterType": "checkbox"
				},
				{
					"name": "size",
					"title": "Size",
					"type": "checkbox",
					"options": ["XS", "S", "M", "L", "XL"],
					"filterType": "checkbox"
				},
				{
					"name": "duration",
					"title": "Duration",
					"type": "text",
					"filterType": "range"
				},
				{
					"name": "tour_date",
					"title": "Date",
					"type": "date",
					"filterType": "date"
				},
				{
					"name": "destination",
					"title": "Destination",
					"type": "relation",
					"relatedPostType": "destinations",
					"filterType": "select"
				}
			],
			"taxonomies": []
		},
		"reviews": {
			"title": "Reviews",
			"withFront": false,
			"supports": [
				"title",
				"editor"
			],
			"metaFields": [
				{
					"name": "tour",
					"title": "Tour",
					"type": "relation",
					"relatedPostType": "tours"
				},
				{
					"name": "rating",
					"title": "Rating",
					"type": "select",
					"options": [
						1,
						2,
						3,
						4,
						5
					]
				},
				{
					"name": "user",
					"title": "User",
					"type": "relation",
					"relatedPostType": "user",
					"filterType": "select"
				}
			],
			"taxonomies": []
		}
	},
	"relations": [
		{
			"type": "one_to_many",
			"from": "destinations",
			"to": "tours"
		},
		{
			"type": "one_to_many",
			"from": "tours",
			"to": "reviews"
		},
		{
			"type": "one_to_many",
			"from": "user",
			"to": "reviews"
		},
		{
			"type": "one_to_many",
			"from": "user",
			"to": "destination_category"
		}
	],
	"tags": [
		"archive-template",
		"front-end submission"
	]
}' ), 'limit' => 150, 'usage' => 8 ];
	}

}
