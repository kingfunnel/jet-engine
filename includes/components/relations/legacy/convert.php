<?php
namespace Jet_Engine\Relations\Legacy;

use Jet_Engine\Relations\Relation;

class Convert {

	public function __construct() {
		add_action( 'wp_ajax_jet_engine_relations_convert', array( $this, 'convert_callback' ) );
	}

	/**
	 * AJAX callback to convert relation
	 *
	 * @return [type] [description]
	 */
	public function convert_callback() {

		if ( empty( $_REQUEST['_nonce'] ) && ! wp_verify_nonce( $_REQUEST['_nonce'], 'jet-engine-relations' ) ) {
			wp_send_json_error( 'Link is expired. Please reload page and try again' );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Access denied' );
		}

		$rel_id = isset( $_REQUEST['relID'] ) ? absint( $_REQUEST['relID'] ) : false;

		if ( ! $rel_id ) {
			wp_send_json_error( 'Relation ID was not found in the request' );
		}

		$this->convert_relation( $rel_id );

	}

	/**
	 * Convert relation by ID
	 *
	 * @param  [type] $rel_id [description]
	 * @return [type]         [description]
	 */
	public function convert_relation( $rel_id ) {

		$relation = jet_engine()->relations->data->get_item_for_edit( $rel_id );

		if ( ! $relation ) {
			return;
		}

		$args = array_merge( $relation['args'], array( 'id' => $rel_id, 'is_legacy' => false ) );
		$this->convert_legacy_relation_items( $args );

		if ( false === strpos( $args['parent_object'], '::' ) ) {
			$args['parent_object'] = jet_engine()->relations->types_helper->type_name_by_parts( 'posts', $args['parent_object'] );
		}

		if ( false === strpos( $args['child_object'], '::' ) ) {
			$args['child_object']  = jet_engine()->relations->types_helper->type_name_by_parts( 'posts', $args['child_object'] );
		}

		if ( ! empty( $args['parent_rel'] ) ) {
			$args['parent_relation'] = $args['parent_rel'];
		}

		jet_engine()->relations->data->set_request( $args );
		$updated = jet_engine()->relations->data->edit_item( false );

		wp_send_json_success();

	}

	/**
	 * Convert legacy realtion items
	 *
	 * @param  [type] $args [description]
	 * @return [type]       [description]
	 */
	public function convert_legacy_relation_items( $args ) {

		global $wpdb;

		$parent_object  = $args['parent_object'];
		$child_object   = $args['child_object'];
		$posts_table    = $wpdb->posts;
		$postmeta_table = $wpdb->postmeta;

		$parent_posts = $wpdb->get_results( "SELECT ID FROM $posts_table WHERE post_status = 'publish' AND post_type = '$parent_object'", OBJECT_K );
		$hash         = jet_engine()->relations->legacy->get_relation_hash( $parent_object, $child_object );

		if ( empty( $parent_posts ) ) {
			return;
		}

		$parent_posts = implode( ', ', array_keys( $parent_posts ) );
		$meta         = $wpdb->get_results( "SELECT post_id AS parent, meta_value AS child FROM $postmeta_table WHERE meta_key = '$hash' AND post_id IN ( $parent_posts )" );

		if ( empty( $meta ) ) {
			return;
		}

		$relation = new Relation( $args['id'], $args, true );

		foreach ( $meta as $row ) {
			$relation->update( $row->parent, $row->child );
		}

	}

}
