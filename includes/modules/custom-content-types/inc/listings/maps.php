<?php
namespace Jet_Engine\Modules\Custom_Content_Types\Listings;

use Jet_Engine\Modules\Custom_Content_Types\Module;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Maps {

	public $coord_key = null;
	public $source    = null;

	public function __construct( $source ) {

		if ( ! jet_engine()->modules->get_module( 'maps-listings' )->instance ) {
			return;
		}

		$this->coord_key = jet_engine()->modules->get_module( 'maps-listings' )->instance->lat_lng->meta_key;
		$this->source    = $source;

		add_filter(
			'jet-engine/maps-listing/source',
			array( $this, 'set_cct_source' ),
			10, 2
		);

		add_filter(
			'jet-engine/maps-listing/get-address-from-field',
			array( $this, 'get_address_from_cct_field' ),
			10, 3
		);

		add_filter(
			'jet-engine/maps-listing/rest/object/' . $this->source,
			array( $this, 'get_cct_item' ),
			10, 2
		);

		add_action(
			'jet-engine/maps-listings/update-address-coord-field',
			array( $this, 'update_address_coord_field' ),
			10, 3
		);

		add_filter(
			'jet-engine/maps-listing/settings/fields',
			array( $this, 'add_cct_fields' )
		);

		add_action(
			'jet-engine/maps-listing/hook-preload/cct',
			array( $this, 'add_preload_hooks' )
		);

		add_filter(
			'jet-engine/custom-content-types/db/exclude-fields',
			array( $this, 'exclude_coord_field' )
		);

		add_filter(
			'jet-engine/maps-listing/failure-message-key',
			array( $this, 'add_failure_key' ),
			10, 2
		);

		add_filter(
			'jet-engine/maps-listing/get-marker-types',
			array( $this, 'add_cct_marker_image_type' )
		);

		add_filter(
			'jet-engine/maps-listing/get-marker-label-types',
			array( $this, 'add_cct_marker_label_type' )
		);

		add_action(
			'jet-engine/maps-listing/widget/custom-marker-label-controls',
			array( $this, 'add_marker_cct_field_control' )
		);

		add_filter(
			'jet-engine/maps-listing/custom-marker/dynamic_image_cct',
			array( $this, 'get_marker_from_cct_field' ),
			10, 3
		);

		add_filter(
			'jet-engine/maps-listing/marker-label/cct_field',
			array( $this, 'get_marker_from_cct_field' ),
			10, 3
		);

		add_filter(
			'jet-engine/blocks-views/maps-listing/attributes',
			array( $this, 'add_marker_cct_field_attr' )
		);

	}

	public function set_cct_source( $source, $obj ) {

		if ( ! isset( $obj->cct_slug ) ) {
			return $source;
		}

		return $this->source;
	}

	public function get_address_from_cct_field( $result, $obj, $field ) {

		if ( ! isset( $obj->cct_slug ) ) {
			return $result;
		}

		if ( ! isset( $obj->$field ) ) {
			return '';
		}

		return $obj->$field;
	}

	public function get_cct_item( $result, $obj_id ) {

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
			return $result;
		}

		$content_type = Module::instance()->manager->get_content_types( $type );

		if ( ! $content_type ) {
			return $result;
		}

		$flag = \OBJECT;
		$content_type->db->set_format_flag( $flag );

		return $content_type->db->get_item( $obj_id );
	}

	public function update_address_coord_field( $obj, $value, $lat_lng ) {

		if ( ! isset( $obj->cct_slug ) || ! isset( $obj->_ID ) ) {
			return;
		}

		$content_type = Module::instance()->manager->get_content_types( $obj->cct_slug );

		if ( ! $content_type ) {
			return;
		}

		$coord_key = $lat_lng->meta_key;

		if ( ! $content_type->db->column_exists( $coord_key ) ) {
			$content_type->db->insert_table_columns( array( $coord_key => 'text' ) );
		}

		$content_type->db->update( array( $coord_key => $value ), array( '_ID' => $obj->_ID ) );
	}

	public function add_cct_fields( $fields ) {

		$cct_groups = array();

		foreach ( Module::instance()->manager->get_content_types() as $type => $instance ) {

			$cct_fields = $instance->get_fields_list( 'text' );
			$prefixed_fields = array();

			if ( empty( $cct_fields ) ) {
				continue;
			}

			foreach ( $cct_fields as $key => $label ) {

				if ( 'cct_status' === $key ) {
					continue;
				}

				$prefixed_fields[] = array(
					'value' => 'cct::' . $type . '__' . $key,
					'label' => $label,
				);
			}

			$cct_groups[] = array(
				'label'  => __( 'Content Type:', 'jet-engine' ) . ' ' . $instance->get_arg( 'name' ),
				'values' => $prefixed_fields,
			);
		}

		$cct_groups = wp_list_pluck( $cct_groups, 'values', 'label' );

		return array_merge( $fields, $cct_groups );
	}

	public function add_preload_hooks( $field ) {

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
			$lan_lng  = jet_engine()->modules->get_module( 'maps-listings' )->instance->lat_lng;

			$address = $lan_lng->get_address_from_fields_group( $cct_item, $fields );

			if ( ! $address ) {
				return;
			}

			$coord = $lan_lng->get( $cct_item, $address );

		}, 10, 3 );

	}

	public function exclude_coord_field( $exclude ) {

		if ( $this->coord_key ) {
			$exclude[] = $this->coord_key;
		}

		return $exclude;
	}

	public function add_failure_key( $result, $obj ) {

		if ( ! isset( $obj->cct_slug ) || ! isset( $obj->_ID ) ) {
			return $result;
		}

		return sprintf( 'CCT(%1$s) #%2$s', $obj->cct_slug, $obj->_ID );
	}

	public function add_cct_marker_image_type( $types ) {
		$types['dynamic_image_cct'] = __( 'Custom Content Type Dynamic Image', 'jet-engine' );
		return $types;
	}

	public function add_cct_marker_label_type( $types ) {
		$types['cct_field'] = __( 'Custom Content Type Field', 'jet-engine' );
		return $types;
	}

	public function add_marker_cct_field_control( $widget ) {

		$groups = array();

		foreach ( Module::instance()->manager->get_content_types() as $type => $instance ) {

			$fields = $instance->get_fields_list();
			$prefixed_fields = array();

			foreach ( $fields as $key => $label ) {
				$prefixed_fields[ $type . '__' . $key ] = $label;
			}

			$groups[] = array(
				'label'   => __( 'Content Type:', 'jet-engine' ) . ' ' . $instance->get_arg( 'name' ),
				'options' => $prefixed_fields,
			);

		}

		$widget->add_control(
			'marker_cct_field',
			array(
				'label'      => __( 'Field', 'jet-engine' ),
				'type'       => \Elementor\Controls_Manager::SELECT,
				'groups'     => $groups,
				'conditions' => array(
					'relation' => 'or',
					'terms'    => array(
						array(
							'relation' => 'and',
							'terms'    => array(
								array(
									'name'  => 'marker_type',
									'value' => 'text',
								),
								array(
									'name'  => 'marker_label_type',
									'value' => 'cct_field',
								),
							),
						),
						array(
							'name'  => 'marker_type',
							'value' => 'dynamic_image_cct',
						),
					),
				),
			)
		);

	}

	public function get_marker_from_cct_field( $result, $obj, $settings ) {

		if ( ! isset( $obj->cct_slug ) ) {
			return $result;
		}

		if ( empty( $settings['marker_cct_field'] ) ) {
			return $result;
		}

		$field = $settings['marker_cct_field'];

		return jet_engine()->listings->data->get_prop( $field, $obj );
	}

	public function add_marker_cct_field_attr( $attrs = array() ) {

		$attrs['marker_cct_field'] = array(
			'type'    => 'string',
			'default' => '',
		);

		return $attrs;
	}

}
