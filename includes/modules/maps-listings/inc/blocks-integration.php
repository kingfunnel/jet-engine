<?php
namespace Jet_Engine\Modules\Maps_Listings;

class Blocks_Integration {

	/**
	 * Constructor for the class
	 */
	public function __construct() {
		add_action( 'jet-engine/blocks-views/register-block-types', array( $this, 'register_block_types' ) );
		add_filter( 'jet-engine/blocks-views/editor/config',        array( $this, 'add_editor_config' ) );
	}

	/**
	 * Register block types
	 *
	 * @param  object $blocks_types
	 * @return void
	 */
	public function register_block_types( $blocks_types ) {
		require jet_engine()->modules->modules_path( 'maps-listings/inc/blocks-types/maps-listings.php' );

		$maps_listing_type = new Maps_Listing_Blocks_Views_Type();

		$blocks_types->register_block_type( $maps_listing_type );
	}

	/**
	 * Add editor config.
	 *
	 * @param  array $config
	 * @return array
	 */
	public function add_editor_config( $config = array() ) {

		$marker_types = Module::instance()->get_marker_types();
		$marker_types['icon'] = __( 'Image/Icon', 'jet-engine' );
		unset( $marker_types['image'] );

		$marker_types_for_js = array();

		foreach ( $marker_types as $value => $label ) {
			$marker_types_for_js[] = array(
				'value' => $value,
				'label' => $label,
			);
		}

		$marker_label_types = Module::instance()->get_marker_label_types();
		$marker_label_types_for_js = array();

		foreach ( $marker_label_types as $value => $label ) {
			$marker_label_types_for_js[] = array(
				'value' => $value,
				'label' => $label,
			);
		}

		$config['atts']['mapsListing'] = jet_engine()->blocks_views->block_types->get_block_atts( 'maps-listing' );

		$config['mapsListingConfig'] = array(
			'markerTypes'      => $marker_types_for_js,
			'markerLabelTypes' => $marker_label_types_for_js,
		);

		return $config;
	}

}
