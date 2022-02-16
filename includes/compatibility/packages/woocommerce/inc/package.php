<?php

namespace Jet_Engine\Compatibility\Packages\Jet_Engine_Woo_Package;

class Package {

	/**
	 * A reference to an instance of this class.
	 *
	 * @access private
	 * @var    object
	 */
	private static $instance = null;

	/**
	 * Constructor for the class
	 */
	public function __construct() {
		$this->init_package_components();
	}

	/**
	 * Init package components
	 *
	 * @return void
	 */
	public function init_package_components() {

		require_once $this->package_path( 'listings/manager.php' );
		new Listings\Manager();

		require_once $this->package_path( 'query-builder/manager.php' );
		Query_Builder\Manager::instance();

	}

	/**
	 * Return path inside package.
	 *
	 * @param string $relative_path
	 *
	 * @return string
	 */
	public function package_path( $relative_path = '' ) {
		return jet_engine()->plugin_path( 'includes/compatibility/packages/woocommerce/inc/' . $relative_path );
	}

	/**
	 * Return url inside package.
	 *
	 * @param string $relative_path
	 *
	 * @return string
	 */
	public function package_url( $relative_path = '' ) {
		return jet_engine()->plugin_url( 'includes/compatibility/packages/woocommerce/inc/' . $relative_path );
	}

	/**
	 * Returns the instance.
	 *
	 * @access public
	 * @return object
	 */
	public static function instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;

	}

}
