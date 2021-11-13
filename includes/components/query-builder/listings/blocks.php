<?php
namespace Jet_Engine\Query_Builder\Listings;

use Jet_Engine\Query_Builder\Manager as Query_Manager;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Blocks {

	public $source;
	public $source_meta;

	public function __construct() {

		$this->source      = Query_Manager::instance()->listings->source;
		$this->source_meta = Query_Manager::instance()->listings->source_meta;

		add_filter(
			'jet-engine/blocks-views/editor/config/object/' . $this->source,
			array( $this, 'setup_blocks_object' ), 10, 2
		);

		add_filter(
			'jet-engine/listing/render/object/' . $this->source,
			array( $this, 'get_block_preview_object' ), 10, 3
		);

		add_action(
			'jet-engine/blocks/editor/settings-meta-box',
			array( $this, 'add_editor_settings' )
		);

		add_action(
			'jet-engine/blocks/editor/save-settings',
			array( $this, 'save_editor_settings' )
		);

	}

	public function save_editor_settings( $post_id ) {
		if ( ! isset( $_POST[ $this->source_meta ] ) ) {
			delete_post_meta( $post_id, $this->source_meta );
		} else {
			update_post_meta( $post_id, $this->source_meta, absint( $_POST[ $this->source_meta ] ) );
		}
	}

	public function add_editor_settings( $post ) {

		$query_id = get_post_meta( $post->ID, $this->source_meta, true );
		$name     = $this->source_meta;

		echo '<div class="components-base-control__field">';
			echo '<label class="components-base-control__label" for="' . $name . '">';
				_e( 'Select query', 'jet-engine' );
			echo '</label>';
			echo '<select id="' . $name . '" name="' . $name . '" class="components-select-control__input">';
			foreach ( Query_Manager::instance()->get_queries_for_options() as $key => $value ) {
				printf( '<option value="%1$s"%3$s>%2$s</option>',
					$key,
					$value,
					selected( $query_id, $key, false )
				);
			}
			echo '</select>';
		echo '</div>';
	}

	/**
	 * Setup blocks preview object ID
	 */
	public function setup_blocks_object() {
		return get_the_ID();
	}

	/**
	 * Returns preview object
	 *
	 * @param  [type] $object    [description]
	 * @param  [type] $object_id [description]
	 * @return [type]            [description]
	 */
	public function get_block_preview_object( $object, $object_id, $listing ) {

		if ( 'query' !== $listing['listing_source'] ) {
			return false;
		}

		return Query_Manager::instance()->listings->get_preview_object_for_document( $object_id );

	}

}
