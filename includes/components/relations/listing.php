<?php
namespace Jet_Engine\Relations;

/**
 * Relations manager
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Define Listing class
 */
class Listing {

	private $processed_relation = false;

	private $processed_listing = false;

	private $listing_source = 'relation_meta_data';

	public function __construct() {

		// setup and reset currently processed relation
		add_action( 'jet-engine/relations/macros/get-related', array( $this, 'set_relation' ) );
		add_action( 'jet-engine/listings/setup', array( $this, 'set_listing' ) );
		add_action( 'jet-engine/listings/reset', array( $this, 'reset_listing' ) );

		// add relation meta fields into available sources for dynamic widgets
		add_filter( 'jet-engine/listings/data/sources', array( $this, 'add_meta_source' ) );

		// Elementor integration
		add_action( 'jet-engine/listings/dynamic-field/source-controls', array( $this, 'elementor_dynamic_field_controls' ) );

		// Blocks integration
		add_filter( 'jet-engine/blocks-views/editor-data', array( $this, 'blocks_register_relations_meta' ) );

		// Elementor + Blocks
		add_filter( 'jet-engine/listings/dynamic-image/fields', array( $this, 'dynamic_image_controls' ) );
		add_filter( 'jet-engine/listings/dynamic-link/fields', array( $this, 'dynamic_link_controls' ), 10, 2 );

		// Process meta value
		add_filter( 'jet-engine/listings/dynamic-field/field-value', array( $this, 'return_meta_value' ), 10, 2 );
		add_filter( 'jet-engine/listings/dynamic-image/custom-image', array( $this, 'custom_image_renderer' ), 10, 2 );
		add_filter( 'jet-engine/listings/dynamic-image/custom-url', array( $this, 'custom_image_url' ), 10, 2 );
		add_filter( 'jet-engine/listings/dynamic-link/custom-url', array( $this, 'custom_link_url' ), 10, 2 );
	}

	/**
	 * Set currently processed relation object
	 *
	 * @param [type] $relation [description]
	 */
	public function set_relation( $relation ) {
		$this->processed_relation = $relation;
	}

	/**
	 * Setup current listing for relation
	 */
	public function set_listing( $listing_id ) {
		if ( ! $this->processed_listing && $this->processed_relation ) {
			$this->processed_listing = $listing_id;
		}
	}

	/**
	 * Reset listing and relation when its processed
	 */
	public function reset_listing( $listing_id ) {

		if ( $this->processed_listing && $this->processed_relation && $this->processed_listing === $listing_id ) {
			$this->processed_listing  = false;
			$this->processed_relation = false;
		}
	}

	/**
	 * Register meta source for realtions meta data
	 *
	 * @param [type] $sources [description]
	 */
	public function add_meta_source( $sources ) {

		$meta_fields = jet_engine()->relations->get_active_relations_meta_fields();

		if ( ! empty( $meta_fields ) ) {
			$sources[ $this->listing_source ] = __( 'Relation Meta Data', 'jet-engine' );
		}

		return $sources;
	}

	/**
	 * Process meta value
	 *
	 * @return [type] [description]
	 */
	public function return_meta_value( $result, $settings ) {

		$source = ! empty( $settings['dynamic_field_source'] ) ? $settings['dynamic_field_source'] : false;

		if ( $this->listing_source !== $source ) {
			return $result;
		}

		$data = ! empty( $settings['dynamic_field_relation_meta'] ) ? $settings['dynamic_field_relation_meta'] : false;

		if ( ! $data ) {
			return $result;
		}

		$data     = explode( '::', $data );
		$rel_id   = $data[0];
		$field    = $data[1];
		$relation = jet_engine()->relations->get_active_relations( $rel_id );

		if ( ! $relation ) {
			return $result;
		}

		$object_context  = isset( $settings['object_context'] ) ? $settings['object_context'] : false;
		$current_context = 'rel_' . $rel_id;
		$default_object  = false;
		$current_object  = false;

		if ( $object_context === $current_context ) {

			$default_object = jet_engine()->listings->data->get_current_object();
			$current_object = $relation->apply_context();

			if ( is_array( $current_object ) ) {
				$current_object = (object) $current_object;
			}

			if ( $current_object && is_object( $current_object ) ) {
				jet_engine()->listings->data->set_current_object( $current_object );
			}

		}

		$meta = $relation->get_current_meta( $field );

		if ( $object_context === $current_context && $default_object && $current_object ) {
			jet_engine()->listings->data->set_current_object( $default_object );
		}

		return $meta;

	}

	/**
	 * Returns relation meta value for selected settings from all settings list
	 *
	 * @param  [type] $setting  [description]
	 * @param  [type] $settings [description]
	 * @return [type]           [description]
	 */
	public function get_meta_from( $setting, $settings ) {

		$source = ! empty( $settings[ $setting ] ) ? $settings[ $setting ] : false;

		if ( ! $source || false === strpos( $source, $this->listing_source . '::' ) ) {
			return false;
		}

		$data     = explode( '::', $source );
		$rel_id   = $data[1];
		$field    = $data[2];
		$relation = jet_engine()->relations->get_active_relations( $rel_id );

		if ( ! $relation ) {
			return false;
		}

		return $relation->get_current_meta( $field );

	}

	/**
	 * Renders custom image for given relation meta
	 *
	 * @return [type] [description]
	 */
	public function custom_image_renderer( $result = false, $settings = array() ) {

		$image = $this->get_meta_from( 'dynamic_image_source', $settings );
		$size  = isset( $settings['dynamic_image_size'] ) ? $settings['dynamic_image_size'] : 'full';

		if ( is_array( $image ) && isset( $image['url'] ) ) {

			if ( $size && 'full' !== $size ) {
				$image = $image['id'];
			} else {
				$image = $image['url'];
			}

		} elseif ( is_array( $image ) ) {
			$image = array_values( $image );
			$image = $image[0];
		}

		if ( ! $image ) {
			return $result;
		}

		ob_start();

		if ( filter_var( $image, FILTER_VALIDATE_URL ) ) {
			printf( '<img src="%1$s" alt="%2$s">', $image, '' );
		} else {
			$current_object = jet_engine()->listings->data->get_current_object();
			$alt            = apply_filters( 'jet-engine/relations/meta/image-alt/', false );
			echo wp_get_attachment_image( $image, $size, false, array( 'alt' => $alt ) );
		}

		return ob_get_clean();

	}

	/**
	 * Returns custom link URL for Dynamic Field widget/block
	 *
	 * @param  [type] $result   [description]
	 * @param  [type] $settings [description]
	 * @return [type]           [description]
	 */
	public function custom_link_url( $result, $settings ) {

		$url = $this->get_meta_from( 'dynamic_link_source', $settings );

		if ( is_numeric( $url ) ) {
			$url = get_permalink( $url );
		}

		if ( ! $url ) {
			return $result;
		} else {
			return $url;
		}

	}

	/**
	 * Returns custom link URL for image link for Dynamic Image widget/block
	 *
	 * @param  [type] $result   [description]
	 * @param  [type] $settings [description]
	 * @return [type]           [description]
	 */
	public function custom_image_url( $result, $settings ) {

		$url = $this->get_meta_from( 'image_link_source', $settings );

		if ( is_numeric( $url ) ) {
			$url = get_permalink( $url );
		}

		if ( ! $url ) {
			return $result;
		} else {
			return $url;
		}

	}

	/**
	 * Returns meta fields list for the requested context
	 *
	 * @param  [type] $context [description]
	 * @return [type]          [description]
	 */
	public function get_meta_fields_for_options( $context = 'elementor', $prefix = false, $type = array() ) {

		$raw_fields  = jet_engine()->relations->get_active_relations_meta_fields();
		$meta_fields = array();

		if ( empty( $raw_fields ) ) {
			return $meta_fields;
		}

		foreach ( $raw_fields as $rel_id => $rel_data ) {

			$group = array();

			foreach ( $rel_data['fields'] as $field ) {

				if ( ! empty( $type ) && ! in_array( $field['type'], $type ) ) {
					continue;
				}

				$key = $rel_id . '::' . $field['name'];

				if ( $prefix ) {
					$key = $this->listing_source . '::' . $key;
				}

				if ( 'blocks' === $context ) {
					$group[] = array(
						'value' => $key,
						'label' => $field['title'],
					);
				} else {
					$group[ $key ] = $field['title'];
				}

			}

			if ( ! empty( $group ) ) {

				$label = $rel_data['label'];

				if ( $prefix ) {
					$label = __( 'Relation Meta Data', 'jet-engine' ) . ': ' . $label;
				}

				if ( 'blocks' === $context ) {
					$meta_fields[] = array(
						'label'  => $label,
						'values' => $group,
					);
				} else {
					$meta_fields[] = array(
						'label'   => $label,
						'options' => $group,
					);
				}

			}
		}

		return $meta_fields;

	}

	/**
	 * Register realtiosn meta fields for the block editor configuration
	 *
	 * @param  [type] $config [description]
	 * @return [type]         [description]
	 */
	public function blocks_register_relations_meta( $config ) {

		$config['relationsMeta'] = $this->get_meta_fields_for_options( 'blocks' );

		return $config;
	}

	/**
	 * Register realtion meta source control for the Elementor dynamic field widget
	 *
	 * @param  [type] $widget [description]
	 * @return [type]         [description]
	 */
	public function elementor_dynamic_field_controls( $widget ) {

		$meta_fields = $this->get_meta_fields_for_options( 'elementor' );

		if ( empty( $meta_fields ) ) {
			return;
		}

		$widget->add_control(
			'dynamic_field_relation_meta',
			array(
				'label'     => __( 'Meta Field', 'jet-engine' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => '',
				'groups'    => $meta_fields,
				'condition' => array(
					'dynamic_field_source' => $this->listing_source,
				),
			)
		);

	}

	/**
	 * Returns list of allowed media meta fields
	 *
	 * @param  [type] $result [description]
	 * @return [type]         [description]
	 */
	public function dynamic_image_controls( $result ) {

		$image_fields = $this->get_meta_fields_for_options( 'elementor', true, array( 'media' ) );

		if ( ! empty( $image_fields ) ) {
			$result = array_merge( $result, $image_fields );
		}

		return $result;

	}

	/**
	 * Returns list of allowed fields to use as links
	 *
	 * @param  [type] $result [description]
	 * @return [type]         [description]
	 */
	public function dynamic_link_controls( $result ) {

		$fields = $this->get_meta_fields_for_options( 'elementor', true );

		if ( ! empty( $fields ) ) {
			$result = array_merge( $result, $fields );
		}

		return $result;

	}

}
