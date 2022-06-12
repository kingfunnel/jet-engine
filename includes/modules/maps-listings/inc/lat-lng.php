<?php
namespace Jet_Engine\Modules\Maps_Listings;

class Lat_Lng {

	public $geo_api_url       = 'https://maps.googleapis.com/maps/api/geocode/json';
	public $meta_key          = '_jet_maps_coord';
	public $field_groups      = array();
	public $done              = false;
	public $failures          = array();
	public $current_source    = null;

	/**
	 * Constructor for the class
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'hook_preload' ) );
	}

	/**
	 * Set current source
	 *
	 * @param $source
	 */
	public function set_current_source( $source ) {
		$this->current_source = $source;
	}

	/**
	 * Get source instance
	 *
	 * @return false|Source\Base
	 */
	public function get_source_instance() {

		if ( ! $this->current_source ) {
			return false;
		}

		return Module::instance()->sources->get_source( $this->current_source );
	}

	/**
	 * Hook meta-fields preloading
	 *
	 * @return void
	 */
	public function hook_preload() {

		$preload = Module::instance()->settings->get( 'enable_preload_meta' );

		if ( ! $preload ) {
			return;
		}

		$preload_fields = Module::instance()->settings->get( 'preload_meta' );

		if ( empty( $preload_fields ) ) {
			return;
		}

		$preload_fields = explode( ',', $preload_fields );
		$preload_fields = array_map( 'trim', $preload_fields );

		$sources = Module::instance()->sources->get_sources();

		if ( empty( $sources ) ) {
			return;
		}

		foreach ( $sources as $source ) {
			$source->preload_hooks( $preload_fields );
		}

	}

	/**
	 * Get address value from post object and field name
	 *
	 * @param object $post  Post object.
	 * @param string $field Field name.
	 *
	 * @return mixed
	 */
	public function get_address_from_field( $post, $field ) {

		$source = $this->get_source_instance();

		if ( $source ) {
			return $source->get_field_value( $post, $field );
		}

		// For backward compatibility.
		return apply_filters( 'jet-engine/maps-listing/get-address-from-field', false, $post, $field );
	}

	/**
	 * Get address string from post object and field names array
	 *
	 * @param object $post   Post object.
	 * @param array  $fields Fields array.
	 *
	 * @return bool|string
	 */
	public function get_address_from_fields_group( $post = null, $fields = array() ) {

		$group = array();

		if ( empty( $fields ) || ! is_array( $fields ) ) {
			return false;
		}

		foreach ( $fields as $field ) {
			if ( ! empty( $_POST[ $field ] ) ) {
				$group[] = $_POST[ $field ];
			} else {
				$group[] = $this->get_address_from_field( $post, $field );
			}
		}

		$group = array_filter( $group );

		if ( empty( $group ) ) {
			return false;
		} else {
			return implode( ', ', $group );
		}

	}

	/**
	 * Preload fields groups
	 */
	public function preload_groups( $post_id ) {

		if ( $this->done ) {
			return;
		}

		$group = false;
		$post  = false;

		$source = $this->get_source_instance();

		if ( $source ) {
			$group = $source->get_field_groups();
			$post  = $source->get_obj_by_id( $post_id );
		}

		if ( empty( $group ) || empty( $post ) ) {
			return;
		}

		foreach ( $group as $fields ) {

			$address = $this->get_address_from_fields_group( $post, $fields );

			if ( ! $address ) {
				continue;
			}

			$coord = $this->get( $post, $address );
		}

		$this->done = true;

	}

	/**
	 * Preload field address
	 *
	 * @param  int    $post_id
	 * @param  string $address
	 * @return void
	 */
	public function preload( $post_id, $address ) {

		if ( empty( $address ) ) {
			return;
		}

		$post   = false;
		$source = $this->get_source_instance();

		if ( $source ) {
			$post = $source->get_obj_by_id( $post_id );
		}

		$coord = $this->get( $post, $address );
	}

	/**
	 * Returns remote coordinates by location
	 *
	 * @param  [type] $location [description]
	 * @return [type]           [description]
	 */
	public function get_remote( $location ) {

		$api_key           = Module::instance()->settings->get( 'api_key' );
		$use_geocoding_key = Module::instance()->settings->get( 'use_geocoding_key' );
		$geocoding_key     = Module::instance()->settings->get( 'geocoding_key' );

		if ( $use_geocoding_key && $geocoding_key ) {
			$api_key = $geocoding_key;
		}

		// Do nothing if api key not provided
		if ( ! $api_key ) {
			return false;
		}

		// Prepare request data
		$location    = esc_attr( $location );
		$api_key     = esc_attr( $api_key );
		$request_url = add_query_arg(
			array(
				'address' => urlencode( $location ),
				'key'     => urlencode( $api_key )
			),
			esc_url( $this->geo_api_url )
		);

		$response = wp_remote_get( $request_url );
		$json     = wp_remote_retrieve_body( $response );
		$data     = json_decode( $json, true );

		$coord = isset( $data['results'][0]['geometry']['location'] )
			? $data['results'][0]['geometry']['location']
			: false;

		if ( ! $coord ) {
			return false;
		}

		return $coord;

	}

	/**
	 * Get not-post related coordinates
	 *
	 * @param  [type] $location [description]
	 * @return [type]           [description]
	 */
	public function get_from_transient( $location ) {

		$key   = md5( $location );
		$coord = get_transient( $key );

		if ( ! $coord ) {

			$coord = $this->get_remote( $location );

			if ( $coord ) {
				set_transient( $key, $coord, WEEK_IN_SECONDS );
			}

		}

		return $coord;

	}

	/**
	 * Prints failures message
	 */
	public function failures_message() {

		if ( empty( $this->failures ) ) {
			return;
		}

		if ( 5 <= count( $this->failures ) ) {
			$message = __( 'We can`t get coordinates for multiple locations', 'jet-engine' );
		} else {

			$locations = array();

			foreach ( $this->failures as $key => $location ) {
				$locations[] = sprintf( '%1$s (%2$s)', $location, $key );
			}

			$message = __( 'We can`t get coordinates for locations: ', 'jet-engine' ) . implode( ', ', $locations );

		}

		$message .= __( '. Please check your API key (you can validate it in maps settings or check in Google Console), make sure Geocoding API is enabled.', 'jet-engine' );

		return sprintf( '<div style="border: 1px solid #f00; color: #f00;  padding: 20px; margin: 10px 0;">%s</div>', $message );

	}

	public function maybe_add_offset( $coordinates = array() ) {
		
		$add_offset = Module::instance()->settings->get( 'add_offset' );

		if ( ! $add_offset ) {
			return $coordinates;
		}

		$offset_rate = apply_filters( 'jet-engine/maps-listing/offset-rate', 100000 );

		$offset_lat = ( 10 - rand( 0, 20 ) ) / $offset_rate;
		$offset_lng = ( 10 - rand( 0, 20 ) ) / $offset_rate;

		if ( isset( $coordinates['lat'] ) ) {
			$coordinates['lat'] = $coordinates['lat'] + $offset_lat;
		}

		if ( isset( $coordinates['lng'] ) ) {
			$coordinates['lng'] = $coordinates['lng'] + $offset_lng;
		}

		return $coordinates;

	}

	/**
	 * Returns lat and lang for passed address
	 *
	 * @param  int|object $post     Post ID or object
	 * @param  string     $location Location
	 *
	 * @return array|bool
	 */
	public function get( $post, $location ) {

		if ( is_array( $location ) ) {
			return $this->maybe_add_offset( $location );
		}

		$key   = md5( $location );
		$meta  = $this->get_address_from_field( $post, $this->meta_key );

		if ( ! empty( $meta ) && $key === $meta['key'] ) {
			return $this->maybe_add_offset( $meta['coord'] );
		}

		$coord = $this->get_remote( $location );

		if ( ! $coord ) {
			if ( $location ) {
				$this->add_failure( $post, $location );
			}
			return false;
		}

		$this->update_address_coord_field( $post, $key, $coord );

		return $this->maybe_add_offset( $coord );

	}

	public function add_failure( $post, $location ) {

		$key    = false;
		$source = $this->get_source_instance();

		if ( $source ) {
			$key = $source->get_failure_key( $post );
		}

		// For backward compatibility.
		if ( ! $key ) {
			$key = apply_filters( 'jet-engine/maps-listing/failure-message-key', $key, $post );
		}

		if ( ! $key ) {
			return;
		}

		$this->failures[ $key ] = $location;
	}

	public function update_address_coord_field( $post, $key, $coord ) {

		$value = array(
			'key'   => $key,
			'coord' => $coord,
		);

		$source = $this->get_source_instance();

		if ( $source ) {
			$source->update_field_value( $post, $this->meta_key, $value );
			return;
		}

		// For backward compatibility.
		do_action( 'jet-engine/maps-listings/update-address-coord-field', $post, $value, $this );
	}

}
