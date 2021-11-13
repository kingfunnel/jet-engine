<?php
/**
 * Elementor views manager
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Engine_Blocks_Views_Type_Base' ) ) {

	/**
	 * Define Jet_Engine_Blocks_Views_Type_Base class
	 */
	abstract class Jet_Engine_Blocks_Views_Type_Base {

		protected $namespace = 'jet-engine/';

		public $block_manager    = null;
		public $controls_manager = null;

		public function __construct() {

			$attributes = $this->get_attributes();

			if ( class_exists( 'JET_SM\Gutenberg\Block_Manager' ) && class_exists( 'JET_SM\Gutenberg\Block_Manager' ) ) {
				$this->set_style_manager_instance();
				$this->add_style_manager_options();
				do_action( 'jet-engine/blocks-views/' . $this->get_name() . '/add-extra-style-options', $this );

				//add_action( 'enqueue_block_editor_assets', array( $this, 'add_style_manager_options' ), -1 );
			}

			/**
			 * Set default blocks attributes to avoid errors
			 */
			$attributes['className'] = array(
				'type' => 'string',
				'default' => '',
			);

			register_block_type(
				$this->namespace . $this->get_name(),
				array(
					'attributes'      => $attributes,
					'render_callback' => array( $this, 'render_callback' ),
					'editor_style'    => 'jet-engine-frontend',
				)
			);
		}

		abstract public function get_name();

		/**
		 * Return attributes array
		 *
		 * @return array
		 */
		abstract public function get_attributes();

		/**
		 * Retruns attra from input array if not isset, get from defaults
		 *
		 * @return [type] [description]
		 */
		public function get_attr( $attr = '', $all = array() ) {
			if ( isset( $all[ $attr ] ) ) {
				return $all[ $attr ];
			} else {
				$defaults = $this->get_attributes();
				return isset( $defaults[ $attr ]['default'] ) ? $defaults[ $attr ]['default'] : '';
			}
		}

		/**
		 * Check if is blocks edit mode
		 *
		 * @return boolean [description]
		 */
		public function is_edit_mode() {
			return ( isset( $_GET['context'] ) && 'edit' === $_GET['context'] && isset( $_GET['attributes'] ) && $_GET['_locale'] );
		}

		/**
		 * Allow to filter raw attributes from block type instance to adjust JS and PHP attributes format
		 *
		 * @param  [type] $attributes [description]
		 * @return [type]             [description]
		 */
		public function prepare_attributes( $attributes ) {
			return $attributes;
		}

		/**
		 * Set style manager class instance
		 *
		 * @return boolean
		 */
		public function set_style_manager_instance(){

			$name = $this->namespace . $this->get_name();

			$this->block_manager    = \JET_SM\Gutenberg\Block_Manager::get_instance();
			$this->controls_manager = new \JET_SM\Gutenberg\Controls_Manager( $name );
		}

		public function css_selector( $el = '' ) {
			return sprintf( '{{WRAPPER}}.jet-listing-%1$s%2$s', $this->get_name(), $el );
		}

		/**
		 * Add style block options
		 *
		 * @return boolean
		 */
		public function add_style_manager_options() {}

		/**
		 * Set css classes
		 *
		 * @return boolean
		 */
		public function set_css_scheme() {
			$this->css_scheme = [];
		}

		public function get_render_instance( $attributes ) {
			return jet_engine()->listings->get_render_instance( $this->get_name(), $attributes );
		}

		public function render_callback( $attributes = array() ) {

			$item       = $this->get_name();
			$listing    = isset( $_REQUEST['listing'] ) ? $_REQUEST['listing'] : false;
			$listing_id = isset( $_REQUEST['post_id'] ) ? absint( $_REQUEST['post_id'] ) : false;
			$object_id  = isset( $_REQUEST['object'] ) ? absint( $_REQUEST['object'] ) : jet_engine()->listings->data->get_current_object();
			$attributes = $this->prepare_attributes( $attributes );
			$render     = $this->get_render_instance( $attributes );

			if ( ! $render ) {
				return __( 'Item renderer class not found', 'jet-engine' );
			}

			if ( ! $listing_id ) {
				$listing_id = jet_engine()->blocks_views->render->get_current_listing_id();
			}

			$render->setup_listing( $listing, $object_id, true, $listing_id );

			return $render->get_content();

		}

	}

}
