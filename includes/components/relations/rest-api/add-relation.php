<?php
/**
 * Add tax endpoint
 */

class Jet_Engine_CPT_Rest_Add_Relation extends Jet_Engine_Base_API_Endpoint {

	/**
	 * Returns route name
	 *
	 * @return string
	 */
	public function get_name() {
		return 'add-relation';
	}

	public function safe_get( $args = array(), $group = '', $key = '', $default = false ) {
		return isset( $args[ $group ][ $key ] ) ? $args[ $group ][ $key ] : $default;
	}

	/**
	 * API callback
	 *
	 * @return void
	 */
	public function callback( $request ) {

		$params = $request->get_params();

		jet_engine()->relations->data->set_request( array(
			'name'                => $this->safe_get( $params, 'general_settings', 'name' ),
			'post_type_1'         => $this->safe_get( $params, 'general_settings', 'post_type_1' ),
			'post_type_2'         => $this->safe_get( $params, 'general_settings', 'post_type_2' ),
			'type'                => $this->safe_get( $params, 'general_settings', 'type' ),
			'parent_relation'     => $this->safe_get( $params, 'general_settings', 'parent_relation' ),
			'post_type_1_control' => $this->safe_get( $params, 'advanced_settings', 'post_type_1_control' ),
			'post_type_2_control' => $this->safe_get( $params, 'advanced_settings', 'post_type_2_control' ),
		) );

		$item_id = jet_engine()->relations->data->create_item( false );

		return rest_ensure_response( array(
			'success' => ! empty( $item_id ),
			'item_id' => $item_id,
			'notices' => jet_engine()->relations->get_notices(),
		) );

	}

	/**
	 * Returns endpoint request method - GET/POST/PUT/DELTE
	 *
	 * @return string
	 */
	public function get_method() {
		return 'POST';
	}

	/**
	 * Check user access to current end-popint
	 *
	 * @return bool
	 */
	public function permission_callback( $request ) {
		return current_user_can( 'manage_options' );
	}

}
