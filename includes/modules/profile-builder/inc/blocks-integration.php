<?php
namespace Jet_Engine\Modules\Profile_Builder;

class Blocks_Integration {

	/**
	 * Constructor for the class
	 */
	public function __construct() {
		add_filter( 'jet-engine/blocks-views/editor/config', array( $this, 'register_pages_options' ), 10 );
	}

	/**
	 * Register options for select profile builder pages control
	 */
	public function register_pages_options( $config ) {

		$pages    = array();
		$settings = Module::instance()->settings->get();

		if ( ! empty( $settings['account_page_structure'] ) ) {

			$options = array();

			foreach ( $settings['account_page_structure'] as $page ) {
				$options[] = array(
					'value' => 'account_page::' . $page['slug'],
					'label' => $page['title'],
				);
			}

			$pages[] = array(
				'label'  => __( 'Account Page', 'jet-engine' ),
				'values' => $options,
			);

		}

		if ( ! empty( $settings['enable_single_user_page'] ) && ! empty( $settings['user_page_structure'] ) ) {

			$options = array();

			foreach ( $settings['user_page_structure'] as $page ) {
				$options[] = array(
					'value' => 'single_user_page::' . $page['slug'],
					'label' => $page['title'],
				);
			}

			$pages[] = array(
				'label'  => __( 'Single User Page', 'jet-engine' ),
				'values' => $options,
			);

		}

		if ( ! empty( $pages ) ) {
			$config['profileBuilderPages'] = $pages;
		}

		return $config;

	}

}
