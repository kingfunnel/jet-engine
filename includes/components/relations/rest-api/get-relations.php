<?php
/**
 * Get all relations endpoint
 */

class Jet_Engine_CPT_Rest_Get_Relations extends Jet_Engine_Base_API_Endpoint {

	/**
	 * Returns route name
	 *
	 * @return string
	 */
	public function get_name() {
		return 'get-relations';
	}

	/**
	 * API callback
	 *
	 * @return void
	 */
	public function callback( $request ) {

		$relations = jet_engine()->relations->data->get_items();

		return rest_ensure_response( array(
			'success' => true,
			'data'    => $this->prepare_items( $relations ),
		) );

	}

	/**
	 * Prepare items to sent into editor
	 *
	 * @param  [type] $items [description]
	 * @return [type]        [description]
	 */
	public function prepare_items( $items ) {
		return array_map( function( $item ) {
			$item['hash'] = jet_engine()->relations->get_relation_hash( $item['post_type_1'], $item['post_type_2'] );
			return $item;
		}, $items );
	}

	/**
	 * Returns endpoint request method - GET/POST/PUT/DELTE
	 *
	 * @return string
	 */
	public function get_method() {
		return 'GET';
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