<?php
namespace Jet_Engine\Modules\Maps_Listings\Source;

use Jet_Engine\Modules\Maps_Listings\Module;

abstract class Base {

	public $lat_lng      = null;
	public $field_groups = array();

	public function __construct() {
		$this->lat_lng = Module::instance()->lat_lng;
	}

	/**
	 * Returns source ID
	 *
	 * @return string
	 */
	abstract public function get_id();

	abstract public function get_obj_by_id( $id );

	abstract public function get_field_value( $obj, $field );

	abstract public function update_field_value( $obj, $field, $value );

	abstract public function get_failure_key( $obj );

	public function preload_hooks( $preload_fields ) {
		$fields = array_filter( $preload_fields, array( $this, 'filtered_preload_fields' ) );

		if ( empty( $fields ) ) {
			return;
		}

		$this->add_preload_hooks( $fields );
	}

	public function filtered_preload_fields( $field ) {
		return true;
	}

	public function add_preload_hooks( $preload_fields ) {}

	/**
	 * Preload field address
	 *
	 * @param  int    $obj_id
	 * @param  string $address
	 * @return void
	 */
	public function preload( $obj_id, $address ) {
		$this->lat_lng->set_current_source( $this->get_id() );
		$this->lat_lng->preload( $obj_id, $address );
	}

	/**
	 * Preload fields groups
	 *
	 * @param  int  $obj_id
	 * @return void
	 */
	public function preload_groups( $obj_id ) {
		$this->lat_lng->set_current_source( $this->get_id() );
		$this->lat_lng->preload_groups( $obj_id );
	}

	public function get_field_groups() {
		return $this->field_groups;
	}

}
