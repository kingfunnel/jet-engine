<?php
namespace Jet_Engine\Modules\Custom_Content_Types\Listings;

use Jet_Engine\Modules\Custom_Content_Types\Module;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Manager {

	public $source = 'custom_content_type';
	public $current_item = false;

	/**
	 * Class constructor
	 */
	public function __construct() {

		require_once Module::instance()->module_path( 'listings/query.php' );
		require_once Module::instance()->module_path( 'listings/blocks.php' );
		require_once Module::instance()->module_path( 'listings/popups.php' );
		require_once Module::instance()->module_path( 'listings/context.php' );
		require_once Module::instance()->module_path( 'listings/maps.php' );

		new Query( $this->source );
		new Blocks( $this );
		new Popups();
		new Context();
		new Maps( $this->source );

		if ( jet_engine()->has_elementor() ) {
			require_once Module::instance()->module_path( 'listings/elementor.php' );
			new Elementor( $this );
		}

		add_filter(
			'jet-engine/templates/listing-sources',
			array( $this, 'register_listing_source' )
		);

		add_filter(
			'jet-engine/templates/admin-columns/type/' . $this->source,
			array( $this, 'type_admin_column_cb' ),
			10, 2
		);

		add_action(
			'jet-engine/templates/listing-options',
			array( $this, 'register_listing_popup_options' )
		);

		add_action(
			'jet-engine/templates/create/data',
			array( $this, 'modify_inject_listing_settings' ),
			99
		);

		add_filter(
			'jet-engine/listing/data/object-fields-groups',
			array( $this, 'add_source_fields' )
		);

		add_filter(
			'jet-engine/listings/dynamic-image/custom-image',
			array( $this, 'custom_image_renderer' ),
			10, 2
		);

		add_filter(
			'jet-engine/listings/dynamic-image/custom-url',
			array( $this, 'custom_image_url' ),
			10, 2
		);

		add_filter(
			'jet-engine/listings/dynamic-link/custom-url',
			array( $this, 'custom_link_url' ),
			10, 2
		);

		add_filter(
			'jet-engine/listing/custom-post-id',
			array( $this, 'set_item_id' ),
			10, 2
		);

		add_filter(
			'jet-engine/listings/dynamic-link/delete-url-args',
			array( $this, 'set_delete_url_args' )
		);

		add_filter(
			'jet-engine/listings/delete-post/query-args',
			array( $this, 'set_final_delete_query_args' ),
			10, 2
		);

		add_action(
			'jet-engine/listings/delete-post/before',
			array( $this, 'maybe_delete_content_type_item' )
		);

		add_filter( 'jet-engine/listings/macros/current-id', function( $result, $object ) {

			if ( isset( $object->cct_slug ) ) {
				$result = $object->_ID;
			}

			return $result;

		}, 10, 2 );

		add_filter( 'jet-engine/listing/render/default-settings', function( $settings ) {
			$settings['jet_cct_query'] = '{}';
			return $settings;
		} );

	}

	public function type_admin_column_cb( $result, $listing_settings ) {

		$type = isset( $listing_settings['cct_type'] ) ? $listing_settings['cct_type'] : $listing_settings['listing_post_type'];

		if ( ! $type ) {
			return $result;
		}

		$type_instance = Module::instance()->manager->get_content_types( $type );

		if ( ! $type_instance ) {
			return $result;
		}

		return $type_instance->get_arg( 'name' );

	}

	public function maybe_delete_content_type_item( $manager ) {

		if ( empty( $_GET['cct_slug'] ) ) {
			return;
		}

		$type = Module::instance()->manager->get_content_types( esc_attr( $_GET['cct_slug'] ) );

		if ( ! $type ) {
			return;
		}

		$item_id = absint( $_GET[ $manager->query_var ] );
		$handler = $type->get_item_handler();
		$this->current_item = $type->db->get_item( $item_id );

		if ( ! $this->current_item ) {
			return;
		}

		add_filter( 'jet-engine/custom-content-types/user-has-access', array( $this, 'check_user_access_on_delete' ) );

		$handler->delete_item( $item_id, false );

		remove_filter( 'jet-engine/custom-content-types/user-has-access', array( $this, 'check_user_access_on_delete' ) );

		$redirect = ! empty( $_GET['redirect'] ) ? esc_url( $_GET['redirect'] ) : home_url( '/' );

		if ( $redirect ) {
			wp_redirect( $redirect );
			die();
		}

	}

	public function check_user_access_on_delete( $res ) {

		if ( ! is_user_logged_in() ) {
			return false;
		}

		if ( ! $res ) {
			if ( $this->current_item && absint( $this->current_item['cct_author_id'] ) === get_current_user_id() ) {
				$res = true;
			}
		}

		return $res;

	}

	public function set_delete_url_args( $args = array() ) {

		$current_object = jet_engine()->listings->data->get_current_object();

		if ( ! isset( $current_object->cct_slug ) ) {
			return $args;
		}

		$args['post_id'] = $current_object->_ID;
		$args['cct_slug'] = $current_object->cct_slug;

		return $args;

	}

	public function set_final_delete_query_args( $query_args, $request_args ) {

		if ( ! empty( $request_args['cct_slug'] ) ) {
			$query_args['cct_slug'] = $request_args['cct_slug'];
		}

		return $query_args;
	}

	public function set_item_id( $id, $object ) {

		if ( isset( $object->cct_slug ) ) {
			$id = $object->_ID;
		}

		return $id;

	}

	/**
	 * Register content types object fields
	 *
	 * @param [type] $groups [description]
	 */
	public function add_source_fields( $groups ) {

		foreach ( Module::instance()->manager->get_content_types() as $type => $instance ) {

			$fields = $instance->get_fields_list();
			$prefixed_fields = array(
				$type . '___ID' => __( 'Item ID', 'jet-engine' ),
			);

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

	/**
	 * Returns custom value from dynamic prop by setting
	 * @param  [type] $setting  [description]
	 * @param  [type] $settings [description]
	 * @return [type]           [description]
	 */
	public function get_custom_value_by_setting( $setting, $settings ) {

		$current_object = jet_engine()->listings->data->get_current_object();

		if ( ! isset( $current_object->cct_slug ) ) {
			return false;
		}

		$field  = isset( $settings[ $setting ] ) ? $settings[ $setting ] : '';
		$prefix = $current_object->cct_slug . '__';

		if ( '_permalink' === $field ) {
			$post_id = ! empty( $current_object->cct_single_post_id ) ? $current_object->cct_single_post_id : get_the_ID();

			if ( $post_id ) {
				return get_permalink( $post_id );
			} else {
				return false;
			}

		}

		if ( false === strpos( $field, $prefix ) ) {
			return false;
		}

		$prop = str_replace( $prefix, '', $field );

		$result = false;

		if ( isset( $current_object->$prop ) ) {
			$result = $current_object->$prop;
		} elseif ( isset( $current_object->$field ) ) { // for Single Post
			$result = $current_object->$field;
		}

		return $result;

	}

	/**
	 * Returns custom URL for the dynamic image
	 *
	 * @param  [type] $result   [description]
	 * @param  [type] $settings [description]
	 * @return [type]           [description]
	 */
	public function custom_image_url( $result, $settings ) {

		$url = $this->get_custom_value_by_setting( 'image_link_source', $settings );

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
	 * Returns custom URL for dynamic link widget
	 *
	 * @param  [type] $result   [description]
	 * @param  [type] $settings [description]
	 * @return [type]           [description]
	 */
	public function custom_link_url( $result, $settings ) {

		$url = $this->get_custom_value_by_setting( 'dynamic_link_source', $settings );

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
	 * Custom image renderer for custom content type
	 *
	 * @return [type] [description]
	 */
	public function custom_image_renderer( $result = false, $settings = array() ) {

		$image = $this->get_custom_value_by_setting( 'dynamic_image_source', $settings );
		$size  = isset( $settings['dynamic_image_size'] ) ? $settings['dynamic_image_size'] : 'full';

		if ( ! $image ) {
			return $result;
		}

		ob_start();

		if ( filter_var( $image, FILTER_VALIDATE_URL ) ) {
			printf( '<img src="%1$s" alt="%2$s">', $image, '' );
		} else {

			$current_object = jet_engine()->listings->data->get_current_object();

			$alt = apply_filters(
				'jet-engine/cct/image-alt/' . $current_object->cct_slug,
				false,
				$current_object
			);

			echo wp_get_attachment_image( $image, $size, false, array( 'alt' => $alt ) );
		}

		return ob_get_clean();

	}

	/**
	 * Register listing source
	 *
	 * @param  [type] $sources [description]
	 * @return [type]          [description]
	 */
	public function register_listing_source( $sources ) {
		$sources[ $this->source ] = __( 'Custom Content Type', 'jet-engine' );
		return $sources;
	}

	/**
	 * Register additional options for the listing popup
	 *
	 * @return [type] [description]
	 */
	public function register_listing_popup_options() {
		?>
		<div class="jet-listings-popup__form-row jet-template-listing jet-template-<?php echo $this->source; ?>">
			<label for="listing_content_type"><?php esc_html_e( 'From content type:', 'jet-engine' ); ?></label>
			<select id="listing_content_type" name="listing_content_type">
				<option value=""><?php _e( 'Select content type...', 'jet-engine' ); ?></option>
				<?php
				foreach ( Module::instance()->manager->get_content_types() as $type => $instance ) {
					printf( '<option value="%1$s">%2$s</option>', $type, $instance->get_arg( 'name' ) );
				}
			?></select>
		</div>
		<?php
	}

	/**
	 * Modify inject listing settings
	 *
	 * @param  array $template_data
	 * @return array
	 */
	public function modify_inject_listing_settings( $template_data ) {

		if ( ! isset( $_REQUEST['listing_source'] ) || $this->source !== $_REQUEST['listing_source'] ) {
			return $template_data;
		}

		if ( empty( $_REQUEST['listing_content_type'] ) ) {
			return $template_data;
		}

		if ( empty( $template_data['meta_input']['_listing_data'] ) ) {
			return $template_data;
		}

		$cct = esc_attr( $_REQUEST['listing_content_type'] );

		$template_data['meta_input']['_listing_data']['post_type']                    = $cct;
		$template_data['meta_input']['_elementor_page_settings']['listing_post_type'] = $cct;
		$template_data['meta_input']['_elementor_page_settings']['cct_type']          = $cct;

		return $template_data;

	}

	/**
	 * Set default blocks source
	 *
	 * @param [type] $object [description]
	 * @param [type] $editor [description]
	 */
	public function set_blocks_source( $object, $editor ) {

		$preview = $this->setup_preview( $object );

		if ( ! empty( $preview ) ) {
			return $preview['_ID'];
		} else {
			return false;
		}

	}

	/**
	 * Setup preview
	 *
	 * @return [type] [description]
	 */
	public function setup_preview( $document = false ) {

		if ( ! $document ) {
			$document = jet_engine()->listings->data->get_listing();
		}

		$source = $document->get_settings( 'listing_source' );

		if ( $this->source !== $source ) {
			return false;
		}

		$content_type = $document->get_settings( 'listing_post_type' );

		if ( ! $content_type ) {
			return false;
		}

		$type = Module::instance()->manager->get_content_types( $content_type );

		if ( ! $type ) {
			return false;
		}

		$flag = \OBJECT;
		$type->db->set_format_flag( $flag );

		$items = $type->db->query( array(), 1 );

		if ( ! empty( $items ) ) {

			$items[0]->cct_slug = $content_type;

			jet_engine()->listings->data->set_current_object( $items[0] );
			return $items[0];
		} else {
			return false;
		}

	}

}
