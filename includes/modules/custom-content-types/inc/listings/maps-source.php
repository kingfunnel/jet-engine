<?php
namespace Jet_Engine\Modules\Custom_Content_Types\Listings;

use Jet_Engine\Modules\Custom_Content_Types\Module;

class CCT_Maps_Source extends \Jet_Engine\Modules\Maps_Listings\Source\Base {

	/**
	 * Returns source ID
	 *
	 * @return string
	 */
	public function get_id() {
		return Module::instance()->listings->source;
	}

	public function get_obj_by_id( $id ) {

		$listing = jet_engine()->listings->data->get_listing();
		$type    = false;

		if ( 'query' === $listing->get_settings( 'listing_source' ) ) {
			$query_id = $listing->get_settings( '_query_id' );
			$query    = \Jet_Engine\Query_Builder\Manager::instance()->get_query_by_id( $query_id );

			if ( $query ) {
				$type = ! empty( $query->query['content_type'] ) ? $query->query['content_type'] : false;
			}

		} else {
			$type = jet_engine()->listings->data->get_listing_post_type();
		}

		if ( ! $type ) {
			return null;
		}

		$content_type = Module::instance()->manager->get_content_types( $type );

		if ( ! $content_type ) {
			return null;
		}

		$flag = \OBJECT;
		$content_type->db->set_format_flag( $flag );

		return $content_type->db->get_item( $id );
	}

	public function get_field_value( $obj, $field ) {

		if ( is_object( $obj ) ) {
			$obj = get_object_vars( $obj );
		}

		if ( ! isset( $obj['cct_slug'] ) ) {
			return '';
		}

		if ( ! isset( $obj[ $field ] ) ) {
			return '';
		}

		return $obj[ $field ];
	}

	public function update_field_value( $obj, $field, $value ) {

		if ( ! isset( $obj->cct_slug ) || ! isset( $obj->_ID ) ) {
			return;
		}

		$content_type = Module::instance()->manager->get_content_types( $obj->cct_slug );

		if ( ! $content_type ) {
			return;
		}

		$coord_key = $this->lat_lng->meta_key;

		if ( ! $content_type->db->column_exists( $coord_key ) ) {
			$content_type->db->insert_table_columns( array( $coord_key => 'text' ) );
		}

		$content_type->db->update( array( $coord_key => $value ), array( '_ID' => $obj->_ID ) );
	}

	public function get_failure_key( $obj ) {

		if ( ! isset( $obj->cct_slug ) || ! isset( $obj->_ID ) ) {
			return '';
		}

		return sprintf( 'CCT(%1$s) #%2$s', $obj->cct_slug, $obj->_ID );
	}

	public function add_preload_hooks( $preload_fields ) {

		foreach ( $preload_fields as $field ) {

			$fields = explode( '+', $field );
			$fields = array_map( function ( $field_item ) {
				return str_replace( 'cct::', '', $field_item );
			}, $fields );

			$field_data = explode( '__', $fields[0] );

			$type = $field_data[0];

			$fields = array_map( function ( $field_item ) use ( $type ) {
				return str_replace( $type . '__', '', $field_item );
			}, $fields );

			add_action( 'jet-engine/custom-content-types/updated-item/' . $type, function ( $item, $prev_item, $handler ) use ( $fields ) {

				if ( empty( $item['_ID'] ) ) {
					return;
				}

				$cct_item = (object) $handler->get_factory()->db->get_item( $item['_ID'] );

				$this->lat_lng->set_current_source( $this->get_id() );
				$address = $this->lat_lng->get_address_from_fields_group( $cct_item, $fields );

				if ( ! $address ) {
					return;
				}

				$coord = $this->lat_lng->get( $cct_item, $address );

			}, 10, 3 );
		}
	}

	public function filtered_preload_fields( $field ) {
		return false !== strpos( $field, 'cct::' );
	}

}
