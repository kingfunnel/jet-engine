<?php
namespace Jet_Engine\Modules\Rest_API_Listings\Listings;

use Jet_Engine\Modules\Rest_API_Listings\Module;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Blocks {

	public $manager;

	public function __construct( $manager ) {

		$this->manager = $manager;

		add_filter(
			'jet-engine/blocks-views/editor/config/object/' . $this->manager->source,
			array( $this, 'setup_blocks_object' ), 10, 2
		);

		add_filter(
			'jet-engine/listing/render/object/' . $this->manager->source,
			array( $this, 'get_block_preview_object' ), 10, 3
		);

		add_filter(
			'jet-engine/blocks-views/dynamic-link-sources',
			array( $this, 'add_plain_source_fileds' ), 10, 3
		);

		add_filter(
			'jet-engine/blocks-views/listing-grid/attributes',
			array( $this, 'listing_grid_atts' )
		);

		add_action(
			'jet-engine/blocks-views/editor-script/after',
			array( $this, 'editor_js' )
		);

	}

	public function editor_js() {
		wp_enqueue_script(
			'jet-engine-rest-api-blocks-editor',
			Module::instance()->module_url( 'assets/js/admin/blocks/blocks.js' ),
			array(),
			jet_engine()->get_version(),
			true
		);
	}

	public function listing_grid_atts( $attributes ) {

		$attributes['jet_rest_query'] = array(
			'type' => 'string',
			'default' => '',
		);

		return $attributes;

	}

	public function add_plain_source_fileds( $groups ) {
		return $this->manager->add_source_fields_for_js( $groups, 'blocks' );
	}

	/**
	 * Setup blocks preview object ID
	 */
	public function setup_blocks_object() {
		$object = $this->manager->setup_preview();
		return false;
	}

	/**
	 * Returns preview object
	 *
	 * @param  [type] $object    [description]
	 * @param  [type] $object_id [description]
	 * @return [type]            [description]
	 */
	public function get_block_preview_object( $object, $object_id, $listing ) {
		$object = $this->manager->setup_preview();
		return $object;
	}

}
