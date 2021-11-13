<?php
namespace Jet_Engine\Modules\Custom_Content_Types\Listings;

use Jet_Engine\Modules\Custom_Content_Types\Module;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Elementor {

	public $manager;

	public function __construct( $manager ) {

		$this->manager = $manager;

		add_filter(
			'jet-engine/listings/dynamic-image/fields',
			array( $this, 'add_image_source_fields' ), 10, 2
		);

		add_filter(
			'jet-engine/listings/dynamic-link/fields',
			array( $this->manager, 'add_source_fields' ),
			10, 2
		);

		add_action(
			'jet-engine/listings/document/get-preview/' . $this->manager->source,
			array( $this->manager, 'setup_preview' )
		);

		add_action(
			'jet-engine/listings/document/custom-source-control',
			array( $this, 'add_document_controls' )
		);

		add_action(
			'elementor/document/after_save',
			array( $this, 'update_settings_on_document_save' ),
			10, 2
		);

		add_filter(
			'jet-engine/elementor-views/frontend/custom-listing-url',
			array( $this, 'custom_listing_url' ),
			10, 2
		);

	}

	public function update_settings_on_document_save( $document, $data ) {

		if ( empty( $data['settings'] ) || empty( $data['settings']['listing_source'] ) ) {
			return;
		}

		if ( $this->manager->source !== $data['settings']['listing_source'] ) {
			return;
		}

		if ( $data['settings']['cct_type'] === $data['settings']['listing_post_type'] ) {
			return;
		}

		$prev_data = get_post_meta( $document->get_main_id(), '_elementor_page_settings', true );

		if ( ! empty( $data['settings']['cct_type'] ) ) {
			$prev_data['listing_post_type'] = $data['settings']['cct_type'];
		} else {
			$prev_data['cct_type'] = $data['settings']['listing_post_type'];
		}

		update_post_meta( $document->get_main_id(), '_elementor_page_settings', wp_slash( $prev_data ) );

	}

	/**
	 * Add document-specific controls
	 */
	public function add_document_controls( $document ) {

		$content_types = array( '' => __( 'Select content type...', 'jet-engine' ) );

		foreach ( Module::instance()->manager->get_content_types() as $type => $instance ) {
			$content_types[ $type ] = $instance->get_arg( 'name' );
		}

		$document->add_control(
			'cct_type',
			array(
				'label'       => esc_html__( 'Content type:', 'jet-engine' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'default'     => '',
				'options'     => $content_types,
				'label_block' => true,
				'condition'   => array(
					'listing_source' => $this->manager->source,
				),
			)
		);

	}

	/**
	 * Register content types media fields
	 *
	 * @param [type] $groups [description]
	 */
	public function add_image_source_fields( $groups, $for ) {

		foreach ( Module::instance()->manager->get_content_types() as $type => $instance ) {

			$fields = $instance->get_fields_list( $for );
			$prefixed_fields = array();

			if ( empty( $fields ) ) {
				continue;
			}

			foreach ( $fields as $key => $label ) {
				$prefixed_fields[ $type . '__' . $key ] = $label;
			}

			$groups[] = array(
				'label'   => __( 'Content Type:', 'jet-engine' ) . ' ' . $instance->get_arg( 'name' ),
				'options' => $prefixed_fields,
			);
		}

		return $groups;

	}

	public function custom_listing_url( $result, $settings ) {

		$url = $this->manager->get_custom_value_by_setting( 'listing_link_source', $settings );

		if ( is_numeric( $url ) ) {
			$url = get_permalink( $url );
		}

		if ( ! $url ) {
			return $result;
		} else {
			return $url;
		}
	}

}
