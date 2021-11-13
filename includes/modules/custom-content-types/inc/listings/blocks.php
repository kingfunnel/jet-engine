<?php
namespace Jet_Engine\Modules\Custom_Content_Types\Listings;

use Jet_Engine\Modules\Custom_Content_Types\Module;

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

		Module::instance()->query_dialog()->assets();

		wp_enqueue_script(
			'jet-engine-cct-blocks-editor',
			Module::instance()->module_url( 'assets/js/admin/blocks/blocks.js' ),
			array(),
			jet_engine()->get_version(),
			true
		);

		wp_localize_script(
			'jet-engine-cct-blocks-editor',
			'JetEngineCCTBlocksData',
			apply_filters( 'jet-engine/custom-content-types/blocks/data', array(
				'fetchPath' => Module::instance()->query_dialog()->api_path(),
			) )
		);

	}

	public function listing_grid_atts( $attributes ) {

		$attributes['jet_cct_query'] = array(
			'type' => 'string',
			'default' => '',
		);

		return $attributes;

	}

	public function add_plain_source_fileds( $groups ) {

		foreach ( Module::instance()->manager->get_content_types() as $type => $instance ) {

			$fields = $instance->get_fields_list( 'plain' );
			$prefixed_fields = array();

			if ( empty( $fields ) ) {
				continue;
			}

			foreach ( $fields as $key => $label ) {
				$prefixed_fields[] = array(
					'value' => $type . '__' . $key,
					'label' => $label,
				);
			}

			$groups[] = array(
				'label'  => __( 'Content Type:', 'jet-engine' ) . ' ' . $instance->get_arg( 'name' ),
				'values' => $prefixed_fields,
			);
		}

		return $groups;
	}

	/**
	 * Setup blocks preview object ID
	 */
	public function setup_blocks_object() {

		$object = $this->manager->setup_preview();

		if ( $object ) {
			return $object->_ID;
		} else {
			return false;
		}
	}

	/**
	 * Returns preview object
	 *
	 * @param  [type] $object    [description]
	 * @param  [type] $object_id [description]
	 * @return [type]            [description]
	 */
	public function get_block_preview_object( $object, $object_id, $listing ) {

		$content_type = $listing['listing_post_type'];

		if ( ! $content_type ) {
			return false;
		}

		$type = Module::instance()->manager->get_content_types( $content_type );

		if ( ! $type ) {
			return false;
		}

		$flag = \OBJECT;
		$type->db->set_format_flag( $flag );

		$item = $type->db->get_item( $object_id );

		return $item;

	}

}
