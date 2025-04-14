<?php
namespace Jet_Engine\Modules\Rest_API_Listings\Auth_Types;

class Custom_Header extends Base {

	/**
	 * Return auth type ID
	 *
	 * @return [type] [description]
	 */
	public function get_id() {
		return 'custom-header';
	}

	/**
	 * Return auth type name
	 *
	 * @return [type] [description]
	 */
	public function get_name() {
		return __( 'Custom Header', 'jet-engine' );
	}

	/**
	 * Initialize authorization
	 *
	 * @return void
	 */
	public function init() {
		add_filter( 'jet-engine/rest-api-listings/request/args', array( $this, 'set_header' ), 10, 2 );
		add_filter( 'jet-engine/rest-api-listings/data/item-for-register', array( $this, 'modify_endpoint_args' ) );
	}

	public function set_header( $args, $request ) {

		$endpoint = $request->get_endpoint();

		if ( ! $this->is_current_type_endpoint( $endpoint ) ) {
			return $args;
		}

		$custom_headers = array();

		if ( ! empty( $endpoint['custom_headers'] ) ) {
			$custom_headers = $endpoint['custom_headers'];
		} elseif ( ! empty( $endpoint['custom_header_name'] ) && ! empty( $endpoint['custom_header_value'] ) ) {
			$custom_headers = array(
				array(
					'custom_header_name'  => $endpoint['custom_header_name'],
					'custom_header_value' => $endpoint['custom_header_value'],
				),
			);
		}

		if ( empty( $custom_headers) ) {
			return $args;
		}

		if ( ! isset( $args['headers'] ) ) {
			$args['headers'] = array();
		}

		foreach ( $custom_headers as $custom_header ) {
			$header = $custom_header['custom_header_name'];
			$value  = $custom_header['custom_header_value'];

			$args['headers'][ $header ] = $value;
		}

		return $args;

	}

	public function modify_endpoint_args( $endpoint ) {

		if ( empty( $endpoint['custom_headers'] )
			 && ! empty( $endpoint['custom_header_name'] )
			 && ! empty( $endpoint['custom_header_value'] )
		) {
			$endpoint['custom_headers'] = array(
				array(
					'custom_header_name'  => $endpoint['custom_header_name'],
					'custom_header_value' => $endpoint['custom_header_value'],
				),
			);
		}

		return $endpoint;
	}

	/**
	 * Initialize authorization
	 *
	 * @return [type] [description]
	 */
	public function register_controls() {
		?>
		<cx-vui-component-wrapper
			:wrapper-css="[ 'fullwidth-control' ]"
			:conditions="[
				{
					input: settings.authorization,
					compare: 'equal',
					value: true,
				},
				{
					input: settings.auth_type,
					compare: 'equal',
					value: 'custom-header',
				}
			]"
		>
			<div class="cx-vui-inner-panel">
				<cx-vui-repeater
					button-label="<?php _e( 'New Header', 'jet-engine' ); ?>"
					button-style="accent"
					button-size="mini"
					v-model="settings.custom_headers"
					@add-new-item="addNewRepeaterField( $event, 'custom_headers', { 'custom_header_name': '', 'custom_header_value': '' } )"
				>
					<cx-vui-repeater-item
						v-for="( header, index ) in settings.custom_headers"
						:title="settings.custom_headers[ index ].custom_header_name"
						:subtitle="settings.custom_headers[ index ].custom_header_value"
						:collapsed="isCollapsed( header )"
						:index="index"
						@clone-item="cloneRepeaterField( $event, 'custom_headers' )"
						@delete-item="deleteRepeaterField( $event, 'custom_headers' )"
						:key="header._id"
					>
						<cx-vui-input
							label="<?php _e( 'Header name', 'jet-engine' ); ?>"
							description="<?php _e( 'Set authorization header name. Could be found in your API docs', 'jet-engine' ); ?>"
							:wrapper-css="[ 'equalwidth' ]"
							size="fullwidth"
							:value="settings.custom_headers[ index ].custom_header_name"
							@input="setRepeaterFieldProp( 'custom_headers', index, 'custom_header_name', $event )"
						></cx-vui-input>
						<cx-vui-input
							label="<?php _e( 'Header value', 'jet-engine' ); ?>"
							description="<?php _e( 'Set authorization header value. Could be found in your API docs or you user profile related to this API', 'jet-engine' ); ?>"
							:wrapper-css="[ 'equalwidth' ]"
							size="fullwidth"
							:value="settings.custom_headers[ index ].custom_header_value"
							@input="setRepeaterFieldProp( 'custom_headers', index, 'custom_header_value', $event )"
						></cx-vui-input>
					</cx-vui-repeater-item>
				</cx-vui-repeater>
			</div>
		</cx-vui-component-wrapper>
		<?php
	}

	/**
	 * Register form-related controls
	 *
	 * @return [type] [description]
	 */
	public function register_form_controls() {
		?>
		<div class="jet-form-editor__row" v-if="result.authorization && 'custom-header' === result.auth_type">
			<div class="jet-form-editor__row-label"><?php
				_e( 'Header name:', 'jet-engine' );
			?></div>
			<div class="jet-form-editor__row-control">
				<input type="text" @input="setField( $event, 'custom_header_name' )" :value="result.custom_header_name">
			</div>
			&nbsp;&nbsp;&nbsp;&nbsp;<div class="jet-form-editor__row-notice"><?php _e( 'Set authorization header name. Could be found in your API docs', 'jet-engine' ); ?></div>
		</div>
		<div class="jet-form-editor__row" v-if="result.authorization && 'custom-header' === result.auth_type">
			<div class="jet-form-editor__row-label"><?php
				_e( 'Header name:', 'jet-engine' );
			?></div>
			<div class="jet-form-editor__row-control">
				<input type="text" @input="setField( $event, 'custom_header_value' )" :value="result.custom_header_value">
			</div>
			&nbsp;&nbsp;&nbsp;&nbsp;<div class="jet-form-editor__row-notice"><?php _e( 'Set authorization header value. Could be found in your API docs or you user profile related to this API', 'jet-engine' ); ?></div>
		</div>
		<?php
	}

	/**
	 * Initialize authorization
	 *
	 * @return [type] [description]
	 */
	public function register_args( $args = array() ) {

		$args['custom_headers'] = array(
			'type'    => 'regular',
			'default' => array(),
		);

		// Leave legacy arguments for backwards compatibility( Rollback Version ).
		$args['custom_header_name'] = array(
			'type'    => 'regular',
			'default' => '',
		);

		$args['custom_header_value'] = array(
			'type'    => 'regular',
			'default' => '',
		);

		return $args;

	}

}
